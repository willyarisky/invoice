<?php

namespace App\Controllers;

use App\Models\Setting;
use App\Services\ViewData;
use Zero\Lib\DB\DBML;
use Zero\Lib\Http\Request;
use Zero\Lib\Http\Response;
use Zero\Lib\Session;
use Zero\Lib\Validation\ValidationException;

class VendorsController
{
    public function index()
    {
        $layout = ViewData::appLayout();
        $status = Session::get('vendor_status');

        Session::remove('vendor_status');

        $vendors = DBML::table('vendors')
            ->select('id', 'name', 'email', 'phone', 'address')
            ->orderBy('name')
            ->get();

        return view('vendors/index', array_merge($layout, [
            'vendors' => $vendors,
            'vendorCount' => count($vendors),
            'status' => $status,
        ]));
    }

    public function create()
    {
        $layout = ViewData::appLayout();
        $errors = Session::get('vendor_errors') ?? [];
        $old = Session::get('vendor_old') ?? [];

        Session::remove('vendor_errors');
        Session::remove('vendor_old');

        return view('vendors/create', array_merge($layout, [
            'errors' => $errors,
            'old' => $old,
        ]));
    }

    public function store(Request $request): Response
    {
        Session::remove('vendor_status');
        Session::remove('vendor_errors');
        Session::remove('vendor_old');

        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'min:2'],
                'email' => ['string'],
                'phone' => ['string', 'max:40'],
                'address' => ['string'],
            ]);
        } catch (ValidationException $exception) {
            $messages = array_map(
                static fn (array $errors): string => (string) ($errors[0] ?? ''),
                $exception->errors()
            );

            Session::set('vendor_errors', $messages);
            Session::set('vendor_old', $request->all());

            return Response::redirect('/vendors/create');
        }

        $email = strtolower(trim((string) ($data['email'] ?? '')));

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::set('vendor_errors', ['email' => 'Please enter a valid email address.']);
            Session::set('vendor_old', $request->all());

            return Response::redirect('/vendors/create');
        }

        $timestamp = date('Y-m-d H:i:s');
        $phone = trim((string) ($data['phone'] ?? ''));
        $address = trim((string) ($data['address'] ?? ''));

        DBML::table('vendors')->insert([
            'name' => trim((string) $data['name']),
            'email' => $email === '' ? null : $email,
            'phone' => $phone === '' ? null : $phone,
            'address' => $address === '' ? null : $address,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        Session::set('vendor_status', 'Vendor added successfully.');

        return Response::redirect('/vendors');
    }

    public function show(int $vendor)
    {
        $layout = ViewData::appLayout();
        $status = Session::get('vendor_status');

        Session::remove('vendor_status');

        $record = DBML::table('vendors')
            ->select('id', 'name', 'email', 'phone', 'address')
            ->where('id', $vendor)
            ->first();

        if ($record === null) {
            Session::set('vendor_status', 'Vendor not found.');
            return Response::redirect('/vendors');
        }

        $transactions = DBML::table('transactions as t')
            ->select(
                't.id',
                't.type',
                't.amount',
                't.currency',
                't.date',
                't.description',
                't.source',
                't.invoice_id',
                't.category_id',
                'c.name as category_name',
                'i.invoice_no'
            )
            ->leftJoin('categories as c', 'c.id', '=', 't.category_id')
            ->leftJoin('invoices as i', 'i.id', '=', 't.invoice_id')
            ->where('t.vendor_id', $vendor)
            ->orderByDesc('t.date')
            ->orderByDesc('t.id')
            ->get();

        $transactionCount = count($transactions);
        $totalSpent = 0.0;
        $transactionRows = [];
        foreach ($transactions as $transaction) {
            $type = strtolower((string) ($transaction['type'] ?? 'expense'));
            $amount = (float) ($transaction['amount'] ?? 0);
            $currency = $transaction['currency'] ?? null;
            $amountLabel = Setting::formatMoney($amount, $currency);
            if ($type === 'expense') {
                $amountLabel = '-' . $amountLabel;
            }
            $source = $transaction['source'] ?? '';
            $hasInvoice = $source === 'invoice' && !empty($transaction['invoice_id']);
            if ($type === 'expense') {
                $totalSpent += $amount;
            }

            $transactionRows[] = [
                'id' => $transaction['id'] ?? null,
                'date' => $transaction['date'] ?? '',
                'type' => $type,
                'type_label' => ucfirst($type),
                'type_badge_class' => $type === 'expense' ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-700',
                'category_name' => $transaction['category_name'] ?? '—',
                'reference_label' => $hasInvoice
                    ? ('Invoice ' . ($transaction['invoice_no'] ?? ''))
                    : ($transaction['description'] ?? 'Manual entry'),
                'reference_url' => $hasInvoice ? route('invoices.show', ['invoice' => $transaction['invoice_id']]) : '',
                'amount_label' => $amountLabel,
                'amount_class' => $type === 'expense' ? 'text-rose-600' : 'text-emerald-600',
            ];
        }

        return view('vendors/show', array_merge($layout, [
            'vendor' => $record,
            'transactions' => $transactionRows,
            'transactionCount' => $transactionCount,
            'totalSpentLabel' => Setting::formatMoney($totalSpent),
            'status' => $status,
        ]));
    }
}
