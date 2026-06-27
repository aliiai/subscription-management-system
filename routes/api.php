<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\ActivityLogController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\JournalEntryController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\RevenueRecognitionController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public authentication endpoints.
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:6,1');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:6,1');

    // Authenticated, tenant-scoped company endpoints.
    Route::middleware(['auth:sanctum', 'role:company', 'throttle:api'])->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);

        // Plans.
        Route::apiResource('plans', PlanController::class);

        // Customers.
        Route::apiResource('customers', CustomerController::class);

        // Subscriptions.
        Route::apiResource('subscriptions', SubscriptionController::class);

        // Invoices (generate before the resource so it isn't shadowed by {invoice}).
        Route::post('invoices/generate', [InvoiceController::class, 'generate']);
        Route::apiResource('invoices', InvoiceController::class)->except(['update']);

        // Payments.
        Route::apiResource('payments', PaymentController::class)->except(['update']);

        // Revenue recognition.
        Route::get('revenue-recognition', [RevenueRecognitionController::class, 'index']);
        Route::post('revenue-recognition/recognize', [RevenueRecognitionController::class, 'recognize']);

        // Accounting.
        Route::get('accounts', [AccountController::class, 'index']);
        Route::get('journal-entries', [JournalEntryController::class, 'index']);
        Route::get('journal-entries/{entry}', [JournalEntryController::class, 'show']);

        // Reports.
        Route::get('reports/income-statement', [ReportController::class, 'incomeStatement']);
        Route::get('reports/balance-sheet', [ReportController::class, 'balanceSheet']);

        // Dashboard.
        Route::get('dashboard', [DashboardController::class, 'index']);

        // Notifications.
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('notifications/read-all', [NotificationController::class, 'readAll']);
        Route::get('notifications/{notification}', [NotificationController::class, 'show']);

        // Settings.
        Route::get('settings', [SettingsController::class, 'index']);
        Route::put('settings/company', [SettingsController::class, 'updateCompany']);
        Route::put('settings/profile', [SettingsController::class, 'updateProfile']);
        Route::put('settings/password', [SettingsController::class, 'updatePassword']);
        Route::delete('settings/deactivate', [SettingsController::class, 'deactivate']);

        // Activity log.
        Route::get('activity-log', [ActivityLogController::class, 'index']);
    });
});
