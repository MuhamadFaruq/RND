<?php

namespace App\Http\Controllers;

use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductionController extends Controller
{
    /**
     * Store a production log into production_activities.
     */
    // Dashboard: Ringkasan singkat untuk semua user

   public function index()
{
    /** @var \App\Models\User $user */
    $user = auth()->user();
    $operatorId = $user->id; 

    try {
        // 1. Ambil Antrean Kerja (Work Queue)
        // Mengambil order yang statusnya 'knitting' untuk diproses operator
        $workQueue = \App\Models\MarketingOrder::where('status', 'knitting')
            ->latest()
            ->take(5) // Mengambil 5 antrean terbaru
            ->get();

        // 2. Ambil data hari ini khusus operator yang login
        $activitiesToday = ProductionActivity::where('operator_id', $operatorId)
            ->whereDate('created_at', today())
            ->get();

        // 3. Hitung total dari JSON technical_data
        $totalKgToday = $activitiesToday->sum(function ($activity) {
            return $activity->technical_data['kg'] ?? 0;
        });

        $totalRollToday = $activitiesToday->sum(function ($activity) {
            return $activity->technical_data['roll'] ?? 0;
        });
            
        // 4. Ambil data untuk tabel logbook (Pagination)
        $activities = ProductionActivity::with('marketingOrder')
            ->where('operator_id', $operatorId)
            ->latest()
            ->paginate(10);
            
    } catch (\Exception $e) {
        $workQueue = collect([]); // Jika error, buat koleksi kosong agar view tidak crash
        $totalKgToday = 0;
        $totalRollToday = 0;
        $activities = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
    }

    // 5. Kirim semua variabel ke view (Pastikan workQueue ada di sini)
    return view('livewire.operator.logbook', [
        'workQueue'       => $workQueue, // Ini yang tadi menyebabkan error
        'totalKgToday'    => $totalKgToday,
        'totalRollToday'  => $totalRollToday,
        'activities'      => $activities,
        'totalOrders'     => \App\Models\MarketingOrder::count(),
        'activeOperators' => \App\Models\User::where('role', 'operator')->count(),
    ]);
}


// Monitoring: Tampilan Command Center yang Anda miliki saat ini
public function monitoringIndex()
{
    $stats = [
        'order_aktif'   => \App\Models\MarketingOrder::where('status', '!=', 'completed')->count(),
        'total_pesanan' => \App\Models\MarketingOrder::count(),
        'order_overdue' => \App\Models\MarketingOrder::where('status', 'knitting')->count(),
        'order_selesai' => \App\Models\MarketingOrder::where('status', 'completed')->count(),
    ];

    return Inertia::render('Admin/MonitoringDashboard', [
        'stats'  => $stats,
        'orders' => \App\Models\MarketingOrder::latest()->get(),
    ]);
}

    public function create($division)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $targetDiv = strtolower($division);
        $userDiv = strtolower($user->division);

        // Akses khusus Jalur Finishing & QC
        $stenterPath = ['stenter', 'compactor', 'heat-setting', 'relax-dryer', 'tumbler', 'fleece'];
        $qcPath = ['qc', 'pengujian', 'qe', 'quality-control'];

        if ($user->role === 'operator') {
            $isDirectMatch = ($userDiv === $targetDiv);
            $isAllowedStenter = ($userDiv === 'stenter' && in_array($targetDiv, $stenterPath));
            $isAllowedQC = (in_array($userDiv, $qcPath) && in_array($targetDiv, $qcPath));

            if (!$isDirectMatch && !$isAllowedStenter && !$isAllowedQC) {
                return redirect()->route('operator.divisions')
                    ->with('error', 'AKSES DITOLAK: Anda terdaftar di divisi ' . strtoupper($user->division));
            }
        }

        return inertia('Production/LogBookForm', [
            'division' => $division,
            'tanggal_mesin' => now()->format('Y-m-d'), 
            'operator_name' => $user->name,
        ]);
    }

    public function storeLog(Request $request)
    {
        try {
            $validated = $request->validate([
                'sap_no' => ['required', 'integer', 'exists:marketing_orders,sap_no'],
                'division_name' => ['required', 'string', 'max:255'],
                'status' => ['nullable', 'in:knitting,dyeing,finishing,qc,completed'],
                'technical_data' => ['nullable', 'array'],
            ]);

            /** @var \App\Models\User $user */
            $user = $request->user();

            $targetDiv = strtolower($request->division_name);
            $userDiv = strtolower($user->division);
            $stenterPath = ['stenter', 'compactor', 'heat-setting', 'relax-dryer', 'tumbler', 'fleece'];
            $qcPath = ['qc', 'pengujian', 'qe', 'quality-control'];

            $authorized = ($userDiv === $targetDiv) || 
                          ($userDiv === 'stenter' && in_array($targetDiv, $stenterPath)) ||
                          (in_array($userDiv, $qcPath) && in_array($targetDiv, $qcPath));

            if ($user->role === 'operator' && !$authorized) {
                return back()->withErrors(['message' => 'Otoritas ditolak untuk divisi ' . $request->division_name]);
            }

            $marketingOrder = MarketingOrder::where('sap_no', $validated['sap_no'])->firstOrFail();

            $activityStatus = $validated['status'] ?? $this->inferActivityStatus($validated['division_name']);
            $nextOrderStatus = $this->inferNextMarketingOrderStatus($validated['division_name']);

            DB::transaction(function () use ($marketingOrder, $validated, $user, $activityStatus, $nextOrderStatus) {
                ProductionActivity::create([
                    'marketing_order_id' => $marketingOrder->id,
                    'division_name' => $validated['division_name'],
                    'operator_id' => $user->id,
                    'status' => $activityStatus,
                    'technical_data' => $validated['technical_data'] ?? null,
                ]);

                if ($nextOrderStatus !== null && $marketingOrder->status !== $nextOrderStatus) {
                    $marketingOrder->update(['status' => $nextOrderStatus]);
                }
            });

            return redirect()->route('operator.divisions')->with('success', 'Log berhasil disimpan!');

        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Gagal: ' . $e->getMessage()]);
        }
    }

    public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'sap_no' => 'required|unique:marketing_orders,sap_no',
            'art_no' => 'required',
            'tanggal' => 'required|date',
            'pelanggan' => 'required|string',
            'warna' => 'required|string',
            'status' => 'nullable'
        ]);

        $validated['status'] = 'knitting';
        MarketingOrder::create($validated);

        return redirect()->route('marketing.orders.index')->with('success', 'Order Dibuat!');
    }

    public function marketingOrderBySap(int $sapNo)
    {
        try {
            $order = MarketingOrder::where('sap_no', $sapNo)->firstOrFail();
            return response()->json([
                'ok' => true,
                'data' => [
                    'pelanggan' => $order->pelanggan,
                    'warna' => $order->warna,
                    'art_no' => $order->art_no,
                    'material' => $order->material,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => 'SAP tidak ditemukan'], 404);
        }
    }

    private function normalizeDivision(string $divisionName): string
    {
        return strtolower(trim($divisionName));
    }

    private function inferActivityStatus(string $divisionName): string
    {
        $d = $this->normalizeDivision($divisionName);
        if (str_contains($d, 'knit')) return 'knitting';
        if (Str::contains($d, ['dye', 'scr', 'warna'])) return 'dyeing';
        if (preg_match('/(relax|compact|stenter|heat|finish|tumbler|fleece)/', $d)) return 'finishing';
        if (Str::contains($d, ['qc', 'qe', 'uji'])) return 'qc';
        return 'knitting'; // Default jika tidak terdeteksi
    }

    private function inferNextMarketingOrderStatus(string $divisionName): ?string
    {
        $d = $this->normalizeDivision($divisionName);
        if (str_contains($d, 'knit')) return 'dyeing';
        if (Str::contains($d, ['dye', 'scr'])) return 'finishing';
        if (preg_match('/(relax|heat|compact)/', $d)) return 'finishing';
        if (preg_match('/(stenter|tumbler|fleece)/', $d)) return 'qc';
        if (Str::contains($d, ['qc', 'qe'])) return 'completed';
        return null;
    }

    public function storeKnittingLog(Request $request)
    {
        $request->validate(['tanggal' => 'required|date', 'kg' => 'required|numeric']);
        \App\Models\KnittingLog::create(['user_id' => auth()->id(), ...$request->all()]);
        return redirect()->back()->with('message', 'Data berhasil disimpan!');
    }
}