<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role as PermissionRole;
use Tests\TestCase;

class AdminHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_health_page_and_api(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.health'))
            ->assertOk()
            ->assertSee('System Status');

        $this->actingAs($admin)
            ->getJson(route('admin.health.api'))
            ->assertOk()
            ->assertJsonStructure([
                'ok',
                'app' => ['ok', 'env', 'debug'],
                'db' => ['ok'],
                'cache' => ['ok', 'store'],
                'queue' => ['ok', 'connection', 'notes'],
                'storage' => ['ok', 'disk'],
                'tmdb' => ['configured', 'language'],
            ]);
    }

    public function test_non_admin_cannot_access_health_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.health'))
            ->assertForbidden();

        $this->actingAs($user)
            ->getJson(route('admin.health.api'))
            ->assertForbidden();
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create();
        PermissionRole::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncRoles(['admin']);

        return $admin;
    }
}
