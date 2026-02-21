<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role as PermissionRole;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_require_authentication(): void
    {
        $response = $this->get('/admin/addContent');

        $response->assertRedirect('/login');
    }

    public function test_non_admin_user_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create();
        PermissionRole::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $user->syncRoles(['user']);

        $response = $this->actingAs($user)->get('/admin/addContent');

        $response->assertForbidden();
    }

    public function test_admin_user_can_access_admin_routes(): void
    {
        $user = User::factory()->create();
        PermissionRole::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user->syncRoles(['admin']);

        $response = $this->actingAs($user)->get('/admin/addContent');

        $response->assertOk();
    }
}
