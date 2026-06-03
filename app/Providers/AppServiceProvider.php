<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
    public const HOME = '/operator/logbook';
    /**
     * Bootstrap any application services.
     */
    public function boot() : void
    {
        \Carbon\Carbon::setLocale('id');

        // Configure Rate Limiters
        $this->configureRateLimiting();

        // Cukup gunakan ini untuk bypass Super Admin
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            // Gunakan value yang benar: 'super-admin'
            return $user->role === 'super-admin' ? true : null;
        });
        Gate::define('access-superadmin', function ($user) {
            return $user->role === 'admin' || $user->role === 'super-admin'; //
        });
        
        Gate::define('is-super-admin', function ($user) {
            return $user->role === 'super-admin';
        });
        
        // Daftarkan tag <x-app-layout> agar mengarah ke layouts/app.blade.php
        Blade::component('layouts.app', 'app-layout');
        
        // Daftarkan juga guest-layout jika diperlukan
        Blade::component('layouts.guest', 'guest-layout');
        Blade::component('layouts.app', 'layouts.app');
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // General API rate limiter
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Strict rate limiter for authentication/login
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function (Request $request, array $headers) {
                $seconds = $headers['Retry-After'] ?? 60;
                return response()->view('errors.429', ['seconds' => $seconds], 429, $headers);
            });
        });

        // Rate limiter for high-frequency production updates (Operators)
        RateLimiter::for('production', function (Request $request) {
            return $request->user()?->role === 'super-admin'
                ? Limit::none()
                : Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });
    }
}
