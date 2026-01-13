<?php

namespace App\Controllers;

use App\Models\Admin;
use App\Models\Setting;
use App\Services\ViewData;
use Mail;
use Zero\Lib\Auth\Auth;
use Zero\Lib\DB\DBML;
use Zero\Lib\Http\Request;
use Zero\Lib\Http\Response;
use Zero\Lib\Session;
use Zero\Lib\Validation\ValidationException;

class CustomersController
{
    public function index()
    {
        $layout = ViewData::appLayout();
        $status = Session::get('customer_status');

        Session::remove('customer_status');

        $today = date('Y-m-d');
        $overdueRow = DBML::table('invoices')
            ->select(DBML::raw('COALESCE(SUM(total), 0) as total'))
            ->where('status', 'sent')
            ->where('due_date', '<', $today)
            ->first();
        $openRow = DBML::table('invoices')
            ->select(DBML::raw('COALESCE(SUM(total), 0) as total'))
            ->where('status', 'sent')
            ->where(function ($query) use ($today) {
                $query->where('due_date', '>=', $today)
                    ->orWhereNull('due_date');
            })
            ->first();
        $draftRow = DBML::table('invoices')
            ->select(DBML::raw('COALESCE(SUM(total), 0) as total'))
            ->where('status', 'draft')
            ->first();

        $totals = [
            'overdue' => (float) ($overdueRow['total'] ?? 0),
            'open' => (float) ($openRow['total'] ?? 0),
            'draft' => (float) ($draftRow['total'] ?? 0),
        ];

        $customers = DBML::table('customers as c')
            ->select(
                'c.id',
                'c.name',
                'c.email',
                'c.address',
                DBML::raw('COALESCE(COUNT(i.id), 0) as invoice_count'),
                DBML::raw('COALESCE(SUM(i.total), 0) as lifetime_value'),
                DBML::raw('COALESCE(SUM(i.total), 0) as total_invoices'),
                DBML::raw("COALESCE(SUM(CASE WHEN i.status = 'paid' THEN i.total ELSE 0 END), 0) as total_paid"),
                DBML::raw("COALESCE(SUM(CASE WHEN i.status = 'sent' AND i.due_date IS NOT NULL AND i.due_date < '{$today}' THEN i.total ELSE 0 END), 0) as total_overdue")
            )
            ->leftJoin('invoices as i', 'i.customer_id', '=', 'c.id')
            ->groupBy('c.id', 'c.name', 'c.email', 'c.address')
            ->orderBy('c.name')
            ->get();

        $defaultCurrency = Setting::getValue('default_currency');
        foreach ($customers as $index => $customer) {
            $search = strtolower(trim((string) ($customer['name'] ?? '') . ' ' . (string) ($customer['email'] ?? '')));
            $customers[$index]['search'] = $search;
            $customers[$index]['total_label'] = Setting::formatMoney((float) ($customer['total_invoices'] ?? 0), $defaultCurrency);
            $customers[$index]['paid_label'] = Setting::formatMoney((float) ($customer['total_paid'] ?? 0), $defaultCurrency);
            $customers[$index]['overdue_label'] = Setting::formatMoney((float) ($customer['total_overdue'] ?? 0), $defaultCurrency);
        }

        return view('customers/index', array_merge($layout, [
            'customers' => $customers,
            'status' => $status,
            'totals' => $totals,
        ]));
    }

    public function create()
    {
        $layout = ViewData::appLayout();
        $errors = Session::get('customer_errors') ?? [];
        $old = Session::get('customer_old') ?? [];

        Session::remove('customer_errors');
        Session::remove('customer_old');

        return view('customers/create', array_merge($layout, [
            'errors' => $errors,
            'old' => $old,
        ]));
    }

    public function show(int $customer)
    {
        $layout = ViewData::appLayout();
        $emailStatus = Session::get('customer_email_status');
        $emailErrors = Session::get('customer_email_errors') ?? [];
        $emailOld = Session::get('customer_email_old') ?? [];

        Session::remove('customer_email_status');
        Session::remove('customer_email_errors');
        Session::remove('customer_email_old');

        $record = DBML::table('customers')
            ->select('id', 'name', 'email', 'address')
            ->where('id', $customer)
            ->first();

        if ($record === null) {
            Session::set('customer_status', 'Customer not found.');
            return Response::redirect('/customers');
        }

        $invoices = DBML::table('invoices as i')
            ->select('i.id', 'i.invoice_no', 'i.date', 'i.due_date', 'i.status', 'i.currency', 'i.total')
            ->where('i.customer_id', $customer)
            ->orderByDesc('i.date')
            ->orderByDesc('i.id')
            ->get();

        $today = date('Y-m-d');
        $totals = [
            'overdue' => 0.0,
            'open' => 0.0,
            'paid' => 0.0,
        ];

        foreach ($invoices as $invoice) {
            $status = strtolower((string) ($invoice['status'] ?? 'draft'));
            $amount = (float) ($invoice['total'] ?? 0);

            if ($status === 'paid') {
                $totals['paid'] += $amount;
                continue;
            }

            $dueDate = (string) ($invoice['due_date'] ?? '');
            if ($dueDate !== '' && $dueDate < $today) {
                $totals['overdue'] += $amount;
                continue;
            }

            $totals['open'] += $amount;
        }

        $customerName = (string) ($record['name'] ?? 'Customer');
        $parts = preg_split('/\s+/', trim($customerName));
        $initials = '';
        foreach (array_filter($parts) as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
            if (strlen($initials) >= 2) {
                break;
            }
        }
        $initials = $initials !== '' ? $initials : 'CL';

        $defaultCurrency = Setting::getValue('default_currency');
        $totalsLabels = [
            'overdue' => Setting::formatMoney((float) ($totals['overdue'] ?? 0), $defaultCurrency),
            'open' => Setting::formatMoney((float) ($totals['open'] ?? 0), $defaultCurrency),
            'paid' => Setting::formatMoney((float) ($totals['paid'] ?? 0), $defaultCurrency),
        ];

        $statusColors = [
            'paid' => 'bg-emerald-100 text-emerald-700',
            'sent' => 'bg-sky-100 text-sky-700',
            'draft' => 'bg-amber-100 text-amber-700',
        ];
        $invoiceRows = [];
        foreach ($invoices as $invoice) {
            $status = strtolower((string) ($invoice['status'] ?? 'draft'));
            $invoiceRows[] = [
                'id' => $invoice['id'] ?? null,
                'invoice_no' => $invoice['invoice_no'] ?? '—',
                'date' => $invoice['date'] ?? '—',
                'due_date' => $invoice['due_date'] ?? '—',
                'status_label' => ucfirst($status),
                'badge_class' => $statusColors[$status] ?? 'bg-stone-100 text-stone-700',
                'total_label' => Setting::formatMoney((float) ($invoice['total'] ?? 0), $invoice['currency'] ?? null),
            ];
        }

        $adminEmail = $this->resolveAdminEmail();
        $brandName = (string) ($layout['brandName'] ?? 'Invoice App');
        $defaultSubject = 'Message from ' . $brandName;
        $defaultMessage = "Hi {$customerName},\n\nI wanted to reach out regarding your account.\n\nThanks,\n{$brandName}";
        $emailSubject = $emailOld['subject'] ?? $defaultSubject;
        $emailMessage = $emailOld['message'] ?? $defaultMessage;
        $autoOpenEmailModal = !empty($emailErrors);
        $ccAdminDefault = $adminEmail !== '';
        $currentUserEmail = '';
        $currentUser = $layout['currentUser'] ?? null;
        if ($currentUser && isset($currentUser->email)) {
            $currentUserEmail = trim((string) $currentUser->email);
        }

        return view('customers/show', array_merge($layout, [
            'customer' => $record,
            'customerName' => $customerName,
            'initials' => $initials,
            'invoiceRows' => $invoiceRows,
            'totalsLabels' => $totalsLabels,
            'emailStatus' => $emailStatus,
            'emailErrors' => $emailErrors,
            'emailOld' => $emailOld,
            'emailSubject' => $emailSubject,
            'emailMessage' => $emailMessage,
            'autoOpenEmailModal' => $autoOpenEmailModal,
            'ccAdminDefault' => $ccAdminDefault,
            'currentUserEmail' => $currentUserEmail,
            'adminEmail' => $adminEmail,
        ]));
    }

    public function edit(int $customer)
    {
        $layout = ViewData::appLayout();
        $errors = Session::get('customer_edit_errors') ?? [];
        $old = Session::get('customer_edit_old') ?? [];

        Session::remove('customer_edit_errors');
        Session::remove('customer_edit_old');

        $record = DBML::table('customers')
            ->select('id', 'name', 'email', 'address')
            ->where('id', $customer)
            ->first();

        if ($record === null) {
            Session::set('customer_status', 'Customer not found.');
            return Response::redirect('/customers');
        }

        $values = [
            'name' => (string) ($record['name'] ?? ''),
            'email' => (string) ($record['email'] ?? ''),
            'address' => (string) ($record['address'] ?? ''),
        ];

        if ($old !== []) {
            $values = array_merge($values, $old);
        }

        return view('customers/edit', array_merge($layout, [
            'customer' => $record,
            'errors' => $errors,
            'values' => $values,
        ]));
    }

    public function store(Request $request): Response
    {
        Session::remove('customer_status');
        Session::remove('customer_errors');
        Session::remove('customer_old');

        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'min:2'],
                'email' => ['required', 'email', 'unique:customers,email'],
                'address' => ['string'],
            ]);
        } catch (ValidationException $exception) {
            $messages = array_map(
                static fn (array $errors): string => (string) ($errors[0] ?? ''),
                $exception->errors()
            );

            Session::set('customer_errors', $messages);
            Session::set('customer_old', $request->all());

            return Response::redirect('/customers/create');
        }

        $address = trim((string) ($data['address'] ?? ''));
        $timestamp = date('Y-m-d H:i:s');

        DBML::table('customers')->insert([
            'name' => trim((string) $data['name']),
            'email' => strtolower(trim((string) $data['email'])),
            'address' => $address === '' ? null : $address,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        Session::set('customer_status', 'Customer added successfully.');

        return Response::redirect('/customers');
    }

    public function update(int $customer, Request $request): Response
    {
        Session::remove('customer_status');
        Session::remove('customer_edit_errors');
        Session::remove('customer_edit_old');

        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'min:2'],
                'email' => ['required', 'email', 'unique:customers,email,' . $customer . ',id'],
                'address' => ['string'],
            ]);
        } catch (ValidationException $exception) {
            $messages = array_map(
                static fn (array $errors): string => (string) ($errors[0] ?? ''),
                $exception->errors()
            );

            Session::set('customer_edit_errors', $messages);
            Session::set('customer_edit_old', $request->all());

            return Response::redirect('/customers/' . $customer . '/edit');
        }

        $address = trim((string) ($data['address'] ?? ''));
        $timestamp = date('Y-m-d H:i:s');

        DBML::table('customers')->where('id', $customer)->update([
            'name' => trim((string) $data['name']),
            'email' => strtolower(trim((string) $data['email'])),
            'address' => $address === '' ? null : $address,
            'updated_at' => $timestamp,
        ]);

        Session::set('customer_status', 'Customer updated successfully.');

        return Response::redirect('/customers');
    }

    public function sendEmail(int $customer, Request $request): Response
    {
        Session::remove('customer_email_status');
        Session::remove('customer_email_errors');
        Session::remove('customer_email_old');

        $record = DBML::table('customers')
            ->select('id', 'name', 'email')
            ->where('id', $customer)
            ->first();

        if ($record === null) {
            Session::set('customer_email_errors', ['email' => 'Customer not found.']);
            return Response::redirect('/customers');
        }

        $customerEmail = trim((string) ($record['email'] ?? ''));
        if ($customerEmail === '') {
            Session::set('customer_email_errors', ['email' => 'Customer does not have an email address.']);
            return Response::redirect('/customers/' . $customer);
        }

        try {
            $data = $request->validate([
                'subject' => ['required', 'string', 'min:3', 'max:150'],
                'message' => ['required', 'string', 'min:3'],
                'cc_admin' => ['boolean'],
                'cc_myself' => ['boolean'],
            ]);
        } catch (ValidationException $exception) {
            $messages = array_map(
                static fn (array $errors): string => (string) ($errors[0] ?? ''),
                $exception->errors()
            );

            Session::set('customer_email_errors', $messages);
            Session::set('customer_email_old', $request->all());

            return Response::redirect('/customers/' . $customer);
        }

        $subject = trim((string) $data['subject']);
        $message = trim((string) $data['message']);
        $adminEmail = $this->resolveAdminEmail();
        $ccAdmin = (bool) ($data['cc_admin'] ?? false);
        $ccMyself = (bool) ($data['cc_myself'] ?? false);
        $currentUserEmail = $this->resolveCurrentUserEmail();

        $safeBody = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
        $html = '<div style="font-family: Arial, sans-serif; line-height: 1.6;">' . $safeBody . '</div>';

        Mail::send(function ($mail) use ($customerEmail, $record, $subject, $html, $ccAdmin, $adminEmail, $ccMyself, $currentUserEmail) {
            $mail->to($customerEmail, (string) ($record['name'] ?? 'Customer'))
                ->subject($subject)
                ->html($html);

            if ($ccAdmin && $adminEmail !== '' && $adminEmail !== $customerEmail) {
                $mail->cc($adminEmail);
            }

            if ($ccMyself && $currentUserEmail !== '' && $currentUserEmail !== $adminEmail && $currentUserEmail !== $customerEmail) {
                $mail->cc($currentUserEmail);
            }
        });

        Session::set('customer_email_status', 'Email sent successfully.');

        return Response::redirect('/customers/' . $customer);
    }

    private function resolveAdminEmail(): string
    {
        $user = Auth::user();
        if (! $user || empty($user->email)) {
            return '';
        }

        $email = strtolower((string) $user->email);
        if ($email === '') {
            return '';
        }

        if (Admin::query()->where('email', $email)->exists()) {
            return $email;
        }

        return '';
    }

    private function resolveCurrentUserEmail(): string
    {
        $user = Auth::user();
        if (! $user || empty($user->email)) {
            return '';
        }

        $email = trim((string) $user->email);
        if ($email === '') {
            return '';
        }

        return $email;
    }
}
