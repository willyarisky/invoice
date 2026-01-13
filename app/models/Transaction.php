<?php

namespace App\Models;

use Zero\Lib\Model;

class Transaction extends Model
{
    protected ?string $table = 'transactions';

    protected array $fillable = [
        'type',
        'amount',
        'currency',
        'date',
        'description',
        'source',
        'vendor_id',
        'invoice_id',
    ];

    protected bool $timestamps = true;
}
