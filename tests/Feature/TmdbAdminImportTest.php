<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    private function makeAdmin(): User
    {
        $admin = User::factory()->create();
        PermissionRole::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncRoles(['admin']);

        return $admin;
    }
}
