<?php

namespace App\Services\Tmdb;

use App\Jobs\ImportTvSeasonEpisodesJob;
use App\Models\Content;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Season;
use App\Services\Tmdb\Exceptions\TmdbException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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

        $content = Content::query()->updateOrCreate(
            [
                'tmdb_type' => $type,
                'tmdb_id' => $resolvedTmdbId,
            ],
            $payload
        );

        if ($type === 'tv') {
            $this->importTvSeasonsFromDetails($content, $details);
        }

        return $content;
    }

    public function dispatchTvEpisodeImports(Content $content, ?int $maxSeasons = null): int
    {
        if ($content->tmdb_type !== 'tv' || empty($content->tmdb_id)) {
            return 0;
        }

        $seasonQuery = $content->seasons()->orderBy('season_number');
        if ($maxSeasons === null) {
            $maxSeasons = (int) config('services.tmdb.tv_import_season_limit', 0);
        }
        if ($maxSeasons > 0) {
            $seasonQuery->limit($maxSeasons);
        }

        $seasons = $seasonQuery->get();
        foreach ($seasons as $season) {
            ImportTvSeasonEpisodesJob::dispatch($content->id, $season->id);
        }

        return $seasons->count();
    }

    public function importTvSeasonEpisodes(Content $content, Season $season): int
    {
        if ($content->tmdb_type !== 'tv' || empty($content->tmdb_id)) {
            throw new TmdbException('Series content must have tmdb_type=tv and valid tmdb_id.');
        }

        $tmdbTvId = (int) $content->tmdb_id;
        $seasonNumber = (int) $season->season_number;
        $payload = $this->client->getTvSeasonDetails($tmdbTvId, $seasonNumber);
        $today = now()->toDateString();
        $seasonFallbackDate = $season->release_date?->toDateString()
            ?: $content->release_date?->toDateString()
            ?: $today;

        $season->update([
            'tmdb_id' => isset($payload['id']) ? (int) $payload['id'] : $season->tmdb_id,
            'release_date' => $this->parseDateOrFallback($payload['air_date'] ?? null, $seasonFallbackDate),
            'poster_path' => $payload['poster_path'] ?? $season->poster_path,
            'overview' => isset($payload['overview']) && trim((string) $payload['overview']) !== ''
                ? (string) $payload['overview']
                : $season->overview,
            'episode_count' => isset($payload['episodes']) && is_array($payload['episodes'])
                ? count($payload['episodes'])
                : $season->episode_count,
            'tmdb_last_synced_at' => now(),
        ]);

        $runtimeFallback = (int) ($content->runtime_minutes ?: $content->duration ?: 1);
        $episodes = collect($payload['episodes'] ?? [])
            ->filter(fn (array $item): bool => isset($item['episode_number']) && (int) $item['episode_number'] > 0)
            ->values();

        foreach ($episodes as $episodeItem) {
            $episodeNumber = (int) $episodeItem['episode_number'];
            $runtimeMinutes = isset($episodeItem['runtime']) && (int) $episodeItem['runtime'] > 0
                ? (int) $episodeItem['runtime']
                : $runtimeFallback;
            $airDate = $this->parseDateOrFallback($episodeItem['air_date'] ?? null, $seasonFallbackDate);
            $stillPath = isset($episodeItem['still_path']) && trim((string) $episodeItem['still_path']) !== ''
                ? (string) $episodeItem['still_path']
                : null;

            Episode::query()->updateOrCreate(
                [
                    'season_id' => $season->id,
                    'episode_number' => $episodeNumber,
                ],
                [
                    'tmdb_id' => isset($episodeItem['id']) ? (int) $episodeItem['id'] : null,
                    'title' => trim((string) ($episodeItem['name'] ?? '')) ?: "Episode {$episodeNumber}",
                    'duration' => max(1, $runtimeMinutes),
                    'runtime_minutes' => max(1, $runtimeMinutes),
                    'release_date' => $airDate,
                    'plot' => trim((string) ($episodeItem['overview'] ?? '')) ?: null,
                    'cover_path' => $stillPath,
                    'still_path' => $stillPath,
                    'tmdb_last_synced_at' => now(),
                ]
            );
        }

        return $episodes->count();
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

    private function importTvSeasonsFromDetails(Content $content, array $details): Collection
    {
        $seasons = collect($details['seasons'] ?? [])
            ->filter(fn (array $season): bool => isset($season['season_number']) && (int) $season['season_number'] >= 0)
            ->values();
        $fallbackDate = $content->release_date?->toDateString() ?: now()->toDateString();

        return $seasons->map(function (array $seasonPayload) use ($content, $fallbackDate): Season {
            $seasonNumber = (int) $seasonPayload['season_number'];
            $releaseDate = $this->parseDateOrFallback($seasonPayload['air_date'] ?? null, $fallbackDate);
            $overview = trim((string) ($seasonPayload['overview'] ?? ''));
            $posterPath = isset($seasonPayload['poster_path']) && trim((string) $seasonPayload['poster_path']) !== ''
                ? (string) $seasonPayload['poster_path']
                : null;

            return Season::query()->updateOrCreate(
                [
                    'serie_id' => $content->id,
                    'season_number' => $seasonNumber,
                ],
                [
                    'tmdb_id' => isset($seasonPayload['id']) ? (int) $seasonPayload['id'] : null,
                    'release_date' => $releaseDate,
                    'poster_path' => $posterPath,
                    'overview' => $overview !== '' ? $overview : null,
                    'episode_count' => isset($seasonPayload['episode_count']) ? (int) $seasonPayload['episode_count'] : null,
                    'tmdb_last_synced_at' => now(),
                ]
            );
        });
    }

    private function parseDateOrFallback(mixed $rawDate, string $fallbackDate): string
    {
        if (is_string($rawDate) && trim($rawDate) !== '') {
            try {
                return Carbon::parse($rawDate)->toDateString();
            } catch (\Throwable) {
            }
        }

        return $fallbackDate;
    }
}
