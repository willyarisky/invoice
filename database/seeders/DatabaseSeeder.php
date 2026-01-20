<?php

declare(strict_types=1);

namespace Database\Seeders;

use Zero\Lib\DB\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SettingsSeeder::class,
            CurrencySeeder::class,
            CategorySeeder::class,
            TaxSeeder::class,
            CustomerSeeder::class,
            VendorSeeder::class,
            InvoiceSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
