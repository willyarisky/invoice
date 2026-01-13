<?php

namespace App\Models;

use Zero\Lib\Model;

class Invoice extends Model
{
    protected ?string $table = 'invoices';

    protected array $fillable = [
        'customer_id',
        'invoice_no',
        'date',
        'due_date',
        'status',
        'currency',
        'total',
        'tax_id',
        'tax_rate',
        'tax_amount',
        'notes',
        'public_uuid',
    ];

    protected bool $timestamps = true;
}
