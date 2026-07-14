<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Pagination\Paginator;
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
        Paginator::defaultView('partials.pagination');

        // Point password reset links at the admin reset screen.
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return route('admin.password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()]);
        });
    }
}
