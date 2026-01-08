<?php

namespace App\Models;

use Zero\Lib\Model;

class Admin extends Model
{
    protected ?string $table = 'admin';

    protected array $fillable = [
        'name',
        'email',
        'password_hash',
        'last_login',
    ];

    protected bool $timestamps = true;
}
