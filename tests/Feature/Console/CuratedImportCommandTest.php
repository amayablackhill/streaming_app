<?php

namespace Tests\Feature\Console;

use App\Models\Content;
use App\Models\CuratedList;
use App\Models\CuratedListItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CuratedImportCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_json_and_remains_idempotent(): void
    {
        config()->set('services.tmdb.token', 'test-token');

        Http::fake($this->tmdbResponder());

        $path = storage_path('app/testing/curated_import.json');
        File::ensureDirectoryExists(dirname($path));

        File::put($path, json_encode([
            'list' => [
                'name' => 'Festival Picks',
                'slug' => 'festival-picks',
            ],
            'items' => [
                ['tmdb_id' => 111, 'tmdb_type' => 'movie', 'rank' => 1],
                ['tmdb_id' => 222, 'tmdb_type' => 'tv', 'rank' => 2],
            ],
        ], JSON_PRETTY_PRINT));

        $this->artisan("curated:import {$path}")
            ->assertExitCode(0);

        $list = CuratedList::query()->where('slug', 'festival-picks')->firstOrFail();

        $this->assertDatabaseCount('curated_list_items', 2);
        $this->assertDatabaseCount('contents', 2);

        File::put($path, json_encode([
            'list' => [
                'name' => 'Festival Picks',
                'slug' => 'festival-picks',
            ],
            'items' => [
                ['tmdb_id' => 111, 'tmdb_type' => 'movie', 'rank' => 2],
                ['tmdb_id' => 222, 'tmdb_type' => 'tv', 'rank' => 1],
            ],
        ], JSON_PRETTY_PRINT));

        $this->artisan("curated:import {$path}")
            ->assertExitCode(0);

        $arrival = Content::query()->where('tmdb_type', 'movie')->where('tmdb_id', 111)->firstOrFail();
        $serial = Content::query()->where('tmdb_type', 'tv')->where('tmdb_id', 222)->firstOrFail();

        $this->assertSame(2, CuratedListItem::query()
            ->where('curated_list_id', $list->id)
            ->where('content_id', $arrival->id)
            ->value('rank'));

        $this->assertSame(1, CuratedListItem::query()
            ->where('curated_list_id', $list->id)
            ->where('content_id', $serial->id)
            ->value('rank'));

        $this->assertDatabaseCount('curated_list_items', 2);
    }

    public function test_it_reports_unresolved_and_ambiguous_rows(): void
    {
        config()->set('services.tmdb.token', 'test-token');

        Http::fake($this->tmdbResponder());

        $path = storage_path('app/testing/curated_import.csv');
        File::ensureDirectoryExists(dirname($path));

        File::put($path, <<<CSV
title,tmdb_type,rank,year
Single Match,movie,1,2016
Ambiguous,movie,2,2020
Unknown,movie,3,2022
CSV
        );

        $this->artisan("curated:import {$path} --slug=mixed-list")
            ->expectsOutputToContain('Unresolved entries: 1')
            ->expectsOutputToContain('Ambiguous entries: 1')
            ->assertExitCode(0);

        $list = CuratedList::query()->where('slug', 'mixed-list')->firstOrFail();
        $this->assertSame(1, CuratedListItem::query()->where('curated_list_id', $list->id)->count());
    }

    private function tmdbResponder(): \Closure
    {
        return function (Request $request) {
            $url = $request->url();

            if (str_contains($url, '/search/movie')) {
                parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
                $term = $query['query'] ?? '';

                return match ($term) {
                    'Single Match' => Http::response([
                        'page' => 1,
                        'results' => [
                            ['id' => 333, 'title' => 'Single Match', 'release_date' => '2016-02-10', 'vote_average' => 7.2],
                        ],
                    ], 200),
                    'Ambiguous' => Http::response([
                        'page' => 1,
                        'results' => [
                            ['id' => 444, 'title' => 'Ambiguous A', 'release_date' => '2020-01-10', 'vote_average' => 7.0],
                            ['id' => 445, 'title' => 'Ambiguous B', 'release_date' => '2020-06-10', 'vote_average' => 6.8],
                        ],
                    ], 200),
                    'Unknown' => Http::response([
                        'page' => 1,
                        'results' => [],
                    ], 200),
                    default => Http::response(['page' => 1, 'results' => []], 200),
                };
            }

            if (str_contains($url, '/movie/111/videos')) {
                return Http::response(['results' => []], 200);
            }

            if (str_contains($url, '/movie/111')) {
                return Http::response([
                    'id' => 111,
                    'title' => 'Arrival',
                    'release_date' => '2016-11-11',
                    'runtime' => 116,
                    'vote_average' => 7.9,
                    'vote_count' => 12000,
                    'overview' => 'Linguist meets alien visitors.',
                    'genres' => [['id' => 1, 'name' => 'Sci-Fi']],
                ], 200);
            }

            if (str_contains($url, '/tv/222/videos')) {
                return Http::response(['results' => []], 200);
            }

            if (str_contains($url, '/tv/222')) {
                return Http::response([
                    'id' => 222,
                    'name' => 'The Serial',
                    'first_air_date' => '2021-01-01',
                    'episode_run_time' => [45],
                    'vote_average' => 7.4,
                    'vote_count' => 3400,
                    'overview' => 'Serialized story.',
                    'genres' => [['id' => 2, 'name' => 'Drama']],
                ], 200);
            }

            if (str_contains($url, '/movie/333/videos')) {
                return Http::response(['results' => []], 200);
            }

            if (str_contains($url, '/movie/333')) {
                return Http::response([
                    'id' => 333,
                    'title' => 'Single Match',
                    'release_date' => '2016-02-10',
                    'runtime' => 102,
                    'vote_average' => 7.2,
                    'vote_count' => 4500,
                    'overview' => 'Single match movie.',
                    'genres' => [['id' => 3, 'name' => 'Thriller']],
                ], 200);
            }

            return Http::response([], 404);
        };
    }
}

