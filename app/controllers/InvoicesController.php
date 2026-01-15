<?php

namespace App\Controllers;

use DateTime;
use Throwable;
use App\Models\Setting;
use App\Services\ViewData;
use App\Controllers\Concerns\BuildsPagination;
use Mail;
use View;
use Zero\Lib\Auth\Auth;
use Zero\Lib\Database;
use Zero\Lib\DB\DBML;
use Zero\Lib\Http\Request;
use Zero\Lib\Http\Response;
use Zero\Lib\Session;
use Zero\Lib\Validation\ValidationException;

class InvoicesController
{
    use BuildsPagination;
    /**
     * Draft, sent, and paid are the allowed lifecycle phases.
     *
     * @var string[]
     */
    private array $allowedStatuses = ['draft', 'sent', 'paid'];

    public function index(Request $request)
    {
        $layout = ViewData::appLayout();
        $customerId = (int) $request->input('customer_id', 0);
        $page = (int) $request->input('page', 1);
        $perPage = 15;
        $filterCustomer = null;

        if ($customerId > 0) {
            $filterCustomer = DBML::table('customers')
                ->select('id', 'name')
                ->where('id', $customerId)
                ->first();

            if ($filterCustomer === null) {
                $filterCustomer = ['id' => $customerId, 'name' => 'Customer #' . $customerId];
            }
        }

        $query = DBML::table('invoices as i')
            ->select('i.id', 'i.invoice_no', 'i.date', 'i.due_date', 'i.status', 'i.currency', 'i.total', 'c.name as customer_name')
            ->leftJoin('customers as c', 'c.id', '=', 'i.customer_id')
            ->orderByDesc('i.date')
            ->orderByDesc('i.id');

        if ($customerId > 0) {
            $query->where('i.customer_id', $customerId);
        }

        $paginator = $query->paginate($perPage, $page);
        $invoices = $paginator->items();
        $invoiceRows = [];
        $statusColors = [
            'paid' => 'bg-green-100 text-green-800',
            'sent' => 'bg-blue-100 text-blue-800',
            'draft' => 'bg-yellow-100 text-yellow-800',
        ];
        foreach ($invoices as $invoice) {
            $status = strtolower((string) ($invoice['status'] ?? 'draft'));
            $invoiceRows[] = [
                'id' => $invoice['id'] ?? null,
                'invoice_no' => $invoice['invoice_no'] ?? '—',
                'customer_name' => $invoice['customer_name'] ?? 'Unknown customer',
                'date' => $invoice['date'] ?? '—',
                'due_date' => $invoice['due_date'] ?? '—',
                'status_label' => ucfirst($status),
                'badge_class' => $statusColors[$status] ?? 'bg-stone-100 text-stone-700',
                'total_label' => Setting::formatMoney((float) ($invoice['total'] ?? 0), $invoice['currency'] ?? null),
                'search' => strtolower((string) ($invoice['invoice_no'] ?? '') . ' ' . (string) ($invoice['customer_name'] ?? '') . ' ' . (string) ($invoice['date'] ?? '') . ' ' . (string) ($invoice['due_date'] ?? '') . ' ' . (string) ($invoice['status'] ?? '')),
                'show_url' => route('invoices.show', ['invoice' => $invoice['id'] ?? 0]),
            ];
        }

        $pagination = $this->buildPaginationData(
            $paginator,
            route('invoices.index'),
            $request->all()
        );

        return view('invoices/index', array_merge($layout, [
            'invoices' => $invoiceRows,
            'filterCustomer' => $filterCustomer,
            'pagination' => $pagination,
        ]));
    }

    public function create()
    {
        return view('invoices/create', $this->resolveCreateViewData());
    }

    public function store(Request $request)
    {
        $customerId = (int) $request->input('customer_id');
        $invoiceNo = trim((string) $request->input('invoice_no', $this->generateInvoiceNumber()));
        $invoiceDate = $this->normaliseDate((string) $request->input('date'), fallback: date('Y-m-d'));
        $dueDate = $this->normaliseDate((string) $request->input('due_date'), allowEmpty: true);
        $status = 'draft';
        $currency = strtoupper(trim((string) $request->input('currency', Setting::getValue('default_currency'))));
        $items = $this->normaliseItems($request->input('items', []));
        $taxIdInput = (int) $request->input('tax_id', 0);
        $notes = trim((string) $request->input('notes', ''));

        if ($customerId <= 0) {
            return view('invoices/create', $this->resolveCreateViewData(
                $request->all(),
                'Please choose a customer before saving the invoice.',
                $invoiceNo
            ));
        }

        if ($items === []) {
            return view('invoices/create', $this->resolveCreateViewData(
                $request->all(),
                'Add at least one line item to calculate the invoice total.',
                $invoiceNo
            ));
        }

        if ($currency === '' || !preg_match('/^[A-Z]{3,4}$/', $currency)) {
            $currency = strtoupper(trim(Setting::getValue('default_currency')));
        }

        $customerExists = DBML::table('customers')->where('id', $customerId)->exists();

        if (!$customerExists) {
            return view('invoices/create', $this->resolveCreateViewData(
                $request->all(),
                'The selected customer could not be found. Please create the customer first.',
                $invoiceNo
            ));
        }

        $subtotal = array_reduce($items, static fn ($carry, $item) => $carry + $item['subtotal'], 0.0);
        $taxData = $this->resolveInvoiceTax($taxIdInput, $subtotal);
        $taxId = $taxData['tax_id'];
        $taxRate = $taxData['tax_rate'];
        $taxAmount = $taxData['tax_amount'];
        $total = $subtotal + $taxAmount;
        $publicUuid = $this->generatePublicUuid();
        $timestamp = date('Y-m-d H:i:s');

        $connection = Database::write();
        $pdo = $connection->connection;

        try {
            $pdo->beginTransaction();

            $invoiceStmt = $pdo->prepare('
                INSERT INTO invoices (customer_id, invoice_no, date, due_date, status, currency, tax_id, tax_rate, tax_amount, total, notes, public_uuid, created_at, updated_at)
                VALUES (:customer_id, :invoice_no, :date, :due_date, :status, :currency, :tax_id, :tax_rate, :tax_amount, :total, :notes, :public_uuid, :created_at, :updated_at)
            ');

            $invoiceStmt->execute([
                ':customer_id' => $customerId,
                ':invoice_no' => $invoiceNo,
                ':date' => $invoiceDate,
                ':due_date' => $dueDate,
                ':status' => $status,
                ':currency' => $currency,
                ':tax_id' => $taxId > 0 ? $taxId : null,
                ':tax_rate' => number_format($taxRate, 2, '.', ''),
                ':tax_amount' => number_format($taxAmount, 2, '.', ''),
                ':total' => number_format($total, 2, '.', ''),
                ':notes' => $notes !== '' ? $notes : null,
                ':public_uuid' => $publicUuid,
                ':created_at' => $timestamp,
                ':updated_at' => $timestamp,
            ]);

            $invoiceId = (int) $pdo->lastInsertId();

            $itemStmt = $pdo->prepare('
                INSERT INTO invoice_items (invoice_id, description, qty, unit_price, subtotal, created_at, updated_at)
                VALUES (:invoice_id, :description, :qty, :unit_price, :subtotal, :created_at, :updated_at)
            ');

            foreach ($items as $item) {
                $itemStmt->execute([
                    ':invoice_id' => $invoiceId,
                    ':description' => $item['description'],
                    ':qty' => $item['qty'],
                    ':unit_price' => number_format($item['unit_price'], 2, '.', ''),
                    ':subtotal' => number_format($item['subtotal'], 2, '.', ''),
                    ':created_at' => $timestamp,
                    ':updated_at' => $timestamp,
                ]);
            }

            if ($status === 'paid') {
                $transactionStmt = $pdo->prepare('
                    INSERT INTO transactions (type, amount, currency, date, description, source, vendor_id, invoice_id, created_at, updated_at)
                    VALUES (:type, :amount, :currency, :date, :description, :source, :vendor_id, :invoice_id, :created_at, :updated_at)
                ');

                $transactionStmt->execute([
                    ':type' => 'income',
                    ':amount' => number_format($total, 2, '.', ''),
                    ':currency' => $currency,
                    ':date' => $invoiceDate,
                    ':description' => sprintf('Invoice %s paid', $invoiceNo),
                    ':source' => 'invoice',
                    ':vendor_id' => null,
                    ':invoice_id' => $invoiceId,
                    ':created_at' => $timestamp,
                    ':updated_at' => $timestamp,
                ]);
            }

            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return view('invoices/create', $this->resolveCreateViewData(
                $request->all(),
                'We could not save the invoice. ' . $exception->getMessage(),
                $invoiceNo
            ));
        }

        $statusLabel = ucfirst($status);
        $this->logInvoiceEvent($invoiceId, 'created', 'Invoice created', null, null, $timestamp);
        $this->logInvoiceEvent($invoiceId, 'status_changed', 'Status set to ' . $statusLabel, null, null, $timestamp);

        return Response::redirectRoute('invoices.show', ['invoice' => $invoiceId]);
    }

    public function show(int $invoice)
    {
        $layout = ViewData::appLayout();
        $emailStatus = Session::get('invoice_email_status');
        $emailErrors = Session::get('invoice_email_errors') ?? [];
        $emailOld = Session::get('invoice_email_old') ?? [];
        $paymentStatus = Session::get('invoice_payment_status');
        $paymentErrors = Session::get('invoice_payment_errors') ?? [];
        $paymentOld = Session::get('invoice_payment_old') ?? [];

        Session::remove('invoice_email_status');
        Session::remove('invoice_email_errors');
        Session::remove('invoice_email_old');
        Session::remove('invoice_payment_status');
        Session::remove('invoice_payment_errors');
        Session::remove('invoice_payment_old');

        $invoiceRecord = DBML::table('invoices as i')
            ->select(
                'i.id',
                'i.invoice_no',
                'i.date',
                'i.due_date',
                'i.status',
                'i.currency',
                'i.total',
                'i.tax_id',
                'i.tax_rate',
                'i.tax_amount',
                'i.notes',
                'i.public_uuid',
                'i.created_at',
                'i.updated_at',
                'c.name as customer_name',
                'c.email as customer_email',
                'c.address as customer_address',
                't.name as tax_name'
            )
            ->leftJoin('customers as c', 'c.id', '=', 'i.customer_id')
            ->leftJoin('taxes as t', 't.id', '=', 'i.tax_id')
            ->where('i.id', $invoice)
            ->first();

        if ($invoiceRecord === null) {
            return Response::json(['message' => 'Invoice not found'], 404);
        }
        $invoiceRecord['public_uuid'] = $this->ensureInvoicePublicUuid(
            (int) ($invoiceRecord['id'] ?? $invoice),
            $invoiceRecord['public_uuid'] ?? null
        );

        $items = DBML::table('invoice_items')
            ->where('invoice_id', $invoice)
            ->orderBy('id')
            ->get();

        $paymentTransaction = DBML::table('transactions')
            ->where('invoice_id', $invoice)
            ->where('source', 'invoice')
            ->orderByDesc('id')
            ->first();

        $events = DBML::table('invoice_events')
            ->where('invoice_id', $invoice)
            ->orderByDesc('created_at')
            ->get();

        $detailView = $this->buildInvoiceDetailViewData($invoiceRecord, $items);
        $showData = $this->buildInvoiceShowViewData(
            $invoiceRecord,
            $detailView,
            $events,
            $paymentTransaction,
            $emailOld,
            $emailErrors,
            $paymentErrors,
            $paymentOld
        );

        $pageTitle = 'Invoice ' . ($invoiceRecord['invoice_no'] ?? '');

        return view('invoices/show', array_merge($layout, $detailView, $showData, [
            'invoice' => $invoiceRecord,
            'items' => $detailView['items'] ?? $items,
            'emailStatus' => $emailStatus,
            'emailErrors' => $emailErrors,
            'emailOld' => $emailOld,
            'paymentStatus' => $paymentStatus,
            'paymentErrors' => $paymentErrors,
            'paymentOld' => $paymentOld,
            'paymentTransaction' => $paymentTransaction,
            'detailView' => $detailView,
            'pageTitle' => $pageTitle,
        ]));
    }

    public function edit(int $invoice): Response
    {
        $record = DBML::table('invoices as i')
            ->select(
                'i.id',
                'i.customer_id',
                'i.invoice_no',
                'i.date',
                'i.due_date',
                'i.status',
                'i.currency',
                'i.tax_id',
                'i.tax_rate',
                'i.tax_amount',
                'i.notes',
                't.name as tax_name'
            )
            ->leftJoin('taxes as t', 't.id', '=', 'i.tax_id')
            ->where('i.id', $invoice)
            ->first();

        if ($record === null) {
            Session::set('invoice_email_errors', ['email' => 'Invoice not found.']);
            return Response::redirect('/invoices');
        }

        $items = DBML::table('invoice_items')
            ->where('invoice_id', $invoice)
            ->orderBy('id')
            ->get();

        return view('invoices/create', $this->buildInvoiceFormViewData($record, $items));
    }

    public function duplicate(int $invoice): Response
    {
        $record = DBML::table('invoices as i')
            ->select(
                'i.id',
                'i.customer_id',
                'i.date',
                'i.due_date',
                'i.currency',
                'i.tax_id',
                'i.tax_rate',
                'i.tax_amount',
                'i.notes',
                't.name as tax_name'
            )
            ->leftJoin('taxes as t', 't.id', '=', 'i.tax_id')
            ->where('i.id', $invoice)
            ->first();

        if ($record === null) {
            Session::set('invoice_email_errors', ['email' => 'Invoice not found.']);
            return Response::redirect('/invoices');
        }

        $items = DBML::table('invoice_items')
            ->where('invoice_id', $invoice)
            ->orderBy('id')
            ->get();

        return view('invoices/create', $this->buildInvoiceDuplicateViewData($record, $items));
    }

    public function update(int $invoice, Request $request): Response
    {
        $record = DBML::table('invoices as i')
            ->select(
                'i.id',
                'i.customer_id',
                'i.invoice_no',
                'i.date',
                'i.due_date',
                'i.status',
                'i.currency',
                'i.tax_id',
                'i.tax_rate',
                'i.tax_amount',
                'i.notes',
                't.name as tax_name'
            )
            ->leftJoin('taxes as t', 't.id', '=', 'i.tax_id')
            ->where('i.id', $invoice)
            ->first();

        if ($record === null) {
            Session::set('invoice_email_errors', ['email' => 'Invoice not found.']);
            return Response::redirect('/invoices');
        }

        $customerId = (int) $request->input('customer_id');
        $invoiceNo = trim((string) $request->input('invoice_no', (string) ($record['invoice_no'] ?? '')));
        $invoiceDate = $this->normaliseDate((string) $request->input('date'), fallback: date('Y-m-d'));
        $dueDate = $this->normaliseDate((string) $request->input('due_date'), allowEmpty: true);
        $status = strtolower((string) ($record['status'] ?? 'draft'));
        $currency = strtoupper(trim((string) $request->input('currency', Setting::getValue('default_currency'))));
        $items = $this->normaliseItems($request->input('items', []));
        $taxIdInput = (int) $request->input('tax_id', 0);
        $notes = trim((string) $request->input('notes', ''));

        if ($customerId <= 0) {
            return view('invoices/create', $this->buildInvoiceFormViewData(
                $record,
                $items,
                $request->all(),
                'Please choose a customer before saving the invoice.'
            ));
        }

        if ($items === []) {
            return view('invoices/create', $this->buildInvoiceFormViewData(
                $record,
                $items,
                $request->all(),
                'Add at least one line item to calculate the invoice total.'
            ));
        }

        if ($currency === '' || !preg_match('/^[A-Z]{3,4}$/', $currency)) {
            $currency = strtoupper(trim(Setting::getValue('default_currency')));
        }

        $customerExists = DBML::table('customers')->where('id', $customerId)->exists();

        if (!$customerExists) {
            return view('invoices/create', $this->buildInvoiceFormViewData(
                $record,
                $items,
                $request->all(),
                'The selected customer could not be found. Please create the customer first.'
            ));
        }

        $subtotal = array_reduce($items, static fn ($carry, $item) => $carry + $item['subtotal'], 0.0);
        $taxData = $this->resolveInvoiceTax($taxIdInput, $subtotal);
        $taxId = $taxData['tax_id'];
        $taxRate = $taxData['tax_rate'];
        $taxAmount = $taxData['tax_amount'];
        $total = $subtotal + $taxAmount;
        $timestamp = date('Y-m-d H:i:s');

        $connection = Database::write();
        $pdo = $connection->connection;

        try {
            $pdo->beginTransaction();

            $invoiceStmt = $pdo->prepare('
                UPDATE invoices
                SET customer_id = :customer_id,
                    invoice_no = :invoice_no,
                    date = :date,
                    due_date = :due_date,
                    status = :status,
                    currency = :currency,
                    tax_id = :tax_id,
                    tax_rate = :tax_rate,
                    tax_amount = :tax_amount,
                    total = :total,
                    notes = :notes,
                    updated_at = :updated_at
                WHERE id = :id
            ');

            $invoiceStmt->execute([
                ':customer_id' => $customerId,
                ':invoice_no' => $invoiceNo,
                ':date' => $invoiceDate,
                ':due_date' => $dueDate,
                ':status' => $status,
                ':currency' => $currency,
                ':tax_id' => $taxId > 0 ? $taxId : null,
                ':tax_rate' => number_format($taxRate, 2, '.', ''),
                ':tax_amount' => number_format($taxAmount, 2, '.', ''),
                ':total' => number_format($total, 2, '.', ''),
                ':notes' => $notes !== '' ? $notes : null,
                ':updated_at' => $timestamp,
                ':id' => $invoice,
            ]);

            $deleteItems = $pdo->prepare('DELETE FROM invoice_items WHERE invoice_id = :invoice_id');
            $deleteItems->execute([':invoice_id' => $invoice]);

            $itemStmt = $pdo->prepare('
                INSERT INTO invoice_items (invoice_id, description, qty, unit_price, subtotal, created_at, updated_at)
                VALUES (:invoice_id, :description, :qty, :unit_price, :subtotal, :created_at, :updated_at)
            ');

            foreach ($items as $item) {
                $itemStmt->execute([
                    ':invoice_id' => $invoice,
                    ':description' => $item['description'],
                    ':qty' => $item['qty'],
                    ':unit_price' => number_format($item['unit_price'], 2, '.', ''),
                    ':subtotal' => number_format($item['subtotal'], 2, '.', ''),
                    ':created_at' => $timestamp,
                    ':updated_at' => $timestamp,
                ]);
            }

            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return view('invoices/create', $this->buildInvoiceFormViewData(
                $record,
                $items,
                $request->all(),
                'We could not update the invoice. ' . $exception->getMessage()
            ));
        }

        $previousStatus = strtolower((string) ($record['status'] ?? 'draft'));
        if ($previousStatus !== $status) {
            $statusLabel = ucfirst($status);
            $this->logInvoiceEvent($invoice, 'status_changed', 'Status set to ' . $statusLabel, null, null, $timestamp);
        }

        return Response::redirect('/invoices/' . $invoice);
    }

    public function markSent(int $invoice): Response
    {
        $record = DBML::table('invoices')->select('id', 'status')->where('id', $invoice)->first();

        if ($record === null) {
            Session::set('invoice_email_errors', ['email' => 'Invoice not found.']);
            return Response::redirect('/invoices');
        }

        $status = strtolower((string) ($record['status'] ?? 'draft'));
        if ($status === 'paid') {
            Session::set('invoice_email_status', 'Invoice is already marked as paid.');
            return Response::redirect('/invoices/' . $invoice);
        }

        if ($status === 'sent') {
            Session::set('invoice_email_status', 'Invoice already marked as sent.');
            return Response::redirect('/invoices/' . $invoice);
        }

        $timestamp = date('Y-m-d H:i:s');
        DBML::table('invoices')->where('id', $invoice)->update([
            'status' => 'sent',
            'updated_at' => $timestamp,
        ]);

        $this->logInvoiceEvent($invoice, 'status_changed', 'Status set to Sent', null, null, $timestamp);
        Session::set('invoice_email_status', 'Invoice marked as sent.');

        return Response::redirect('/invoices/' . $invoice);
    }

    public function download(int $invoice): Response
    {
        $record = DBML::table('invoices as i')
            ->select(
                'i.id',
                'i.invoice_no',
                'i.date',
                'i.due_date',
                'i.status',
                'i.currency',
                'i.total',
                'i.tax_id',
                'i.tax_rate',
                'i.tax_amount',
                'i.notes',
                'i.public_uuid',
                'c.name as customer_name',
                'c.email as customer_email',
                'c.address as customer_address',
                't.name as tax_name'
            )
            ->leftJoin('customers as c', 'c.id', '=', 'i.customer_id')
            ->leftJoin('taxes as t', 't.id', '=', 'i.tax_id')
            ->where('i.id', $invoice)
            ->first();

        if ($record === null) {
            return Response::redirect('/invoices');
        }

        $items = DBML::table('invoice_items')
            ->where('invoice_id', $invoice)
            ->orderBy('id')
            ->get();

        $pdfContent = $this->renderInvoicePdfContent($record, $items);
        $pdfName = $this->buildInvoicePdfName($record);

        return Response::make($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . addcslashes($pdfName, '"\\') . '"',
        ]);
    }

    public function publicView(string $uuid): Response
    {
        $uuid = trim($uuid);
        if ($uuid === '') {
            return Response::text('Invoice not found.', 404);
        }

        $record = DBML::table('invoices as i')
            ->select(
                'i.id',
                'i.invoice_no',
                'i.date',
                'i.due_date',
                'i.status',
                'i.currency',
                'i.total',
                'i.tax_id',
                'i.tax_rate',
                'i.tax_amount',
                'i.notes',
                'i.public_uuid',
                'c.name as customer_name',
                'c.email as customer_email',
                'c.address as customer_address',
                't.name as tax_name'
            )
            ->leftJoin('customers as c', 'c.id', '=', 'i.customer_id')
            ->leftJoin('taxes as t', 't.id', '=', 'i.tax_id')
            ->where('i.public_uuid', $uuid)
            ->first();

        if ($record === null) {
            return Response::text('Invoice not found.', 404);
        }

        $items = DBML::table('invoice_items')
            ->where('invoice_id', (int) ($record['id'] ?? 0))
            ->orderBy('id')
            ->get();

        $detailView = $this->buildInvoiceDetailViewData($record, $items);
        $pageTitle = 'Invoice ' . ($record['invoice_no'] ?? '');

        return view('invoices/public', array_merge($detailView, [
            'invoice' => $record,
            'items' => $detailView['items'] ?? $items,
            'detailView' => $detailView,
            'pageTitle' => $pageTitle,
        ]));
    }

    public function sendEmail(int $invoice, Request $request): Response
    {
        Session::remove('invoice_email_status');
        Session::remove('invoice_email_errors');
        Session::remove('invoice_email_old');

        $record = DBML::table('invoices as i')
            ->select(
                'i.id',
                'i.invoice_no',
                'i.date',
                'i.due_date',
                'i.status',
                'i.currency',
                'i.total',
                'i.tax_id',
                'i.tax_rate',
                'i.tax_amount',
                'i.notes',
                'i.public_uuid',
                'c.name as customer_name',
                'c.email as customer_email',
                'c.address as customer_address',
                't.name as tax_name'
            )
            ->leftJoin('customers as c', 'c.id', '=', 'i.customer_id')
            ->leftJoin('taxes as t', 't.id', '=', 'i.tax_id')
            ->where('i.id', $invoice)
            ->first();

        if ($record === null) {
            Session::set('invoice_email_errors', ['email' => 'Invoice not found.']);
            return Response::redirect('/invoices');
        }

        $record['public_uuid'] = $this->ensureInvoicePublicUuid(
            $invoice,
            $record['public_uuid'] ?? null
        );

        $customerEmail = trim((string) ($record['customer_email'] ?? ''));
        $emailInput = trim((string) $request->input('email', ''));
        if ($emailInput !== '' && !filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
            Session::set('invoice_email_errors', ['email' => 'Enter a valid email address.']);
            Session::set('invoice_email_old', $request->all());
            return Response::redirect('/invoices/' . $invoice);
        }

        $recipientEmail = $emailInput !== '' ? $emailInput : $customerEmail;
        if ($recipientEmail === '') {
            Session::set('invoice_email_errors', ['email' => 'Recipient email is required.']);
            Session::set('invoice_email_old', $request->all());
            return Response::redirect('/invoices/' . $invoice);
        }

        try {
            $data = $request->validate([
                'subject' => ['required', 'string', 'min:3', 'max:150'],
                'message' => ['required', 'string', 'min:3'],
                'cc_myself' => ['boolean'],
            ]);
        } catch (ValidationException $exception) {
            $messages = array_map(
                static fn (array $errors): string => (string) ($errors[0] ?? ''),
                $exception->errors()
            );

            Session::set('invoice_email_errors', $messages);
            Session::set('invoice_email_old', $request->all());

            return Response::redirect('/invoices/' . $invoice);
        }

        $subject = trim((string) $data['subject']);
        $message = trim((string) $data['message']);
        $bccMyself = (bool) ($data['cc_myself'] ?? false);
        $currentUserEmail = $this->resolveCurrentUserEmail();

        $items = DBML::table('invoice_items')
            ->where('invoice_id', $invoice)
            ->orderBy('id')
            ->get();

        $businessName = Setting::getValue('business_name');
        $brandName = $businessName !== '' ? $businessName : 'Invoice App';
        $invoiceNo = (string) ($record['invoice_no'] ?? 'Invoice');
        $due = (string) ($record['due_date'] ?? '');
        $totalLabel = Setting::formatMoney((float) ($record['total'] ?? 0), $record['currency'] ?? null);
        $publicUuid = trim((string) ($record['public_uuid'] ?? ''));
        $publicUrl = $publicUuid !== '' ? route('invoices.public', ['uuid' => $publicUuid]) : '';
        $companyAddress = Setting::getValue('company_address');
        $companyLogo = trim((string) Setting::getValue('company_logo'));
        $companyEmail = Setting::getValue('company_email');
        if ($companyEmail === '') {
            $companyEmail = Setting::getValue('mail_from_address');
        }
        $companyPhone = Setting::getValue('company_phone');
        $trackingToken = bin2hex(random_bytes(16));
        $trackingUrl = route('invoices.email.open', ['invoice' => $invoice, 'token' => $trackingToken]);
        $messageWithTokens = $this->replaceInvoiceEmailTokens(
            $message,
            $record,
            $invoiceNo,
            $totalLabel,
            $due,
            $brandName,
            $publicUrl
        );
        $messageHtml = $this->formatInvoiceEmailBody($messageWithTokens);
        $html = View::render('mail/invoice', [
            'invoice' => $record,
            'items' => $items,
            'messageHtml' => $messageHtml,
            'invoiceUrl' => route('invoices.show', ['invoice' => $invoice]),
            'brandName' => $brandName,
            'companyPhone' => $companyPhone,
            'trackingUrl' => $trackingUrl,
        ]);

        $pdfContent = $this->renderInvoicePdfContent($record, $items);
        $pdfName = $this->buildInvoicePdfName($record);

        Mail::send(function ($mail) use ($record, $recipientEmail, $subject, $html, $bccMyself, $currentUserEmail, $pdfContent, $pdfName) {
            $mail->to($recipientEmail, (string) ($record['customer_name'] ?? 'Customer'))
                ->subject($subject)
                ->html($html);

            if ($bccMyself && $currentUserEmail !== '' && $currentUserEmail !== $recipientEmail) {
                $mail->bcc($currentUserEmail);
            }

            $mail->attach($pdfName, $pdfContent, 'application/pdf');
        });

        $timestamp = date('Y-m-d H:i:s');
        $currentStatus = strtolower((string) ($record['status'] ?? 'draft'));
        if ($currentStatus !== 'paid' && $currentStatus !== 'sent') {
            DBML::table('invoices')->where('id', $invoice)->update([
                'status' => 'sent',
                'updated_at' => $timestamp,
            ]);

            $this->logInvoiceEvent($invoice, 'status_changed', 'Status set to Sent', null, null, $timestamp);
        }

        $this->logInvoiceEvent(
            $invoice,
            'email_sent',
            'Email sent',
            $recipientEmail !== '' ? 'To: ' . $recipientEmail : null,
            $trackingToken,
            $timestamp
        );

        Session::set('invoice_email_status', 'Email sent successfully.');

        return Response::redirect('/invoices/' . $invoice);
    }

    public function recordPayment(int $invoice, Request $request): Response
    {
        Session::remove('invoice_payment_status');
        Session::remove('invoice_payment_errors');
        Session::remove('invoice_payment_old');

        $record = DBML::table('invoices as i')
            ->select(
                'i.id',
                'i.invoice_no',
                'i.status',
                'i.currency',
                'i.total',
                'c.name as customer_name'
            )
            ->leftJoin('customers as c', 'c.id', '=', 'i.customer_id')
            ->where('i.id', $invoice)
            ->first();

        if ($record === null) {
            Session::set('invoice_payment_errors', ['amount' => 'Invoice not found.']);
            return Response::redirect('/invoices');
        }

        $currentStatus = strtolower((string) ($record['status'] ?? 'draft'));
        if ($currentStatus === 'paid') {
            Session::set('invoice_payment_errors', ['amount' => 'This invoice is already marked as paid.']);
            return Response::redirect('/invoices/' . $invoice);
        }

        $amountRaw = trim((string) $request->input('amount', ''));
        $dateRaw = trim((string) $request->input('date', ''));
        $description = trim((string) $request->input('description', ''));

        $errors = [];

        if ($amountRaw === '' || !is_numeric($amountRaw)) {
            $errors['amount'] = 'Enter a valid amount.';
        } else {
            $amount = round((float) $amountRaw, 2);
            if ($amount <= 0) {
                $errors['amount'] = 'Amount must be greater than zero.';
            }
        }

        if ($dateRaw === '') {
            $errors['date'] = 'Select a payment date.';
        } else {
            $dateValue = DateTime::createFromFormat('Y-m-d', $dateRaw);
            if ($dateValue === false) {
                $errors['date'] = 'Select a valid payment date.';
            }
        }

        if (! empty($errors)) {
            Session::set('invoice_payment_errors', $errors);
            Session::set('invoice_payment_old', $request->all());

            return Response::redirect('/invoices/' . $invoice);
        }

        $amount = round((float) $amountRaw, 2);
        $dateValue = DateTime::createFromFormat('Y-m-d', $dateRaw);
        $paymentDate = $dateValue ? $dateValue->format('Y-m-d') : date('Y-m-d');
        $timestamp = date('Y-m-d H:i:s');

        $invoiceNo = (string) ($record['invoice_no'] ?? 'Invoice');
        $description = $description !== '' ? $description : 'Payment for ' . $invoiceNo;

        DBML::table('transactions')->insert([
            'type' => 'income',
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => (string) ($record['currency'] ?? 'USD'),
            'date' => $paymentDate,
            'description' => $description,
            'source' => 'invoice',
            'vendor_id' => null,
            'invoice_id' => $invoice,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        DBML::table('invoices')->where('id', $invoice)->update([
            'status' => 'paid',
            'updated_at' => $timestamp,
        ]);

        $amountLabel = Setting::formatMoney($amount, $record['currency'] ?? null);
        $this->logInvoiceEvent(
            $invoice,
            'payment_recorded',
            'Payment recorded',
            'Amount: ' . $amountLabel,
            null,
            $timestamp
        );
        $this->logInvoiceEvent(
            $invoice,
            'status_changed',
            'Status set to Paid',
            null,
            null,
            $timestamp
        );

        Session::set('invoice_payment_status', 'Payment recorded successfully.');

        return Response::redirect('/invoices/' . $invoice);
    }

    public function updatePayment(int $invoice, Request $request): Response
    {
        Session::remove('invoice_payment_status');
        Session::remove('invoice_payment_errors');
        Session::remove('invoice_payment_old');

        $transaction = DBML::table('transactions')
            ->where('invoice_id', $invoice)
            ->where('source', 'invoice')
            ->orderByDesc('id')
            ->first();

        if ($transaction === null) {
            Session::set('invoice_payment_errors', ['amount' => 'No payment found for this invoice.']);
            return Response::redirect('/invoices/' . $invoice);
        }

        $amountRaw = trim((string) $request->input('amount', ''));
        $dateRaw = trim((string) $request->input('date', ''));
        $description = trim((string) $request->input('description', ''));

        $errors = [];

        if ($amountRaw === '' || !is_numeric($amountRaw)) {
            $errors['amount'] = 'Enter a valid amount.';
        } else {
            $amount = round((float) $amountRaw, 2);
            if ($amount <= 0) {
                $errors['amount'] = 'Amount must be greater than zero.';
            }
        }

        if ($dateRaw === '') {
            $errors['date'] = 'Select a payment date.';
        } else {
            $dateValue = DateTime::createFromFormat('Y-m-d', $dateRaw);
            if ($dateValue === false) {
                $errors['date'] = 'Select a valid payment date.';
            }
        }

        if (! empty($errors)) {
            Session::set('invoice_payment_errors', $errors);
            Session::set('invoice_payment_old', $request->all());

            return Response::redirect('/invoices/' . $invoice);
        }

        $amount = round((float) $amountRaw, 2);
        $dateValue = DateTime::createFromFormat('Y-m-d', $dateRaw);
        $paymentDate = $dateValue ? $dateValue->format('Y-m-d') : date('Y-m-d');
        $timestamp = date('Y-m-d H:i:s');

        $description = $description !== '' ? $description : (string) ($transaction['description'] ?? '');

        DBML::table('transactions')
            ->where('id', (int) $transaction['id'])
            ->update([
                'amount' => number_format($amount, 2, '.', ''),
                'date' => $paymentDate,
                'description' => $description !== '' ? $description : null,
                'updated_at' => $timestamp,
            ]);

        DBML::table('invoices')->where('id', $invoice)->update([
            'status' => 'paid',
            'updated_at' => $timestamp,
        ]);

        $currency = $transaction['currency'] ?? null;
        $amountLabel = Setting::formatMoney($amount, $currency);
        $this->logInvoiceEvent(
            $invoice,
            'payment_updated',
            'Payment updated',
            'Amount: ' . $amountLabel,
            null,
            $timestamp
        );
        $this->logInvoiceEvent(
            $invoice,
            'status_changed',
            'Status set to Paid',
            null,
            null,
            $timestamp
        );

        Session::set('invoice_payment_status', 'Payment updated successfully.');

        return Response::redirect('/invoices/' . $invoice);
    }

    public function trackEmailOpen(int $invoice, string $token): Response
    {
        $token = trim($token);
        if ($token === '') {
            return $this->pixelResponse();
        }

        $sent = DBML::table('invoice_events')
            ->where('invoice_id', $invoice)
            ->where('type', 'email_sent')
            ->where('token', $token)
            ->first();

        if ($sent !== null) {
            $alreadyOpened = DBML::table('invoice_events')
                ->where('invoice_id', $invoice)
                ->where('type', 'email_opened')
                ->where('token', $token)
                ->exists();

            if (! $alreadyOpened) {
                $request = Request::instance();
                $ip = $request->ip();
                $agent = trim((string) $request->header('user-agent', ''));
                $details = [];

                if ($ip) {
                    $details[] = 'IP: ' . $ip;
                }
                if ($agent !== '') {
                    $details[] = 'Agent: ' . $agent;
                }

                $detail = $details !== [] ? implode(' | ', $details) : null;

                $this->logInvoiceEvent(
                    $invoice,
                    'email_opened',
                    'Email opened',
                    $detail,
                    $token
                );
            }
        }

        return $this->pixelResponse();
    }

    /**
     * @param array<string, mixed> $old
     * @return array<string, mixed>
     */
    private function resolveCreateViewData(array $old = [], ?string $error = null, ?string $invoiceNumber = null): array
    {
        $layout = ViewData::appLayout();
        $customers = DBML::table('customers')->orderBy('name')->get();
        $hasCustomers = !empty($customers);
        $defaultCurrency = Setting::getValue('default_currency');
        $selectedCustomer = (string) ($old['customer_id'] ?? '');
        $selectedStatus = strtolower((string) ($old['status'] ?? 'draft'));
        if (!in_array($selectedStatus, $this->allowedStatuses, true)) {
            $selectedStatus = 'draft';
        }

        $lineItems = $old['items'] ?? [
            ['description' => '', 'qty' => 1, 'unit_price' => '0.00', 'subtotal' => '0.00'],
        ];
        if (!is_array($lineItems) || $lineItems === []) {
            $lineItems = [['description' => '', 'qty' => 1, 'unit_price' => '0.00', 'subtotal' => '0.00']];
        }
        $lineItems = array_values(array_map(static function (array $item): array {
            return [
                'description' => (string) ($item['description'] ?? ''),
                'qty' => (int) ($item['qty'] ?? 1),
                'unit_price' => (string) ($item['unit_price'] ?? '0.00'),
                'subtotal' => (string) ($item['subtotal'] ?? '0.00'),
            ];
        }, $lineItems));

        $selectedCurrency = strtoupper(trim((string) ($old['currency'] ?? $defaultCurrency)));
        $currencyOptions = Setting::currencyOptions();
        if ($selectedCurrency !== '' && !array_key_exists($selectedCurrency, $currencyOptions)) {
            $currencyOptions = [$selectedCurrency => $selectedCurrency] + $currencyOptions;
        }

        $selectedTaxId = (string) ($old['tax_id'] ?? '');
        if ($selectedTaxId === '0') {
            $selectedTaxId = '';
        }
        $selectedTaxRate = (float) ($old['tax_rate'] ?? 0);
        $selectedTaxName = trim((string) ($old['tax_name'] ?? ''));
        $taxOptions = [];
        $taxMap = [];
        foreach (DBML::table('taxes')->orderBy('name')->get() as $tax) {
            $taxId = (string) ($tax['id'] ?? '');
            if ($taxId === '') {
                continue;
            }
            $taxName = trim((string) ($tax['name'] ?? ''));
            $taxRate = (float) ($tax['rate'] ?? 0);
            $rateLabel = number_format($taxRate, 2, '.', '');
            $label = $taxName !== '' ? sprintf('%s (%s%%)', $taxName, $rateLabel) : sprintf('Tax (%s%%)', $rateLabel);
            $taxOptions[] = [
                'id' => $taxId,
                'name' => $taxName,
                'rate' => $taxRate,
                'label' => $label,
            ];
            $taxMap[$taxId] = [
                'rate' => $taxRate,
                'label' => $label,
            ];
        }
        if ($selectedTaxId !== '' && !array_key_exists($selectedTaxId, $taxMap)) {
            $rateLabel = number_format($selectedTaxRate, 2, '.', '');
            $label = $selectedTaxName !== '' ? sprintf('%s (%s%%)', $selectedTaxName, $rateLabel) : sprintf('Tax (%s%%)', $rateLabel);
            $taxOptions[] = [
                'id' => $selectedTaxId,
                'name' => $selectedTaxName,
                'rate' => $selectedTaxRate,
                'label' => $label,
            ];
            $taxMap[$selectedTaxId] = [
                'rate' => $selectedTaxRate,
                'label' => $label,
            ];
        }

        $customerAddressMap = [];
        foreach ($customers as $customer) {
            $customerId = (string) ($customer['id'] ?? '');
            if ($customerId === '') {
                continue;
            }
            $customerAddressMap[$customerId] = (string) ($customer['address'] ?? '');
        }

        $selectedCustomerJson = json_encode((string) $selectedCustomer, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $customerAddressJson = json_encode($customerAddressMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $lineItemsJson = json_encode($lineItems, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $selectedCurrencyJson = json_encode($selectedCurrency, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $selectedTaxJson = json_encode($selectedTaxId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $taxesJson = json_encode($taxMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $formValues = [
            'customer_id' => $selectedCustomer,
            'status' => $selectedStatus,
            'currency' => $selectedCurrency,
            'date' => (string) ($old['date'] ?? date('Y-m-d')),
            'due_date' => (string) ($old['due_date'] ?? ''),
            'notes' => (string) ($old['notes'] ?? ''),
            'tax_id' => $selectedTaxId,
        ];

        return array_merge($layout, [
            'customers' => $customers,
            'hasCustomers' => $hasCustomers,
            'invoiceNumber' => $invoiceNumber ?? $this->generateInvoiceNumber(),
            'today' => date('Y-m-d'),
            'statusOptions' => array_map(
                static fn (string $status): array => ['value' => $status, 'label' => ucfirst($status)],
                $this->allowedStatuses
            ),
            'defaultCurrency' => $defaultCurrency,
            'currencyOptions' => $currencyOptions,
            'taxOptions' => $taxOptions,
            'selectedCustomer' => $selectedCustomer,
            'selectedStatus' => $selectedStatus,
            'selectedCurrency' => $selectedCurrency,
            'selectedTaxId' => $selectedTaxId,
            'selectedCustomerJson' => $selectedCustomerJson !== false ? $selectedCustomerJson : '""',
            'customerAddressJson' => $customerAddressJson !== false ? $customerAddressJson : '{}',
            'lineItemsJson' => $lineItemsJson !== false ? $lineItemsJson : '[]',
            'selectedCurrencyJson' => $selectedCurrencyJson !== false ? $selectedCurrencyJson : '""',
            'selectedTaxJson' => $selectedTaxJson !== false ? $selectedTaxJson : '""',
            'taxesJson' => $taxesJson !== false ? $taxesJson : '{}',
            'formValues' => $formValues,
            'lineItems' => $lineItems,
            'error' => $error,
        ]);
    }

    /**
     * @param array<string, mixed> $invoice
     * @param array<int, array<string, mixed>> $items
     * @param array<string, mixed> $old
     * @return array<string, mixed>
     */
    private function buildInvoiceFormViewData(array $invoice, array $items, array $old = [], ?string $error = null): array
    {
        $lineItems = [];
        foreach ($items as $item) {
            $lineItems[] = [
                'description' => (string) ($item['description'] ?? ''),
                'qty' => (int) ($item['qty'] ?? 1),
                'unit_price' => (string) ($item['unit_price'] ?? '0.00'),
                'subtotal' => (string) ($item['subtotal'] ?? '0.00'),
            ];
        }

        $taxIdValue = (int) ($invoice['tax_id'] ?? 0);
        $values = [
            'customer_id' => (string) ($invoice['customer_id'] ?? ''),
            'invoice_no' => (string) ($invoice['invoice_no'] ?? ''),
            'date' => (string) ($invoice['date'] ?? ''),
            'due_date' => (string) ($invoice['due_date'] ?? ''),
            'status' => (string) ($invoice['status'] ?? 'draft'),
            'currency' => (string) ($invoice['currency'] ?? Setting::getValue('default_currency')),
            'tax_id' => $taxIdValue > 0 ? (string) $taxIdValue : '',
            'tax_rate' => (string) ($invoice['tax_rate'] ?? ''),
            'tax_name' => (string) ($invoice['tax_name'] ?? ''),
            'notes' => (string) ($invoice['notes'] ?? ''),
            'items' => $lineItems,
        ];

        if ($old !== []) {
            $values = array_merge($values, $old);
        }

        if (empty($values['items'])) {
            $values['items'] = $lineItems;
        }

        $data = $this->resolveCreateViewData($values, $error, (string) ($values['invoice_no'] ?? $invoice['invoice_no'] ?? ''));
        $data['pageTitle'] = 'Edit Invoice';
        $data['formAction'] = route('invoices.update', ['invoice' => $invoice['id'] ?? 0]);
        $data['submitLabel'] = 'Save changes';

        return $data;
    }

    /**
     * @param array<string, mixed> $invoice
     * @param array<int, array<string, mixed>> $items
     * @return array<string, mixed>
     */
    private function buildInvoiceDuplicateViewData(array $invoice, array $items): array
    {
        $lineItems = [];
        foreach ($items as $item) {
            $lineItems[] = [
                'description' => (string) ($item['description'] ?? ''),
                'qty' => (int) ($item['qty'] ?? 1),
                'unit_price' => (string) ($item['unit_price'] ?? '0.00'),
                'subtotal' => (string) ($item['subtotal'] ?? '0.00'),
            ];
        }

        $taxIdValue = (int) ($invoice['tax_id'] ?? 0);
        $values = [
            'customer_id' => (string) ($invoice['customer_id'] ?? ''),
            'date' => (string) ($invoice['date'] ?? ''),
            'due_date' => (string) ($invoice['due_date'] ?? ''),
            'currency' => (string) ($invoice['currency'] ?? Setting::getValue('default_currency')),
            'tax_id' => $taxIdValue > 0 ? (string) $taxIdValue : '',
            'tax_rate' => (string) ($invoice['tax_rate'] ?? ''),
            'tax_name' => (string) ($invoice['tax_name'] ?? ''),
            'notes' => (string) ($invoice['notes'] ?? ''),
            'items' => $lineItems,
        ];

        $data = $this->resolveCreateViewData($values, null, $this->generateInvoiceNumber());
        $data['pageTitle'] = 'Duplicate Invoice';
        $data['submitLabel'] = 'Save invoice';

        return $data;
    }

    private function generateInvoiceNumber(): string
    {
        $latest = DBML::table('invoices')
            ->select('invoice_no')
            ->orderByDesc('id')
            ->first();

        if ($latest && preg_match('/(\d+)/', (string) ($latest['invoice_no'] ?? ''), $matches)) {
            $next = (int) $matches[1] + 1;
        } else {
            $count = DBML::table('invoices')->count();
            $next = $count + 1;
        }

        return sprintf('INV-%05d', $next);
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array{description: string, qty: int, unit_price: float, subtotal: float}>
     */
    private function normaliseItems(array $items): array
    {
        $normalised = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $description = trim((string) ($item['description'] ?? ''));
            if ($description === '') {
                continue;
            }

            $qty = (int) ($item['qty'] ?? 1);
            $qty = $qty <= 0 ? 1 : $qty;

            $unitPrice = (float) ($item['unit_price'] ?? 0);
            if ($unitPrice < 0) {
                $unitPrice = 0;
            }

            $subtotal = round($qty * $unitPrice, 2);

            $normalised[] = [
                'description' => $description,
                'qty' => $qty,
                'unit_price' => round($unitPrice, 2),
                'subtotal' => $subtotal,
            ];
        }

        return $normalised;
    }

    /**
     * @return array{tax_id: int|null, tax_rate: float, tax_amount: float, tax_name: string}
     */
    private function resolveInvoiceTax(int $taxId, float $subtotal): array
    {
        if ($taxId <= 0) {
            return [
                'tax_id' => null,
                'tax_rate' => 0.0,
                'tax_amount' => 0.0,
                'tax_name' => '',
            ];
        }

        $tax = DBML::table('taxes')
            ->select('id', 'name', 'rate')
            ->where('id', $taxId)
            ->first();

        if ($tax === null) {
            return [
                'tax_id' => null,
                'tax_rate' => 0.0,
                'tax_amount' => 0.0,
                'tax_name' => '',
            ];
        }

        $rate = (float) ($tax['rate'] ?? 0);
        $amount = round($subtotal * ($rate / 100), 2);

        return [
            'tax_id' => (int) ($tax['id'] ?? $taxId),
            'tax_rate' => $rate,
            'tax_amount' => $amount,
            'tax_name' => (string) ($tax['name'] ?? ''),
        ];
    }

    private function normaliseDate(?string $value, bool $allowEmpty = false, ?string $fallback = null): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $allowEmpty ? null : ($fallback ?? date('Y-m-d'));
        }

        $date = DateTime::createFromFormat('Y-m-d', $value);

        if ($date === false) {
            return $allowEmpty ? null : ($fallback ?? date('Y-m-d'));
        }

        return $date->format('Y-m-d');
    }

    /**
     * @param array<string, mixed> $record
     * @param array<int, array<string, mixed>> $items
     */
    private function renderInvoicePdfContent(array $record, array $items): string
    {
        $detailView = $this->buildInvoiceDetailViewData($record, $items);

        try {
            return render_pdf('invoice', [
                'invoice' => $record,
                'items' => $detailView['items'] ?? $items,
                'brandName' => $detailView['brandName'] ?? 'Invoice App',
                'companyAddress' => $detailView['companyAddress'] ?? '',
                'companyLogo' => $detailView['companyLogo'] ?? '',
                'companyEmail' => $detailView['companyEmail'] ?? '',
                'companyAddressHtml' => $detailView['companyAddressHtml'] ?? '',
                'customerAddressHtml' => $detailView['customerAddressHtml'] ?? '',
                'invoiceNo' => $detailView['invoiceNo'] ?? '',
                'issued' => $detailView['issued'] ?? '',
                'due' => $detailView['due'] ?? '',
                'hasNotes' => $detailView['hasNotes'] ?? false,
                'notesHtml' => $detailView['notesHtml'] ?? '',
                'subtotalLabel' => $detailView['subtotalLabel'] ?? '',
                'taxLabel' => $detailView['taxLabel'] ?? '',
                'taxAmountLabel' => $detailView['taxAmountLabel'] ?? '',
                'hasTax' => $detailView['hasTax'] ?? false,
                'amountDueLabel' => $detailView['amountDueLabel'] ?? '',
                'totalLabel' => $detailView['totalLabel'] ?? '',
            ], [
                'subtotalLabel' => $detailView['subtotalLabel'] ?? '',
                'taxLabel' => $detailView['taxLabel'] ?? '',
                'taxAmountLabel' => $detailView['taxAmountLabel'] ?? '',
                'amountDueLabel' => $detailView['amountDueLabel'] ?? '',
                'totalLabel' => $detailView['totalLabel'] ?? '',
            ]);
        } catch (Throwable) {
            return $this->buildInvoicePdf($record, $items);
        }
    }

    /**
     * @param array<string, mixed> $invoice
     * @param array<int, array<string, mixed>> $items
     * @return array<string, mixed>
     */
    private function buildInvoiceDetailViewData(array $invoice, array $items): array
    {
        $businessName = Setting::getValue('business_name');
        $brandName = $businessName !== '' ? $businessName : 'Invoice App';
        $invoiceNo = (string) ($invoice['invoice_no'] ?? 'Invoice');
        $issued = (string) ($invoice['date'] ?? '-');
        $due = (string) ($invoice['due_date'] ?? '-');
        $companyAddress = (string) Setting::getValue('company_address');
        $companyLogo = trim((string) Setting::getValue('company_logo'));
        $companyEmail = trim((string) Setting::getValue('company_email'));
        if ($companyEmail === '') {
            $companyEmail = trim((string) Setting::getValue('mail_from_address'));
        }
        $customerAddress = trim((string) ($invoice['customer_address'] ?? ''));
        $invoiceNotes = trim((string) ($invoice['notes'] ?? ''));
        $hasNotes = $invoiceNotes !== '';
        $taxId = (int) ($invoice['tax_id'] ?? 0);
        $taxRate = (float) ($invoice['tax_rate'] ?? 0);
        $taxAmount = (float) ($invoice['tax_amount'] ?? 0);
        $taxName = trim((string) ($invoice['tax_name'] ?? ''));
        $hasTax = $taxId > 0 || $taxRate > 0 || $taxAmount > 0;

        $subtotal = 0.0;
        $itemRows = [];
        foreach ($items as $item) {
            $subtotal += (float) ($item['subtotal'] ?? 0);
            $itemRows[] = [
                'description' => (string) ($item['description'] ?? ''),
                'qty' => (int) ($item['qty'] ?? 0),
                'unit_label' => Setting::formatMoney((float) ($item['unit_price'] ?? 0), $invoice['currency'] ?? null),
                'subtotal_label' => Setting::formatMoney((float) ($item['subtotal'] ?? 0), $invoice['currency'] ?? null),
            ];
        }

        $subtotalLabel = Setting::formatMoney($subtotal, $invoice['currency'] ?? null);
        $taxAmountLabel = Setting::formatMoney($taxAmount, $invoice['currency'] ?? null);
        $taxRateLabel = number_format($taxRate, 2, '.', '');
        $taxLabel = '';
        if ($hasTax) {
            $taxLabel = $taxName !== '' ? sprintf('%s (%s%%)', $taxName, $taxRateLabel) : sprintf('Tax (%s%%)', $taxRateLabel);
        }
        $totalLabel = Setting::formatMoney((float) ($invoice['total'] ?? 0), $invoice['currency'] ?? null);
        $amountDue = strtolower((string) ($invoice['status'] ?? '')) === 'paid' ? 0.0 : (float) ($invoice['total'] ?? 0);
        $amountDueLabel = Setting::formatMoney($amountDue, $invoice['currency'] ?? null);
        $companyAddressHtml = nl2br(htmlspecialchars($companyAddress, ENT_QUOTES, 'UTF-8'));
        $customerAddressHtml = nl2br(htmlspecialchars($customerAddress, ENT_QUOTES, 'UTF-8'));
        $notesHtml = nl2br(htmlspecialchars($invoiceNotes, ENT_QUOTES, 'UTF-8'));

        return [
            'invoice' => $invoice,
            'items' => $itemRows,
            'brandName' => $brandName,
            'invoiceNo' => $invoiceNo,
            'issued' => $issued,
            'due' => $due,
            'companyAddress' => $companyAddress,
            'companyLogo' => $companyLogo,
            'companyEmail' => $companyEmail,
            'customerAddress' => $customerAddress,
            'invoiceNotes' => $invoiceNotes,
            'hasNotes' => $hasNotes,
            'subtotalLabel' => $subtotalLabel,
            'hasTax' => $hasTax,
            'taxLabel' => $taxLabel,
            'taxAmountLabel' => $taxAmountLabel,
            'totalLabel' => $totalLabel,
            'amountDueLabel' => $amountDueLabel,
            'companyAddressHtml' => $companyAddressHtml,
            'customerAddressHtml' => $customerAddressHtml,
            'notesHtml' => $notesHtml,
        ];
    }

    /**
     * @param array<string, mixed> $invoice
     * @param array<string, mixed> $detail
     * @param array<int, array<string, mixed>> $events
     * @param array<string, mixed>|null $paymentTransaction
     * @param array<string, mixed> $emailOld
     * @param array<string, mixed> $emailErrors
     * @param array<string, mixed> $paymentErrors
     * @param array<string, mixed> $paymentOld
     * @return array<string, mixed>
     */
    private function buildInvoiceShowViewData(
        array $invoice,
        array $detail,
        array $events,
        ?array $paymentTransaction,
        array $emailOld,
        array $emailErrors,
        array $paymentErrors,
        array $paymentOld
    ): array {
        $status = strtolower((string) ($invoice['status'] ?? 'draft'));
        $statusColors = [
            'paid' => 'bg-green-100 text-green-800 border-green-200',
            'sent' => 'bg-blue-100 text-blue-800 border-blue-200',
            'draft' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
        ];
        $badge = $statusColors[$status] ?? 'bg-stone-100 text-stone-700 border-stone-200';

        $invoiceNo = (string) ($detail['invoiceNo'] ?? $invoice['invoice_no'] ?? 'Invoice');
        $brandName = (string) ($detail['brandName'] ?? 'Invoice App');
        $due = (string) ($detail['due'] ?? $invoice['due_date'] ?? '-');
        $totalLabel = (string) ($detail['totalLabel'] ?? Setting::formatMoney((float) ($invoice['total'] ?? 0), $invoice['currency'] ?? null));
        $publicUuid = trim((string) ($invoice['public_uuid'] ?? ''));
        $publicUrl = $publicUuid !== '' ? route('invoices.public', ['uuid' => $publicUuid]) : '';

        $defaultSubject = $invoiceNo . ' from ' . $brandName;
        $defaultTemplate = (string) Setting::getValue('invoice_email_message');
        $defaultMessage = $this->replaceInvoiceEmailTokens(
            $defaultTemplate,
            $invoice,
            $invoiceNo,
            $totalLabel,
            $due,
            $brandName,
            $publicUrl
        );
        $emailSubject = $emailOld['subject'] ?? $defaultSubject;
        $emailMessage = $emailOld['message'] ?? $defaultMessage;
        $autoOpenEmailModal = !empty($emailErrors);
        $autoOpenPaymentModal = !empty($paymentErrors);

        $hasPaymentTransaction = !empty($paymentTransaction);
        $paymentTransactionAmount = null;
        $paymentTransactionDate = '';
        $paymentTransactionDescription = '';
        $paymentTransactionLabel = '';
        if ($hasPaymentTransaction) {
            $transactionAmountValue = (float) ($paymentTransaction['amount'] ?? 0);
            $paymentTransactionAmount = number_format($transactionAmountValue, 2, '.', '');
            $paymentTransactionDate = (string) ($paymentTransaction['date'] ?? '');
            $paymentTransactionDescription = trim((string) ($paymentTransaction['description'] ?? ''));
            $paymentTransactionLabel = Setting::formatMoney($transactionAmountValue, $paymentTransaction['currency'] ?? null);
        }
        $paymentAmount = $paymentOld['amount'] ?? ($paymentTransactionAmount ?? number_format((float) ($invoice['total'] ?? 0), 2, '.', ''));
        $paymentDate = $paymentOld['date'] ?? ($paymentTransactionDate !== '' ? $paymentTransactionDate : date('Y-m-d'));
        $paymentDescription = $paymentOld['description'] ?? ($paymentTransactionDescription !== '' ? $paymentTransactionDescription : ('Payment for ' . $invoiceNo));
        $paymentFormAction = $hasPaymentTransaction
            ? route('invoices.payment.update', ['invoice' => $invoice['id']])
            : route('invoices.payment', ['invoice' => $invoice['id']]);
        $paymentActionLabel = $hasPaymentTransaction ? 'Update payment' : 'Record payment';
        $paymentModalTitle = $hasPaymentTransaction ? 'Update payment' : 'Record payment';
        $paymentModalSubtitle = $hasPaymentTransaction
            ? 'Adjust the payment details for this invoice.'
            : 'This will create a transaction and mark the invoice paid.';

        $publicUrlJson = json_encode($publicUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        $currentUser = Auth::user();
        $currentUserEmail = '';
        if ($currentUser && isset($currentUser->email)) {
            $currentUserEmail = trim((string) $currentUser->email);
        }

        $timeline = $this->buildInvoiceTimeline($invoice, $events);
        $timelineCount = count($timeline);
        $createdEvent = null;
        $lastSentEvent = null;
        $lastOpenedEvent = null;
        $lastStatusEvent = null;
        foreach ($timeline as $event) {
            $type = $event['type'] ?? '';
            if ($type === 'created' && $createdEvent === null) {
                $createdEvent = $event;
            }
            if ($type === 'email_sent' && $lastSentEvent === null) {
                $lastSentEvent = $event;
            }
            if ($type === 'email_opened' && $lastOpenedEvent === null) {
                $lastOpenedEvent = $event;
            }
            if ($type === 'status_changed' && $lastStatusEvent === null) {
                $lastStatusEvent = $event;
            }
        }

        return [
            'status' => $status,
            'statusLabel' => ucfirst($status),
            'badge' => $badge,
            'defaultSubject' => $defaultSubject,
            'emailSubject' => $emailSubject,
            'emailMessage' => $emailMessage,
            'autoOpenEmailModal' => $autoOpenEmailModal,
            'autoOpenPaymentModal' => $autoOpenPaymentModal,
            'hasPaymentTransaction' => $hasPaymentTransaction,
            'paymentTransactionAmount' => $paymentTransactionAmount,
            'paymentTransactionDate' => $paymentTransactionDate,
            'paymentTransactionDescription' => $paymentTransactionDescription,
            'paymentTransactionLabel' => $paymentTransactionLabel,
            'paymentAmount' => $paymentAmount,
            'paymentDate' => $paymentDate,
            'paymentDescription' => $paymentDescription,
            'paymentFormAction' => $paymentFormAction,
            'paymentActionLabel' => $paymentActionLabel,
            'paymentModalTitle' => $paymentModalTitle,
            'paymentModalSubtitle' => $paymentModalSubtitle,
            'publicUrl' => $publicUrl,
            'publicUrlJson' => $publicUrlJson !== false ? $publicUrlJson : '""',
            'currentUserEmail' => $currentUserEmail,
            'timeline' => $timeline,
            'timelineItems' => $timeline,
            'timelineCount' => $timelineCount,
            'createdEvent' => $createdEvent,
            'lastSentEvent' => $lastSentEvent,
            'lastOpenedEvent' => $lastOpenedEvent,
            'lastStatusEvent' => $lastStatusEvent,
        ];
    }

    /**
     * @param array<string, mixed> $invoice
     */
    private function replaceInvoiceEmailTokens(
        string $message,
        array $invoice,
        string $invoiceNo,
        string $totalLabel,
        string $due,
        string $brandName,
        string $publicUrl
    ): string {
        return strtr($message, [
            '{customer_name}' => (string) ($invoice['customer_name'] ?? 'there'),
            '{invoice_no}' => $invoiceNo,
            '{total}' => $totalLabel,
            '{due_date}' => $due,
            '{company_name}' => $brandName,
            '{invoice_public_url}' => $publicUrl,
        ]);
    }

    private function formatInvoiceEmailBody(string $message): string
    {
        $message = trim($message);
        if ($message === '') {
            return '';
        }

        $normalized = str_replace(["\r\n", "\r"], "\n", $message);
        $hasHtml = strip_tags($normalized) !== $normalized;

        if ($hasHtml) {
            return nl2br($normalized, false);
        }

        return nl2br(htmlspecialchars($normalized, ENT_QUOTES, 'UTF-8'), false);
    }

    private function ensureInvoicePublicUuid(int $invoiceId, ?string $uuid): string
    {
        $uuid = trim((string) $uuid);
        if ($uuid !== '') {
            return $uuid;
        }

        $uuid = $this->generatePublicUuid();
        $timestamp = date('Y-m-d H:i:s');
        DBML::table('invoices')->where('id', $invoiceId)->update([
            'public_uuid' => $uuid,
            'updated_at' => $timestamp,
        ]);

        return $uuid;
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

    private function logInvoiceEvent(
        int $invoiceId,
        string $type,
        string $summary,
        ?string $detail = null,
        ?string $token = null,
        ?string $timestamp = null
    ): void {
        $timestamp ??= date('Y-m-d H:i:s');

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

    /**
     * @param array<string, mixed> $invoice
     * @param array<int, array<string, mixed>> $events
     * @return array<int, array{type: string, summary: string, detail: string, timestamp: string}>
     */
    private function buildInvoiceTimeline(array $invoice, array $events): array
    {
        $timeline = [];
        $hasCreated = false;

        foreach ($events as $event) {
            $type = (string) ($event['type'] ?? '');
            if ($type === 'created') {
                $hasCreated = true;
            }

            $timeline[] = [
                'type' => $type,
                'summary' => (string) ($event['summary'] ?? ucfirst($type)),
                'detail' => (string) ($event['detail'] ?? ''),
                'timestamp' => $this->formatTimelineTimestamp($event['created_at'] ?? null),
                'sort' => (string) ($event['created_at'] ?? ''),
            ];
        }

        if (! $hasCreated && !empty($invoice['created_at'])) {
            $status = ucfirst(strtolower((string) ($invoice['status'] ?? 'draft')));
            $timeline[] = [
                'type' => 'created',
                'summary' => 'Invoice created',
                'detail' => 'Status: ' . $status,
                'timestamp' => $this->formatTimelineTimestamp($invoice['created_at']),
                'sort' => (string) $invoice['created_at'],
            ];
        }

        usort($timeline, static fn (array $a, array $b): int => strcmp($b['sort'], $a['sort']));

        return array_map(static function (array $event): array {
            unset($event['sort']);

            return $event;
        }, $timeline);
    }

    private function formatTimelineTimestamp(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $time = strtotime($value);
        if ($time === false) {
            return $value;
        }

        return date('M j, Y H:i', $time);
    }

    private function pixelResponse(): Response
    {
        $pixel = base64_decode('R0lGODlhAQABAPAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');

        return Response::make($pixel ?: '', 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
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

    /**
     * @param array<string, mixed> $invoice
     * @param array<int, array<string, mixed>> $items
     */
    private function buildInvoicePdf(array $invoice, array $items): string
    {
        $lines = $this->buildInvoicePdfLines($invoice, $items);
        $content = $this->buildPdfContentStream($lines);

        $objects = [];
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj";
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj";
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj";
        $objects[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj";
        $objects[] = "5 0 obj\n<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream\nendobj";

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

        return $pdf;
    }

    /**
     * @param array<string, mixed> $invoice
     * @param array<int, array<string, mixed>> $items
     * @return array<int, string>
     */
    private function buildInvoicePdfLines(array $invoice, array $items): array
    {
        $invoiceNo = trim((string) ($invoice['invoice_no'] ?? ''));
        $customerName = trim((string) ($invoice['customer_name'] ?? ''));
        $customerAddress = trim((string) ($invoice['customer_address'] ?? ''));
        $issued = trim((string) ($invoice['date'] ?? ''));
        $due = trim((string) ($invoice['due_date'] ?? ''));
        $subtotalValue = 0.0;
        foreach ($items as $item) {
            $subtotalValue += (float) ($item['subtotal'] ?? 0);
        }
        $subtotalLabel = Setting::formatMoney($subtotalValue, $invoice['currency'] ?? null);
        $taxAmountValue = (float) ($invoice['tax_amount'] ?? 0);
        $taxRateValue = (float) ($invoice['tax_rate'] ?? 0);
        $taxName = trim((string) ($invoice['tax_name'] ?? ''));
        $taxId = (int) ($invoice['tax_id'] ?? 0);
        $hasTax = $taxId > 0 || $taxRateValue > 0 || $taxAmountValue > 0;
        $taxAmountLabel = Setting::formatMoney($taxAmountValue, $invoice['currency'] ?? null);
        $taxRateLabel = number_format($taxRateValue, 2, '.', '');
        $total = Setting::formatMoney((float) ($invoice['total'] ?? 0), $invoice['currency'] ?? null);

        $lines = [];
        if ($invoiceNo !== '') {
            $lines[] = 'Invoice ' . $invoiceNo;
        }
        $dateParts = [];
        if ($issued !== '') {
            $dateParts[] = 'Issued: ' . $issued;
        }
        if ($due !== '') {
            $dateParts[] = 'Due: ' . $due;
        }
        if (!empty($dateParts)) {
            $lines[] = implode('  ', $dateParts);
        }

        $hasCustomerInfo = $customerName !== '' || $customerAddress !== '';
        if ($hasCustomerInfo) {
            $lines[] = '';
            if ($customerName !== '') {
                $lines[] = 'Bill to: ' . $customerName;
            }
            if ($customerAddress !== '') {
                foreach (explode("\n", str_replace(["\r\n", "\r"], "\n", $customerAddress)) as $addressLine) {
                    $lines[] = 'Address: ' . $addressLine;
                }
            }
        }

        if (!empty($items)) {
            $lines[] = '';
            $lines[] = 'Items:';
            foreach ($items as $item) {
                $description = trim((string) ($item['description'] ?? ''));
                $qty = (int) ($item['qty'] ?? 0);
                $unit = Setting::formatMoney((float) ($item['unit_price'] ?? 0), $invoice['currency'] ?? null);
                $subtotal = Setting::formatMoney((float) ($item['subtotal'] ?? 0), $invoice['currency'] ?? null);

                if ($description !== '') {
                    $line = sprintf('%s (x%d @ %s) = %s', $description, $qty, $unit, $subtotal);
                } else {
                    $line = sprintf('x%d @ %s = %s', $qty, $unit, $subtotal);
                }
                $lines[] = $line;
            }
        }

        $lines[] = '';
        $lines[] = 'Subtotal: ' . $subtotalLabel;
        if ($hasTax) {
            $taxLabel = $taxName !== '' ? $taxName : 'Tax';
            $lines[] = sprintf('%s (%s%%): %s', $taxLabel, $taxRateLabel, $taxAmountLabel);
        }
        $lines[] = 'Total: ' . $total;

        $wrapped = [];
        foreach ($lines as $line) {
            $wrapped = array_merge($wrapped, $this->wrapPdfLine($line));
        }

        return $wrapped;
    }

    /**
     * @param array<int, string> $lines
     */
    private function buildPdfContentStream(array $lines): string
    {
        $content = [];
        $content[] = 'BT';
        $content[] = '/F1 12 Tf';
        $content[] = '14 TL';
        $content[] = '50 760 Td';

        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $content[] = 'T*';
            }
            $content[] = '(' . $this->escapePdfText($line) . ') Tj';
        }

        $content[] = 'ET';

        return implode("\n", $content);
    }

    /**
     * @return array<int, string>
     */
    private function wrapPdfLine(string $line, int $limit = 90): array
    {
        $clean = $this->sanitizePdfText($line);
        if ($clean === '') {
            return [''];
        }

        $wrapped = wordwrap($clean, $limit, "\n", true);

        return explode("\n", $wrapped);
    }

    private function sanitizePdfText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[^\x20-\x7E]/', '?', $text);

        return trim((string) $text);
    }

    private function escapePdfText(string $text): string
    {
        $text = $this->sanitizePdfText($text);

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    /**
     * @param array<string, mixed> $invoice
     */
    private function buildInvoicePdfName(array $invoice): string
    {
        $invoiceNo = (string) ($invoice['invoice_no'] ?? 'invoice');
        $safe = preg_replace('/[^A-Za-z0-9_\-]/', '-', $invoiceNo);
        $safe = trim((string) $safe, '-');

        if ($safe === '') {
            $safe = 'invoice';
        }

        return $safe . '.pdf';
    }
}
