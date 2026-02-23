<?php

namespace Tests\Feature;

use App\Jobs\ImportTvSeasonEpisodesJob;
use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role as PermissionRole;
use Tests\TestCase;

class TmdbAdminImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_search_page_shows_disabled_message_when_token_missing(): void
    {
        config()->set('services.tmdb.token', '');
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('admin.tmdb.search'));

        $response
            ->assertOk()
            ->assertSee('TMDB disabled');
    }

    public function test_admin_import_is_blocked_when_token_missing(): void
    {
        config()->set('services.tmdb.token', '');
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.tmdb.import'), [
            'tmdb_id' => 123,
            'tmdb_type' => 'movie',
        ]);

        $response
            ->assertRedirect(route('admin.tmdb.search'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('contents', 0);
    }

    public function test_admin_import_requires_non_null_tmdb_id_and_type(): void
    {
        config()->set('services.tmdb.token', 'configured');
        $admin = $this->makeAdmin();

        $response = $this->from(route('admin.tmdb.search'))->actingAs($admin)->post(route('admin.tmdb.import'), [
            'tmdb_id' => null,
            'tmdb_type' => null,
        ]);

        $response
            ->assertRedirect(route('admin.tmdb.search'))
            ->assertSessionHasErrors(['tmdb_id', 'tmdb_type']);
    }

    public function test_tv_import_queues_episode_jobs_for_imported_seasons(): void
    {
        config()->set('services.tmdb.token', 'configured');
        $admin = $this->makeAdmin();
        Bus::fake();

        Http::fake([
            'https://api.themoviedb.org/3/tv/777/videos*' => Http::response([
                'results' => [
                    ['site' => 'YouTube', 'type' => 'Trailer', 'official' => true, 'key' => 'seriesTrailer777'],
                ],
            ], 200),
            'https://api.themoviedb.org/3/tv/777*' => Http::response([
                'id' => 777,
                'name' => 'Queued Series',
                'first_air_date' => '2024-01-01',
                'episode_run_time' => [50],
                'vote_average' => 7.7,
                'vote_count' => 900,
                'overview' => 'Series overview',
                'poster_path' => '/series.jpg',
                'backdrop_path' => '/series-bg.jpg',
                'genres' => [['id' => 18, 'name' => 'Drama']],
                'seasons' => [
                    ['id' => 3001, 'season_number' => 1, 'air_date' => '2024-01-01', 'episode_count' => 8],
                    ['id' => 3002, 'season_number' => 2, 'air_date' => '2025-01-01', 'episode_count' => 8],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.tmdb.import'), [
            'tmdb_id' => 777,
            'tmdb_type' => 'tv',
        ]);

        $imported = Content::query()->where('tmdb_id', 777)->where('tmdb_type', 'tv')->first();

        $response->assertRedirect('/series/' . $imported?->id);
        $this->assertNotNull($imported);
        Bus::assertDispatched(ImportTvSeasonEpisodesJob::class, 2);
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create();
        PermissionRole::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncRoles(['admin']);

        return $admin;
    }
}
