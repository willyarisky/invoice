<?php

declare(strict_types=1);

namespace Database\Seeders;

use Zero\Lib\DB\DBML;
use Zero\Lib\DB\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $currencies = [
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'is_default' => 1],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '', 'is_default' => 0],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '', 'is_default' => 0],
            ['code' => 'PHP', 'name' => 'Philippine Peso', 'symbol' => '', 'is_default' => 0],
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => '', 'is_default' => 0],
        ];

        foreach ($currencies as $currency) {
            $exists = DBML::table('currencies')->where('code', $currency['code'])->exists();
            if ($exists) {
                continue;
            }

            DBML::table('currencies')->insert([
                'code' => $currency['code'],
                'name' => $currency['name'],
                'symbol' => $currency['symbol'],
                'is_default' => $currency['is_default'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $hasDefault = DBML::table('currencies')->where('is_default', 1)->exists();
        if (!$hasDefault) {
            DBML::table('currencies')->where('code', 'USD')->update(['is_default' => 1]);
        }
    }
}
