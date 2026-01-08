<?php

declare(strict_types=1);

namespace Database\Seeders;

use Zero\Lib\Crypto;
use Zero\Lib\DB\DBML;
use Zero\Lib\DB\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'admin@example.com';
        $adminExists = DBML::table('admin')->where('email', $email)->exists();
        $userExists = DBML::table('users')->where('email', $email)->exists();

        $now = date('Y-m-d H:i:s');
        $data = [
            'name' => 'Admin',
            'email' => $email,
            'password' => 'password',
        ];
        $hashed = Crypto::hash($data['password']);

        if (! $adminExists) {
            DBML::table('admin')->insert([
                'name' => $data['name'],
                'email' => $data['email'],
                'password_hash' => $hashed,
                'last_login' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (! $userExists) {
            DBML::table('users')->insert([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $hashed,
                'email_verified_at' => $now,
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
