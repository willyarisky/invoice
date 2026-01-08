<?php

namespace App\Controllers;

use Zero\Lib\DB\DBML;

class HomeController
{
    public function index()
    {
        $recentInvoices = DBML::table('invoices as i')
            ->select('i.id', 'i.invoice_no', 'i.date', 'i.due_date', 'i.status', 'i.currency', 'i.total', 'c.name as client_name')
            ->leftJoin('clients as c', 'c.id', '=', 'i.client_id')
            ->orderByDesc('i.date')
            ->orderByDesc('i.id')
            ->limit(6)
            ->get();

        $statusRows = DBML::table('invoices')
            ->select('status', DBML::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        $statusSummary = ['draft' => 0, 'sent' => 0, 'paid' => 0];

        foreach ($statusRows as $row) {
            $key = strtolower((string) ($row['status'] ?? ''));
            if (array_key_exists($key, $statusSummary)) {
                $statusSummary[$key] = (int) ($row['total'] ?? 0);
            }
        }

        $invoiceCount = DBML::table('invoices')->count();
        $clientCount = DBML::table('clients')->count();

        $outstandingRow = DBML::table('invoices')
            ->selectRaw('COALESCE(SUM(total), 0) as amount')
            ->where('status', '!=', 'paid')
            ->first();

        $outstanding = (float) ($outstandingRow['amount'] ?? 0);

        return view('pages/home', [
            'recentInvoices' => $recentInvoices,
            'statusSummary' => $statusSummary,
            'metrics' => [
                'totalInvoices' => $invoiceCount,
                'totalClients' => $clientCount,
                'outstanding' => $outstanding,
            ],
        ]);
    }
}
