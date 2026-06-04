<?php

namespace App\Livewire\Marketing;

use Livewire\Component;
use App\Models\MarketingOrder;
use App\Models\ActivityLog;
use App\Services\MarketingOrderService;
use Livewire\WithPagination;
use Carbon\Carbon;

class MarketingDashboard extends Component
{
    use WithPagination;

    // Properti Publik
    public $currentMenu = 'dashboard'; 
    public $dateRange = 'semua';

    protected $listeners = ['change-menu' => 'setMenu'];

    /**
     * Inisialisasi awal menu berdasarkan URL query
     */
    public function mount()
    {
        if (!auth()->check() || (auth()->user()->role !== 'marketing' && !auth()->user()->isSuperAdmin())) {
            abort(403, 'Akses Ditolak.');
        }

        $this->currentMenu = request()->query('menu', 'dashboard');
        
        // Allowed menus
        $allowedMenus = ['dashboard', 'input', 'orders', 'calculator'];
        if (!in_array($this->currentMenu, $allowedMenus)) {
            $this->currentMenu = 'dashboard';
        }
    }

    public $search = '';
    
    // Properties for Detail Modal
    public $selectedOrder;
    public $activitiesLogs = [];
    public $showDetail = false;
    public function updatedSearch()
    {
        $this->resetPage(); // Reset halaman ke 1 saat mengetik
    }
    /**
     * Navigasi Menu Dashboard
     */
    public function setMenu($menu)
    {
        $this->currentMenu = $menu;
        $this->js("window.history.replaceState(null, '', '/marketing/dashboard?menu={$menu}')");
    }

    /**
     * Reset pagination saat filter waktu diubah
     */
    public function updatedDateRange()
    {
        $this->resetPage();
    }

    public function pushOperator($orderId)
    {
        $order = MarketingOrder::find($orderId);
        $order->update(['is_urgent' => true]);

        session()->flash('success', "Operator Divisi telah diperingatkan untuk Artikel #{$order->art_no}");
        $this->dispatch('show-toast', 
            message: "Sinyal Prioritas Terkirim ke Operator untuk Artikel #{$order->art_no}!",
            type: 'success'
        );
    }

    public function openDetail($id)
    {
        $order = MarketingOrder::findOrFail($id);
        // Store as plain array — Livewire 3 cannot serialize Eloquent Model as public property
        $this->selectedOrder = $order->toArray();
        $this->loadTrackingLogs($order->art_no);
        $this->showDetail = true;
    }

    public function closeDetail()
    {
        $this->showDetail = false;
        $this->selectedOrder = null;
        $this->activitiesLogs = [];
    }

    public function loadTrackingLogs($artNo)
    {
        $service = app(MarketingOrderService::class);
        $this->activitiesLogs = $service->getTrackingLogs($artNo);
    }

    public function deleteOrder($id)
    {
        try {
            $order = MarketingOrder::findOrFail($id);
            $artNo = $order->art_no;
            $sapNo = $order->sap_no;
            
            $user = auth()->user();
            
            // Check if production has already started (there are production activities recorded)
            $productionCount = $order->productionActivities()->count();
            
            if ($productionCount > 0) {
                // If not Superadmin, block it completely
                if (!$user->isSuperAdmin()) {
                    $this->dispatch('show-error-toast', message: 'Order tidak dapat dihapus karena sudah masuk tahap produksi. Hubungi Plant Manager / Superadmin.');
                    return;
                }
            }
            
            // SIMPAN KE COLD STORAGE (ARCHIVE)
            \App\Models\ArchivedOrder::create([
                'original_order_id' => $order->id,
                'sap_no' => $sapNo,
                'art_no' => $artNo,
                'tanggal' => $order->tanggal,
                'pelanggan' => $order->pelanggan,
                'mkt' => $order->mkt,
                'original_data' => $order->toArray(),
                'production_logs' => $order->productionActivities->toArray(),
                'deleted_by' => $user->id,
                'reason' => "Dihapus dari Marketing Dashboard",
            ]);

            $order->forceDelete(); // Menghapus secara permanen agar data di tabel berelasi juga terhapus (cascade) dan nomor artikel/SAP bisa dipakai ulang
            // LOGGING AUDIT TRAIL
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'DELETE_ORDER',
                'division'    => $user->role ?? 'MARKETING',
                'art_no'      => $artNo,
                'sap_no'      => $sapNo,
                'description' => "Menghapus Order Artikel: {$artNo}. Alasan: Dihapus oleh " . $user->name . ($productionCount > 0 ? " (Order sudah berjalan produksi)" : " (Order belum berjalan produksi)"),
            ]);

            if ($this->selectedOrder && ($this->selectedOrder['id'] ?? null) == $id) {
                $this->closeDetail();
            }
            $this->dispatch('show-toast', message: 'Order berhasil dihapus & dicatat di log sistem.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('show-error-toast', message: 'Gagal menghapus order: ' . $e->getMessage());
        }
    }

    public function exportExcel()
    {
        $query = MarketingOrder::query();
        $this->applyTimeFilter($query);
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('art_no', 'like', "%{$this->search}%")
                ->orWhere('pelanggan', 'like', "%{$this->search}%")
                ->orWhere('sap_no', 'like', "%{$this->search}%");
            });
        }

        $orders = $query->latest()->get();
        $labelPeriode = strtoupper($this->dateRange);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\MarketingOrdersExport($orders, $labelPeriode), 
            'Duniatex_Dashboard_Report_' . now()->format('Y-m-d') . '.xlsx'
        );
    }
    /**
     * Logika Filter Waktu (Bisa digunakan ulang)
     */
    protected function applyTimeFilter($query)
    {
        if ($this->dateRange === 'harian') {
            $query->whereDate('created_at', today());
        } elseif ($this->dateRange === 'mingguan') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($this->dateRange === 'bulanan') {
            $query->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
        } elseif ($this->dateRange === 'tahunan') {
            $query->whereYear('created_at', now()->year);
        }
    }

    /**
     * Mendapatkan Data Beban Kerja Mesin (5 Divisi Terpadu)
     */
    protected function getMachineWorkload()
    {
        $stages = [
            [
                'name' => 'Knitting',
                'unit' => 'Weaving Unit',
                'color' => 'brand',
                'icon' => '',
                'statuses' => ['knitting'],
                'desc_name' => 'Knitting'
            ],
            [
                'name' => 'Dyeing',
                'unit' => 'Dyeing & Finishing Unit',
                'color' => 'indigo',
                'icon' => '',
                'statuses' => ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'stenter', 'tumbler', 'fleece', 'finishing'],
                'desc_name' => 'Dyeing & Finishing'
            ],
            [
                'name' => 'Pengujian',
                'unit' => 'Testing Unit',
                'color' => 'amber',
                'icon' => '',
                'statuses' => ['pengujian'],
                'desc_name' => 'Pengujian'
            ],
            [
                'name' => 'QE',
                'unit' => 'QE Unit',
                'color' => 'cyan',
                'icon' => '',
                'statuses' => ['qe'],
                'desc_name' => 'QE'
            ],
            [
                'name' => 'Finished',
                'unit' => 'Completed Unit',
                'color' => 'emerald',
                'icon' => '',
                'statuses' => ['finished'],
                'desc_name' => 'Selesai Produksi'
            ],
        ];

        $maxCapacity = \App\Models\Setting::where('key', 'max_capacity')->first()->value ?? 1000;

        foreach ($stages as &$stage) {
            $stage['load_count'] = MarketingOrder::whereIn('status', $stage['statuses'])->count();
            // Load dihitung berdasarkan total KG antrean dibandingkan kapasitas mesin
            $totalKgInQueue = MarketingOrder::whereIn('status', $stage['statuses'])->sum('kg_target');
            
            $stage['load_kg'] = $totalKgInQueue;
            $stage['percentage'] = $maxCapacity > 0 ? min(($totalKgInQueue / $maxCapacity) * 100, 100) : 0;
            $stage['desc'] = "Antrean di " . $stage['desc_name'];
            $stage['is_full'] = $stage['percentage'] >= 90;
        }

        return $stages;
    }

    public function render()
    {   
        // 1. Inisialisasi Query & Jalankan Filter
        $orderQuery = MarketingOrder::with('productionActivities');
        $this->applyTimeFilter($orderQuery);

        // 2. Terapkan Filter Search (Penting agar input "HALLO" berfungsi)
        $orderQuery->when($this->search, function($q) {
            $q->where('art_no', 'like', "%{$this->search}%")
            ->orWhere('pelanggan', 'like', "%{$this->search}%")
            ->orWhere('sap_no', 'like', "%{$this->search}%");
        });

        // 3. Cache hasil getMachineWorkload agar tidak dipanggil berulang (19 query per panggilan)
        $stages = $this->getMachineWorkload();
        $maxCapacity = \App\Models\Setting::where('key', 'max_capacity')->first()->value ?? 1000;

        // 4. Kembalikan View dengan data yang sudah terfilter
        // Prepare recent orders with deviation flags
        $recentOrders = $orderQuery->latest()->take(10)->get();
        $recentOrders = $recentOrders->map(function($order) {
            $activity = $order->productionActivities->sortByDesc('created_at')->first();
            $kgTarget = $order->kg_target;
            $rollTarget = $order->roll_target;
            $kgDeviation = $kgTarget > 0 && $activity && is_numeric($activity->kg) && $activity->kg > 0 && (abs($activity->kg - $kgTarget) / $kgTarget) > 0.1;
            $rollDeviation = $rollTarget > 0 && $activity && is_numeric($activity->roll) && $activity->roll > 0 && (abs($activity->roll - $rollTarget) / $rollTarget) > 0.1;
            $order->deviation = $kgDeviation || $rollDeviation;
            $order->overdue = $order->created_at->diffInDays(now()) > 3;
            return $order;
        });

        return view('livewire.marketing.marketing-dashboard', [
            'totalOrder'     => (clone $orderQuery)->count(),
            'allOrders'      => (clone $orderQuery)->latest()->paginate(10),

            // HANYA GUNAKAN SATU recentOrders (mengambil dari $orderQuery yang sudah difilter)
            'recentOrders'   => $recentOrders,

            'knittingOrder'  => MarketingOrder::where('status', 'knitting')->count(),
            'activeOrder'    => MarketingOrder::whereNotIn('status', ['knitting', 'finished'])->count(),
            'completedOrder' => MarketingOrder::where('status', 'finished')->count(),

            'stuckOrders'    => MarketingOrder::where('status', 'knitting')
                                    ->where('created_at', '<=', now()->subDays(2))
                                    ->count(),

            'stages'         => $stages,
            'factoryLoad'    => collect($stages)->avg('percentage'),
            'maxCapacity'    => $maxCapacity,
        ])->layout('layouts.app');
    }
}