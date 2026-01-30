<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
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
    public function boot()
    {
        // Cukup gunakan ini untuk bypass Super Admin
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            // Ubah pengecekan role
            return $user->role === 'superadmin' ? true : null;
        });
        Gate::define('access-superadmin', function ($user) {
            return $user->role === 'admin' || $user->role === 'superadmin'; //
        });
    }
}
