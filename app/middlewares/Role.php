<?php

namespace App\Middlewares;

use Zero\Lib\Auth\Auth as AuthManager;
use Zero\Lib\Http\Response;

class Role
{
    public function handle(string $role): ?Response
    {
        $user = AuthManager::user();

        if (! $user) {
            return Response::redirect('/login');
        }

        $role = strtolower(trim($role));

        if ($role === 'admin') {
            return null;
        }

        return Response::redirect('/');
    }
}
