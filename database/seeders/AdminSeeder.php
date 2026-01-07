<?php

declare(strict_types=1);

namespace Database\Seeders;

use Zero\Lib\DB\DBML;
use Zero\Lib\DB\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'admin@example.com';

        if (DBML::table('admin')->where('email', $email)->exists()) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        DBML::table('admin')->insert([
            'name' => 'Admin',
            'email' => $email,
            'password_hash' => password_hash('password', PASSWORD_BCRYPT),
            'last_login' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
