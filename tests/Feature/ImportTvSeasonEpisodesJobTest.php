<?php

namespace Tests\Feature;

use App\Jobs\ImportTvSeasonEpisodesJob;
use App\Models\Content;
use App\Models\Genre;
use App\Models\Season;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportTvSeasonEpisodesJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_imports_episodes_and_stays_idempotent(): void
    {
        config()->set('services.tmdb.token', 'test-token');

        $genre = Genre::query()->create(['name' => 'Drama']);
        $content = Content::query()->create([
            'title' => 'Sample Series',
            'genre_id' => $genre->id,
            'release_date' => '2024-01-01',
            'duration' => 50,
            'rating' => 80,
            'description' => 'Sample series description.',
            'director' => 'Unknown',
            'type' => 'serie',
            'tmdb_type' => 'tv',
            'tmdb_id' => 222,
        ]);

        $season = Season::query()->create([
            'serie_id' => $content->id,
            'tmdb_id' => 5201,
            'season_number' => 1,
            'release_date' => '2024-01-01',
            'overview' => 'Season one',
        ]);

        Http::fake([
            'https://api.themoviedb.org/3/tv/222/season/1*' => Http::response([
                'id' => 5201,
                'air_date' => '2024-01-01',
                'season_number' => 1,
                'name' => 'Season 1',
                'overview' => 'Season one overview.',
                'poster_path' => '/season-1.jpg',
                'episodes' => [
                    [
                        'id' => 9101,
                        'episode_number' => 1,
                        'name' => 'Episode One',
                        'runtime' => 48,
                        'air_date' => '2024-01-01',
                        'overview' => 'Episode one overview.',
                        'still_path' => '/episode-1.jpg',
                    ],
                    [
                        'id' => 9102,
                        'episode_number' => 2,
                        'name' => 'Episode Two',
                        'runtime' => 47,
                        'air_date' => '2024-01-08',
                        'overview' => 'Episode two overview.',
                        'still_path' => '/episode-2.jpg',
                    ],
                ],
            ], 200),
        ]);

        $job = new ImportTvSeasonEpisodesJob($content->id, $season->id);
        $job->handle(app(\App\Services\Tmdb\TmdbImportService::class));
        $job->handle(app(\App\Services\Tmdb\TmdbImportService::class));

        $this->assertDatabaseCount('episodes', 2);
        $this->assertDatabaseHas('episodes', [
            'season_id' => $season->id,
            'episode_number' => 1,
            'tmdb_id' => 9101,
            'runtime_minutes' => 48,
            'cover_path' => '/episode-1.jpg',
            'still_path' => '/episode-1.jpg',
        ]);
        $this->assertDatabaseHas('episodes', [
            'season_id' => $season->id,
            'episode_number' => 2,
            'tmdb_id' => 9102,
            'runtime_minutes' => 47,
            'cover_path' => '/episode-2.jpg',
            'still_path' => '/episode-2.jpg',
        ]);
    }
}
