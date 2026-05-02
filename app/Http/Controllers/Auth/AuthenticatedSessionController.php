<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\View\View;
use App\Models\ActivityLog;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Ambil data user yang baru saja login
        $user = $request->user();

        // Logika Pengalihan Berdasarkan Role
        // Jika Marketing, langsung diarahkan ke Monitoring
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'LOGIN',
            'model' => 'USER',
            'description' => "User {$user->name} berhasil masuk ke sistem.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        if ($user->role === 'marketing') {
            return redirect()->intended(route('dashboard'));
        }

        // Jika Operator
        // app/Http/Controllers/Auth/AuthenticatedSessionController.php

        if ($user->role === 'operator') {
            $divisi = strtolower($user->division);
            
            // Jika Stenter, langsung ke form input, jangan ke halaman seleksi
            if ($divisi === 'stenter') {
                return redirect()->intended(route('log.create', ['division' => 'stenter']));
            }
            
            return redirect()->intended(route('operator.divisions'));
        }

        // Default redirect jika role tidak spesifik (misal: Superadmin)
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'LOGOUT',
                'model' => 'USER',
                'description' => "User {$user->name} keluar dari sistem.",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
