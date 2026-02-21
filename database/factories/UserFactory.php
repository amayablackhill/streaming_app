<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    private static ?int $cachedRoleId = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $name = fake()->name();

        return [
            'name' => $name,
            'username' => $name,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'role_id' => $this->resolveRoleId(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    private function resolveRoleId(): ?int
    {
        if (!Schema::hasTable('roles') || !Schema::hasColumn('users', 'role_id')) {
            return null;
        }

        if (self::$cachedRoleId !== null && DB::table('roles')->where('id', self::$cachedRoleId)->exists()) {
            return self::$cachedRoleId;
        }

        $roleId = DB::table('roles')
            ->whereRaw('LOWER(name) = ?', ['user'])
            ->value('id');

        if (!$roleId) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        self::$cachedRoleId = (int) $roleId;

        return self::$cachedRoleId;
    }
}
