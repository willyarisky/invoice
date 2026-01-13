<?php

namespace App\Models;

use Zero\Lib\Model;

class Customer extends Model
{
    protected ?string $table = 'customers';

    protected array $fillable = [
        'name',
        'email',
        'address',
    ];

    protected bool $timestamps = true;
}
