<?php

declare(strict_types=1);

namespace Database\Seeders;

use Zero\Lib\DB\DBML;
use Zero\Lib\DB\Seeder;

class TaxSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $taxes = [
            ['name' => 'VAT', 'rate' => 12.50],
            ['name' => 'Sales Tax', 'rate' => 8.25],
            ['name' => 'Service Tax', 'rate' => 5.00],
        ];

        foreach ($taxes as $tax) {
            if (DBML::table('taxes')->where('name', $tax['name'])->exists()) {
                continue;
            }

            DBML::table('taxes')->insert([
                'name' => $tax['name'],
                'rate' => number_format($tax['rate'], 2, '.', ''),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
