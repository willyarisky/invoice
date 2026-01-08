<?php

namespace App\Controllers\Admin;

use App\Models\User;
use App\Services\Auth\EmailVerificationService;
use Throwable;
use Zero\Lib\Crypto;
use Zero\Lib\DB\DBML;
use Zero\Lib\Http\Request;
use Zero\Lib\Http\Response;
use Zero\Lib\Session;
use Zero\Lib\Validation\ValidationException;

class UsersController
{
    public function create(): Response
    {
        $status = Session::get('admin_user_status');
        $errors = Session::get('admin_user_errors') ?? [];
        $old = Session::get('admin_user_old') ?? [];

        Session::remove('admin_user_status');
        Session::remove('admin_user_errors');
        Session::remove('admin_user_old');

        $users = DBML::table('users')
            ->select('id', 'name', 'email', 'email_verified_at', 'created_at')
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        return view('admin/users/create', compact('status', 'errors', 'old', 'users'));
    }

    public function store(Request $request): Response
    {
        Session::remove('admin_user_errors');
        Session::remove('admin_user_old');
        Session::remove('admin_user_status');

        try {
            $data = $request->validate(
                [
                    'name' => ['required', 'string', 'min:3'],
                    'email' => ['required', 'email', 'unique:users,email'],
                    'password' => ['required', 'string', 'min:8', 'password:letters,numbers', 'confirmed'],
                    'password_confirmation' => ['required', 'string'],
                ],
                [
                    'password.min' => 'Passwords must contain at least :min characters.',
                ],
                [
                    'password' => 'password',
                    'password_confirmation' => 'password confirmation',
                ]
            );
        } catch (ValidationException $exception) {
            $messages = array_map(
                static fn (array $errors): string => (string) ($errors[0] ?? ''),
                $exception->errors()
            );

            Session::set('admin_user_errors', $messages);
            Session::set('admin_user_old', [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'send_verification' => (bool) $request->input('send_verification'),
            ]);

            return Response::redirect('/admin/users');
        }

        $sendVerification = (bool) $request->input('send_verification');

        $payload = [
            'name' => (string) $data['name'],
            'email' => strtolower((string) $data['email']),
            'password' => Crypto::hash($data['password']),
        ];

        if (! $sendVerification) {
            $payload['email_verified_at'] = date('Y-m-d H:i:s');
        }

        $user = User::create($payload);

        $status = 'User created successfully.';

        if ($sendVerification) {
            try {
                EmailVerificationService::send($user);
                $status = 'User created. Verification email sent.';
            } catch (Throwable) {
                $status = 'User created, but the verification email could not be sent.';
            }
        }

        Session::set('admin_user_status', $status);

        return Response::redirect('/admin/users');
    }
}
