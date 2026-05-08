<?php

namespace App\Livewire\Marketing;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MarketingOrder;

class Monitoring extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = ''; // Untuk filter status

    // Reset halaman ke nomor 1 setiap kali user mengetik pencarian
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Query Pipeline Utama
        $orders = MarketingOrder::query()
            ->when($this->search, function($query) {
                $query->where('sap_no', 'like', '%' . $this->search . '%')
                      ->orWhere('pelanggan', 'like', '%' . $this->search . '%')
                      ->orWhere('art_no', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function($query) {
                $query->where('status', $this->statusFilter);
            })
            ->latest()
            ->paginate(10);

        // Hitung Statistik Real-time
        $stats = [
            'total' => MarketingOrder::count(),
            'knitting' => MarketingOrder::where('status', 'knitting')->count(),
            'active' => MarketingOrder::whereIn('status', ['knitting', 'dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'stenter', 'tumbler', 'fleece', 'pengujian', 'qe'])->count(),
            'completed' => MarketingOrder::where('status', 'finished')->count(),
        ];

        return view('livewire.marketing.monitoring', [
            'orders' => $orders,
            'stats' => $stats
        ])->layout('layouts.app');
    }
}