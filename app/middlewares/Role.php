<?php

namespace App\Middlewares;

use App\Models\Admin;
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
            $email = strtolower((string) ($user->email ?? ''));

            if ($email !== '' && Admin::query()->where('email', $email)->exists()) {
                return null;
            }
        }

        return Response::redirect('/');
    }
}
