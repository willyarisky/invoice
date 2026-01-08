<?php

$encryption = \App\Models\Setting::getValue('mail_encryption');
if (is_string($encryption)) {
    $encryption = strtolower(trim($encryption));
    if ($encryption === '' || $encryption === 'null' || $encryption === 'none') {
        $encryption = null;
    }
}

if ($encryption !== null && ! in_array($encryption, ['ssl', 'tls'], true)) {
    $encryption = null;
}

return [
    'default' => \App\Models\Setting::getValue('mail_mailer'),

    'from' => [
        'address' => \App\Models\Setting::getValue('mail_from_address'),
        'name' => \App\Models\Setting::getValue('mail_from_name'),
    ],

    'smtp' => [
        'host' => \App\Models\Setting::getValue('mail_host'),
        'port' => (int) \App\Models\Setting::getValue('mail_port'),
        'username' => \App\Models\Setting::getValue('mail_username') ?: null,
        'password' => \App\Models\Setting::getValue('mail_password') ?: null,
        'encryption' => $encryption, // tls, ssl, or null
        'timeout' => (int) env('MAIL_TIMEOUT', 30),
        'hello' => env('MAIL_HELO_DOMAIN', null) ?: null,
        'allow_self_signed' => filter_var(env('MAIL_ALLOW_SELF_SIGNED', false), FILTER_VALIDATE_BOOL),
        'verify_peer' => filter_var(env('MAIL_VERIFY_PEER', true), FILTER_VALIDATE_BOOL),
    ],
];
