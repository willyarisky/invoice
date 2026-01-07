<?php

namespace App\Controllers;

class SettingsController
{
    public function index()
    {
        $settings = [
            ['label' => 'Business Name', 'value' => 'Akaunting Lite'],
            ['label' => 'Default Currency', 'value' => 'USD'],
            ['label' => 'Primary Accent', 'value' => 'Stone'],
            ['label' => 'Notification Emails', 'value' => 'enabled'],
        ];

        return view('settings/index', [
            'settings' => $settings,
        ]);
    }
}
