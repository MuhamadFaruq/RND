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

    // Properti yang terhubung ke wire:model.live di Blade Anda
    public $search = '';
    public $statusFilter = '';
    public $timeFilter = '';

    // Reset halaman ke nomor 1 setiap kali filter berubah
    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }
    public function updatingTimeFilter() { $this->resetPage(); }

    public $startDate, $endDate; // Tambahkan properti ini

    public function exportExcel()
    {
        $query = MarketingOrder::query();
        $labelPeriode = "Semua Waktu";

        // 1. Logika Filter & Label Periode
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('tanggal', [$this->startDate, $this->endDate]);
            $labelPeriode = \Carbon\Carbon::parse($this->startDate)->format('d/m/Y') . ' s/d ' . \Carbon\Carbon::parse($this->endDate)->format('d/m/Y');
        } 
        elseif ($this->timeFilter) {
            // Jika menggunakan filter cepat (Harian, Mingguan, dll)
            $labelPeriode = strtoupper($this->timeFilter);
            $this->applyTimeFilter($query); // Gunakan fungsi filter waktu Anda
        }

        // 2. Filter Tambahan
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

        // 3. Kirim data dan label ke Export Class
        return Excel::download(
            new MarketingOrdersExport($orders, $labelPeriode), 
            'Duniatex_Report_' . str_replace('/', '-', $labelPeriode) . '.xlsx'
        );
    }

    public function deleteOrder($id)
    {
        MarketingOrder::findOrFail($id)->delete();
        session()->flash('message', 'Order berhasil dihapus.');
    }

    public function render()
{
    $query = MarketingOrder::query();

    // 1. Filter Waktu (Rentang)
    if ($this->timeFilter) {
        $now = now();
        switch ($this->timeFilter) {
            case 'today':
                $query->whereDate('created_at', $now->today());
                break;
            case 'weekly':
                $query->whereBetween('created_at', [$now->startOfWeek()->toDateTimeString(), $now->endOfWeek()->toDateTimeString()]);
                break;
            case 'monthly':
                $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
                break;
            case 'yearly':
                $query->whereYear('created_at', $now->year);
                break;
        }
    }

    // 2. Filter Status & Search
    $query->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
          ->when($this->search, function($q) {
              $q->where('sap_no', 'like', "%{$this->search}%")
                ->orWhere('pelanggan', 'like', "%{$this->search}%")
                ->orWhere('art_no', 'like', "%{$this->search}%");
          });

    return view('components.marketing.order-list', [
        'orders' => $query->latest()->paginate(10),
        'totalOrder' => MarketingOrder::count(),
        'pendingOrder' => MarketingOrder::where('status', 'pending')->count(),
        'activeOrder' => MarketingOrder::where('status', 'in-progress')->count(),
        'completedOrder' => MarketingOrder::where('status', 'completed')->count(),
    ])->layout('layouts.app');
}
}