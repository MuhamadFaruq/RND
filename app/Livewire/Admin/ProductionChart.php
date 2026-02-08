<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\ProductionActivity;
use Carbon\Carbon;

class ProductionChart extends Component
{
    public $period = 'weekly';

    // Fungsi ini akan dipanggil apabila butang Mingguan/Bulanan diklik
    public function setPeriod($p)
    {
        $this->period = $p;
    }

    public function render()
    {
        $trends = $this->getTrendData();

        return view('livewire.admin.production-chart', [
            'trends' => $trends
        ]);
    }

    private function getTrendData()
    {
        $data = [];
        $count = ($this->period === 'monthly') ? 30 : 7;

        for ($i = $count - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $total = ProductionActivity::whereDate('created_at', $date)->count();
            
            $data[] = [
                'day' => $date->format('D'),
                'total' => $total
            ];
        }
        return $data;
    }
}