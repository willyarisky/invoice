<?php

declare(strict_types=1);

namespace Database\Seeders;

use Zero\Lib\DB\DBML;
use Zero\Lib\DB\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $categories = [
            'Office Supplies',
            'Travel',
            'Software',
            'Utilities',
            'Marketing',
        ];

        foreach ($categories as $name) {
            if (DBML::table('categories')->where('name', $name)->exists()) {
                continue;
            }

            DBML::table('categories')->insert([
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
