<?php

use App\Controllers\Auth\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\Auth\EmailVerificationController;
use App\Controllers\HomeController;
use App\Controllers\Auth\PasswordResetController;
use App\Controllers\Admin\UsersController as AdminUsersController;
use App\Controllers\ClientsController;
use App\Controllers\InvoicesController;
use App\Controllers\SettingsController;
use App\Controllers\TransactionsController;
use App\Controllers\VendorsController;
use App\Middlewares\Auth as AuthMiddleware;
use App\Middlewares\Guest as GuestMiddleware;
use App\Middlewares\Role as RoleMiddleware;
use Zero\Lib\Router;

Router::group(['middleware' => [AuthMiddleware::class]], function () {
    Router::get('/', [HomeController::class, 'index'])->name('home');

    Router::get('/invoices', [InvoicesController::class, 'index'])->name('invoices.index');
    Router::get('/invoices/create', [InvoicesController::class, 'create'])->name('invoices.create');
    Router::get('/invoices/{invoice}/duplicate', [InvoicesController::class, 'duplicate'])->name('invoices.duplicate');
    Router::post('/invoices', [InvoicesController::class, 'store'])->name('invoices.store');
    Router::get('/invoices/{invoice}/edit', [InvoicesController::class, 'edit'])->name('invoices.edit');
    Router::post('/invoices/{invoice}/update', [InvoicesController::class, 'update'])->name('invoices.update');
    Router::post('/invoices/{invoice}/mark-sent', [InvoicesController::class, 'markSent'])->name('invoices.markSent');
    Router::post('/invoices/{invoice}/email', [InvoicesController::class, 'sendEmail'])->name('invoices.email');
    Router::post('/invoices/{invoice}/payment', [InvoicesController::class, 'recordPayment'])->name('invoices.payment');
    Router::post('/invoices/{invoice}/payment/update', [InvoicesController::class, 'updatePayment'])->name('invoices.payment.update');
    Router::get('/invoices/{invoice}/download', [InvoicesController::class, 'download'])->name('invoices.download');
    Router::get('/invoices/{invoice}', [InvoicesController::class, 'show'])->name('invoices.show');

    Router::get('/clients', [ClientsController::class, 'index'])->name('clients.index');
    Router::get('/clients/create', [ClientsController::class, 'create'])->name('clients.create');
    Router::get('/clients/{client}', [ClientsController::class, 'show'])->name('clients.show');
    Router::get('/clients/{client}/edit', [ClientsController::class, 'edit'])->name('clients.edit');
    Router::post('/clients', [ClientsController::class, 'store'])->name('clients.store');
    Router::post('/clients/{client}/email', [ClientsController::class, 'sendEmail'])->name('clients.email');
    Router::post('/clients/{client}/update', [ClientsController::class, 'update'])->name('clients.update');
    Router::get('/transactions', [TransactionsController::class, 'index'])->name('transactions.index');
    Router::get('/transactions/create', [TransactionsController::class, 'create'])->name('transactions.create');
    Router::get('/transactions/{transaction}', [TransactionsController::class, 'show'])->name('transactions.show');
    Router::get('/transactions/{transaction}/edit', [TransactionsController::class, 'edit'])->name('transactions.edit');
    Router::post('/transactions', [TransactionsController::class, 'store'])->name('transactions.store');
    Router::post('/transactions/{transaction}/update', [TransactionsController::class, 'update'])->name('transactions.update');
    Router::get('/vendors', [VendorsController::class, 'index'])->name('vendors.index');
    Router::get('/vendors/create', [VendorsController::class, 'create'])->name('vendors.create');
    Router::post('/vendors', [VendorsController::class, 'store'])->name('vendors.store');
    Router::get('/vendors/{vendor}', [VendorsController::class, 'show'])->name('vendors.show');
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

Router::get('/invoices/{invoice}/email-open/{token}', [InvoicesController::class, 'trackEmailOpen'])->name('invoices.email.open');
Router::get('/invoices/public/{uuid}', [InvoicesController::class, 'publicView'])->name('invoices.public');

Router::group(['middleware' => [AuthMiddleware::class, [RoleMiddleware::class, 'admin']]], function () {
    Router::get('/settings/admin', [AdminUsersController::class, 'create'])->name('settings.admin.users');
    Router::post('/settings/admin', [AdminUsersController::class, 'store'])->name('settings.admin.users.store');
    Router::post('/settings/admin/{user}/update', [AdminUsersController::class, 'update'])->name('settings.admin.users.update');
    Router::post('/settings/admin/{user}/delete', [AdminUsersController::class, 'delete'])->name('settings.admin.users.delete');
});

// Guest-only authentication routes
Router::group(['middleware' => GuestMiddleware::class, 'name' => 'auth'], function () {
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
