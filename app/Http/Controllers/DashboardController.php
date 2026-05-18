<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Dashboard Dispatcher: Mengarahkan user berdasarkan role.
     */
    public function index()
    {
        $user = auth()->user();

        // 1. Operator: Go to Logbook
        if (in_array($user->role, ['operator', 'knitting', 'dyeing', 'relax-dryer', 'finishing', 'stenter', 'tumbler', 'fleece', 'pengujian', 'qe'])) {
            return redirect()->route('operator.logbook');
        }

        // 2. Marketing: Go to Marketing Dashboard
        if ($user->role === 'marketing') {
            return redirect()->route('marketing.dashboard');
        }

        // 3. Admin & Superadmin: Show the standard dashboard view
        return view('dashboard');
    }

    /**
     * Impersonate User (Super-Admin only)
     */
    public function impersonate($id)
    {
        $userToImpersonate = User::findOrFail($id);

        if (auth()->user()->role !== 'super-admin') {
            abort(403);
        }

        session()->put('impersonator_id', auth()->id());
        auth()->login($userToImpersonate);

        return redirect()->route('dashboard');
    }

    /**
     * Stop Impersonating
     */
    public function stopImpersonate()
    {
        if (!session()->has('impersonator_id')) {
            return redirect()->route('dashboard');
        }

        $adminId = session()->pull('impersonator_id');
        $adminUser = User::find($adminId);

        auth()->login($adminUser);

        return redirect()->route('admin.users');
    }

    /**
     * API untuk Realtime Stats (Digunakan oleh widget atau polling jika ada)
     */
    public function getRealTimeStats()
    {
        return response()->json([
            'total_articles' => MarketingOrder::count(),
            'daily_activity' => ProductionActivity::whereDate('created_at', today())->count(),
            'active_users'   => User::whereNotNull('last_seen')->where('last_seen', '>=', now()->subMinutes(5))->count(),
        ]);
    }

    /**
     * API Check Maintenance
     */
    public function checkMaintenanceStatus()
    {
        return response()->json([
            'is_down' => app()->isDownForMaintenance()
        ]);
    }
}