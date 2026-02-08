<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\ProductionActivity;
use App\Models\MarketingOrder;
use App\Models\Division;
use Carbon\Carbon;

class Monitoring extends Component
{
    public function render()
    {
        // Ambil 10 aktivitas produksi terakhir dari semua divisi
        $latestActivities = ProductionActivity::with(['marketingOrder', 'user', 'division'])
            ->latest()
            ->take(10)
            ->get();

        // Hitung total produksi hari ini (KG)
        $todayProduction = ProductionActivity::whereDate('created_at', Carbon::today())->sum('berat_kg');

        // Ringkasan per Divisi untuk hari ini
        $divisionStats = Division::withCount(['productionActivities' => function($query) {
            $query->whereDate('created_at', Carbon::today());
        }])->get();

        return view('livewire.admin.monitoring', [
            'latestActivities' => $latestActivities,
            'todayProduction' => $todayProduction,
            'divisionStats' => $divisionStats,
            'currentTime' => Carbon::now()->format('H:i:s')
        ])->layout('layouts.app'); // Gunakan layout utama
    }
}