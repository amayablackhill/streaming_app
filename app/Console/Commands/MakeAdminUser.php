<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class MakeAdminUser extends Command
{
    protected $signature = 'app:make-admin {email : Email of the user to promote}';

    protected $description = 'Promote an existing user to admin using Spatie roles';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $user = User::query()->where('email', $email)->first();

        if (!$user) {
            $this->error("User not found: {$email}");

            return self::FAILURE;
        }

        Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $user->assignRole('admin');

        // Keep legacy role_id aligned only for schema compatibility.
        if (Schema::hasColumn('users', 'role_id') && Schema::hasTable('roles')) {
            $legacyAdminRoleId = DB::table('roles')
                ->whereRaw('LOWER(name) = ?', ['admin'])
                ->value('id');

            if ($legacyAdminRoleId) {
                $user->forceFill(['role_id' => $legacyAdminRoleId])->save();
            }
        }

        $this->info("User {$email} promoted to admin.");

        return self::SUCCESS;
    }
}

