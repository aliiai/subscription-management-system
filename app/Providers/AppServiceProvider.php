<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.layouts.dashboard', function ($view): void {
            $tenant = Auth::user()?->tenant;

            $view->with([
                'headerNotifications' => $tenant
                    ? $tenant->notifications()->latest()->limit(5)->get()
                    : collect(),
                'unreadNotificationsCount' => $tenant
                    ? $tenant->notifications()->unread()->count()
                    : 0,
            ]);
        });
    }
}
