<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Season;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WatchEpisodePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_watch_episode_renders_series_context_and_navigation(): void
    {
        $genre = Genre::query()->create(['name' => 'Drama']);
        $series = Content::query()->create([
            'title' => 'Series Test',
            'genre_id' => $genre->id,
            'release_date' => '2024-01-01',
            'duration' => 50,
            'rating' => 80,
            'description' => 'Series overview',
            'director' => 'Director',
            'type' => 'serie',
        ]);

        $season = Season::query()->create([
            'serie_id' => $series->id,
            'season_number' => 1,
            'release_date' => '2024-01-01',
        ]);

        $episodeOne = Episode::query()->create([
            'season_id' => $season->id,
            'episode_number' => 1,
            'title' => 'Pilot',
            'duration' => 45,
            'release_date' => '2024-01-01',
        ]);

        $episodeTwo = Episode::query()->create([
            'season_id' => $season->id,
            'episode_number' => 2,
            'title' => 'Chapter Two',
            'duration' => 46,
            'release_date' => '2024-01-08',
        ]);

        $response = $this->get(route('episodes.watch', [$series->id, $season->id, $episodeOne->id]));

        $response
            ->assertOk()
            ->assertSee('Series Test')
            ->assertSee('S01E01')
            ->assertSee('Pilot')
            ->assertSee(route('episodes.watch', [$series->id, $season->id, $episodeTwo->id]), false);
    }

    public function test_watch_episode_returns_404_when_episode_not_in_requested_season_or_series(): void
    {
        $genre = Genre::query()->create(['name' => 'Drama']);

        $seriesA = Content::query()->create([
            'title' => 'Series A',
            'genre_id' => $genre->id,
            'release_date' => '2024-01-01',
            'duration' => 50,
            'rating' => 80,
            'description' => 'Series A overview',
            'director' => 'Director A',
            'type' => 'serie',
        ]);

        $seriesB = Content::query()->create([
            'title' => 'Series B',
            'genre_id' => $genre->id,
            'release_date' => '2024-01-01',
            'duration' => 50,
            'rating' => 80,
            'description' => 'Series B overview',
            'director' => 'Director B',
            'type' => 'serie',
        ]);

        $seasonA = Season::query()->create([
            'serie_id' => $seriesA->id,
            'season_number' => 1,
            'release_date' => '2024-01-01',
        ]);

        $seasonB = Season::query()->create([
            'serie_id' => $seriesB->id,
            'season_number' => 1,
            'release_date' => '2024-01-01',
        ]);

        $episodeB = Episode::query()->create([
            'season_id' => $seasonB->id,
            'episode_number' => 1,
            'title' => 'Wrong Episode',
            'duration' => 45,
            'release_date' => '2024-01-01',
        ]);

        $this->get(route('episodes.watch', [$seriesA->id, $seasonA->id, $episodeB->id]))
            ->assertNotFound();
    }
}
