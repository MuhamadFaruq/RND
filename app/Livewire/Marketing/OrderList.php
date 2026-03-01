<?php

namespace App\Livewire\Marketing;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MarketingOrder;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\MarketingOrdersExport;
use Carbon\Carbon;

class OrderList extends Component
{
    use WithPagination;

    // 1. Deklarasi Properti (Hanya sekali saja)
    public $search = '';
    public $statusFilter = '';
    public $dateRange = 'semua'; 
    public $startDate, $endDate;

    // 2. Lifecycle Hooks (Reset pagination saat filter berubah)
    public function updatedSearch() { $this->resetPage(); }
    public function updatedStatusFilter() { $this->resetPage(); }
    public function updatedDateRange() { $this->resetPage(); }
    public function updatedStartDate() { $this->resetPage(); }
    public function updatedEndDate() { $this->resetPage(); }

    public function exportExcel()
    {
        $query = MarketingOrder::query();
        $labelPeriode = "Semua Waktu";

        // Filter Export agar sinkron dengan UI
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('tanggal', [$this->startDate, $this->endDate]);
            $labelPeriode = Carbon::parse($this->startDate)->format('d/m/Y') . ' s/d ' . Carbon::parse($this->endDate)->format('d/m/Y');
        } elseif ($this->dateRange !== 'semua') {
            // PERBAIKAN: Gunakan properti dateRange yang baru
            $labelPeriode = strtoupper($this->dateRange);
            $this->applyTimeFilter($query); 
        }

        // Filter Tambahan (Status & Search)
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('sap_no', 'like', "%{$this->search}%")
                ->orWhere('pelanggan', 'like', "%{$this->search}%");
            });
        }

        $orders = $query->latest()->get();

        return Excel::download(
            new MarketingOrdersExport($orders, $labelPeriode), 
            'Duniatex_Report_' . str_replace('/', '-', $labelPeriode) . '.xlsx'
        );
    }

    // Fungsi pembantu untuk filter waktu
    protected function applyTimeFilter($query)
    {
        if ($this->dateRange === 'harian') {
            $query->whereDate('tanggal', today());
        } elseif ($this->dateRange === 'mingguan') {
            $query->whereBetween('tanggal', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($this->dateRange === 'bulanan') {
            $query->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year);
        } elseif ($this->dateRange === 'tahunan') {
            $query->whereYear('tanggal', now()->year);
        }
    }

    public function deleteOrder($id)
    {
        MarketingOrder::findOrFail($id)->delete();
        session()->flash('message', 'Order berhasil dihapus.');
    }

    public function render()
    {
        $query = MarketingOrder::query();

        // 1. Jalankan Filter Waktu
        $this->applyTimeFilter($query);

        // 2. Filter Tanggal Manual
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('tanggal', [$this->startDate, $this->endDate]);
        }

        // 3. Filter Status & Search
        $query->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, function($q) {
                $q->where('sap_no', 'like', "%{$this->search}%")
                    ->orWhere('pelanggan', 'like', "%{$this->search}%")
                    ->orWhere('art_no', 'like', "%{$this->search}%");
            });

        // 4. Hitung Total Berdasarkan Query ter-filter
        $filteredTotal = (clone $query)->count();

        return view('livewire.marketing.order-list', [
            'orders' => $query->latest()->paginate(10),
            'totalOrder' => $filteredTotal,
            'knittingOrder' => MarketingOrder::where('status', 'knitting')->count(),
            'activeOrder' => MarketingOrder::whereNotIn('status', ['knitting', 'finished'])->count(),
            'completedOrder' => MarketingOrder::where('status', 'finished')->count(),
        ])->layout('layouts.app');
    }
}