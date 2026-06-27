<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Company\ActivityLogController;
use App\Http\Controllers\Company\CustomerController;
use App\Http\Controllers\Company\InvoiceController;
use App\Http\Controllers\Company\NotificationController;
use App\Http\Controllers\Company\PaymentController;
use App\Http\Controllers\Company\PlanController;
use App\Http\Controllers\Company\ReportController;
use App\Http\Controllers\Company\RevenueRecognitionController;
use App\Http\Controllers\Company\SettingsController;
use App\Http\Controllers\Company\SubscriptionController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');

    Route::post('register', [RegisteredUserController::class, 'store'])
        ->name('register');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::get('dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::middleware('role:company')->prefix('company')->name('company.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'company'])->name('dashboard');

        Route::get('customers', [CustomerController::class, 'index'])->name('customers');
        Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        Route::get('plans', [PlanController::class, 'index'])->name('plans');
        Route::post('plans', [PlanController::class, 'store'])->name('plans.store');
        Route::put('plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
        Route::delete('plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');

        Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions');
        Route::post('subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
        Route::put('subscriptions/{subscription}', [SubscriptionController::class, 'update'])->name('subscriptions.update');
        Route::delete('subscriptions/{subscription}', [SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');

        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices');
        Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::post('invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::delete('invoices/{invoice}', [InvoiceController::class, 'void'])->name('invoices.void');

        Route::get('payments', [PaymentController::class, 'index'])->name('payments');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

        Route::get('revenue-recognition', [RevenueRecognitionController::class, 'index'])->name('revenue-recognition');
        Route::post('revenue-recognition/recognize', [RevenueRecognitionController::class, 'recognize'])->name('revenue-recognition.recognize');

        Route::get('income-statement', [ReportController::class, 'incomeStatement'])->name('income-statement');
        Route::get('balance-sheet', [ReportController::class, 'balanceSheet'])->name('balance-sheet');

        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::get('notifications/{notification}', [NotificationController::class, 'open'])->name('notifications.open');

        Route::get('settings', [SettingsController::class, 'index'])->name('settings');
        Route::put('settings/company', [SettingsController::class, 'updateCompany'])->name('settings.company');
        Route::put('settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
        Route::put('settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
        Route::delete('settings/deactivate', [SettingsController::class, 'deactivate'])->name('settings.deactivate');

        Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log');
    });
});
