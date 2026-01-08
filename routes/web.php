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
    Router::post('/clients', [ClientsController::class, 'store'])->name('clients.store');
    Router::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Router::post('/settings/company', [SettingsController::class, 'updateCompany'])->name('settings.company.update');
    Router::get('/settings/currency', [SettingsController::class, 'currency'])->name('settings.currency.index');
    Router::post('/settings/currency', [SettingsController::class, 'updateCurrency'])->name('settings.currency.update');
    Router::post('/settings/currency/add', [SettingsController::class, 'storeCurrency'])->name('settings.currency.store');
    Router::post('/settings/currency/{currency}/update', [SettingsController::class, 'updateCurrencyEntry'])->name('settings.currency.entry.update');
    Router::post('/settings/currency/{currency}/delete', [SettingsController::class, 'deleteCurrency'])->name('settings.currency.delete');
    Router::get('/settings/email', [SettingsController::class, 'email'])->name('settings.email.index');
    Router::post('/settings/email', [SettingsController::class, 'updateEmail'])->name('settings.email.update');
    Router::get('/settings/categories', [SettingsController::class, 'categories'])->name('settings.categories.index');
    Router::post('/settings/categories', [SettingsController::class, 'storeCategory'])->name('settings.categories.store');
    Router::post('/settings/categories/{category}/update', [SettingsController::class, 'updateCategory'])->name('settings.categories.update');
    Router::post('/settings/categories/{category}/delete', [SettingsController::class, 'deleteCategory'])->name('settings.categories.delete');
    Router::get('/settings/taxes', [SettingsController::class, 'taxes'])->name('settings.taxes.index');
    Router::post('/settings/taxes', [SettingsController::class, 'storeTax'])->name('settings.taxes.store');
    Router::post('/settings/taxes/{tax}/update', [SettingsController::class, 'updateTax'])->name('settings.taxes.update');
    Router::post('/settings/taxes/{tax}/delete', [SettingsController::class, 'deleteTax'])->name('settings.taxes.delete');
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
