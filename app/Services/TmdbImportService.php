<?php

namespace App\Services;

use App\Models\Content;
use App\Models\Genre;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TmdbImportService
{
    private string $apiKey;
    private string $bearerToken;
    private string $language;
    private string $imageBaseUrl;
    private int $throttleMs;

    public function __construct()
    {
        $this->apiKey = (string) config('services.tmdb.api_key');
        $this->bearerToken = (string) config('services.tmdb.bearer_token', '');
        $this->language = (string) config('services.tmdb.language', 'en-US');
        $this->imageBaseUrl = rtrim((string) config('services.tmdb.image_base_url', 'https://image.tmdb.org/t/p/w500'), '/');
        $this->throttleMs = (int) config('services.tmdb.throttle_ms', 250);
    }

    public function canRun(): bool
    {
        return $this->apiKey !== '' || $this->bearerToken !== '';
    }

    /**
     * @return array{processed:int,created:int,updated:int,skipped:int,errors:int}
     */
    public function importPopularMovies(
        int $pages = 1,
        int $limit = 20,
        bool $downloadPosters = true,
        bool $refreshExisting = false
    ): array {
        $stats = ['processed' => 0, 'created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0];

        if (!$this->canRun()) {
            return $stats;
        }

        $genreMap = $this->resolveGenreMap();
        if ($genreMap->isEmpty()) {
            return $stats;
        }

        $remaining = max(1, $limit);

        for ($page = 1; $page <= max(1, $pages); $page++) {
            if ($remaining <= 0) {
                break;
            }

            $discover = $this->request('discover/movie', [
                'sort_by' => 'popularity.desc',
                'include_adult' => false,
                'page' => $page,
            ]);

            if (!$discover) {
                $stats['errors']++;
                continue;
            }

            $movies = collect($discover['results'] ?? [])
                ->filter(fn (array $movie) => !empty($movie['title']) && !empty($movie['release_date']))
                ->take($remaining);

            foreach ($movies as $movie) {
                $stats['processed']++;
                $remaining--;

                $existing = Content::query()
                    ->where('title', (string) $movie['title'])
                    ->where('release_date', (string) $movie['release_date'])
                    ->where('type', 'film')
                    ->first();

                $needsDetails = $refreshExisting
                    || !$existing
                    || empty($existing->director)
                    || (int) ($existing->duration ?? 0) <= 1;

                $details = null;
                if ($needsDetails) {
                    $details = $this->movieDetails((int) $movie['id']);
                    if (!$details) {
                        $stats['errors']++;
                        continue;
                    }
                }

                $genreId = $this->resolveGenreId($genreMap, $movie, $details);
                if (!$genreId) {
                    $stats['skipped']++;
                    continue;
                }

                $payload = [
                    'genre_id' => $genreId,
                    'duration' => max(1, (int) ($details['runtime'] ?? ($existing->duration ?? 100))),
                    'rating' => (int) max(0, min(100, round(((float) ($movie['vote_average'] ?? 0)) * 10))),
                    'description' => (string) ($movie['overview'] ?? ($details['overview'] ?? 'No description available')),
                    'director' => (string) ($this->resolveDirector($details) ?? ($existing->director ?? 'Unknown')),
                    'picture' => $this->resolvePosterFilename((int) $movie['id'], $movie['poster_path'] ?? null, $downloadPosters, $refreshExisting, $existing?->picture),
                    'video' => $existing->video ?? null,
                ];

                $content = Content::updateOrCreate(
                    [
                        'title' => (string) $movie['title'],
                        'release_date' => (string) $movie['release_date'],
                        'type' => 'film',
                    ],
                    $payload
                );

                if ($existing && $content->id === $existing->id) {
                    $stats['updated']++;
                } else {
                    $stats['created']++;
                }

                if ($remaining <= 0) {
                    break;
                }
            }
        }

        return $stats;
    }

    private function resolveGenreMap(): Collection
    {
        return Cache::remember("tmdb:genres:{$this->language}", now()->addHours(24), function () {
            $response = $this->request('genre/movie/list');
            if (!$response) {
                return collect();
            }

            $localGenresByName = Genre::query()
                ->get()
                ->keyBy(fn (Genre $genre) => mb_strtolower($genre->name));

            return collect($response['genres'] ?? [])
                ->filter(fn (array $genre) => isset($genre['id'], $genre['name']))
                ->mapWithKeys(function (array $genre) use (&$localGenresByName) {
                    $name = (string) $genre['name'];
                    $key = mb_strtolower($name);

                    if (!$localGenresByName->has($key)) {
                        $localGenresByName->put($key, Genre::create(['name' => $name]));
                    }

                    return [(int) $genre['id'] => $localGenresByName->get($key)->id];
                });
        });
    }

    private function movieDetails(int $movieId): ?array
    {
        return Cache::remember("tmdb:movie:{$movieId}:{$this->language}", now()->addHours(12), function () use ($movieId) {
            return $this->request("movie/{$movieId}", ['append_to_response' => 'credits']);
        });
    }

    private function request(string $endpoint, array $query = []): ?array
    {
        $this->pace();

        $query = array_merge([
            'language' => $this->language,
        ], $query);

        if ($this->apiKey !== '') {
            $query['api_key'] = $this->apiKey;
        }

        $client = Http::baseUrl('https://api.themoviedb.org/3')
            ->acceptJson()
            ->timeout(20)
            ->retry(3, 600);

        if ($this->bearerToken !== '') {
            $client = $client->withToken($this->bearerToken);
        }

        $response = $client->get($endpoint, $query);
        if (!$response->ok()) {
            return null;
        }

        $json = $response->json();
        return is_array($json) ? $json : null;
    }

    private function pace(): void
    {
        if ($this->throttleMs > 0) {
            usleep($this->throttleMs * 1000);
        }
    }

    private function resolveGenreId(Collection $genreMap, array $movie, ?array $details): ?int
    {
        $fromDetails = collect($details['genres'] ?? [])
            ->map(fn (array $genre) => $genreMap->get((int) $genre['id']))
            ->filter()
            ->first();

        if ($fromDetails) {
            return (int) $fromDetails;
        }

        $fromDiscover = collect($movie['genre_ids'] ?? [])
            ->map(fn ($genreId) => $genreMap->get((int) $genreId))
            ->filter()
            ->first();

        if ($fromDiscover) {
            return (int) $fromDiscover;
        }

        return Genre::query()->value('id');
    }

    private function resolveDirector(?array $details): ?string
    {
        if (!$details) {
            return null;
        }

        $director = collect($details['credits']['crew'] ?? [])
            ->first(fn (array $crew) => ($crew['job'] ?? null) === 'Director');

        return is_array($director) ? ($director['name'] ?? null) : null;
    }

    private function resolvePosterFilename(
        int $movieId,
        ?string $posterPath,
        bool $downloadPosters,
        bool $refreshExisting,
        ?string $fallback
    ): ?string {
        if (!$downloadPosters || !is_string($posterPath) || $posterPath === '') {
            return $fallback;
        }

        $posterFilename = "tmdb_{$movieId}.jpg";
        $storagePath = "movies/{$posterFilename}";

        if (!$refreshExisting && Storage::disk('public')->exists($storagePath)) {
            return $posterFilename;
        }

        $this->pace();
        $response = Http::timeout(20)->retry(3, 600)->get($this->imageBaseUrl.$posterPath);
        if (!$response->ok()) {
            return $fallback;
        }

        Storage::disk('public')->put($storagePath, $response->body());

        return $posterFilename;
    }
}
