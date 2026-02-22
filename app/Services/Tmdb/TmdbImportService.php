<?php

namespace App\Services\Tmdb;

use App\Models\Content;
use App\Models\Genre;
use App\Services\Tmdb\Exceptions\TmdbException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TmdbImportService
{
    public function __construct(private readonly TmdbClient $client)
    {
    }

    /**
     * @return array<int, array{
     *   tmdb_id:int,
     *   tmdb_type:string,
     *   title:string,
     *   release_date:?string,
     *   rating_average:?float,
     *   poster_path:?string
     * }>
     */
    public function search(string $query, string $type = 'movie', int $page = 1): array
    {
        $payload = $this->client->search($query, $type, $page);
        $tmdbType = Str::lower($type);

        return collect($payload['results'] ?? [])
            ->map(function (array $item) use ($tmdbType): array {
                return [
                    'tmdb_id' => (int) ($item['id'] ?? 0),
                    'tmdb_type' => $tmdbType,
                    'title' => (string) ($item['title'] ?? $item['name'] ?? ''),
                    'release_date' => $item['release_date'] ?? $item['first_air_date'] ?? null,
                    'rating_average' => isset($item['vote_average']) ? (float) $item['vote_average'] : null,
                    'poster_path' => isset($item['poster_path']) ? (string) $item['poster_path'] : null,
                ];
            })
            ->filter(fn (array $item): bool => $item['tmdb_id'] > 0 && $item['title'] !== '')
            ->values()
            ->all();
    }

    public function importByTmdb(string $type, int $tmdbId): Content
    {
        $type = Str::lower(trim($type));
        $tmdbId = (int) $tmdbId;

        if (!in_array($type, ['movie', 'tv'], true)) {
            throw new TmdbException('tmdb_type must be movie or tv.');
        }

        if ($tmdbId <= 0) {
            throw new TmdbException('tmdb_id must be a positive integer.');
        }

        $details = $this->client->getDetails($type, $tmdbId);
        $videos = $this->client->getVideos($type, $tmdbId);

        $resolvedTmdbId = (int) ($details['id'] ?? 0);
        if ($resolvedTmdbId <= 0) {
            throw new TmdbException('TMDB details response does not include a valid id.');
        }

        $title = trim((string) ($details['title'] ?? $details['name'] ?? ''));
        if ($title === '') {
            throw new TmdbException('TMDB details response does not include a valid title.');
        }

        $releaseDate = $this->resolveReleaseDate($type, $details);
        if ($releaseDate === null) {
            throw new TmdbException('TMDB details response does not include a release date compatible with local schema.');
        }

        $runtime = $this->resolveRuntime($type, $details);
        $overview = trim((string) ($details['overview'] ?? ''));
        $ratingAverage = isset($details['vote_average']) ? round((float) $details['vote_average'], 2) : null;
        $ratingCount = isset($details['vote_count']) ? (int) $details['vote_count'] : null;
        $posterPath = isset($details['poster_path']) && $details['poster_path'] !== '' ? (string) $details['poster_path'] : null;
        $backdropPath = isset($details['backdrop_path']) && $details['backdrop_path'] !== '' ? (string) $details['backdrop_path'] : null;
        $youtubeTrailerId = $this->resolveYoutubeTrailerId($videos);
        $genreId = $this->resolveGenreId($details);

        $content = Content::query()
            ->where('tmdb_type', $type)
            ->where('tmdb_id', $resolvedTmdbId)
            ->first();

        $payload = [
            'tmdb_id' => $resolvedTmdbId,
            'tmdb_type' => $type,
            'title' => $title,
            'genre_id' => $genreId,
            'release_date' => $releaseDate,
            'duration' => $runtime,
            'rating' => $ratingAverage !== null ? (int) max(0, min(100, round($ratingAverage * 10))) : null,
            'description' => $overview !== '' ? $overview : 'No overview available.',
            'director' => $this->resolveDirector($details),
            'type' => $type === 'movie' ? 'film' : 'serie',
            'overview' => $overview !== '' ? $overview : null,
            'runtime_minutes' => $runtime,
            'rating_average' => $ratingAverage,
            'rating_count' => $ratingCount,
            'poster_path' => $posterPath,
            'backdrop_path' => $backdropPath,
            'youtube_trailer_id' => $youtubeTrailerId,
            'video' => $youtubeTrailerId,
            'tmdb_last_synced_at' => now(),
        ];

        if ($content) {
            $content->fill($payload);
            $content->save();

            return $content->fresh();
        }

        return Content::query()->create($payload);
    }

    private function resolveReleaseDate(string $type, array $details): ?string
    {
        $rawDate = $type === 'movie'
            ? Arr::get($details, 'release_date')
            : Arr::get($details, 'first_air_date');

        if (!is_string($rawDate) || trim($rawDate) === '') {
            return null;
        }

        try {
            return Carbon::parse($rawDate)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolveRuntime(string $type, array $details): int
    {
        $runtime = $type === 'movie'
            ? (int) ($details['runtime'] ?? 0)
            : (int) collect($details['episode_run_time'] ?? [])->filter()->first();

        return max(1, $runtime);
    }

    private function resolveYoutubeTrailerId(array $videosPayload): ?string
    {
        $videos = collect($videosPayload['results'] ?? []);

        $preferred = $videos->first(function (array $video): bool {
            return ($video['site'] ?? null) === 'YouTube'
                && ($video['type'] ?? null) === 'Trailer'
                && (bool) ($video['official'] ?? false)
                && !empty($video['key']);
        });

        if (is_array($preferred)) {
            return (string) $preferred['key'];
        }

        $fallback = $videos->first(function (array $video): bool {
            return ($video['site'] ?? null) === 'YouTube'
                && ($video['type'] ?? null) === 'Trailer'
                && !empty($video['key']);
        });

        return is_array($fallback) ? (string) $fallback['key'] : null;
    }

    private function resolveGenreId(array $details): int
    {
        $genreName = collect($details['genres'] ?? [])
            ->map(fn (array $genre) => isset($genre['name']) ? trim((string) $genre['name']) : '')
            ->first(fn (string $name) => $name !== '');

        if (is_string($genreName) && $genreName !== '') {
            return Genre::query()->firstOrCreate(['name' => $genreName])->id;
        }

        $firstGenreId = Genre::query()->value('id');
        if ($firstGenreId) {
            return (int) $firstGenreId;
        }

        return (int) Genre::query()->create(['name' => 'Uncategorized'])->id;
    }

    private function resolveDirector(array $details): string
    {
        $director = collect($details['credits']['crew'] ?? [])
            ->first(fn (array $crew): bool => ($crew['job'] ?? null) === 'Director' && !empty($crew['name']));

        if (is_array($director) && !empty($director['name'])) {
            return (string) $director['name'];
        }

        $creator = collect($details['created_by'] ?? [])->first(fn (array $item): bool => !empty($item['name']));
        if (is_array($creator)) {
            return (string) $creator['name'];
        }

        return 'Unknown';
    }
}
