<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Setting;
use Zero\Lib\Console\Command\CommandInterface;
use Zero\Lib\Console\Input;
use Zero\Lib\DB\DBML;
use Zero\Lib\Database;

final class ImportLegacySqlCommand implements CommandInterface
{
    public function getName(): string
    {
        return 'import:legacy-sql';
    }

    public function getDescription(): string
    {
        return 'Import customers, vendors, invoices, invoice items, and transactions from a legacy SQL dump.';
    }

    public function getUsage(): string
    {
        return 'php zero import:legacy-sql --file=storage/old.sql --company=1 [--dry-run]';
    }

    public function execute(array $argv, ?Input $input = null): int
    {
        $input ??= new Input([], []);
        $filePath = (string) ($input->option('file') ?? $input->argument(0, ''));
        $companyId = (int) ($input->option('company') ?? 1);
        $dryRun = (bool) ($input->option('dry-run') ?? false);

        if ($filePath === '') {
            $filePath = storage_path('old.sql');
        }

        if (! $this->isAbsolutePath($filePath)) {
            $candidate = storage_path($filePath);
            if (is_file($candidate)) {
                $filePath = $candidate;
            }
        }

        if (! is_file($filePath)) {
            $this->writeLine('SQL file not found: ' . $filePath);
            return 1;
        }

        $this->writeLine('Importing legacy data from ' . $filePath);
        $this->writeLine('Company filter: ' . $companyId);
        if ($dryRun) {
            $this->writeLine('Dry run enabled: no data will be written.');
        }

        $customerMap = [];
        $vendorMap = [];
        $invoiceMap = [];
        $stats = [
            'customers_inserted' => 0,
            'customers_skipped' => 0,
            'vendors_inserted' => 0,
            'vendors_skipped' => 0,
            'invoices_inserted' => 0,
            'invoices_skipped' => 0,
            'invoices_ignored' => 0,
            'invoice_items_inserted' => 0,
            'invoice_items_skipped' => 0,
            'transactions_inserted' => 0,
            'transactions_skipped' => 0,
            'transactions_ignored' => 0,
            'placeholders_used' => 0,
        ];

        try {
            Database::startTransaction();
            $this->importContacts($filePath, $companyId, $dryRun, $customerMap, $vendorMap, $stats);
            Database::commit();
        } catch (\Throwable $exception) {
            Database::rollback();
            $this->writeLine('Failed to import contacts: ' . $exception->getMessage());
            return 1;
        }

        try {
            Database::startTransaction();
            $this->importInvoices($filePath, $companyId, $dryRun, $customerMap, $stats, $invoiceMap);
            $this->importInvoiceItems($filePath, $companyId, $dryRun, $invoiceMap, $stats);
            Database::commit();
        } catch (\Throwable $exception) {
            Database::rollback();
            $this->writeLine('Failed to import invoices: ' . $exception->getMessage());
            return 1;
        }

        try {
            Database::startTransaction();
            $this->importTransactions($filePath, $companyId, $dryRun, $vendorMap, $invoiceMap, $stats);
            Database::commit();
        } catch (\Throwable $exception) {
            Database::rollback();
            $this->writeLine('Failed to import transactions: ' . $exception->getMessage());
            return 1;
        }

        $this->writeLine('Customers inserted: ' . $stats['customers_inserted']);
        $this->writeLine('Customers skipped: ' . $stats['customers_skipped']);
        $this->writeLine('Vendors inserted: ' . $stats['vendors_inserted']);
        $this->writeLine('Vendors skipped: ' . $stats['vendors_skipped']);
        $this->writeLine('Invoices inserted: ' . $stats['invoices_inserted']);
        $this->writeLine('Invoices skipped: ' . $stats['invoices_skipped']);
        $this->writeLine('Invoices ignored: ' . $stats['invoices_ignored']);
        $this->writeLine('Invoice items inserted: ' . $stats['invoice_items_inserted']);
        $this->writeLine('Invoice items skipped: ' . $stats['invoice_items_skipped']);
        $this->writeLine('Transactions inserted: ' . $stats['transactions_inserted']);
        $this->writeLine('Transactions skipped: ' . $stats['transactions_skipped']);
        $this->writeLine('Transactions ignored: ' . $stats['transactions_ignored']);
        if ($stats['placeholders_used'] > 0) {
            $this->writeLine('Customer email placeholders used: ' . $stats['placeholders_used']);
        }

        return 0;
    }

    private function importContacts(
        string $filePath,
        int $companyId,
        bool $dryRun,
        array &$customerMap,
        array &$vendorMap,
        array &$stats
    ): void {
        $now = date('Y-m-d H:i:s');

        foreach ($this->iterateInsertRows($filePath, 'grw_contacts') as $row) {
            if ((int) ($row['company_id'] ?? 0) !== $companyId) {
                continue;
            }

            if (!empty($row['deleted_at'] ?? '')) {
                continue;
            }

            $type = strtolower(trim((string) ($row['type'] ?? '')));
            if ($type === 'customer') {
                $result = $this->importCustomerRow($row, $dryRun, $now, $stats);
                if ($result['id'] !== null) {
                    $customerMap[(int) ($row['id'] ?? 0)] = $result['id'];
                }
                continue;
            }

            if ($type === 'vendor') {
                $result = $this->importVendorRow($row, $dryRun, $now, $stats);
                if ($result['id'] !== null) {
                    $vendorMap[(int) ($row['id'] ?? 0)] = $result['id'];
                }
            }
        }
    }

    private function importCustomerRow(array $row, bool $dryRun, string $now, array &$stats): array
    {
        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '') {
            $name = 'Customer ' . (string) ($row['id'] ?? '');
        }

        $email = strtolower(trim((string) ($row['email'] ?? '')));
        if ($email === '') {
            $email = 'customer-' . (string) ($row['id'] ?? 'unknown') . '@import.invalid';
            $stats['placeholders_used']++;
        }

        $existing = DBML::table('customers')->select('id')->where('email', $email)->first();
        if (!empty($existing['id'])) {
            $stats['customers_skipped']++;
            return ['id' => (int) $existing['id']];
        }

        $address = $this->formatLegacyAddress($row);
        $createdAt = $this->normalizeTimestamp($row['created_at'] ?? null, $now);
        $updatedAt = $this->normalizeTimestamp($row['updated_at'] ?? null, $createdAt);

        if ($dryRun) {
            $stats['customers_inserted']++;
            return ['id' => null];
        }

        $id = DBML::table('customers')->insert([
            'name' => $name,
            'email' => $email,
            'address' => $address !== '' ? $address : null,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);

        $stats['customers_inserted']++;

        return ['id' => (int) $id];
    }

    private function importVendorRow(array $row, bool $dryRun, string $now, array &$stats): array
    {
        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '') {
            $name = 'Vendor ' . (string) ($row['id'] ?? '');
        }

        $email = strtolower(trim((string) ($row['email'] ?? '')));
        $phone = trim((string) ($row['phone'] ?? ''));
        $address = $this->formatLegacyAddress($row);
        $createdAt = $this->normalizeTimestamp($row['created_at'] ?? null, $now);
        $updatedAt = $this->normalizeTimestamp($row['updated_at'] ?? null, $createdAt);

        $existingQuery = DBML::table('vendors')->select('id');
        if ($email !== '') {
            $existingQuery->where('email', $email);
        } else {
            $existingQuery->where('name', $name);
        }
        $existing = $existingQuery->first();

        if (!empty($existing['id'])) {
            $stats['vendors_skipped']++;
            return ['id' => (int) $existing['id']];
        }

        if ($dryRun) {
            $stats['vendors_inserted']++;
            return ['id' => null];
        }

        $id = DBML::table('vendors')->insert([
            'name' => $name,
            'email' => $email !== '' ? $email : null,
            'phone' => $phone !== '' ? $phone : null,
            'address' => $address !== '' ? $address : null,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);

        $stats['vendors_inserted']++;

        return ['id' => (int) $id];
    }

    private function importTransactions(
        string $filePath,
        int $companyId,
        bool $dryRun,
        array $vendorMap,
        array $invoiceMap,
        array &$stats
    ): void {
        $defaultCurrency = Setting::getValue('default_currency');
        $now = date('Y-m-d H:i:s');

        foreach ($this->iterateInsertRows($filePath, 'grw_transactions') as $row) {
            if ((int) ($row['company_id'] ?? 0) !== $companyId) {
                continue;
            }

            if (!empty($row['deleted_at'] ?? '')) {
                continue;
            }

            $type = strtolower(trim((string) ($row['type'] ?? '')));
            if (!in_array($type, ['income', 'expense'], true)) {
                $stats['transactions_ignored']++;
                continue;
            }

            $amountValue = number_format((float) ($row['amount'] ?? 0), 2, '.', '');
            $currency = strtoupper(trim((string) ($row['currency_code'] ?? $defaultCurrency)));
            if ($currency === '') {
                $currency = (string) $defaultCurrency;
            }

            $date = $this->normalizeDate(
                (string) ($row['paid_at'] ?? $row['created_at'] ?? ''),
                date('Y-m-d')
            );

            $description = trim((string) ($row['description'] ?? ''));
            if ($description === '') {
                $description = trim((string) ($row['reference'] ?? ''));
            }
            if ($description === '') {
                $description = 'Imported transaction';
            }
            if (strlen($description) > 255) {
                $description = substr($description, 0, 255);
            }

            $vendorId = null;
            if ($type === 'expense') {
                $contactId = (int) ($row['contact_id'] ?? 0);
                if ($contactId > 0 && isset($vendorMap[$contactId]) && $vendorMap[$contactId] > 0) {
                    $vendorId = $vendorMap[$contactId];
                }
            }

            $invoiceId = null;
            $source = 'manual';
            if ($type === 'income') {
                $documentId = (int) ($row['document_id'] ?? 0);
                if ($documentId > 0 && isset($invoiceMap[$documentId]) && $invoiceMap[$documentId] > 0) {
                    $invoiceId = $invoiceMap[$documentId];
                    $source = 'invoice';
                }
            }

            if ($this->transactionExists(
                $type,
                $amountValue,
                $currency,
                $date,
                $description,
                $source,
                $vendorId,
                $invoiceId
            )) {
                $stats['transactions_skipped']++;
                continue;
            }

            $createdAt = $this->normalizeTimestamp($row['created_at'] ?? null, $now);
            $updatedAt = $this->normalizeTimestamp($row['updated_at'] ?? null, $createdAt);

            if ($dryRun) {
                $stats['transactions_inserted']++;
                continue;
            }

            DBML::table('transactions')->insert([
                'type' => $type,
                'amount' => $amountValue,
                'currency' => $currency,
                'date' => $date,
                'description' => $description,
                'source' => $source,
                'vendor_id' => $vendorId,
                'invoice_id' => $invoiceId,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);

            $stats['transactions_inserted']++;
        }
    }

    private function transactionExists(
        string $type,
        string $amount,
        string $currency,
        string $date,
        string $description,
        string $source,
        ?int $vendorId,
        ?int $invoiceId
    ): bool {
        $query = DBML::table('transactions')
            ->select('id')
            ->where('type', $type)
            ->where('amount', $amount)
            ->where('currency', $currency)
            ->where('date', $date)
            ->where('description', $description)
            ->where('source', $source);

        if ($vendorId === null) {
            $query->whereNull('vendor_id');
        } else {
            $query->where('vendor_id', $vendorId);
        }

        if ($invoiceId === null) {
            $query->whereNull('invoice_id');
        } else {
            $query->where('invoice_id', $invoiceId);
        }

        return (bool) $query->first();
    }

    private function importInvoices(
        string $filePath,
        int $companyId,
        bool $dryRun,
        array &$customerMap,
        array &$stats,
        array &$invoiceMap
    ): void {
        $defaultCurrency = Setting::getValue('default_currency');
        $now = date('Y-m-d H:i:s');
        $usedInvoiceNumbers = [];

        foreach ($this->iterateInsertRows($filePath, 'grw_documents') as $row) {
            if ((int) ($row['company_id'] ?? 0) !== $companyId) {
                continue;
            }

            if (!empty($row['deleted_at'] ?? '')) {
                continue;
            }

            $type = strtolower(trim((string) ($row['type'] ?? '')));
            if ($type !== 'invoice') {
                $stats['invoices_ignored']++;
                continue;
            }

            $legacyId = (int) ($row['id'] ?? 0);
            $customerId = $this->resolveInvoiceCustomerId($row, $customerMap, $dryRun, $stats, $now);
            if ($customerId === null || ($customerId === 0 && ! $dryRun)) {
                $stats['invoices_skipped']++;
                continue;
            }

            $documentNumber = trim((string) ($row['document_number'] ?? ''));
            if ($documentNumber === '') {
                $documentNumber = 'INV-LEGACY-' . (string) $legacyId;
            }

            $invoiceNo = $documentNumber;
            if ($this->invoiceNumberExists($invoiceNo) || isset($usedInvoiceNumbers[$invoiceNo])) {
                $invoiceNo = $documentNumber . '-' . (string) $legacyId;
            }

            $existingId = $this->findInvoiceIdByNumber($invoiceNo);
            if ($existingId !== null) {
                $invoiceMap[$legacyId] = $existingId;
                $stats['invoices_skipped']++;
                continue;
            }

            if (isset($usedInvoiceNumbers[$invoiceNo])) {
                $stats['invoices_skipped']++;
                continue;
            }

            $usedInvoiceNumbers[$invoiceNo] = true;

            $currency = strtoupper(trim((string) ($row['currency_code'] ?? $defaultCurrency)));
            if ($currency === '') {
                $currency = (string) $defaultCurrency;
            }

            $issuedAt = $this->normalizeDate((string) ($row['issued_at'] ?? ''), date('Y-m-d'));
            $dueDate = $this->normalizeNullableDate((string) ($row['due_at'] ?? ''));
            $status = $this->mapInvoiceStatus((string) ($row['status'] ?? ''));
            $total = number_format((float) ($row['amount'] ?? 0), 2, '.', '');
            $notes = $this->resolveInvoiceNotes($row);
            $createdAt = $this->normalizeTimestamp($row['created_at'] ?? null, $now);
            $updatedAt = $this->normalizeTimestamp($row['updated_at'] ?? null, $createdAt);
            $publicUuid = $this->generatePublicUuid();

            if ($dryRun) {
                $stats['invoices_inserted']++;
                $invoiceMap[$legacyId] = 0;
                continue;
            }

            $invoiceId = DBML::table('invoices')->insert([
                'customer_id' => $customerId,
                'invoice_no' => $invoiceNo,
                'date' => $issuedAt,
                'due_date' => $dueDate,
                'status' => $status,
                'currency' => $currency,
                'tax_id' => null,
                'tax_rate' => number_format(0, 2, '.', ''),
                'tax_amount' => number_format(0, 2, '.', ''),
                'total' => $total,
                'notes' => $notes !== '' ? $notes : null,
                'public_uuid' => $publicUuid,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);

            $stats['invoices_inserted']++;
            $invoiceMap[$legacyId] = (int) $invoiceId;
        }
    }

    private function importInvoiceItems(
        string $filePath,
        int $companyId,
        bool $dryRun,
        array $invoiceMap,
        array &$stats
    ): void {
        $now = date('Y-m-d H:i:s');

        foreach ($this->iterateInsertRows($filePath, 'grw_document_items') as $row) {
            if ((int) ($row['company_id'] ?? 0) !== $companyId) {
                continue;
            }

            if (!empty($row['deleted_at'] ?? '')) {
                continue;
            }

            $type = strtolower(trim((string) ($row['type'] ?? '')));
            if ($type !== 'invoice') {
                continue;
            }

            $documentId = (int) ($row['document_id'] ?? 0);
            if ($documentId <= 0 || !array_key_exists($documentId, $invoiceMap)) {
                $stats['invoice_items_skipped']++;
                continue;
            }

            $invoiceId = (int) $invoiceMap[$documentId];
            if ($invoiceId <= 0 && ! $dryRun) {
                $stats['invoice_items_skipped']++;
                continue;
            }

            $name = trim((string) ($row['name'] ?? ''));
            $description = trim((string) ($row['description'] ?? ''));
            if ($name !== '' && $description !== '') {
                $description = $name . ' - ' . $description;
            } elseif ($name !== '') {
                $description = $name;
            } elseif ($description === '') {
                $description = 'Invoice item ' . (string) ($row['id'] ?? '');
            }

            if (strlen($description) > 255) {
                $description = substr($description, 0, 255);
            }

            $qty = (int) round((float) ($row['quantity'] ?? 1));
            if ($qty < 1) {
                $qty = 1;
            }

            $unitPrice = number_format((float) ($row['price'] ?? 0), 2, '.', '');
            $subtotal = number_format((float) ($row['total'] ?? 0), 2, '.', '');
            $createdAt = $this->normalizeTimestamp($row['created_at'] ?? null, $now);
            $updatedAt = $this->normalizeTimestamp($row['updated_at'] ?? null, $createdAt);

            if ($invoiceId > 0 && $this->invoiceItemExists($invoiceId, $description, $qty, $unitPrice, $subtotal)) {
                $stats['invoice_items_skipped']++;
                continue;
            }

            if ($dryRun) {
                $stats['invoice_items_inserted']++;
                continue;
            }

            DBML::table('invoice_items')->insert([
                'invoice_id' => $invoiceId,
                'description' => $description,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);

            $stats['invoice_items_inserted']++;
        }
    }

    private function resolveInvoiceCustomerId(
        array $row,
        array &$customerMap,
        bool $dryRun,
        array &$stats,
        string $fallbackTimestamp
    ): ?int {
        $contactId = (int) ($row['contact_id'] ?? 0);
        if ($contactId > 0 && isset($customerMap[$contactId]) && $customerMap[$contactId] > 0) {
            return $customerMap[$contactId];
        }

        $email = strtolower(trim((string) ($row['contact_email'] ?? '')));
        if ($email !== '') {
            $existing = DBML::table('customers')->select('id')->where('email', $email)->first();
            if (!empty($existing['id'])) {
                $customerId = (int) $existing['id'];
                if ($contactId > 0) {
                    $customerMap[$contactId] = $customerId;
                }
                return $customerId;
            }
        }

        $name = trim((string) ($row['contact_name'] ?? ''));
        if ($name === '') {
            $name = 'Customer ' . (string) ($row['document_number'] ?? $row['id'] ?? '');
        }

        if ($email === '') {
            $email = 'customer-doc-' . (string) ($row['id'] ?? 'unknown') . '@import.invalid';
            $stats['placeholders_used']++;
        }

        $address = $this->formatDocumentAddress($row);
        $createdAt = $this->normalizeTimestamp($row['created_at'] ?? null, $fallbackTimestamp);
        $updatedAt = $this->normalizeTimestamp($row['updated_at'] ?? null, $createdAt);

        if ($dryRun) {
            $stats['customers_inserted']++;
            return 0;
        }

        $customerId = DBML::table('customers')->insert([
            'name' => $name,
            'email' => $email,
            'address' => $address !== '' ? $address : null,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);

        $stats['customers_inserted']++;

        if ($contactId > 0) {
            $customerMap[$contactId] = (int) $customerId;
        }

        return (int) $customerId;
    }

    private function mapInvoiceStatus(string $status): string
    {
        $status = strtolower(trim($status));
        if (in_array($status, ['paid', 'partial', 'partial_paid', 'partial-paid'], true)) {
            return 'paid';
        }
        if (in_array($status, ['sent', 'viewed', 'overdue'], true)) {
            return 'sent';
        }

        return 'draft';
    }

    private function resolveInvoiceNotes(array $row): string
    {
        $notes = trim((string) ($row['notes'] ?? ''));
        $footer = trim((string) ($row['footer'] ?? ''));

        if ($notes !== '' && $footer !== '') {
            return $notes . "\n\n" . $footer;
        }

        return $notes !== '' ? $notes : $footer;
    }

    private function formatDocumentAddress(array $row): string
    {
        $lines = [];
        $address = trim((string) ($row['contact_address'] ?? ''));
        if ($address !== '') {
            $lines[] = $address;
        }

        $city = trim((string) ($row['contact_city'] ?? ''));
        $state = trim((string) ($row['contact_state'] ?? ''));
        $zip = trim((string) ($row['contact_zip_code'] ?? ''));

        $cityLineParts = array_filter([$city, $state, $zip], static fn (string $part): bool => $part !== '');
        if (!empty($cityLineParts)) {
            $lines[] = implode(', ', $cityLineParts);
        }

        $country = trim((string) ($row['contact_country'] ?? ''));
        if ($country !== '') {
            $lines[] = $country;
        }

        return implode("\n", $lines);
    }

    private function normalizeNullableDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return null;
        }

        return substr($value, 0, 10);
    }

    private function invoiceNumberExists(string $invoiceNo): bool
    {
        return $this->findInvoiceIdByNumber($invoiceNo) !== null;
    }

    private function findInvoiceIdByNumber(string $invoiceNo): ?int
    {
        $existing = DBML::table('invoices')->select('id')->where('invoice_no', $invoiceNo)->first();
        if (!empty($existing['id'])) {
            return (int) $existing['id'];
        }

        return null;
    }

    private function invoiceItemExists(
        int $invoiceId,
        string $description,
        int $qty,
        string $unitPrice,
        string $subtotal
    ): bool {
        $existing = DBML::table('invoice_items')
            ->select('id')
            ->where('invoice_id', $invoiceId)
            ->where('description', $description)
            ->where('qty', $qty)
            ->where('unit_price', $unitPrice)
            ->where('subtotal', $subtotal)
            ->first();

        return !empty($existing['id']);
    }

    private function generatePublicUuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }

    /**
     * @return \Generator<array<string, mixed>>
     */
    private function iterateInsertRows(string $filePath, string $tableName): \Generator
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return;
        }

        $needle = 'INSERT INTO `' . $tableName . '`';
        $buffer = '';
        $capturing = false;

        while (($line = fgets($handle)) !== false) {
            if (! $capturing) {
                if (strpos($line, $needle) !== false) {
                    $capturing = true;
                    $buffer = $line;
                    if (strpos($line, ';') !== false) {
                        foreach ($this->parseInsertStatement($buffer, $tableName) as $row) {
                            yield $row;
                        }
                        $buffer = '';
                        $capturing = false;
                    }
                }
                continue;
            }

            $buffer .= $line;
            if (strpos($line, ';') !== false) {
                foreach ($this->parseInsertStatement($buffer, $tableName) as $row) {
                    yield $row;
                }
                $buffer = '';
                $capturing = false;
            }
        }

        fclose($handle);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseInsertStatement(string $sql, string $tableName): array
    {
        $pattern = '/INSERT\\s+INTO\\s+`' . preg_quote($tableName, '/') . '`\\s*\\((.*?)\\)\\s*VALUES\\s*(.*);/si';
        if (! preg_match($pattern, $sql, $matches)) {
            return [];
        }

        $columnsRaw = $matches[1] ?? '';
        $valuesRaw = $matches[2] ?? '';

        $columns = array_map(static function (string $column): string {
            return trim($column, " \t\n\r\0\x0B`");
        }, explode(',', $columnsRaw));

        $rows = [];
        foreach ($this->splitInsertRows($valuesRaw) as $rowValues) {
            $values = $this->splitRowValues($rowValues);
            if (count($values) !== count($columns)) {
                continue;
            }
            $rows[] = array_combine($columns, $values);
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    private function splitInsertRows(string $values): array
    {
        $rows = [];
        $length = strlen($values);
        $inString = false;
        $escape = false;
        $depth = 0;
        $start = null;

        for ($i = 0; $i < $length; $i++) {
            $char = $values[$i];

            if ($inString) {
                if ($escape) {
                    $escape = false;
                    continue;
                }
                if ($char === '\\') {
                    $escape = true;
                    continue;
                }
                if ($char === "'") {
                    $inString = false;
                }
                continue;
            }

            if ($char === "'") {
                $inString = true;
                continue;
            }

            if ($char === '(') {
                if ($depth === 0) {
                    $start = $i + 1;
                }
                $depth++;
                continue;
            }

            if ($char === ')') {
                $depth--;
                if ($depth === 0 && $start !== null) {
                    $rows[] = substr($values, $start, $i - $start);
                    $start = null;
                }
            }
        }

        return $rows;
    }

    /**
     * @return array<int, mixed>
     */
    private function splitRowValues(string $row): array
    {
        $values = [];
        $length = strlen($row);
        $inString = false;
        $escape = false;
        $buffer = '';

        for ($i = 0; $i < $length; $i++) {
            $char = $row[$i];

            if ($inString) {
                $buffer .= $char;
                if ($escape) {
                    $escape = false;
                    continue;
                }
                if ($char === '\\') {
                    $escape = true;
                    continue;
                }
                if ($char === "'") {
                    $inString = false;
                }
                continue;
            }

            if ($char === "'") {
                $inString = true;
                $buffer .= $char;
                continue;
            }

            if ($char === ',') {
                $values[] = $this->normalizeSqlValue($buffer);
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        if ($buffer !== '') {
            $values[] = $this->normalizeSqlValue($buffer);
        }

        return $values;
    }

    private function normalizeSqlValue(string $value): mixed
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (strtoupper($value) === 'NULL') {
            return null;
        }

        if ($value[0] === "'" && substr($value, -1) === "'") {
            $value = substr($value, 1, -1);
            return stripcslashes($value);
        }

        return $value;
    }

    private function formatLegacyAddress(array $row): string
    {
        $lines = [];
        $address = trim((string) ($row['address'] ?? ''));
        if ($address !== '') {
            $lines[] = $address;
        }

        $city = trim((string) ($row['city'] ?? ''));
        $state = trim((string) ($row['state'] ?? ''));
        $zip = trim((string) ($row['zip_code'] ?? ''));

        $cityLineParts = array_filter([$city, $state, $zip], static fn (string $part): bool => $part !== '');
        if (!empty($cityLineParts)) {
            $lines[] = implode(', ', $cityLineParts);
        }

        $country = trim((string) ($row['country'] ?? ''));
        if ($country !== '') {
            $lines[] = $country;
        }

        return implode("\n", $lines);
    }

    private function normalizeTimestamp(?string $value, string $fallback): string
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return $fallback;
        }

        return $value;
    }

    private function normalizeDate(string $value, string $fallback): string
    {
        $value = trim($value);
        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return $fallback;
        }

        return substr($value, 0, 10);
    }

    private function isAbsolutePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if ($path[0] === '/' || $path[0] === '\\\\') {
            return true;
        }

        return (bool) preg_match('/^[A-Za-z]:\\\\/', $path);
    }

    private function writeLine(string $message): void
    {
        echo $message . PHP_EOL;
    }
}
