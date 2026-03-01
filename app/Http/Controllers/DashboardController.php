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
    public function index(): \Illuminate\Http\RedirectResponse|\Illuminate\View\View
    {
        $user = auth()->user();

        // 1. Daftar role yang masuk kategori Operator
        $operatorRoles = [
            'knitting', 'dyeing', 'relax-dryer', 'finishing', 
            'stenter', 'tumbler', 'fleece', 'pengujian', 'qe'
        ];

        // 2. Jika user adalah salah satu dari operator di atas
        if (in_array($user->role, $operatorRoles)) {
            return redirect()->route('operator.logbook');
        }

        // 3. Jika user adalah Marketing
        if ($user->role === 'marketing') {
            return redirect()->route('marketing.dashboard');
        }

        if (in_array($user->role, ['super-admin', 'admin'])) {
            // Jika file visual Anda ada di: resources/views/livewire/admin/dashboard.blade.php
            return view('livewire.admin.dashboard'); 
        }

        // 4. Default untuk Admin (Menampilkan statistik umum)
        $stats = [
            ['label' => 'Total SAP Terdaftar', 'value' => MarketingOrder::count(), 'icon' => '📋', 'color' => 'text-blue-600'],
            ['label' => 'Aktivitas Hari Ini', 'value' => ProductionActivity::whereDate('created_at', today())->count(), 'icon' => '⚡', 'color' => 'text-red-600'],
            ['label' => 'User Terdaftar', 'value' => User::count(), 'icon' => '👥', 'color' => 'text-green-600'],
        ];

        return view('dashboard', compact('stats'));
    }

    /**
     * Monitoring Dashboard (Tampilan Admin/Monitoring)
     * Digunakan untuk melihat summary order
     */
    // app/Http/Controllers/DashboardController.php

// app/Http/Controllers/DashboardController.php

public function monitoring()
    {
        // 1. Data Statistik Utama
        $totalOrder = \App\Models\MarketingOrder::count();
        $knittingOrder = \App\Models\MarketingOrder::where('status', 'knitting')->count();
        $activeOrder = \App\Models\MarketingOrder::whereIn('status', [
            'dyeing', 'relax-dryer', 'finishing', 
            'stenter', 'tumbler', 'fleece', 'pengujian', 'qe'
        ])->count();
        $completedOrder = \App\Models\MarketingOrder::where('status', 'completed')->count();

        // 2. Data untuk Progress Table (Tabel 10 SAP Terakhir)
        $recentOrders = \App\Models\MarketingOrder::latest()
            ->take(10)
            ->get();

        // 3. Logika "Machine Workload" (Persentase beban kerja per divisi)
        // Anda bisa menyesuaikan angka pembaginya sesuai dengan kapasitas asli pabrik Anda
        $machineStats = [
            'knitting' => min(($knittingOrder / 20) * 100, 100), // Asumsi kapasitas antrean 20
            'dyeing'   => min(($activeOrder / 15) * 100, 100),  // Asumsi kapasitas proses 15
            'finishing'=> 82, // Nilai statis atau bisa diambil dari query aktivitas finishing
        ];

        return view('livewire.marketing.marketing-dashboard', [
            'totalOrder'     => $totalOrder,
            'knittingOrder'   => $knittingOrder,
            'activeOrder'    => $activeOrder,
            'completedOrder' => $completedOrder,
            'recentOrders'   => $recentOrders,
            'machineStats'   => $machineStats, // Pastikan variabel ini dikirim agar tidak error
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