<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductionActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MonitoringController extends Controller
{
    /**
     * Render halaman Dashboard Monitoring (Inertia)
     */
    public function index()
    {
        return Inertia::render('Admin/MonitoringDashboard');
    }

    /**
     * API Endpoint untuk data Real-Time (JSON)
     */
    public function getRealTimeStats()
    {
        // 1. Logbook Tracker: Hitung inputan 24 jam terakhir per divisi
        $logbookStats = ProductionActivity::where('created_at', '>=', now()->subDay())
            ->select('division_name', DB::raw('count(*) as total'))
            ->groupBy('division_name')
            ->get();

        // 2. System Health: Simulasi (Sesuai permintaan)
        $systemHealth = [
            'cpu' => rand(20, 45),
            'memory' => rand(40, 60),
            'db_status' => 'Healthy',
        ];

        // 3. Error Logs: Ambil 5 kegagalan terakhir
        // Catatan: Pastikan tabel 'failed_access_logs' sudah ada, 
        // jika belum, sementara bisa return array kosong agar tidak error.
        $recentErrors = [];
        if (\Schema::hasTable('failed_access_logs')) {
            $recentErrors = DB::table('failed_access_logs')
                ->latest()
                ->limit(5)
                ->get();
        }

        return response()->json([
            'logbooks' => $logbookStats,
            'health' => $systemHealth,
            'errors' => $recentErrors,
            'server_time' => now()->toDateTimeString(),
        ]);
    }
}