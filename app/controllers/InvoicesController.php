<?php

namespace App\Controllers;

use DateTime;
use Throwable;
use Zero\Lib\Database;
use Zero\Lib\DB\DBML;
use Zero\Lib\Http\Request;
use Zero\Lib\Http\Response;

class InvoicesController
{
    /**
     * Draft, sent, and paid are the allowed lifecycle phases.
     *
     * @var string[]
     */
    private array $allowedStatuses = ['draft', 'sent', 'paid'];

    public function index()
    {
        $invoices = DBML::table('invoices as i')
            ->select('i.id', 'i.invoice_no', 'i.date', 'i.due_date', 'i.status', 'i.total', 'c.name as client_name')
            ->leftJoin('clients as c', 'c.id', '=', 'i.client_id')
            ->orderByDesc('i.date')
            ->orderByDesc('i.id')
            ->get();

        return view('invoices/index', [
            'invoices' => $invoices,
        ]);
    }

    public function create()
    {
        return view('invoices/create', $this->resolveCreateViewData());
    }

    public function store(Request $request)
    {
        $clientId = (int) $request->input('client_id');
        $invoiceNo = trim((string) $request->input('invoice_no', $this->generateInvoiceNumber()));
        $invoiceDate = $this->normaliseDate((string) $request->input('date'), fallback: date('Y-m-d'));
        $dueDate = $this->normaliseDate((string) $request->input('due_date'), allowEmpty: true);
        $status = strtolower(trim((string) $request->input('status', 'draft')));
        $items = $this->normaliseItems($request->input('items', []));

        if ($clientId <= 0) {
            return view('invoices/create', array_merge(
                $this->resolveCreateViewData(),
                [
                    'error' => 'Please choose a client before saving the invoice.',
                    'old' => $request->all(),
                    'invoiceNumber' => $invoiceNo,
                ]
            ));
        }

        if ($items === []) {
            return view('invoices/create', array_merge(
                $this->resolveCreateViewData(),
                [
                    'error' => 'Add at least one line item to calculate the invoice total.',
                    'old' => $request->all(),
                    'invoiceNumber' => $invoiceNo,
                ]
            ));
        }

        if (!in_array($status, $this->allowedStatuses, true)) {
            $status = 'draft';
        }

        $clientExists = DBML::table('clients')->where('id', $clientId)->exists();

        if (!$clientExists) {
            return view('invoices/create', array_merge(
                $this->resolveCreateViewData(),
                [
                    'error' => 'The selected client could not be found. Please create the client first.',
                    'old' => $request->all(),
                    'invoiceNumber' => $invoiceNo,
                ]
            ));
        }

        $total = array_reduce($items, static fn ($carry, $item) => $carry + $item['subtotal'], 0.0);
        $timestamp = date('Y-m-d H:i:s');

        $connection = Database::write();
        $pdo = $connection->connection;

        try {
            $pdo->beginTransaction();

            $invoiceStmt = $pdo->prepare('
                INSERT INTO invoices (client_id, invoice_no, date, due_date, status, total, created_at, updated_at)
                VALUES (:client_id, :invoice_no, :date, :due_date, :status, :total, :created_at, :updated_at)
            ');

            $invoiceStmt->execute([
                ':client_id' => $clientId,
                ':invoice_no' => $invoiceNo,
                ':date' => $invoiceDate,
                ':due_date' => $dueDate,
                ':status' => $status,
                ':total' => number_format($total, 2, '.', ''),
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

            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return view('invoices/create', array_merge(
                $this->resolveCreateViewData(),
                [
                    'error' => 'We could not save the invoice. ' . $exception->getMessage(),
                    'old' => $request->all(),
                    'invoiceNumber' => $invoiceNo,
                ]
            ));
        }

        return Response::redirectRoute('invoices.show', ['invoice' => $invoiceId]);
    }

    public function show(int $invoice)
    {
        $invoiceRecord = DBML::table('invoices as i')
            ->select(
                'i.id',
                'i.invoice_no',
                'i.date',
                'i.due_date',
                'i.status',
                'i.total',
                'c.name as client_name',
                'c.email as client_email',
                'c.address as client_address'
            )
            ->leftJoin('clients as c', 'c.id', '=', 'i.client_id')
            ->where('i.id', $invoice)
            ->first();

        if ($invoiceRecord === null) {
            return Response::json(['message' => 'Invoice not found'], 404);
        }

        $items = DBML::table('invoice_items')
            ->where('invoice_id', $invoice)
            ->orderBy('id')
            ->get();

        return view('invoices/show', [
            'invoice' => $invoiceRecord,
            'items' => $items,
        ]);
    }

    private function resolveCreateViewData(): array
    {
        return [
            'clients' => DBML::table('clients')->orderBy('name')->get(),
            'invoiceNumber' => $this->generateInvoiceNumber(),
            'today' => date('Y-m-d'),
            'statuses' => $this->allowedStatuses,
            'error' => null,
            'old' => [],
        ];
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
}
