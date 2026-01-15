<?php

namespace App\Controllers\Settings;

use App\Models\User;
use App\Services\ViewData;
use Zero\Lib\Crypto;
use Zero\Lib\DB\DBML;
use Zero\Lib\Http\Request;
use Zero\Lib\Http\Response;
use Zero\Lib\Session;
use Zero\Lib\Validation\ValidationException;

class UsersController
{
    public function index(): Response
    {
        $layout = ViewData::appLayout();
        $status = Session::get('settings_user_status');
        $errors = Session::get('settings_user_errors') ?? [];
        $old = Session::get('settings_user_old') ?? [];
        $editId = Session::get('settings_user_edit_id');

        Session::remove('settings_user_status');
        Session::remove('settings_user_errors');
        Session::remove('settings_user_old');
        Session::remove('settings_user_edit_id');

        $users = DBML::table('users')
            ->select('id', 'name', 'email', 'email_verified_at', 'created_at')
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        $autoOpenAddModal = !empty($errors) && empty($editId);
        $autoOpenEditModal = !empty($editId);
        $addOld = empty($editId) ? ($old ?? []) : [];
        $editOld = !empty($editId) ? ($old ?? []) : [];
        $editActionValue = $autoOpenEditModal ? route('settings.users.update', ['user' => $editId]) : '';
        $editActionJson = json_encode($editActionValue, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $editNameJson = json_encode($editOld['name'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $editEmailJson = json_encode($editOld['email'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        $userRows = [];
        foreach ($users as $user) {
            $verifiedAt = $user['email_verified_at'] ?? null;
            $userRows[] = [
                'id' => $user['id'] ?? null,
                'name' => $user['name'] ?? 'User',
                'email' => $user['email'] ?? 'N/A',
                'email_verified_at' => $verifiedAt,
                'verified_label' => $verifiedAt ? 'Verified' : 'Pending',
                'verified_class' => $verifiedAt ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700',
                'created_at' => $user['created_at'] ?? '',
                'edit_action' => route('settings.users.update', ['user' => $user['id'] ?? 0]),
            ];
        }

        return view('settings/users', array_merge($layout, [
            'status' => $status,
            'errors' => $errors,
            'addOld' => $addOld,
            'editOld' => $editOld,
            'editId' => $editId,
            'users' => $userRows,
            'autoOpenAddModal' => $autoOpenAddModal,
            'autoOpenEditModal' => $autoOpenEditModal,
            'editActionJson' => $editActionJson !== false ? $editActionJson : '""',
            'editNameJson' => $editNameJson !== false ? $editNameJson : '""',
            'editEmailJson' => $editEmailJson !== false ? $editEmailJson : '""',
            'settingsActive' => 'users',
        ]));
    }

    public function store(Request $request): Response
    {
        Session::remove('settings_user_errors');
        Session::remove('settings_user_old');
        Session::remove('settings_user_status');

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

            Session::set('settings_user_errors', $messages);
            Session::set('settings_user_old', [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
            ]);

            return Response::redirect('/settings/users');
        }

        $payload = [
            'name' => (string) $data['name'],
            'email' => strtolower((string) $data['email']),
            'password' => Crypto::hash($data['password']),
            'email_verified_at' => date('Y-m-d H:i:s'),
        ];

        User::create($payload);
        Session::set('settings_user_status', 'User created successfully.');

        return Response::redirect('/settings/users');
    }

    public function update(int $user, Request $request): Response
    {
        Session::remove('settings_user_errors');
        Session::remove('settings_user_old');
        Session::remove('settings_user_status');

        $rules = [
            'name' => ['required', 'string', 'min:3'],
            'email' => ['required', 'email', 'unique:users,email,' . $user . ',id'],
        ];

        $password = (string) $request->input('password');
        if ($password !== '') {
            $rules['password'] = ['required', 'string', 'min:8', 'password:letters,numbers', 'confirmed'];
            $rules['password_confirmation'] = ['required', 'string'];
        }

        try {
            $data = $request->validate($rules);
        } catch (ValidationException $exception) {
            $messages = array_map(
                static fn (array $errors): string => (string) ($errors[0] ?? ''),
                $exception->errors()
            );

            Session::set('settings_user_errors', $messages);
            Session::set('settings_user_old', [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
            ]);
            Session::set('settings_user_edit_id', $user);

            return Response::redirect('/settings/users');
        }

        /** @var User|null $record */
        $record = User::query()->where('id', $user)->first();
        if (! $record instanceof User) {
            Session::set('settings_user_errors', ['email' => 'User not found.']);
            Session::set('settings_user_edit_id', $user);

            return Response::redirect('/settings/users');
        }

        $payload = [
            'name' => (string) $data['name'],
            'email' => strtolower((string) $data['email']),
        ];

        if ($password !== '') {
            $payload['password'] = Crypto::hash($password);
        }

        $record->update($payload);

        Session::set('settings_user_status', 'User updated successfully.');

        return Response::redirect('/settings/users');
    }

    public function delete(int $user): Response
    {
        Session::remove('settings_user_errors');
        Session::remove('settings_user_old');
        Session::remove('settings_user_status');

        /** @var User|null $record */
        $record = User::query()->where('id', $user)->first();

        if (! $record instanceof User) {
            Session::set('settings_user_errors', ['email' => 'User not found.']);

            return Response::redirect('/settings/users');
        }

        $record->delete();

        Session::set('settings_user_status', 'User removed successfully.');

        return Response::redirect('/settings/users');
    }
}
