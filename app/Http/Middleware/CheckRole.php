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
        // Jika sistem sedang maintenance
        if (app()->isDownForMaintenance()) {
            // CEK: Apakah dia Super Admin ATAU sedang di-impersonate oleh Admin?
            if (auth()->check() && (auth()->user()->isSuperAdmin() || session()->has('impersonator_id'))) {
                return $next($request);
            }
            
            // Selain itu, paksa ke halaman 503
            abort(503, 'Sistem sedang dalam perawatan oleh Super Admin.');
        }

        // Lanjutkan pengecekan login normal di bawah sini...
        if (!auth()->check()) {
            return redirect('login');
        }

        $user = auth()->user();
        $userRole = \App\Enums\UserRole::tryFrom($user->role);

        // 1. IZINKAN SUPER ADMIN (God Mode)
        if ($userRole === \App\Enums\UserRole::SUPER_ADMIN) {
            return $next($request);
        }

        // 2. CEK APAKAH ROLE USER TERDAFTAR DI PARAMETER $roles
        if (count($roles) > 0 && in_array($user->role, $roles)) {
            return $next($request);
        }

        // 3. FALLBACK UNTUK PREFIX ADMIN (Jika rute tidak mengirim parameter role)
        if ($request->is('admin/*') && ($user->isAdmin() || $user->isSuperAdmin())) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}