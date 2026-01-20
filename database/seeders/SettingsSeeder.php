<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Zero\Lib\DB\DBML;
use Zero\Lib\DB\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $definitions = Setting::definitions();
        $seedOverrides = [
            'business_name' => 'OhMyInvoice',
            'company_address' => '123 Market Street, San Diego, CA',
            'company_email' => 'hello@ohmyinvoice.test',
            'company_phone' => '+1 (555) 010-2000',
            'default_currency' => 'USD',
            'mail_from_address' => 'billing@ohmyinvoice.test',
            'mail_from_name' => 'OhMyInvoice',
            'invoice_email_message' => "Hi {customer_name},\n\nYour invoice {invoice_no} is attached. The total due is {total}.\n\nThanks,\n{company_name}",
        ];

        foreach ($definitions as $key => $definition) {
            if (DBML::table('settings')->where('key', $key)->exists()) {
                continue;
            }

            $value = $seedOverrides[$key] ?? ($definition['default'] ?? '');
            DBML::table('settings')->insert([
                'key' => $key,
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
