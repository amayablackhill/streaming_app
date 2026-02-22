<?php

namespace App\Services\Tmdb;

use App\Services\Tmdb\Exceptions\TmdbException;
use App\Services\Tmdb\Exceptions\TmdbNotConfiguredException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TmdbClient
{
    private const BASE_URL = 'https://api.themoviedb.org/3';

    public function search(string $query, string $type = 'movie', int $page = 1): array
    {
        $type = $this->normalizeType($type);
        $query = trim($query);
        $page = max(1, $page);

        if ($query === '') {
            return ['page' => $page, 'results' => [], 'total_pages' => 0, 'total_results' => 0];
        }

        $cacheKey = sprintf(
            'tmdb:search:%s:%s:%d',
            $type,
            md5(Str::lower($query)),
            $page
        );

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($type, $query, $page) {
            return $this->request("search/{$type}", [
                'query' => $query,
                'page' => $page,
                'include_adult' => false,
            ]);
        });
    }

    public function getDetails(string $type, int $id): array
    {
        $type = $this->normalizeType($type);
        $id = max(1, $id);
        $cacheKey = "tmdb:detail:{$type}:{$id}";

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($type, $id) {
            return $this->request("{$type}/{$id}");
        });
    }

    public function getVideos(string $type, int $id): array
    {
        $type = $this->normalizeType($type);
        $id = max(1, $id);
        $cacheKey = "tmdb:videos:{$type}:{$id}";

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($type, $id) {
            return $this->request("{$type}/{$id}/videos");
        });
    }

    private function request(string $endpoint, array $query = []): array
    {
        $token = (string) config('services.tmdb.token', '');

        if ($token === '') {
            throw new TmdbNotConfiguredException('TMDB_TOKEN is not configured.');
        }

        $language = (string) config('services.tmdb.language', 'en-US');

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(20)
            ->retry(2, 400)
            ->baseUrl(self::BASE_URL)
            ->get($endpoint, array_merge(['language' => $language], $query));

        if (!$response->ok()) {
            throw new TmdbException("TMDB request failed for endpoint [{$endpoint}] with status [{$response->status()}].");
        }

        $json = $response->json();

        if (!is_array($json)) {
            throw new TmdbException("TMDB response for endpoint [{$endpoint}] is not a valid JSON object.");
        }

        return $json;
    }

    private function normalizeType(string $type): string
    {
        $normalized = Str::lower(trim($type));

        if (!in_array($normalized, ['movie', 'tv'], true)) {
            throw new TmdbException('TMDB type must be "movie" or "tv".');
        }

        return $normalized;
    }
}
