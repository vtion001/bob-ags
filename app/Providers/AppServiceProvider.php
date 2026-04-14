<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        Gate::define('access-qa', function ($user) {
            return in_array($user->role, ['admin', 'qa']);
        });

        Gate::define('access-supervisor', function ($user) {
            return in_array($user->role, ['admin', 'qa']);
        });

        Gate::define('manage-settings', function ($user) {
            return $user->role === 'admin';
        });
    }
}
