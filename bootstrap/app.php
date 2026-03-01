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
        // Gabungkan SEMUA alias di sini agar tidak saling menimpa
        $middleware->alias([
            'check.division' => \App\Http\Middleware\CheckDivision::class,
            'role'           => \App\Http\Middleware\CheckRole::class,
            'marketing'      => \App\Http\Middleware\EnsureUserIsMarketing::class,
            'no-back'        => \App\Http\Middleware\PreventBackHistory::class, // Pastikan ini ada
        ]);

        $middleware->web(append: [
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\UserActivity::class,
        ]);
        
        // HAPUS pemanggilan $middleware->alias kedua yang ada di bawahnya
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();