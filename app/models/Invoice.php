<?php

namespace App\Models;

use Zero\Lib\Model;

class Invoice extends Model
{
    protected string $table = 'invoices';

    protected array $fillable = [
        'client_id',
        'invoice_no',
        'date',
        'due_date',
        'status',
        'total',
    ];

    protected bool $timestamps = true;
}
