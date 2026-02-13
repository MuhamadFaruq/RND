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
        // Daftarkan alias agar 'check.division' bisa dikenali di routes/web.php
        $middleware->alias([
            'check.division' => \App\Http\Middleware\CheckDivision::class,
        ]);
        $middleware->web(append: [
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\UserActivity::class,
            // \App\Http\Middleware\CheckDivision::class,
        ]);

        // Tambahkan pendaftaran alias middleware di sini
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class, // Tambahkan baris ini
            'marketing' => \App\Http\Middleware\EnsureUserIsMarketing::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();