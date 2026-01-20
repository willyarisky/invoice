<?php

declare(strict_types=1);

namespace Database\Seeders;

use Zero\Lib\Crypto;
use Zero\Lib\DB\DBML;
use Zero\Lib\DB\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $password = 'password';
        $users = [
            ['name' => 'Owner', 'email' => 'owner@example.com'],
            ['name' => 'Maria Santos', 'email' => 'maria@example.com'],
            ['name' => 'Jordan Lee', 'email' => 'jordan@example.com'],
        ];

        foreach ($users as $user) {
            $userExists = DBML::table('users')->where('email', $user['email'])->exists();
            if ($userExists) {
                continue;
            }

            DBML::table('users')->insert([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Crypto::hash($password),
                'email_verified_at' => $now,
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
