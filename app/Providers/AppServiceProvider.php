<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
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
    public function boot() : void
    {
        // Cukup gunakan ini untuk bypass Super Admin
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            // Ubah pengecekan role
            return $user->role === 'superadmin' ? true : null;
        });
        Gate::define('access-superadmin', function ($user) {
            return $user->role === 'admin' || $user->role === 'superadmin'; //
        });
        
        // Daftarkan tag <x-app-layout> agar mengarah ke layouts/app.blade.php
        Blade::component('layouts.app', 'app-layout');
        
        // Daftarkan juga guest-layout jika diperlukan
        Blade::component('layouts.guest', 'guest-layout');
        Blade::component('layouts.app', 'layouts.app');
    }
}
