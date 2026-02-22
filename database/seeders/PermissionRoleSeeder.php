<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;

class PermissionRoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = ['admin', 'user', 'premium'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        // Keep legacy demo users deterministic while preserving any extra roles set manually.
        User::where('email', 'pau@gmail.com')->first()?->syncRoles(['admin']);
        User::where('email', 'jose@gmail.com')->first()?->syncRoles(['admin']);
        User::where('email', 'test@gmail.com')->first()?->syncRoles(['user']);

        // Ensure every user has at least one role in Spatie.
        User::query()
            ->whereDoesntHave('roles')
            ->each(function (User $user): void {
                $user->assignRole('user');
            });

        // Guarantee there is at least one admin after seeding.
        if (!User::query()->role('admin')->exists()) {
            User::query()->orderBy('id')->first()?->assignRole('admin');
        }
    }
}
