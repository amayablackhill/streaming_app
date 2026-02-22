<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        DB::table('roles')->upsert([
            ['id' => 1, 'name' => 'admin', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'user', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'premium', 'created_at' => $now, 'updated_at' => $now],
        ], ['id'], ['name', 'updated_at']);
    }
}
