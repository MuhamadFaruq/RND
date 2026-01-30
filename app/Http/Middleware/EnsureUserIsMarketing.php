<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsMarketing
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user sudah login dan memiliki role 'marketing'
        if (auth()->check() && auth()->user()->role === 'marketing') {
            return $next($request);
        }

        // Jika bukan marketing, lempar kembali ke dashboard dengan pesan error
        return redirect('/dashboard')->with('error', 'Anda tidak memiliki akses Marketing.');
    }
}