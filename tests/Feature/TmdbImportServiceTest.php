<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Services\Tmdb\Exceptions\TmdbNotConfiguredException;
use App\Services\Tmdb\TmdbImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TmdbImportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_import_creates_new_record(): void
    {
        config()->set('services.tmdb.token', 'test-token');

        Http::fake([
            'https://api.themoviedb.org/3/movie/123/videos*' => Http::response([
                'results' => [
                    ['site' => 'YouTube', 'type' => 'Trailer', 'official' => true, 'key' => 'abcTrailer123'],
                ],
            ], 200),
            'https://api.themoviedb.org/3/movie/123*' => Http::response([
                'id' => 123,
                'title' => 'Arrival',
                'release_date' => '2016-11-11',
                'runtime' => 116,
                'vote_average' => 7.9,
                'vote_count' => 12000,
                'overview' => 'Linguist meets alien visitors.',
                'poster_path' => '/poster.jpg',
                'backdrop_path' => '/backdrop.jpg',
                'genres' => [['id' => 1, 'name' => 'Sci-Fi']],
            ], 200),
        ]);

        $service = app(TmdbImportService::class);
        $content = $service->importByTmdb('movie', 123);

        $this->assertNotNull($content->id);
        $this->assertSame('movie', $content->tmdb_type);
        $this->assertSame(123, (int) $content->tmdb_id);
        $this->assertSame('Arrival', $content->title);
        $this->assertSame('abcTrailer123', $content->youtube_trailer_id);
        $this->assertSame('film', $content->type);
        $this->assertDatabaseCount('contents', 1);
    }

    public function test_import_updates_existing_record_without_duplicates(): void
    {
        config()->set('services.tmdb.token', 'test-token');

        $content = Content::create([
            'title' => 'Old Title',
            'genre_id' => \App\Models\Genre::create(['name' => 'Drama'])->id,
            'release_date' => '2010-01-01',
            'duration' => 100,
            'rating' => 70,
            'description' => 'Old',
            'director' => 'Old Director',
            'type' => 'film',
            'tmdb_type' => 'movie',
            'tmdb_id' => 123,
        ]);

        Http::fake([
            'https://api.themoviedb.org/3/movie/123/videos*' => Http::response([
                'results' => [
                    ['site' => 'YouTube', 'type' => 'Trailer', 'official' => true, 'key' => 'newTrailer'],
                ],
            ], 200),
            'https://api.themoviedb.org/3/movie/123*' => Http::response([
                'id' => 123,
                'title' => 'New Title',
                'release_date' => '2016-11-11',
                'runtime' => 118,
                'vote_average' => 8.1,
                'vote_count' => 9000,
                'overview' => 'Updated overview.',
                'poster_path' => '/new-poster.jpg',
                'backdrop_path' => '/new-backdrop.jpg',
                'genres' => [['id' => 2, 'name' => 'Sci-Fi']],
            ], 200),
        ]);

        $service = app(TmdbImportService::class);
        $updated = $service->importByTmdb('movie', 123);

        $this->assertSame($content->id, $updated->id);
        $this->assertSame('New Title', $updated->title);
        $this->assertSame('newTrailer', $updated->youtube_trailer_id);
        $this->assertDatabaseCount('contents', 1);
    }

    public function test_search_uses_http_fake_and_cache(): void
    {
        config()->set('services.tmdb.token', 'test-token');

        Http::fake([
            'https://api.themoviedb.org/3/search/movie*' => Http::response([
                'page' => 1,
                'results' => [
                    ['id' => 11, 'title' => 'Blade Runner 2049', 'release_date' => '2017-10-06', 'vote_average' => 8.0, 'poster_path' => '/poster.jpg'],
                ],
                'total_pages' => 1,
                'total_results' => 1,
            ], 200),
        ]);

        $service = app(TmdbImportService::class);
        $first = $service->search('Blade Runner', 'movie', 1);
        $second = $service->search('Blade Runner', 'movie', 1);

        $this->assertCount(1, $first);
        $this->assertSame($first, $second);
        Http::assertSentCount(1);
    }

    public function test_missing_token_throws_controlled_exception(): void
    {
        config()->set('services.tmdb.token', '');
        Http::fake();

        $service = app(TmdbImportService::class);

        $this->expectException(TmdbNotConfiguredException::class);
        $service->search('Inception', 'movie', 1);
    }
}
