<?php

use App\Controllers\Auth\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\Auth\EmailVerificationController;
use App\Controllers\HomeController;
use App\Controllers\Auth\PasswordResetController;
use App\Controllers\Auth\RegisterController;
use App\Controllers\Admin\UsersController as AdminUsersController;
use App\Controllers\ClientsController;
use App\Controllers\InvoicesController;
use App\Controllers\SettingsController;
use App\Middlewares\Auth as AuthMiddleware;
use App\Middlewares\Guest as GuestMiddleware;
use App\Middlewares\Role as RoleMiddleware;
use Zero\Lib\Router;

Router::group(['middleware' => [AuthMiddleware::class]], function () {
    Router::get('/', [HomeController::class, 'index'])->name('home');

    Router::get('/invoices', [InvoicesController::class, 'index'])->name('invoices.index');
    Router::get('/invoices/create', [InvoicesController::class, 'create'])->name('invoices.create');
    Router::post('/invoices', [InvoicesController::class, 'store'])->name('invoices.store');
    Router::get('/invoices/{invoice}', [InvoicesController::class, 'show'])->name('invoices.show');

    Router::get('/clients', [ClientsController::class, 'index'])->name('clients.index');
    Router::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
});

// Guest-only authentication routes
Router::group(['middleware' => GuestMiddleware::class, 'name' => 'auth'], function () {
    Router::get('/register', [RegisterController::class, 'show'])->name('register.show');
    Router::post('/register', [RegisterController::class, 'store'])->name('register.store');

    Router::get('/login', [AuthController::class, 'showLogin'])->name('login.show');
    Router::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Router::group(['prefix' => '/password', 'name' => 'password'], function () {
        Router::get('/forgot', [PasswordResetController::class, 'request'])->name('forgot');
        Router::post('/forgot', [PasswordResetController::class, 'email'])->name('email');
        Router::get('/reset/{token}', [PasswordResetController::class, 'show'])->name('reset');
        Router::post('/reset', [PasswordResetController::class, 'update'])->name('update');
    });
});

// Routes that support both guests and authenticated users
Router::group(['prefix' => '/email', 'name' => 'email'], function () {
    Router::get('/verify', [EmailVerificationController::class, 'notice'])->name('verify.notice');
    Router::get('/verify/{token}', [EmailVerificationController::class, 'verify'])->name('verify.process');
    Router::post('/verification-notification', [EmailVerificationController::class, 'resend'])->name('verification.resend');
});

// Authenticated-only routes
Router::group(['middleware' => [AuthMiddleware::class], 'name' => 'auth'], function () {
    Router::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Router::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Router::group(
    [
        'prefix' => '/admin',
        'name' => 'admin',
        'middleware' => [AuthMiddleware::class, [RoleMiddleware::class, 'admin']],
    ],
    function () {
        Router::get('/users', [AdminUsersController::class, 'create'])->name('users.create');
        Router::post('/users', [AdminUsersController::class, 'store'])->name('users.store');
    }
);
