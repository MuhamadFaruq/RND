<?php

namespace App\Livewire\Marketing;

use Livewire\Component;
use App\Models\MarketingOrder;
use Illuminate\Support\Facades\DB;

class MarketingDashboard extends Component
{
    public function render()
    {
        return view('components.marketing.marketing-dashboard', [
            'totalOrder' => MarketingOrder::count(),
            'pendingOrder' => MarketingOrder::where('status', 'pending')->count(),
            'activeOrder' => MarketingOrder::whereNotIn('status', ['completed', 'pending'])->count(),
            'completedOrder' => MarketingOrder::where('status', 'completed')->count(),
            // Ambil 5 aktivitas terbaru khusus marketing
            'recentOrders' => MarketingOrder::latest()->limit(5)->get()
            ])->layout('layouts.app'); 
    }
}