<?php

namespace App\Controllers;

use DateTime;
use App\Models\Setting;
use App\Services\ViewData;
use Zero\Lib\DB\DBML;
use Zero\Lib\Http\Request;
use Zero\Lib\Http\Response;
use Zero\Lib\Session;
use Zero\Lib\Validation\ValidationException;

class TransactionsController
{
    /**
     * @var string[]
     */
    private array $allowedTypes = ['income', 'expense'];

    public function index(Request $request)
    {
        $layout = ViewData::appLayout();
        $status = Session::get('transaction_status');

        Session::remove('transaction_status');

        $invoiceId = (int) $request->input('invoice_id', 0);
        $query = DBML::table('transactions as t')
            ->select(
                't.id',
                't.type',
                't.amount',
                't.currency',
                't.date',
                't.description',
                't.source',
                't.vendor_id',
                't.invoice_id',
                'v.name as vendor_name',
                'i.invoice_no',
                'c.name as client_name'
            )
            ->leftJoin('vendors as v', 'v.id', '=', 't.vendor_id')
            ->leftJoin('invoices as i', 'i.id', '=', 't.invoice_id')
            ->leftJoin('clients as c', 'c.id', '=', 'i.client_id')
            ->orderByDesc('t.date')
            ->orderByDesc('t.id');

        if ($invoiceId > 0) {
            $query->where('t.invoice_id', $invoiceId);
        }

        $transactions = $query->get();

        $defaultCurrency = Setting::getValue('default_currency');
        $transactionRows = [];
        foreach ($transactions as $transaction) {
            $type = strtolower((string) ($transaction['type'] ?? 'expense'));
            $amount = (float) ($transaction['amount'] ?? 0);
            $currency = $transaction['currency'] ?? $defaultCurrency;
            $amountLabel = Setting::formatMoney($amount, $currency);
            if ($type === 'expense') {
                $amountLabel = '-' . $amountLabel;
            }
            $transactionRows[] = [
                'id' => $transaction['id'] ?? null,
                'date' => $transaction['date'] ?? '',
                'type' => $type,
                'type_label' => ucfirst($type),
                'type_badge_class' => $type === 'expense' ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-700',
                'source' => $transaction['source'] ?? 'manual',
                'invoice_id' => $transaction['invoice_id'] ?? null,
                'invoice_no' => $transaction['invoice_no'] ?? '',
                'client_name' => $transaction['client_name'] ?? '—',
                'description' => $transaction['description'] ?? '',
                'vendor_name' => $transaction['vendor_name'] ?? '—',
                'amount_label' => $amountLabel,
                'amount_class' => $type === 'expense' ? 'text-rose-600' : 'text-emerald-600',
            ];
        }

        return view('transactions/index', array_merge($layout, [
            'transactions' => $transactionRows,
            'transactionCount' => count($transactionRows),
            'status' => $status,
        ]));
    }

    public function create()
    {
        $layout = ViewData::appLayout();
        $errors = Session::get('transaction_errors') ?? [];
        $old = Session::get('transaction_old') ?? [];

        Session::remove('transaction_errors');
        Session::remove('transaction_old');

        $vendors = DBML::table('vendors')->select('id', 'name')->orderBy('name')->get();
        $categories = DBML::table('categories')->select('id', 'name')->orderBy('name')->get();

        $currentType = $old['type'] ?? 'expense';
        $currentCurrency = $old['currency'] ?? Setting::getValue('default_currency');
        $currentDate = $old['date'] ?? date('Y-m-d');
        $currentVendor = $old['vendor_id'] ?? '';
        $currentCategory = $old['category_id'] ?? '';
        $currentAmount = $old['amount'] ?? '';
        $currentDescription = $old['description'] ?? '';

        return view('transactions/create', array_merge($layout, [
            'vendors' => $vendors,
            'categories' => $categories,
            'errors' => $errors,
            'currentType' => $currentType,
            'currentCurrency' => $currentCurrency,
            'currentDate' => $currentDate,
            'currentVendor' => $currentVendor,
            'currentCategory' => $currentCategory,
            'currentAmount' => $currentAmount,
            'currentDescription' => $currentDescription,
            'currencyOptions' => Setting::currencyOptions(),
            'typeOptions' => array_map(
                static fn (string $type): array => ['value' => $type, 'label' => ucfirst($type)],
                $this->allowedTypes
            ),
        ]));
    }

    public function show(int $transaction): Response
    {
        $layout = ViewData::appLayout();
        $status = Session::get('transaction_status');

        Session::remove('transaction_status');

        $record = DBML::table('transactions as t')
            ->select(
                't.id',
                't.type',
                't.amount',
                't.currency',
                't.date',
                't.description',
                't.source',
                't.vendor_id',
                't.invoice_id',
                't.category_id',
                'v.name as vendor_name',
                'cat.name as category_name',
                'i.invoice_no',
                'c.name as client_name'
            )
            ->leftJoin('vendors as v', 'v.id', '=', 't.vendor_id')
            ->leftJoin('categories as cat', 'cat.id', '=', 't.category_id')
            ->leftJoin('invoices as i', 'i.id', '=', 't.invoice_id')
            ->leftJoin('clients as c', 'c.id', '=', 'i.client_id')
            ->where('t.id', $transaction)
            ->first();

        if ($record === null) {
            Session::set('transaction_status', 'Transaction not found.');
            return Response::redirect('/transactions');
        }

        $type = strtolower((string) ($record['type'] ?? 'expense'));
        $amount = (float) ($record['amount'] ?? 0);
        $currency = $record['currency'] ?? Setting::getValue('default_currency');
        $amountLabel = Setting::formatMoney($amount, $currency);
        if ($type === 'expense') {
            $amountLabel = '-' . $amountLabel;
        }

        return view('transactions/show', array_merge($layout, [
            'transaction' => $record,
            'status' => $status,
            'type' => $type,
            'typeLabel' => ucfirst($type),
            'amountLabel' => $amountLabel,
            'amountClass' => $type === 'expense' ? 'text-rose-600' : 'text-emerald-600',
            'badgeClass' => $type === 'expense' ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-700',
            'currency' => $currency,
            'source' => $record['source'] ?? 'manual',
        ]));
    }

    public function edit(int $transaction): Response
    {
        $layout = ViewData::appLayout();
        $errors = Session::get('transaction_edit_errors') ?? [];
        $old = Session::get('transaction_edit_old') ?? [];

        Session::remove('transaction_edit_errors');
        Session::remove('transaction_edit_old');

        $record = DBML::table('transactions as t')
            ->select(
                't.id',
                't.type',
                't.amount',
                't.currency',
                't.date',
                't.description',
                't.source',
                't.vendor_id',
                't.invoice_id',
                't.category_id',
                'i.invoice_no'
            )
            ->leftJoin('invoices as i', 'i.id', '=', 't.invoice_id')
            ->where('t.id', $transaction)
            ->first();

        if ($record === null) {
            Session::set('transaction_status', 'Transaction not found.');
            return Response::redirect('/transactions');
        }

        $values = [
            'type' => strtolower((string) ($record['type'] ?? 'expense')),
            'amount' => number_format((float) ($record['amount'] ?? 0), 2, '.', ''),
            'currency' => strtoupper((string) ($record['currency'] ?? Setting::getValue('default_currency'))),
            'date' => (string) ($record['date'] ?? date('Y-m-d')),
            'vendor_id' => (string) ($record['vendor_id'] ?? ''),
            'category_id' => (string) ($record['category_id'] ?? ''),
            'description' => (string) ($record['description'] ?? ''),
        ];

        if ($old !== []) {
            $values = array_merge($values, $old);
        }

        $vendors = DBML::table('vendors')->select('id', 'name')->orderBy('name')->get();
        $categories = DBML::table('categories')->select('id', 'name')->orderBy('name')->get();

        $source = $record['source'] ?? 'manual';
        $linkedInvoice = $source === 'invoice' && !empty($record['invoice_id']);

        return view('transactions/edit', array_merge($layout, [
            'transaction' => $record,
            'vendors' => $vendors,
            'categories' => $categories,
            'errors' => $errors,
            'currentType' => $values['type'] ?? 'expense',
            'currentCurrency' => $values['currency'] ?? Setting::getValue('default_currency'),
            'currentDate' => $values['date'] ?? date('Y-m-d'),
            'currentVendor' => $values['vendor_id'] ?? '',
            'currentCategory' => $values['category_id'] ?? '',
            'currentAmount' => $values['amount'] ?? '',
            'currentDescription' => $values['description'] ?? '',
            'linkedInvoice' => $linkedInvoice,
            'currencyOptions' => Setting::currencyOptions(),
            'typeOptions' => array_map(
                static fn (string $type): array => ['value' => $type, 'label' => ucfirst($type)],
                $this->allowedTypes
            ),
        ]));
    }

    public function store(Request $request): Response
    {
        Session::remove('transaction_status');
        Session::remove('transaction_errors');
        Session::remove('transaction_old');

        try {
            $data = $request->validate([
                'type' => ['required', 'string'],
                'amount' => ['required', 'number', 'min:0.01'],
                'currency' => ['required', 'string', 'min:3', 'max:4'],
                'date' => ['required', 'string'],
                'vendor_id' => ['string'],
                'category_id' => ['string'],
                'description' => ['string', 'max:255'],
            ]);
        } catch (ValidationException $exception) {
            $messages = array_map(
                static fn (array $errors): string => (string) ($errors[0] ?? ''),
                $exception->errors()
            );

            Session::set('transaction_errors', $messages);
            Session::set('transaction_old', $request->all());

            return Response::redirect('/transactions/create');
        }

        $type = strtolower(trim((string) $data['type']));
        if (!in_array($type, $this->allowedTypes, true)) {
            Session::set('transaction_errors', ['type' => 'Choose income or expense.']);
            Session::set('transaction_old', $request->all());

            return Response::redirect('/transactions/create');
        }

        $currency = strtoupper(trim((string) $data['currency']));
        $allowedCurrencies = array_keys(Setting::currencyOptions());
        if ($allowedCurrencies === []) {
            $allowedCurrencies = ['USD'];
        }
        if (!in_array($currency, $allowedCurrencies, true)) {
            $currency = $allowedCurrencies[0];
        }

        $vendorValue = trim((string) ($data['vendor_id'] ?? ''));
        $vendorId = null;
        if ($vendorValue !== '') {
            if (!ctype_digit($vendorValue) || (int) $vendorValue <= 0) {
                Session::set('transaction_errors', ['vendor_id' => 'Select a valid vendor.']);
                Session::set('transaction_old', $request->all());

                return Response::redirect('/transactions/create');
            }
            $vendorId = (int) $vendorValue;
        }
        if ($vendorId !== null && !DBML::table('vendors')->where('id', $vendorId)->exists()) {
            Session::set('transaction_errors', ['vendor_id' => 'Selected vendor could not be found.']);
            Session::set('transaction_old', $request->all());

            return Response::redirect('/transactions/create');
        }

        $categoryValue = trim((string) ($data['category_id'] ?? ''));
        $categoryId = null;
        if ($categoryValue !== '') {
            if (!ctype_digit($categoryValue) || (int) $categoryValue <= 0) {
                Session::set('transaction_errors', ['category_id' => 'Select a valid category.']);
                Session::set('transaction_old', $request->all());

                return Response::redirect('/transactions/create');
            }
            $categoryId = (int) $categoryValue;
        }
        if ($categoryId !== null && !DBML::table('categories')->where('id', $categoryId)->exists()) {
            Session::set('transaction_errors', ['category_id' => 'Selected category could not be found.']);
            Session::set('transaction_old', $request->all());

            return Response::redirect('/transactions/create');
        }

        $amount = round((float) $data['amount'], 2);
        if ($amount <= 0) {
            Session::set('transaction_errors', ['amount' => 'Amount must be greater than zero.']);
            Session::set('transaction_old', $request->all());

            return Response::redirect('/transactions/create');
        }

        $transactionDate = $this->normaliseDate((string) $data['date']);
        $description = trim((string) ($data['description'] ?? ''));
        $timestamp = date('Y-m-d H:i:s');

        DBML::table('transactions')->insert([
            'type' => $type,
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => $currency,
            'date' => $transactionDate,
            'description' => $description === '' ? null : $description,
            'source' => 'manual',
            'vendor_id' => $vendorId,
            'category_id' => $categoryId,
            'invoice_id' => null,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        Session::set('transaction_status', 'Transaction added successfully.');

        return Response::redirect('/transactions');
    }

    public function update(int $transaction, Request $request): Response
    {
        Session::remove('transaction_status');
        Session::remove('transaction_edit_errors');
        Session::remove('transaction_edit_old');

        $record = DBML::table('transactions')
            ->select('id', 'source', 'invoice_id')
            ->where('id', $transaction)
            ->first();

        if ($record === null) {
            Session::set('transaction_status', 'Transaction not found.');
            return Response::redirect('/transactions');
        }

        try {
            $data = $request->validate([
                'type' => ['required', 'string'],
                'amount' => ['required', 'number', 'min:0.01'],
                'currency' => ['required', 'string', 'min:3', 'max:4'],
                'date' => ['required', 'string'],
                'vendor_id' => ['string'],
                'category_id' => ['string'],
                'description' => ['string', 'max:255'],
            ]);
        } catch (ValidationException $exception) {
            $messages = array_map(
                static fn (array $errors): string => (string) ($errors[0] ?? ''),
                $exception->errors()
            );

            Session::set('transaction_edit_errors', $messages);
            Session::set('transaction_edit_old', $request->all());

            return Response::redirect('/transactions/' . $transaction . '/edit');
        }

        $type = strtolower(trim((string) $data['type']));
        if (!in_array($type, $this->allowedTypes, true)) {
            Session::set('transaction_edit_errors', ['type' => 'Choose income or expense.']);
            Session::set('transaction_edit_old', $request->all());

            return Response::redirect('/transactions/' . $transaction . '/edit');
        }

        $currency = strtoupper(trim((string) $data['currency']));
        $allowedCurrencies = array_keys(Setting::currencyOptions());
        if ($allowedCurrencies === []) {
            $allowedCurrencies = ['USD'];
        }
        if (!in_array($currency, $allowedCurrencies, true)) {
            $currency = $allowedCurrencies[0];
        }

        $vendorValue = trim((string) ($data['vendor_id'] ?? ''));
        $vendorId = null;
        if ($vendorValue !== '') {
            if (!ctype_digit($vendorValue) || (int) $vendorValue <= 0) {
                Session::set('transaction_edit_errors', ['vendor_id' => 'Select a valid vendor.']);
                Session::set('transaction_edit_old', $request->all());

                return Response::redirect('/transactions/' . $transaction . '/edit');
            }
            $vendorId = (int) $vendorValue;
        }
        if ($vendorId !== null && !DBML::table('vendors')->where('id', $vendorId)->exists()) {
            Session::set('transaction_edit_errors', ['vendor_id' => 'Selected vendor could not be found.']);
            Session::set('transaction_edit_old', $request->all());

            return Response::redirect('/transactions/' . $transaction . '/edit');
        }

        $categoryValue = trim((string) ($data['category_id'] ?? ''));
        $categoryId = null;
        if ($categoryValue !== '') {
            if (!ctype_digit($categoryValue) || (int) $categoryValue <= 0) {
                Session::set('transaction_edit_errors', ['category_id' => 'Select a valid category.']);
                Session::set('transaction_edit_old', $request->all());

                return Response::redirect('/transactions/' . $transaction . '/edit');
            }
            $categoryId = (int) $categoryValue;
        }
        if ($categoryId !== null && !DBML::table('categories')->where('id', $categoryId)->exists()) {
            Session::set('transaction_edit_errors', ['category_id' => 'Selected category could not be found.']);
            Session::set('transaction_edit_old', $request->all());

            return Response::redirect('/transactions/' . $transaction . '/edit');
        }

        $amount = round((float) $data['amount'], 2);
        if ($amount <= 0) {
            Session::set('transaction_edit_errors', ['amount' => 'Amount must be greater than zero.']);
            Session::set('transaction_edit_old', $request->all());

            return Response::redirect('/transactions/' . $transaction . '/edit');
        }

        $transactionDate = $this->normaliseDate((string) $data['date']);
        $description = trim((string) ($data['description'] ?? ''));
        $timestamp = date('Y-m-d H:i:s');

        DBML::table('transactions')
            ->where('id', $transaction)
            ->update([
                'type' => $type,
                'amount' => number_format($amount, 2, '.', ''),
                'currency' => $currency,
                'date' => $transactionDate,
                'description' => $description === '' ? null : $description,
                'vendor_id' => $vendorId,
                'category_id' => $categoryId,
                'updated_at' => $timestamp,
            ]);

        Session::set('transaction_status', 'Transaction updated successfully.');

        return Response::redirect('/transactions/' . $transaction);
    }

    private function normaliseDate(?string $value, ?string $fallback = null): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $fallback ?? date('Y-m-d');
        }

        $date = DateTime::createFromFormat('Y-m-d', $value);

        if ($date === false) {
            return $fallback ?? date('Y-m-d');
        }

        return $date->format('Y-m-d');
    }
}
