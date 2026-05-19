<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use App\Models\User;

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
        // Force HTTPS in production (required for Render deployment)
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        // 1. Admin Gate: Full System Control
        Gate::define('admin-only', function (User $user) {
            return $user->role === 'admin';
        });

        // 2. Manager Gate: Access to Management (Categories, Products, Suppliers)
        Gate::define('manager-access', function (User $user) {
            return in_array($user->role, ['admin', 'manager']);
        });

        // 3. Staff Gate: Basic Stock Operations
        Gate::define('staff-access', function (User $user) {
            return in_array($user->role, ['admin', 'manager', 'staff']);
        });
    }
}
