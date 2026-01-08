<?php

namespace App\Controllers;

use Zero\Lib\DB\DBML;
use Zero\Lib\Http\Request;
use Zero\Lib\Http\Response;
use Zero\Lib\Session;
use Zero\Lib\Validation\ValidationException;

class ClientsController
{
    public function index()
    {
        $status = Session::get('client_status');
        $errors = Session::get('client_errors') ?? [];
        $old = Session::get('client_old') ?? [];

        Session::remove('client_status');
        Session::remove('client_errors');
        Session::remove('client_old');

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
            'status' => $status,
            'errors' => $errors,
            'old' => $old,
        ]);
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

            return Response::redirect('/clients');
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
}
