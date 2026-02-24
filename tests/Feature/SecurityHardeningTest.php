<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role as PermissionRole;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_routes_include_security_headers(): void
    {
        $response = $this->get(route('about'));

        $response
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy')
            ->assertHeader('Content-Security-Policy');
    }

    public function test_tmdb_routes_are_blocked_when_feature_flag_is_disabled(): void
    {
        config()->set('security.features.tmdb_import', false);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.tmdb.search'))
            ->assertStatus(503);
    }

    public function test_admin_write_routes_are_blocked_when_feature_flag_is_disabled(): void
    {
        config()->set('security.features.admin_writes', false);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('content.add'), [])
            ->assertStatus(503);
    }

    public function test_search_route_is_rate_limited_after_threshold(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 40; $i++) {
            $this->actingAs($user)
                ->get(route('search', ['q' => 'alien']))
                ->assertStatus(200);
        }

        $this->actingAs($user)
            ->get(route('search', ['q' => 'alien']))
            ->assertStatus(429);
    }

    public function test_admin_tmdb_search_is_rate_limited_after_threshold(): void
    {
        config()->set('security.features.tmdb_import', true);
        config()->set('services.tmdb.token', '');
        $admin = $this->makeAdmin();

        for ($i = 0; $i < 20; $i++) {
            $this->actingAs($admin)
                ->get(route('admin.tmdb.search'))
                ->assertStatus(200);
        }

        $this->actingAs($admin)
            ->get(route('admin.tmdb.search'))
            ->assertStatus(429);
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create();
        PermissionRole::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncRoles(['admin']);

        return $admin;
    }
}
