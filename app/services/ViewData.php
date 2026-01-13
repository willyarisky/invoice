<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Setting;
use Zero\Lib\Auth\Auth;
use Zero\Lib\Http\Request;

class ViewData
{
    public static function appLayout(): array
    {
        $brandName = self::brandName('Invoice App');
        $request = Request::instance();
        $path = trim($request->path(), '/');
        $currentUser = Auth::user();
        $isAdmin = false;

        if ($currentUser && isset($currentUser->email)) {
            $isAdmin = Admin::query()
                ->where('email', strtolower((string) $currentUser->email))
                ->exists();
        }

        $navItems = [
            ['label' => 'Dashboard', 'href' => route('home'), 'pattern' => '/^$/'],
            ['label' => 'Invoices', 'href' => route('invoices.index'), 'pattern' => '/^invoices/'],
            ['label' => 'Clients', 'href' => route('clients.index'), 'pattern' => '/^clients/'],
            ['label' => 'Transactions', 'href' => route('transactions.index'), 'pattern' => '/^transactions/'],
            ['label' => 'Vendors', 'href' => route('vendors.index'), 'pattern' => '/^vendors/'],
            ['label' => 'Settings', 'href' => route('settings.index'), 'pattern' => '/^settings/'],
        ];

        foreach ($navItems as &$item) {
            $pattern = $item['pattern'] ?? '';
            $item['isActive'] = $pattern === '/^$/' ? $path === '' : (bool) preg_match($pattern, $path);
            unset($item['pattern']);
        }
        unset($item);

        return [
            'brandName' => $brandName,
            'currentUser' => $currentUser,
            'isAdmin' => $isAdmin,
            'navItems' => $navItems,
            'settingsLinkBase' => 'flex items-center rounded-xl px-3 py-2 text-sm',
        ];
    }

    public static function authLayout(): array
    {
        return [
            'brandName' => self::brandName('Simple Invoice Suite'),
        ];
    }

    private static function brandName(string $fallback): string
    {
        $businessName = (string) Setting::getValue('business_name');
        $businessName = trim($businessName);

        return $businessName !== '' ? $businessName : $fallback;
    }
}
