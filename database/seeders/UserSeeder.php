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
        $email = 'owner@example.com';
        $userExists = DBML::table('users')->where('email', $email)->exists();

        if ($userExists) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $password = 'password';

        DBML::table('users')->insert([
            'name' => 'Owner',
            'email' => $email,
            'password' => Crypto::hash($password),
            'email_verified_at' => $now,
            'remember_token' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
