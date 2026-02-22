<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakeAdminUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_promotes_existing_user_to_admin_role(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.com',
        ]);

        $this->artisan('app:make-admin member@example.com')
            ->expectsOutput('User member@example.com promoted to admin.')
            ->assertExitCode(0);

        $this->assertTrue($user->fresh()->hasRole('admin'));
    }

    public function test_it_fails_when_user_does_not_exist(): void
    {
        $this->artisan('app:make-admin missing@example.com')
            ->expectsOutput('User not found: missing@example.com')
            ->assertExitCode(1);
    }
}

