<?php

namespace App\Http\Controllers;

use App\Models\MarketingOrder;
use App\Models\ProductionActivity; // Pastikan ini di-import
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Tambahkan ini untuk DB raw
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductionExport;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $totalSap = MarketingOrder::count();
        $todayLogs = ProductionActivity::whereDate('created_at', today())->count();
        $totalUsers = User::count();

        $displayStats = [
            ['label' => 'Total SAP Terdaftar', 'value' => (string)$totalSap, 'icon' => '📋', 'color' => 'text-blue-600', 'desc' => 'Total order dari Marketing'],
            ['label' => 'Aktivitas Hari Ini', 'value' => (string)$todayLogs, 'icon' => '⚡', 'color' => 'text-red-600', 'desc' => 'Total input logbook operator'],
            ['label' => 'User Terdaftar', 'value' => (string)$totalUsers, 'icon' => '👥', 'color' => 'text-green-600', 'desc' => 'Total akun dalam sistem'],
        ];

        return view('Dashboard');
    }

    public function monitoring(Request $request): Response
    {
        // 1. Ambil data statistik di luar agar bisa dipakai di return
        $totalOrdersCount = MarketingOrder::count();
        $inProgress = MarketingOrder::whereNotIn('status', ['completed', 'pending'])->count();
        $completed = MarketingOrder::where('status', 'completed')->count();
        $overdueCount = MarketingOrder::where('status', '!=', 'completed')
            ->whereNotNull('tanggal')
            ->where('tanggal', '<', now()->subDays(3))
            ->count();

        // 2. Generate Trends Berdasarkan Periode
        $period = $request->query('period', 'weekly');
        $trends = [];

        if ($period === 'yearly') {
            // Data per bulan dalam 1 tahun terakhir
            for ($i = 11; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $count = \App\Models\ProductionActivity::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count();
                $trends[] = ['day' => $month->format('M'), 'total' => $count];
            }
        } elseif ($period === 'monthly') {
            // Data per minggu dalam 4 minggu terakhir
            for ($i = 3; $i >= 0; $i--) {
                $start = now()->subWeeks($i)->startOfWeek();
                $end = now()->subWeeks($i)->endOfWeek();
                $count = \App\Models\ProductionActivity::whereBetween('created_at', [$start, $end])->count();
                $trends[] = ['day' => 'W'.($i+1), 'total' => $count];
            }
        } else {
            // Default: Weekly (Tetap seperti kode lama Anda)
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $count = \App\Models\ProductionActivity::whereDate('created_at', $date)->count();
                $trends[] = ['day' => $date->format('D'), 'total' => $count];
            }
        }

        // 3. Mapping Data Orders
        $orders = MarketingOrder::with(['productionActivities' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                $knitting = $order->productionActivities->firstWhere('division_name', 'Knitting (Rajutan)');
                $dyeing = $order->productionActivities->firstWhere('division_name', 'SCR / Dyeing (Pewarnaan)');
                $relax = $order->productionActivities->firstWhere('division_name', 'Relax Dryer');

                return [
                    'id' => $order->id,
                    'sap_no' => $order->sap_no,
                    'art_no' => $order->art_no,
                    'pelanggan' => $order->pelanggan,
                    'status' => $order->status,
                    'warna' => $order->warna,
                    // Tambahkan field lain yang dibutuhkan frontend di sini
                ];
            });

            return Inertia::render('Admin/MonitoringDashboard', [ 
                'auth' => ['user' => auth()->user()],
                'orders' => $orders,
                'weeklyTrends' => $trends,
                'stats' => [
                    'total_pesanan' => $totalOrdersCount,
                    'order_aktif' => $inProgress,
                    'order_overdue' => $overdueCount,
                    'order_selesai' => $completed,
                ]
            ]);
    }

    public function getRealTimeStats()
    {
        $logStats = ProductionActivity::select('division_name', DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subDay())
            ->groupBy('division_name')
            ->get()
            ->pluck('total', 'division_name');

        $recentActivities = ProductionActivity::with('operator:id,name')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($act) {
                return [
                    'id' => $act->id,
                    'time' => $act->created_at->format('H:i'),
                    'operator' => $act->operator->name ?? 'System',
                    'division' => $act->division_name,
                    'status' => $act->status
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'knitting'   => $logStats['Knitting (Rajutan)'] ?? 0,
                'dyeing'     => $logStats['SCR / Dyeing (Pewarnaan)'] ?? 0,
                'stenter'    => $logStats['Stenter'] ?? 0,
                'qc'         => $logStats['Pengujian'] ?? 0,
                'activities' => $recentActivities
            ]
        ]);
    }

    /**
     * Export report to Excel.
     */
    public function exportExcel()
    {
        if (Auth::user()->role === 'operator') {
            abort(403, 'Operator tidak diizinkan melakukan export data.');
        }

        try {
            return Excel::download(
                new ProductionExport, 
                'LAPORAN_PRODUKSI_DUNIATEX_'.Carbon::now()->format('d-m-Y').'.xlsx'
            );
        } catch (\Exception $e) {
            Log::error("Export Error: " . $e->getMessage());
            return response()->json(['error' => 'Gagal membuat file excel: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Tampilan daftar divisi untuk Operator (Tanpa menu Marketing).
     */
    public function divisions()
    {
        $allDivisions = [
            ['name' => 'Marketing', 'description' => 'Order Entry & Management', 'icon' => 'M', 'color' => 'red'],
            ['name' => 'Knitting', 'description' => 'Knitting Machine Production Log', 'icon' => 'K', 'color' => 'blue'],
            ['name' => 'SCR/Dyeing', 'description' => 'Dyeing Process & Color Application', 'icon' => 'D', 'color' => 'indigo'],
            ['name' => 'Relax Dryer', 'description' => 'Relaxation & Drying Operations', 'icon' => 'R', 'color' => 'green'],
            ['name' => 'Compactor', 'description' => 'Compaction Process Control', 'icon' => 'C', 'color' => 'purple'],
            ['name' => 'Heat Setting', 'description' => 'Heat Setting & Stabilization', 'icon' => 'H', 'color' => 'orange'],
            ['name' => 'Stenter', 'description' => 'Stenter Finishing (Belah)', 'icon' => 'S', 'color' => 'teal'],
            ['name' => 'Tumbler', 'description' => 'Tumbling & Softening Process', 'icon' => 'T', 'color' => 'blue'],
            ['name' => 'Fleece', 'description' => 'Raising, Brushing & Shearing', 'icon' => 'F', 'color' => 'pink'],
            ['name' => 'Pengujian', 'description' => 'Quality Testing & Measurement', 'icon' => 'P', 'color' => 'yellow'],
            ['name' => 'QE', 'description' => 'Final Quality Evaluation', 'icon' => 'Q', 'color' => 'cyan'],
        ]; 

        // Filter: Menghilangkan menu Marketing untuk halaman Operator
        $filteredDivisions = array_filter($allDivisions, function($division) {
            return $division['name'] !== 'Marketing';
        });

        return Inertia::render('Operator/DivisionSelector', [
            'divisions' => array_values($filteredDivisions)
        ]);
    }

    /**
     * Menampilkan LogBook untuk divisi tertentu beserta antrean order (Work Orders).
     */
    public function showLogBook($division)
    {
        // Mengambil antrean order (misal order yang statusnya belum selesai)
        $workOrders = MarketingOrder::where('status', '!=', 'completed')
                    ->latest()
                    ->get();

        return Inertia::render('Operator/LogBook', [
            'division' => $division,
            'workOrders' => $workOrders 
        ]);
    }

    /**
     * Bagian Manajemen Order untuk Marketing.
     */
    public function marketingOrderList()
    {
        $orders = MarketingOrder::orderBy('created_at', 'desc')->get();
        return Inertia::render('Marketing/OrderList', [
            'orders' => $orders
        ]);
    }

    public function editOrder($id)
    {
        $order = MarketingOrder::findOrFail($id);
        return Inertia::render('Marketing/EditOrder', [
            'order' => $order
        ]);
    }

    public function updateOrder(Request $request, $id)
    {
        $order = MarketingOrder::findOrFail($id);
        
        $validated = $request->validate([
            'art_no'            => 'required',
            'pelanggan'         => 'required|string',
            'target_lebar'      => 'required|numeric',
            'target_gramasi'    => 'required|numeric',
            'roll_target'       => 'nullable|numeric',
            'kg_target'         => 'nullable|numeric',
            'warna'             => 'required|string',
        ]);

        $order->update($validated);

        return redirect()->route('marketing.orders.index')
            ->with('success', "Data Order SAP {$order->sap_no} berhasil diperbarui.");
    }

    public function destroyOrder($id)
    {
        $order = MarketingOrder::findOrFail($id);
        $order->delete();

        return redirect()->route('marketing.orders.index')
            ->with('success', "Order SAP {$order->sap_no} berhasil dihapus.");
    }

    /**
 * Logika Approval & Rejection untuk Manajer QE
 */
public function handleQEAction(Request $request, $id)
    {
        $order = MarketingOrder::findOrFail($id);
        $action = $request->input('action'); // 'approve' atau 'reject'
        $reason = $request->input('reason'); // Alasan jika reject

        if ($action === 'approve') {
            $order->update([
                'status' => 'completed',
                'updated_at' => now()
            ]);
            return back()->with('success', "Order SAP {$order->sap_no} dinyatakan LULUS QC.");
        }

        if ($action === 'reject') {
            // Status dikembalikan ke divisi sebelumnya, misal 'finishing' atau 'stenter'
            $order->update([
                'status' => 'rework',
                'note_qe' => "REJECTED: " . $reason
            ]);
            return back()->with('warning', "Order SAP {$order->sap_no} dikembalikan untuk Rework.");
        }
    }

/**
 * Generate Data Lengkap untuk Label Roll (QR + Detail)
 */
public function generateLabel($sap_no)
    {
        try {
            $order = MarketingOrder::where('sap_no', $sap_no)->firstOrFail();
            
            // String data untuk QR (Standar Scan Gudang)
            $qrString = "SAP:{$order->sap_no}|ART:{$order->art_no}|PLG:{$order->pelanggan}";

            // Generate QR Code Merah Duniatex
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
            Log::error("QR Generate Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }
    }

    /**
 * API Detail untuk Scanner HP Staf Gudang
 */
public function getOrderDetailApi($sap_no)
    {
        try {
            $order = MarketingOrder::where('sap_no', $sap_no)->firstOrFail();
            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Order tidak ditemukan'], 404);
        }
    }
}

