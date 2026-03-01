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
        // Logika 1: Tandai sebagai urgent di database
        $order->update(['is_urgent' => true]);

        // Logika 2: Kirim notifikasi (Contoh: Telegram/WhatsApp)
        // Notification::send($supervisor, new OrderUrgentNotification($order));

        session()->flash('success', "Operator Divisi telah diperingatkan untuk SAP #{$order->sap_no}");
        $this->dispatch('show-toast', 
            message: "Sinyal Prioritas Terkirim ke Operator untuk SAP #{$order->sap_no}!",
            type: 'success'
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
            ['name' => 'Finishing', 'unit' => 'Finishing Unit', 'color' => 'indigo', 'icon' => '✨', 'status' => 'finishing'],
            ['name' => 'Stenter', 'unit' => 'Stenter Unit', 'color' => 'violet', 'icon' => '📏', 'status' => 'stenter'],
            ['name' => 'Tumbler', 'unit' => 'Tumbler Unit', 'color' => 'orange', 'icon' => '🌀', 'status' => 'tumbler'],
            ['name' => 'Fleece', 'unit' => 'Fleece Unit', 'color' => 'rose', 'icon' => '🧥', 'status' => 'fleece'],
            ['name' => 'Pengujian', 'unit' => 'Testing Unit', 'color' => 'emerald', 'icon' => '🔬', 'status' => 'pengujian'],
            ['name' => 'QE', 'unit' => 'QE Unit', 'color' => 'green', 'icon' => '✅', 'status' => 'qe'],
        ];

        foreach ($stages as &$stage) {
            $stage['load'] = MarketingOrder::where('status', $stage['status'])->count();
            // Asumsi kapasitas maksimal antrean per divisi adalah 100
            $stage['percentage'] = min(($stage['load'] / 100) * 100, 100);
            $stage['desc'] = "Antrean di " . $stage['name'];
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

        // 3. Kembalikan View dengan data yang sudah terfilter
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
            
            'stages'         => $this->getMachineWorkload(),
        ])->layout('layouts.app');
    }
}