<?php

namespace App\Models;

use Zero\Lib\Model;

class Vendor extends Model
{
    protected ?string $table = 'vendors';

    protected array $fillable = [
        'name',
        'email',
        'phone',
        'address',
    ];

    protected bool $timestamps = true;
}
