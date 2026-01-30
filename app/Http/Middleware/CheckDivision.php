<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDivision
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // 1. KUNCI UTAMA: Jika Superadmin, abaikan semua pengecekan divisi
        if ($user->role === 'superadmin') {
            return $next($request);
        }

        // 2. CEK OPERATOR BIASA
        $requestedDivision = $request->route('division');
        $userDivision = strtolower($user->division);
        $targetDivision = strtolower($requestedDivision);

        // --- TAMBAHAN LOGIKA UNTUK JALUR STENTER ---
        // Daftar mesin yang boleh diakses jika user divisinya adalah 'stenter'
        $stenterPath = ['stenter', 'compactor', 'heat-setting', 'relax-dryer', 'tumbler', 'fleece'];

        if ($userDivision === 'stenter' && in_array($targetDivision, $stenterPath)) {
            return $next($request);
        }
        // -------------------------------------------

        // Pastikan nama divisi di database (user->division) cocok dengan URL
        if ($userDivision !== $targetDivision) {
            return redirect()->route('operator.divisions')
                ->with('error', 'Akses Ditolak: Anda tidak terdaftar di divisi ini.');
        }

        return $next($request);
    }
}