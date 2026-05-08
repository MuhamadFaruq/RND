<?php

namespace App\Livewire\Marketing;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MarketingOrder;
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
            MarketingOrder::findOrFail($id)->delete();
            if ($this->selectedOrder && ($this->selectedOrder['id'] ?? null) == $id) {
                $this->closeDetail();
            }
            $this->dispatch('show-toast', message: 'Order berhasil dihapus.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('show-error-toast', message: 'Gagal menghapus order: ' . $e->getMessage());
        }
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

    public function loadTrackingLogs(string $sap)
    {
        $service = app(MarketingOrderService::class);
        $this->activitiesLogs = $service->getTrackingLogs($sap);
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