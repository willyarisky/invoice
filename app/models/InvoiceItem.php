<?php

namespace App\Models;

use Zero\Lib\Model;

class InvoiceItem extends Model
{
    protected string $table = 'invoice_items';

    protected array $fillable = [
        'invoice_id',
        'description',
        'qty',
        'unit_price',
        'subtotal',
    ];

    protected bool $timestamps = true;
}
