<?php

namespace App\Livewire\Marketing;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MarketingOrder;
use App\Models\ActivityLog;
use App\Exports\MarketingOrdersExport;
use App\Services\MarketingOrderService;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class OrderList extends Component
{
    use WithPagination;

    // 1. Deklarasi Properti (Hanya sekali saja)
    public $search = '';
    public $statusFilter = '';
    public $dateRange = 'semua'; 
    public $startDate, $endDate;

    // Properties for Detail Modal
    public $selectedOrder;
    public $activitiesLogs = [];
    public $showDetail = false;

    // 2. Lifecycle Hooks (Reset pagination saat filter berubah)
    public function updatedSearch() { $this->resetPage(); }
    public function updatedStatusFilter() { $this->resetPage(); }
    public function updatedDateRange() { $this->resetPage(); }
    public function updatedStartDate() { $this->resetPage(); }
    public function updatedEndDate() { $this->resetPage(); }

    public function exportExcel(MarketingOrderService $service)
    {
        $filters = [
            'search'       => $this->search,
            'statusFilter' => $this->statusFilter,
            'dateRange'    => $this->dateRange,
            'startDate'    => $this->startDate,
            'endDate'      => $this->endDate,
        ];

        $query = $service->getFilteredQuery($filters);
        
        $labelPeriode = "Semua Waktu";
        if ($this->startDate && $this->endDate) {
            $labelPeriode = Carbon::parse($this->startDate)->format('d/m/Y') . ' s/d ' . Carbon::parse($this->endDate)->format('d/m/Y');
        } elseif ($this->dateRange !== 'semua') {
            $labelPeriode = strtoupper($this->dateRange);
        }

        return Excel::download(
            new MarketingOrdersExport($query->latest()->get(), $labelPeriode), 
            'Duniatex_Report_' . str_replace('/', '-', $labelPeriode) . '.xlsx'
        );
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
                'reason' => "Dihapus dari Marketing Order List",
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

    public function loadTrackingLogs(string $artNo)
    {
        $service = app(MarketingOrderService::class);
        $this->activitiesLogs = $service->getTrackingLogs($artNo);
    }

    public function render(MarketingOrderService $service)
    {
        $filters = [
            'search'       => $this->search,
            'statusFilter' => $this->statusFilter,
            'dateRange'    => $this->dateRange,
            'startDate'    => $this->startDate,
            'endDate'      => $this->endDate,
        ];

        $query = $service->getFilteredQuery($filters);
        $summary = $service->getStatusSummary();

        return view('livewire.marketing.order-list', [
            'orders'         => $query->latest()->paginate(10),
            'totalOrder'     => $query->count(),
            'knittingOrder'  => $summary['knitting'],
            'activeOrder'    => $summary['active'],
            'completedOrder' => $summary['completed'],
        ])->layout('layouts.app');
    }
}