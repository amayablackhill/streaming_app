<?php

namespace Database\Seeders;

use App\Models\Content;
use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TmdbContentSeeder extends Seeder
{
    public function run(): void
    {
        $apiKey = (string) config('services.tmdb.api_key');
        $language = (string) config('services.tmdb.language', 'en-US');
        $imageBaseUrl = rtrim((string) config('services.tmdb.image_base_url', 'https://image.tmdb.org/t/p/w500'), '/');
        $limit = (int) env('TMDB_SEED_LIMIT', 12);

        if ($apiKey === '') {
            $this->command->warn('TMDB_API_KEY is not set. Skipping TmdbContentSeeder.');
            return;
        }

        $genreResponse = Http::timeout(15)->get('https://api.themoviedb.org/3/genre/movie/list', [
            'api_key' => $apiKey,
            'language' => $language,
        ]);

        if (!$genreResponse->ok()) {
            $this->command->error('Unable to fetch TMDB genres.');
            return;
        }

        $localGenresByName = Genre::query()
            ->get()
            ->keyBy(fn (Genre $genre) => mb_strtolower($genre->name));

        $tmdbGenres = collect($genreResponse->json('genres', []))
            ->filter(fn (array $genre) => isset($genre['id'], $genre['name']))
            ->mapWithKeys(function (array $genre) use (&$localGenresByName) {
                $name = (string) $genre['name'];
                $key = mb_strtolower($name);

                if (!$localGenresByName->has($key)) {
                    $localGenresByName->put($key, Genre::create(['name' => $name]));
                }

                return [(int) $genre['id'] => $localGenresByName->get($key)->id];
            });

        $discoverResponse = Http::timeout(20)->get('https://api.themoviedb.org/3/discover/movie', [
            'api_key' => $apiKey,
            'language' => $language,
            'sort_by' => 'popularity.desc',
            'include_adult' => false,
            'page' => 1,
        ]);

        if (!$discoverResponse->ok()) {
            $this->command->error('Unable to fetch TMDB discover list.');
            return;
        }

        $movies = collect($discoverResponse->json('results', []))
            ->filter(fn (array $movie) => !empty($movie['title']) && !empty($movie['release_date']))
            ->take($limit);

        $createdOrUpdated = 0;

        foreach ($movies as $movie) {
            $movieId = (int) $movie['id'];

            $detailsResponse = Http::timeout(20)->get("https://api.themoviedb.org/3/movie/{$movieId}", [
                'api_key' => $apiKey,
                'language' => $language,
                'append_to_response' => 'credits',
            ]);

            if (!$detailsResponse->ok()) {
                continue;
            }

            $details = $detailsResponse->json();
            $genreId = collect($details['genres'] ?? [])
                ->map(fn (array $genre) => $tmdbGenres->get((int) $genre['id']))
                ->first();

            if (!$genreId) {
                $genreId = Genre::query()->value('id');
            }

            if (!$genreId) {
                continue;
            }

            $director = collect($details['credits']['crew'] ?? [])
                ->first(fn (array $crew) => ($crew['job'] ?? null) === 'Director');

            $posterFilename = null;
            $posterPath = $details['poster_path'] ?? null;
            if (is_string($posterPath) && $posterPath !== '') {
                $posterFilename = "tmdb_{$movieId}.jpg";
                $posterRemoteUrl = $imageBaseUrl . $posterPath;
                $posterBinary = Http::timeout(20)->get($posterRemoteUrl);

                if ($posterBinary->ok()) {
                    Storage::disk('public')->put("movies/{$posterFilename}", $posterBinary->body());
                } else {
                    $posterFilename = null;
                }
            }

            Content::updateOrCreate(
                [
                    'title' => (string) $details['title'],
                    'release_date' => (string) $details['release_date'],
                    'type' => 'film',
                ],
                [
                    'genre_id' => $genreId,
                    'duration' => max(1, (int) ($details['runtime'] ?? 100)),
                    'rating' => (int) max(0, min(100, round(((float) ($details['vote_average'] ?? 0)) * 10))),
                    'description' => (string) ($details['overview'] ?? 'No description available'),
                    'director' => (string) ($director['name'] ?? 'Unknown'),
                    'picture' => $posterFilename,
                    'video' => null,
                ]
            );

            $createdOrUpdated++;
        }

        $this->command->info("TMDB seed finished. Items created/updated: {$createdOrUpdated}");
    }
}
