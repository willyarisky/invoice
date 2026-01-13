<?php

namespace App\Controllers;

use App\Services\ViewData;
use Zero\Lib\Http\Request;
use Zero\Lib\Auth;

/**
 * Display the authenticated user's dashboard.
 */
class DashboardController
{
    /**
     * Render the dashboard view with the current user context.
     */
    public function index()
    {
        $request = Request::instance();
        $user = Auth::user();

        return view('pages/dashboard', array_merge(ViewData::appLayout(), [
            'user' => $user,
        ]));
    }
}
