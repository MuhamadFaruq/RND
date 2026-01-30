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
        if (Auth::check()) {
            $user = Auth::user();
            // Gunakan update quiet agar tidak memicu event 'updated_at'
            $user->last_seen = now();
            $user->save(['timestamps' => false]);
        }

        return $next($request);
    }
}