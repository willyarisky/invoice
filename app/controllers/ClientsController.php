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

class ClientsController
{
    public function index()
    {
        $layout = ViewData::appLayout();
        $status = Session::get('client_status');

        Session::remove('client_status');

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

        $clients = DBML::table('clients as c')
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
            ->leftJoin('invoices as i', 'i.client_id', '=', 'c.id')
            ->groupBy('c.id', 'c.name', 'c.email', 'c.address')
            ->orderBy('c.name')
            ->get();

        $defaultCurrency = Setting::getValue('default_currency');
        foreach ($clients as $index => $client) {
            $search = strtolower(trim((string) ($client['name'] ?? '') . ' ' . (string) ($client['email'] ?? '')));
            $clients[$index]['search'] = $search;
            $clients[$index]['total_label'] = Setting::formatMoney((float) ($client['total_invoices'] ?? 0), $defaultCurrency);
            $clients[$index]['paid_label'] = Setting::formatMoney((float) ($client['total_paid'] ?? 0), $defaultCurrency);
            $clients[$index]['overdue_label'] = Setting::formatMoney((float) ($client['total_overdue'] ?? 0), $defaultCurrency);
        }

        return view('clients/index', array_merge($layout, [
            'clients' => $clients,
            'status' => $status,
            'totals' => $totals,
        ]));
    }

    public function create()
    {
        $layout = ViewData::appLayout();
        $errors = Session::get('client_errors') ?? [];
        $old = Session::get('client_old') ?? [];

        Session::remove('client_errors');
        Session::remove('client_old');

        return view('clients/create', array_merge($layout, [
            'errors' => $errors,
            'old' => $old,
        ]));
    }

    public function show(int $client)
    {
        $layout = ViewData::appLayout();
        $emailStatus = Session::get('client_email_status');
        $emailErrors = Session::get('client_email_errors') ?? [];
        $emailOld = Session::get('client_email_old') ?? [];

        Session::remove('client_email_status');
        Session::remove('client_email_errors');
        Session::remove('client_email_old');

        $record = DBML::table('clients')
            ->select('id', 'name', 'email', 'address')
            ->where('id', $client)
            ->first();

        if ($record === null) {
            Session::set('client_status', 'Client not found.');
            return Response::redirect('/clients');
        }

        $invoices = DBML::table('invoices as i')
            ->select('i.id', 'i.invoice_no', 'i.date', 'i.due_date', 'i.status', 'i.currency', 'i.total')
            ->where('i.client_id', $client)
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

        $clientName = (string) ($record['name'] ?? 'Client');
        $parts = preg_split('/\s+/', trim($clientName));
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
        $defaultMessage = "Hi {$clientName},\n\nI wanted to reach out regarding your account.\n\nThanks,\n{$brandName}";
        $emailSubject = $emailOld['subject'] ?? $defaultSubject;
        $emailMessage = $emailOld['message'] ?? $defaultMessage;
        $autoOpenEmailModal = !empty($emailErrors);
        $ccAdminDefault = $adminEmail !== '';
        $currentUserEmail = '';
        $currentUser = $layout['currentUser'] ?? null;
        if ($currentUser && isset($currentUser->email)) {
            $currentUserEmail = trim((string) $currentUser->email);
        }

        return view('clients/show', array_merge($layout, [
            'client' => $record,
            'clientName' => $clientName,
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

    public function edit(int $client)
    {
        $layout = ViewData::appLayout();
        $errors = Session::get('client_edit_errors') ?? [];
        $old = Session::get('client_edit_old') ?? [];

        Session::remove('client_edit_errors');
        Session::remove('client_edit_old');

        $record = DBML::table('clients')
            ->select('id', 'name', 'email', 'address')
            ->where('id', $client)
            ->first();

        if ($record === null) {
            Session::set('client_status', 'Client not found.');
            return Response::redirect('/clients');
        }

        $values = [
            'name' => (string) ($record['name'] ?? ''),
            'email' => (string) ($record['email'] ?? ''),
            'address' => (string) ($record['address'] ?? ''),
        ];

        if ($old !== []) {
            $values = array_merge($values, $old);
        }

        return view('clients/edit', array_merge($layout, [
            'client' => $record,
            'errors' => $errors,
            'values' => $values,
        ]));
    }

    public function store(Request $request): Response
    {
        Session::remove('client_status');
        Session::remove('client_errors');
        Session::remove('client_old');

        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'min:2'],
                'email' => ['required', 'email', 'unique:clients,email'],
                'address' => ['string'],
            ]);
        } catch (ValidationException $exception) {
            $messages = array_map(
                static fn (array $errors): string => (string) ($errors[0] ?? ''),
                $exception->errors()
            );

            Session::set('client_errors', $messages);
            Session::set('client_old', $request->all());

            return Response::redirect('/clients/create');
        }

        $address = trim((string) ($data['address'] ?? ''));
        $timestamp = date('Y-m-d H:i:s');

        DBML::table('clients')->insert([
            'name' => trim((string) $data['name']),
            'email' => strtolower(trim((string) $data['email'])),
            'address' => $address === '' ? null : $address,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        Session::set('client_status', 'Client added successfully.');

        return Response::redirect('/clients');
    }

    public function update(int $client, Request $request): Response
    {
        Session::remove('client_status');
        Session::remove('client_edit_errors');
        Session::remove('client_edit_old');

        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'min:2'],
                'email' => ['required', 'email', 'unique:clients,email,' . $client . ',id'],
                'address' => ['string'],
            ]);
        } catch (ValidationException $exception) {
            $messages = array_map(
                static fn (array $errors): string => (string) ($errors[0] ?? ''),
                $exception->errors()
            );

            Session::set('client_edit_errors', $messages);
            Session::set('client_edit_old', $request->all());

            return Response::redirect('/clients/' . $client . '/edit');
        }

        $address = trim((string) ($data['address'] ?? ''));
        $timestamp = date('Y-m-d H:i:s');

        DBML::table('clients')->where('id', $client)->update([
            'name' => trim((string) $data['name']),
            'email' => strtolower(trim((string) $data['email'])),
            'address' => $address === '' ? null : $address,
            'updated_at' => $timestamp,
        ]);

        Session::set('client_status', 'Client updated successfully.');

        return Response::redirect('/clients');
    }

    public function sendEmail(int $client, Request $request): Response
    {
        Session::remove('client_email_status');
        Session::remove('client_email_errors');
        Session::remove('client_email_old');

        $record = DBML::table('clients')
            ->select('id', 'name', 'email')
            ->where('id', $client)
            ->first();

        if ($record === null) {
            Session::set('client_email_errors', ['email' => 'Client not found.']);
            return Response::redirect('/clients');
        }

        $clientEmail = trim((string) ($record['email'] ?? ''));
        if ($clientEmail === '') {
            Session::set('client_email_errors', ['email' => 'Client does not have an email address.']);
            return Response::redirect('/clients/' . $client);
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

            Session::set('client_email_errors', $messages);
            Session::set('client_email_old', $request->all());

            return Response::redirect('/clients/' . $client);
        }

        $subject = trim((string) $data['subject']);
        $message = trim((string) $data['message']);
        $adminEmail = $this->resolveAdminEmail();
        $ccAdmin = (bool) ($data['cc_admin'] ?? false);
        $ccMyself = (bool) ($data['cc_myself'] ?? false);
        $currentUserEmail = $this->resolveCurrentUserEmail();

        $safeBody = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
        $html = '<div style="font-family: Arial, sans-serif; line-height: 1.6;">' . $safeBody . '</div>';

        Mail::send(function ($mail) use ($clientEmail, $record, $subject, $html, $ccAdmin, $adminEmail, $ccMyself, $currentUserEmail) {
            $mail->to($clientEmail, (string) ($record['name'] ?? 'Customer'))
                ->subject($subject)
                ->html($html);

            if ($ccAdmin && $adminEmail !== '' && $adminEmail !== $clientEmail) {
                $mail->cc($adminEmail);
            }

            if ($ccMyself && $currentUserEmail !== '' && $currentUserEmail !== $adminEmail && $currentUserEmail !== $clientEmail) {
                $mail->cc($currentUserEmail);
            }
        });

        Session::set('client_email_status', 'Email sent successfully.');

        return Response::redirect('/clients/' . $client);
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
