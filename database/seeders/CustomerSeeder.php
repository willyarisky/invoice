<?php

declare(strict_types=1);

namespace Database\Seeders;

use Zero\Lib\DB\DBML;
use Zero\Lib\DB\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $customers = [
            [
                'name' => 'Kaleidoscope Studio',
                'email' => 'billing@kaleidoscope.test',
                'address' => "142 Lakeview Blvd\nAustin, TX",
            ],
            [
                'name' => 'Northwind Retail',
                'email' => 'ap@northwind.test',
                'address' => "88 Harbor Drive\nSeattle, WA",
            ],
            [
                'name' => 'Atlas Logistics',
                'email' => 'finance@atlas.test',
                'address' => "17 Pine Street\nDenver, CO",
            ],
            [
                'name' => 'Brightstone Media',
                'email' => 'hello@brightstone.test',
                'address' => "509 Oak Lane\nChicago, IL",
            ],
        ];

        foreach ($customers as $customer) {
            if (DBML::table('customers')->where('email', $customer['email'])->exists()) {
                continue;
            }

            DBML::table('customers')->insert([
                'name' => $customer['name'],
                'email' => $customer['email'],
                'address' => $customer['address'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
