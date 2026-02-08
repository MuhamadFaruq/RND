<?php

namespace App\Http\Controllers;

use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use App\Models\User;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductionExport;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard Utama (Halaman Index setelah Login)
     */
    public function index(): View
    {
        $totalSap = MarketingOrder::count();
        $todayLogs = ProductionActivity::whereDate('created_at', today())->count();
        $totalUsers = User::count();

        $stats = [
            ['label' => 'Total SAP Terdaftar', 'value' => $totalSap, 'icon' => '📋', 'color' => 'text-blue-600'],
            ['label' => 'Aktivitas Hari Ini', 'value' => $todayLogs, 'icon' => '⚡', 'color' => 'text-red-600'],
            ['label' => 'User Terdaftar', 'value' => $totalUsers, 'icon' => '👥', 'color' => 'text-green-600'],
        ];

        // Return ke resources/views/dashboard.blade.php
        return view('dashboard', compact('stats'));
    }

    /**
     * Monitoring Dashboard (Tampilan Admin/Monitoring)
     * Digunakan untuk melihat summary order
     */
    public function monitoring(Request $request): View
    {
        $totalOrdersCount = MarketingOrder::count();
        $inProgress = MarketingOrder::whereNotIn('status', ['completed', 'pending'])->count();
        $completed = MarketingOrder::where('status', 'completed')->count();
        $overdueCount = MarketingOrder::where('status', '!=', 'completed')
            ->whereNotNull('tanggal')
            ->where('tanggal', '<', now()->subDays(3))
            ->count();

        // Get Orders for Table Summary
        $orders = MarketingOrder::latest()->take(50)->get();

        return view('admin.monitoring-dashboard', [
            'orders' => $orders,
            'stats' => [
                'total_pesanan' => $totalOrdersCount,
                'order_aktif' => $inProgress,
                'order_overdue' => $overdueCount,
                'order_selesai' => $completed,
            ]
        ]);
    }

    /**
     * API untuk Realtime Stats (Digunakan oleh widget atau polling jika ada)
     */
    public function getRealTimeStats()
    {
        $logStats = ProductionActivity::select('type', DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subDay())
            ->groupBy('type')
            ->get()
            ->pluck('total', 'type');

        return response()->json([
            'success' => true,
            'data' => [
                'knitting' => $logStats['knitting'] ?? 0,
                'dyeing'   => $logStats['dyeing'] ?? 0,
                'qc'       => $logStats['pengujian'] ?? 0,
            ]
        ]);
    }

    /**
     * Export report to Excel
     */
    public function exportExcel()
    {
        if (Auth::user()->role === 'operator') {
            abort(403, 'Unauthorized.');
        }

        try {
            return Excel::download(
                new ProductionExport, 
                'LAPORAN_PRODUKSI_DUNIATEX_'.now()->format('d-m-Y').'.xlsx'
            );
        } catch (\Exception $e) {
            Log::error("Export Error: " . $e->getMessage());
            return back()->with('error', 'Gagal export data.');
        }
    }

    /**
     * Approval & Rejection untuk Manajer QE
     */
    public function handleQEAction(Request $request, $id)
    {
        $order = MarketingOrder::findOrFail($id);
        $action = $request->input('action'); 
        $reason = $request->input('reason'); 

        if ($action === 'approve') {
            $order->update(['status' => 'completed']);
            return back()->with('success', "Order SAP {$order->sap_no} SELESAI.");
        }

        if ($action === 'reject') {
            $order->update([
                'status' => 'rework',
                'keterangan' => $order->keterangan . " | REJECTED: " . $reason
            ]);
            return back()->with('warning', "Order SAP {$order->sap_no} REWORK.");
        }
    }

    /**
     * Generate Label Roll (QR Code)
     */
    public function generateLabel($sap_no)
    {
        try {
            $order = MarketingOrder::where('sap_no', $sap_no)->firstOrFail();
            $qrString = "SAP:{$order->sap_no}|ART:{$order->art_no}|PLG:{$order->pelanggan}";

            $qrcode = QrCode::size(150)
                ->color(237, 28, 36)
                ->margin(1)
                ->generate($qrString);

            return response()->json([
                'status' => 'success',
                'qrcode' => (string)$qrcode,
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Not Found'], 404);
        }
    }

    /**
     * API Detail untuk Scanner
     */
    public function getOrderDetailApi($sap_no)
    {
        try {
            return response()->json(MarketingOrder::where('sap_no', $sap_no)->firstOrFail());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Not Found'], 404);
        }
    }
}