<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles  <-- Tambahkan tiga titik dan ubah jadi jamak ($roles)
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect('login');
        }

        $user = auth()->user();

        // 1. IZINKAN SUPER ADMIN (God Mode)
        // Pastikan di database tulisannya 'super admin' (pakai spasi)
        if ($user->role === 'super-admin') {
            return $next($request);
        }

        // 2. CEK APAKAH ROLE USER TERDAFTAR DI PARAMETER $roles
        // Sekarang $roles sudah dikenali karena ada tanda '...' di parameter fungsi
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}