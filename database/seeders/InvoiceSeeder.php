<?php

declare(strict_types=1);

namespace Database\Seeders;

use Zero\Lib\DB\DBML;
use Zero\Lib\DB\Seeder;
use Zero\Lib\Support\Str;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        if (DBML::table('invoices')->count('id') > 0) {
            return;
        }

        $customers = DBML::table('customers')->select('id', 'name')->orderBy('id')->get();
        if ($customers === []) {
            return;
        }

        $taxes = DBML::table('taxes')->select('id', 'rate')->orderBy('id')->get();
        $defaultCurrencyRow = DBML::table('currencies')->select('code')->where('is_default', 1)->first();
        $currency = strtoupper((string) ($defaultCurrencyRow['code'] ?? 'USD'));

        $seedInvoices = [
            [
                'customer_index' => 0,
                'invoice_no' => 'INV-1001',
                'status' => 'draft',
                'date' => '-7 days',
                'due' => '+23 days',
                'tax_index' => null,
                'notes' => 'Draft invoice for onboarding package.',
                'items' => [
                    ['Discovery workshop', 1, 500],
                    ['Wireframes', 5, 120],
                ],
            ],
            [
                'customer_index' => 1,
                'invoice_no' => 'INV-1002',
                'status' => 'sent',
                'date' => '-20 days',
                'due' => '+10 days',
                'tax_index' => 0,
                'notes' => 'Retainer for Q3 marketing support.',
                'items' => [
                    ['Monthly retainer', 1, 1800],
                    ['Campaign setup', 2, 350],
                ],
            ],
            [
                'customer_index' => 2,
                'invoice_no' => 'INV-1003',
                'status' => 'paid',
                'date' => '-40 days',
                'due' => '+5 days',
                'tax_index' => 1,
                'notes' => 'Project delivery and handoff.',
                'items' => [
                    ['Design sprint', 1, 2200],
                    ['Implementation', 1, 3200],
                ],
            ],
            [
                'customer_index' => 3,
                'invoice_no' => 'INV-1004',
                'status' => 'paid',
                'date' => '-15 days',
                'due' => '+15 days',
                'tax_index' => 2,
                'notes' => 'Support and maintenance package.',
                'items' => [
                    ['Support hours', 10, 90],
                    ['Incident response', 2, 150],
                ],
            ],
        ];

        foreach ($seedInvoices as $seed) {
            $customer = $customers[$seed['customer_index']] ?? null;
            if ($customer === null) {
                continue;
            }

            $baseTimestamp = strtotime($seed['date']);
            if ($baseTimestamp === false) {
                $baseTimestamp = time();
            }

            $invoiceDate = date('Y-m-d', $baseTimestamp);
            $dueDate = date('Y-m-d', strtotime($seed['due'], $baseTimestamp));
            $createdAt = date('Y-m-d H:i:s', strtotime($invoiceDate . ' 09:00:00'));

            $taxId = null;
            $taxRate = 0.0;
            if ($seed['tax_index'] !== null && isset($taxes[$seed['tax_index']])) {
                $taxId = (int) $taxes[$seed['tax_index']]['id'];
                $taxRate = (float) $taxes[$seed['tax_index']]['rate'];
            }

            $subtotal = 0.0;
            foreach ($seed['items'] as $item) {
                $subtotal += ($item[1] * $item[2]);
            }
            $taxAmount = round($subtotal * ($taxRate / 100), 2);
            $total = round($subtotal + $taxAmount, 2);

            $invoiceId = DBML::table('invoices')->insert([
                'customer_id' => (int) $customer['id'],
                'invoice_no' => $seed['invoice_no'],
                'date' => $invoiceDate,
                'due_date' => $dueDate,
                'status' => $seed['status'],
                'currency' => $currency,
                'total' => number_format($total, 2, '.', ''),
                'tax_id' => $taxId,
                'tax_rate' => number_format($taxRate, 2, '.', ''),
                'tax_amount' => number_format($taxAmount, 2, '.', ''),
                'notes' => $seed['notes'],
                'public_uuid' => Str::uuid(),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($seed['items'] as $item) {
                $qty = (int) $item[1];
                $unit = (float) $item[2];
                $lineTotal = round($qty * $unit, 2);
                DBML::table('invoice_items')->insert([
                    'invoice_id' => $invoiceId,
                    'description' => $item[0],
                    'qty' => $qty,
                    'unit_price' => number_format($unit, 2, '.', ''),
                    'subtotal' => number_format($lineTotal, 2, '.', ''),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            $statusLabel = ucfirst($seed['status']);
            $this->insertEvent($invoiceId, 'created', 'Invoice created', null, null, $createdAt);
            $this->insertEvent($invoiceId, 'status_changed', 'Status set to ' . $statusLabel, null, null, $createdAt);

            if ($seed['status'] !== 'draft') {
                $sentAt = date('Y-m-d H:i:s', strtotime($invoiceDate . ' 11:00:00'));
                $token = Str::random(24);
                $this->insertEvent($invoiceId, 'email_sent', 'Email sent', 'To: customer', $token, $sentAt);
                $openedAt = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($sentAt)));
                $this->insertEvent($invoiceId, 'email_opened', 'Email opened', null, $token, $openedAt);
            }

            if ($seed['status'] === 'paid') {
                $paymentDate = date('Y-m-d', strtotime('+5 days', $baseTimestamp));
                $paymentAt = date('Y-m-d H:i:s', strtotime($paymentDate . ' 15:00:00'));
                DBML::table('transactions')->insert([
                    'type' => 'income',
                    'amount' => number_format($total, 2, '.', ''),
                    'currency' => $currency,
                    'date' => $paymentDate,
                    'description' => 'Payment for ' . $seed['invoice_no'],
                    'source' => 'invoice',
                    'vendor_id' => null,
                    'invoice_id' => $invoiceId,
                    'created_at' => $paymentAt,
                    'updated_at' => $paymentAt,
                ]);
                $detail = 'Amount: ' . $currency . ' ' . number_format($total, 2, '.', '');
                $this->insertEvent($invoiceId, 'payment_recorded', 'Payment recorded', $detail, null, $paymentAt);
                $this->insertEvent($invoiceId, 'status_changed', 'Status set to Paid', null, null, $paymentAt);
            }
        }
    }

    private function insertEvent(
        int $invoiceId,
        string $type,
        string $summary,
        ?string $detail,
        ?string $token,
        string $timestamp
    ): void {
        DBML::table('invoice_events')->insert([
            'invoice_id' => $invoiceId,
            'type' => $type,
            'summary' => $summary,
            'detail' => $detail,
            'token' => $token,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }
}
