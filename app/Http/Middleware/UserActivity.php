<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserActivity
{
    public function handle(Request $request, Closure $next)
    {
        // Jika user sedang login, perbarui waktu 'last_seen' mereka
        // Throttle: hanya update jika selisih > 1 menit untuk menghindari query berlebihan (Livewire polling)
        if (Auth::check()) {
            $user = Auth::user();
            if (!$user->last_seen || $user->last_seen->diffInMinutes(now()) >= 1) {
                $user->last_seen = now();
                $user->saveQuietly();
            }
        }

        return $next($request);
    }
}