<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'check.division' => \App\Http\Middleware\CheckDivision::class,
            'role'           => \App\Http\Middleware\CheckRole::class,
            'marketing'      => \App\Http\Middleware\EnsureUserIsMarketing::class,
            'operator'       => \App\Http\Middleware\EnsureUserIsOperator::class,
            'no-back'        => \App\Http\Middleware\PreventBackHistory::class,
        ]);

        $middleware->web(append: [
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\UserActivity::class,
        ]);

        // TAMBAHKAN INI: Mengecualikan Super Admin dari Maintenance Mode
        $middleware->preventRequestsDuringMaintenance(except: [
            '/',                    // Halaman utama / redirector
            '/login',
            '/logout',
            '/dashboard',
            '/stop-impersonate',    // Memperbaiki typo dari /stop-impersonating
            '/admin/*',
            '/super-admin/*',
            '/operator/*',
            '/marketing/*',
            '/profile/*',           // Izinkan akses profil
            '/livewire/*', 
            '/livewire-*/*',        // Menggunakan wildcard agar tidak terikat hash spesifik
            'livewire/update',      // Endpoint standar update
            '/api/maintenance-check', // Heartbeat maintenance (503 auto-recovery)
            'rnd-duniatex-2026',    // secret key Anda
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();