<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\ProductionActivity;
use App\Models\Setting;
use Carbon\Carbon;

class ProductionChart extends Component
{
    public $period = 'weekly';
    public $trends = [];
    public $divisionLeadTimes = [];
    public $hourlyActivity = [];
    public $selectedDivision = 'all';
    public $chartData = [];
    public $selectedDate;

    public function setPeriod($period)
    {
        $this->period = $period;
        $this->loadData();
    }

    // Tambahkan juga ini agar dropdown divisi berfungsi
    public function updatedSelectedDivision()
    {
        $this->loadData();
    }

    public function mount($selectedDate = null)
    {
        $this->selectedDate = $selectedDate ?? now()->format('Y-m-d');
        $this->loadData(); // Inilah metode yang dicari oleh sistem
    }

    public function loadData()
    {
        $targetDate = \Carbon\Carbon::parse($this->selectedDate);
        
        // 1. Ambil data aktivitas per jam untuk HEATMAP (Tetap sesuai tanggal yang dipilih)
        $this->hourlyActivity = ProductionActivity::selectRaw('HOUR(created_at) as hour, SUM(kg) as total_kg')
            ->whereDate('created_at', $targetDate->format('Y-m-d'))
            ->when($this->selectedDivision !== 'all', fn($q) => $q->where('division_name', $this->selectedDivision))
            ->groupByRaw('HOUR(created_at)')
            ->pluck('total_kg', 'hour')
            ->all();

        // Sinkronisasi data untuk Line Chart JS
        $this->chartData = [];
        for ($i = 0; $i < 24; $i++) { 
            $this->chartData[] = (float)($this->hourlyActivity[$i] ?? 0); 
        }

        // 2. LOGIKA TREN STATIS: Senin sampai Minggu
        // Jika mode mingguan, kunci tanggal awal ke hari Senin di minggu tersebut
        if ($this->period == 'weekly') {
            $startDate = $targetDate->copy()->startOfWeek(); 
            $iterations = 6; // Senin + 6 hari = Minggu
        } else {
            $startDate = $targetDate->copy()->subDays(29);
            $iterations = 29;
        }

        $this->trends = collect(range(0, $iterations))->map(function($i) use ($startDate) {
            $date = $startDate->copy()->addDays($i); 
            $date->settings(['locale' => 'id']);
            
            $query = ProductionActivity::whereDate('created_at', $date->format('Y-m-d'));
            
            if ($this->selectedDivision !== 'all') {
                $query->where('division_name', $this->selectedDivision);
            }

            return [
                'day'   => $date->format('d/m'),
                'label' => $date->translatedFormat('D'), // Hasil: Sen, Sel, Rab, dst
                'total' => (float) ($query->sum('kg') ?: 0)
            ];
        })->all();
    }

    public function render()
    {
        $maxCapacity = Setting::where('key', 'max_capacity')->value('value') ?? 1000;
        $totalInput = collect($this->hourlyActivity)->sum();

        return view('livewire.admin.production-chart', [
            'maxCapacity' => $maxCapacity,
            'totalInput'  => $totalInput,
            'chartData'    => $this->chartData,    
            'selectedDate' => $this->selectedDate,
        ]);
    }
}