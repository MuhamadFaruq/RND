<?php

namespace App\Livewire\Marketing;

use Livewire\Component;
use App\Models\MarketingOrder;
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
        $this->currentMenu = request()->query('menu', 'dashboard');
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

        session()->flash('success', "Operator Divisi telah diperingatkan untuk SAP #{$order->sap_no}");
        $this->dispatch('show-toast', 
            message: "Sinyal Prioritas Terkirim ke Operator untuk SAP #{$order->sap_no}!",
            type: 'success'
        );
    }

    public function openDetail($id)
    {
        $order = MarketingOrder::findOrFail($id);
        // Store as plain array — Livewire 3 cannot serialize Eloquent Model as public property
        $this->selectedOrder = $order->toArray();
        $this->loadTrackingLogs($order->sap_no);
        $this->showDetail = true;
    }

    public function closeDetail()
    {
        $this->showDetail = false;
        $this->selectedOrder = null;
        $this->activitiesLogs = [];
    }

    public function loadTrackingLogs($sap)
    {
        $this->activitiesLogs = \App\Models\ProductionActivity::with('operator')
            ->whereHas('marketingOrder', fn($q) => $q->where('sap_no', $sap))
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('division_name')
            ->toArray();
    }

    public function exportExcel()
    {
        $query = MarketingOrder::query();
        $this->applyTimeFilter($query);
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('sap_no', 'like', "%{$this->search}%")
                ->orWhere('pelanggan', 'like', "%{$this->search}%")
                ->orWhere('art_no', 'like', "%{$this->search}%");
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
     * Mendapatkan Data Beban Kerja Mesin (9 Divisi)
     */
    protected function getMachineWorkload()
    {
        $stages = [
            ['name' => 'Knitting', 'unit' => 'Weaving Unit', 'color' => 'blue', 'icon' => '🧶', 'status' => 'knitting'],
            ['name' => 'Dyeing', 'unit' => 'Coloring Unit', 'color' => 'amber', 'icon' => '🧪', 'status' => 'dyeing'],
            ['name' => 'Relax Dryer', 'unit' => 'Drying Unit', 'color' => 'cyan', 'icon' => '💨', 'status' => 'relax-dryer'],
            ['name' => 'Compactor', 'unit' => 'Compactor Unit', 'color' => 'green', 'icon' => '🌀', 'status' => 'compactor'],
            ['name' => 'Heat Setting', 'unit' => 'Heat Setting Unit', 'color' => 'indigo', 'icon' => '🌡️', 'status' => 'heat-setting'],
            ['name' => 'Stenter', 'unit' => 'Stenter Unit', 'color' => 'violet', 'icon' => '📏', 'status' => 'stenter'],
            ['name' => 'Tumbler', 'unit' => 'Tumbler Unit', 'color' => 'orange', 'icon' => '🌀', 'status' => 'tumbler'],
            ['name' => 'Fleece', 'unit' => 'Fleece Unit', 'color' => 'rose', 'icon' => '🧥', 'status' => 'fleece'],
            ['name' => 'Pengujian', 'unit' => 'Testing Unit', 'color' => 'emerald', 'icon' => '🔬', 'status' => 'pengujian'],
            ['name' => 'QE', 'unit' => 'QE Unit', 'color' => 'green', 'icon' => '✅', 'status' => 'qe'],
        ];

        $maxCapacity = \App\Models\Setting::where('key', 'max_capacity')->first()->value ?? 1000;

        foreach ($stages as &$stage) {
            $stage['load_count'] = MarketingOrder::where('status', $stage['status'])->count();
            // Load dihitung berdasarkan total KG antrean dibandingkan kapasitas mesin
            $totalKgInQueue = MarketingOrder::where('status', $stage['status'])->sum('kg_target');
            
            $stage['load_kg'] = $totalKgInQueue;
            $stage['percentage'] = $maxCapacity > 0 ? min(($totalKgInQueue / $maxCapacity) * 100, 100) : 0;
            $stage['desc'] = "Antrean di " . $stage['name'];
            $stage['is_full'] = $stage['percentage'] >= 90;
        }

        return $stages;
    }

    public function render()
    {   
        // 1. Inisialisasi Query & Jalankan Filter
        $orderQuery = MarketingOrder::query();
        $this->applyTimeFilter($orderQuery);

        // 2. Terapkan Filter Search (Penting agar input "HALLO" berfungsi)
        $orderQuery->when($this->search, function($q) {
            $q->where('sap_no', 'like', "%{$this->search}%")
            ->orWhere('pelanggan', 'like', "%{$this->search}%")
            ->orWhere('art_no', 'like', "%{$this->search}%");
        });

        // 3. Cache hasil getMachineWorkload agar tidak dipanggil berulang (19 query per panggilan)
        $stages = $this->getMachineWorkload();
        $maxCapacity = \App\Models\Setting::where('key', 'max_capacity')->first()->value ?? 1000;

        // 4. Kembalikan View dengan data yang sudah terfilter
        return view('livewire.marketing.marketing-dashboard', [
            'totalOrder'     => (clone $orderQuery)->count(), 
            'allOrders'      => (clone $orderQuery)->latest()->paginate(10),
            
            // HANYA GUNAKAN SATU recentOrders (mengambil dari $orderQuery yang sudah difilter)
            'recentOrders'   => $orderQuery->latest()->take(10)->get(), 
            
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