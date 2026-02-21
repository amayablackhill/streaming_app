<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PermissionRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['admin', 'user', 'premium'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        // Seed deterministic demo role assignments.
        User::where('email', 'pau@gmail.com')->first()?->syncRoles(['admin']);
        User::where('email', 'jose@gmail.com')->first()?->syncRoles(['admin']);
        User::where('email', 'test@gmail.com')->first()?->syncRoles(['user']);
    }
}
