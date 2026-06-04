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
        
        // 1. Ambil data aktivitas per jam untuk HEATMAP (Unik per order per alur)
        $activitiesQuery = ProductionActivity::whereDate('created_at', $targetDate->format('Y-m-d'));
        if ($this->selectedDivision !== 'all') {
            $activitiesQuery->where('division_name', $this->selectedDivision);
        }
        $activities = $activitiesQuery->get();

        // Filter unik per order per alur
        $knitting = $activities->filter(fn($act) => strtoupper($act->division_name) === 'KNITTING')
            ->groupBy('marketing_order_id')->map(fn($group) => $group->sortByDesc('created_at')->first());

        $warnaDivs = ['DYEING', 'RELAX-DRYER', 'COMPACTOR', 'HEAT-SETTING', 'STENTER', 'TUMBLER', 'FLEECE', 'FINISHING'];
        $warna = $activities->filter(fn($act) => in_array(strtoupper($act->division_name), $warnaDivs))
            ->groupBy('marketing_order_id')->map(fn($group) => $group->sortByDesc('created_at')->first());

        $others = $activities->filter(fn($act) => 
            strtoupper($act->division_name) !== 'KNITTING' && 
            !in_array(strtoupper($act->division_name), $warnaDivs)
        )->groupBy('marketing_order_id')->map(fn($group) => $group->sortByDesc('created_at')->first());

        $uniqueActivities = $knitting->concat($warna)->concat($others);

        $this->hourlyActivity = array_fill(0, 24, 0);
        foreach ($uniqueActivities as $act) {
            $hour = (int)$act->created_at->format('H');
            $this->hourlyActivity[$hour] += $act->kg;
        }

        // Sinkronisasi data untuk Line Chart JS
        $this->chartData = [];
        for ($i = 0; $i < 24; $i++) { 
            $this->chartData[] = (float)($this->hourlyActivity[$i] ?? 0); 
        }

        // 2. LOGIKA TREN: Senin sampai Minggu (atau 30 hari ke belakang)
        $days = $this->period == 'weekly' ? 7 : 30;
        $this->trends = collect(range($days - 1, 0))->map(function($i) use ($targetDate) {
            $date = $targetDate->copy()->subDays($i); 
            $date->settings(['locale' => 'id']);
            
            $query = ProductionActivity::whereDate('created_at', $date->format('Y-m-d'));
            if ($this->selectedDivision !== 'all') {
                $query->where('division_name', $this->selectedDivision);
            }
            $dayActivities = $query->get();

            $knitting = $dayActivities->filter(fn($act) => strtoupper($act->division_name) === 'KNITTING')
                ->groupBy('marketing_order_id')->map(fn($group) => $group->sortByDesc('created_at')->first());

            $warnaDivs = ['DYEING', 'RELAX-DRYER', 'COMPACTOR', 'HEAT-SETTING', 'STENTER', 'TUMBLER', 'FLEECE', 'FINISHING'];
            $warna = $dayActivities->filter(fn($act) => in_array(strtoupper($act->division_name), $warnaDivs))
                ->groupBy('marketing_order_id')->map(fn($group) => $group->sortByDesc('created_at')->first());

            $others = $dayActivities->filter(fn($act) => 
                strtoupper($act->division_name) !== 'KNITTING' && 
                !in_array(strtoupper($act->division_name), $warnaDivs)
            )->groupBy('marketing_order_id')->map(fn($group) => $group->sortByDesc('created_at')->first());

            $dayTotal = $knitting->sum('kg') + $warna->sum('kg') + $others->sum('kg');

            return [
                'day'   => $date->format('d/m'),
                'label' => $date->translatedFormat('D'), 
                'total' => (float)$dayTotal
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