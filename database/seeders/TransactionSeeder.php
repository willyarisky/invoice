<?php

declare(strict_types=1);

namespace Database\Seeders;

use Zero\Lib\DB\DBML;
use Zero\Lib\DB\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        if (DBML::table('transactions')->where('source', 'manual')->exists()) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $categories = DBML::table('categories')->select('id', 'name')->orderBy('id')->get();
        $vendors = DBML::table('vendors')->select('id', 'name')->orderBy('id')->get();
        $defaultCurrencyRow = DBML::table('currencies')->select('code')->where('is_default', 1)->first();
        $currency = strtoupper((string) ($defaultCurrencyRow['code'] ?? 'USD'));

        $categoryIds = array_column($categories, 'id');
        $vendorIds = array_column($vendors, 'id');

        $transactions = [
            [
                'type' => 'expense',
                'amount' => 420.00,
                'date' => date('Y-m-d', strtotime('-12 days')),
                'description' => 'Cloud hosting renewal',
                'category_index' => 2,
                'vendor_index' => 0,
            ],
            [
                'type' => 'expense',
                'amount' => 95.00,
                'date' => date('Y-m-d', strtotime('-8 days')),
                'description' => 'Office supplies restock',
                'category_index' => 0,
                'vendor_index' => 3,
            ],
            [
                'type' => 'expense',
                'amount' => 680.00,
                'date' => date('Y-m-d', strtotime('-18 days')),
                'description' => 'Event marketing spend',
                'category_index' => 4,
                'vendor_index' => 2,
            ],
            [
                'type' => 'income',
                'amount' => 350.00,
                'date' => date('Y-m-d', strtotime('-6 days')),
                'description' => 'Consulting session',
                'category_index' => null,
                'vendor_index' => null,
            ],
            [
                'type' => 'expense',
                'amount' => 210.00,
                'date' => date('Y-m-d', strtotime('-4 days')),
                'description' => 'Flight to client site',
                'category_index' => 1,
                'vendor_index' => null,
            ],
        ];

        foreach ($transactions as $entry) {
            $categoryId = null;
            if ($entry['category_index'] !== null && isset($categoryIds[$entry['category_index']])) {
                $categoryId = (int) $categoryIds[$entry['category_index']];
            }

            $vendorId = null;
            if ($entry['vendor_index'] !== null && isset($vendorIds[$entry['vendor_index']])) {
                $vendorId = (int) $vendorIds[$entry['vendor_index']];
            }

            DBML::table('transactions')->insert([
                'type' => $entry['type'],
                'amount' => number_format((float) $entry['amount'], 2, '.', ''),
                'currency' => $currency,
                'date' => $entry['date'],
                'description' => $entry['description'],
                'source' => 'manual',
                'vendor_id' => $vendorId,
                'invoice_id' => null,
                'category_id' => $categoryId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
