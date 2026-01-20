<?php

declare(strict_types=1);

namespace Database\Seeders;

use Zero\Lib\DB\DBML;
use Zero\Lib\DB\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $vendors = [
            [
                'name' => 'Cloudway Hosting',
                'email' => 'support@cloudway.test',
                'phone' => '555-0140',
                'address' => "200 Service Rd\nNew York, NY",
            ],
            [
                'name' => 'Metro Printing',
                'email' => 'orders@metroprint.test',
                'phone' => '555-0188',
                'address' => "45 Paper St\nBoston, MA",
            ],
            [
                'name' => 'Bright Ads Co',
                'email' => 'accounts@brightads.test',
                'phone' => '555-0195',
                'address' => "77 Market Ave\nLos Angeles, CA",
            ],
            [
                'name' => 'River Tools',
                'email' => 'billing@rivertools.test',
                'phone' => '555-0122',
                'address' => "901 Industrial Way\nPortland, OR",
            ],
        ];

        foreach ($vendors as $vendor) {
            $exists = DBML::table('vendors')->where('name', $vendor['name'])->exists();
            if ($exists) {
                continue;
            }

            DBML::table('vendors')->insert([
                'name' => $vendor['name'],
                'email' => $vendor['email'],
                'phone' => $vendor['phone'],
                'address' => $vendor['address'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
