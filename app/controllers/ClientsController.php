<?php

namespace App\Controllers;

use Zero\Lib\DB\DBML;

class ClientsController
{
    public function index()
    {
        $clients = DBML::table('clients as c')
            ->select(
                'c.id',
                'c.name',
                'c.email',
                'c.address',
                DBML::raw('COALESCE(COUNT(i.id), 0) as invoice_count'),
                DBML::raw('COALESCE(SUM(i.total), 0) as lifetime_value')
            )
            ->leftJoin('invoices as i', 'i.client_id', '=', 'c.id')
            ->groupBy('c.id', 'c.name', 'c.email', 'c.address')
            ->orderBy('c.name')
            ->get();

        return view('clients/index', [
            'clients' => $clients,
        ]);
    }
}
