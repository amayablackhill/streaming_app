<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = now();
        $users = [
            [
                'username' => 'Pau',
                'password' => Hash::make('Pau'),
                'email' => 'pau@gmail.com',
                'role_id' => 1
            ],
            [
                'username' => 'Jose',
                'password' => Hash::make('123'),
                'email' => 'jose@gmail.com',
                'role_id' => 1 
            ],
            [
                'username' => 'test',
                'password' => Hash::make('123'),
                'email' => 'test@gmail.com',
                'role_id' => 2
            ]
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                [
                    'username' => $user['username'],
                    'password' => $user['password'],
                    'role_id' => $user['role_id'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
