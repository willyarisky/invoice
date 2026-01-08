<?php

namespace App\Models;

use Zero\Lib\Model;

class Client extends Model
{
    protected ?string $table = 'clients';

    protected array $fillable = [
        'name',
        'email',
        'address',
    ];

    protected bool $timestamps = true;
}
