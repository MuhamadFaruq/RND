<?php
use Livewire\Volt\Component;
use App\Models\ProductionActivity;
use App\Models\Setting;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

new class extends Component
{
    public $period = 'weekly';
    public $trends = [];
    public $divisionLeadTimes = [];
    public $hourlyActivity = [];
    public $selectedDivision = 'all';
    public $chartData = [];
    public $selectedDate;
    
    public function exportPDF() {
        $this->loadData();

        $targetDate = \Carbon\Carbon::parse($this->selectedDate);

        $activities = ProductionActivity::with('marketingOrder')
            ->when($this->selectedDivision !== 'all', fn($q) => $q->where('division_name', $this->selectedDivision))
            ->whereDate('created_at', $targetDate)
            ->latest()
            ->get();

        // Validasi data sebelum generate
        if ($activities->isEmpty()) {
            $this->dispatch('notify', message: 'Tidak ada data untuk diekspor pada tanggal ini.', type: 'error');
            return;
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.production-report', [
            'period' => $this->period,
            'selectedDivision' => $this->selectedDivision,
            'trends' => $this->trends,
            'hourlyActivity' => $this->hourlyActivity,
            'divisionLeadTimes' => $this->divisionLeadTimes,
            'activities' => $activities,
            'generated_at' => now()->format('d M Y H:i'),
            'admin_name' => auth()->user()->name ?? 'Admin'
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()), 
            "Report_" . strtoupper($this->selectedDivision) . "_" . $targetDate->format('Ymd') . ".pdf"
        );
    }

    public function mount($selectedDate = null) { 
        // Mengisi properti class dari parameter yang dikirim parent
        $this->selectedDate = $selectedDate ?? now()->format('Y-m-d');
        $this->loadData();
    }

    public function setPeriod($period) {
        $this->period = $period;
        $this->loadData();
    }

    public function updatedSelectedDivision() {
        $this->loadData();
    }

    public function updatedSelectedDate() {
        $this->loadData();
    }

    public function with()
    {
        $maxCapacity = Setting::where('key', 'max_capacity')->value('value') ?? 1000;
        $targetDate = Carbon::parse($this->selectedDate);

        // Hitung total input untuk logika Heatmap
        $totalInput = collect($this->hourlyActivity)->sum();

        // Logika Chart Data harian
        $productionByHour = ProductionActivity::whereDate('created_at', $targetDate)
            ->selectRaw('HOUR(created_at) as hour, SUM(kg) as total_kg')
            ->groupBy('hour')
            ->pluck('total_kg', 'hour')
            ->toArray();

        $this->chartData = [];
        for ($i = 0; $i < 24; $i++) { 
            $this->chartData[] = (float)($productionByHour[$i] ?? 0); 
        }

        return [
            'chartData'         => $this->chartData,
            'maxCapacity'       => $maxCapacity,
            'hourlyActivity'    => $this->hourlyActivity, 
            'trends'            => $this->trends,
            'divisionLeadTimes' => $this->divisionLeadTimes,
            'selectedDate'      => $this->selectedDate,
            // TAMBAHKAN BARIS INI:
            'totalInput'        => $totalInput, 
        ];
    }

    public function loadData() {
        $days = $this->period == 'weekly' ? 7 : 30;
        // Gunakan Carbon::parse agar format tanggal dari kalender terbaca dengan benar
        $targetDate = \Carbon\Carbon::parse($this->selectedDate);

        // 1. Lead Time tetap sama
        $divs = ['knitting', 'dyeing', 'relax-dryer', 'finishing', 'stenter', 'tumbler', 'fleece', 'pengujian', 'qe'];
        foreach($divs as $d) {
            $this->divisionLeadTimes[$d] = $this->calculateAvgLeadTime($d);
        }
        $this->dispatch('chart-updated');

        // 2. PERBAIKAN: Heatmap sekarang mengikuti $targetDate (bukan hari ini saja)
        $this->hourlyActivity = ProductionActivity::selectRaw('HOUR(created_at) as hour, SUM(kg) as total_kg')
            ->whereDate('created_at', $targetDate)
            ->when($this->selectedDivision !== 'all', fn($q) => $q->where('division_name', $this->selectedDivision))
            ->groupByRaw('HOUR(created_at)')
            ->pluck('total_kg', 'hour')
            ->all();

        // 3. Tren Produksi disesuaikan agar berakhir di $targetDate
        $this->trends = collect(range($days - 1, 0))->map(function($i) use ($targetDate) {
            $date = $targetDate->copy()->subDays($i); // Mundur dari tanggal terpilih
            $date->settings(['locale' => 'id']);
            $query = ProductionActivity::whereDate('created_at', $date->format('Y-m-d'));
            
            if ($this->selectedDivision !== 'all') {
                $query->where('division_name', $this->selectedDivision);
            }

            return [
                'day'   => $date->format('d/m'),
                'label' => $date->translatedFormat('D'), 
                'total' => (float) ($query->sum('kg') ?: 0)
            ];
        })->all();
    }

    private function calculateAvgLeadTime($division) {
        $activities = ProductionActivity::where('division_name', $division)
            ->where('status', 'completed')
            ->with('marketingOrder')
            ->latest()
            ->take(20)
            ->get();

        if ($activities->isEmpty()) return 0;

        $totalHours = $activities->map(function($activity) {
            $startTime = $activity->marketingOrder ? $activity->marketingOrder->created_at : $activity->created_at;
            return $activity->created_at->diffInHours($startTime);
        })->avg();

        return round(($totalHours ?? 0) / 24, 1);
    }
}
?>

@assets
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@3.0.1/dist/chartjs-plugin-annotation.min.js"></script>
@endassets

@script
<script>
    const renderChart = () => {
        const ctx = document.getElementById('productionChart');
        const container = document.getElementById('chartContainer');
        
        if (!ctx || !container) return;

        setTimeout(() => {
            const chartData = JSON.parse(container.getAttribute('data-chart'));
            const maxVal = parseFloat(container.getAttribute('data-max'));

            if (window.productionChart instanceof Chart) {
                window.productionChart.destroy();
            }

            window.productionChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Array.from({length: 24}, (_, i) => `${i}:00`),
                    datasets: [{
                        label: 'Produksi (KG)',
                        data: chartData,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 4,
                        pointStyle: 'circle',
                        pointRadius: 3,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        // 1. MATIKAN LEGENDA OTOMATIS (Lingkaran bawah akan hilang)
                        legend: {
                            display: false 
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMax: Math.max(...chartData) > 0 ? Math.max(...chartData) + 10 : 100,
                            grid: { color: 'rgba(255, 255, 255, 0.05)' },
                            ticks: { color: '#64748b' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#64748b' }
                        }
                    }
                }
            });
        }, 50);
    };

    // 2. FUNGSI TOGGLE UNTUK IKON MATA ATAS
    window.toggleProductionLine = () => {
        if (!window.productionChart) return;

        const isVisible = window.productionChart.isDatasetVisible(0);
        const eyeIcon = document.getElementById('eye-icon-svg');
        const btnText = document.getElementById('btn-text');

        if (isVisible) {
            window.productionChart.hide(0);
            if(eyeIcon) eyeIcon.style.opacity = '0.3'; // Redupkan ikon saat hidden
            if(btnText) btnText.style.color = '#475569';
        } else {
            window.productionChart.show(0);
            if(eyeIcon) eyeIcon.style.opacity = '1'; // Terangkan kembali
            if(btnText) btnText.style.color = '#94a3b8';
        }
    };

    renderChart();

    $wire.on('chart-updated', () => { renderChart(); });

    Livewire.hook('morph.updated', ({ el, component }) => { renderChart(); });
</script>
@endscript

<div class="bg-slate-900/80 rounded-[2rem] p-8 mb-8 shadow-2xl border border-slate-800 backdrop-blur-md">
    {{-- 1. HEADER & CONTROLS --}}
    <div class="flex justify-between items-center mb-10">
        <div>
            <h3 class="text-sm font-black uppercase italic text-red-500 tracking-tighter">Production Analytics Hub</h3>
            <p class="text-[9px] text-slate-500 font-bold uppercase mt-1 italic">Real-time Performance Metrics</p>
        </div>
        
        <div class="flex items-center gap-4">
            <select wire:model.live="selectedDivision" class="bg-slate-950 border-slate-800 text-slate-300 rounded-2xl px-4 py-2.5 text-[9px] font-black uppercase italic focus:ring-2 focus:ring-red-500 outline-none">
                <option value="all">SEMUA DIVISI</option>
                <option value="knitting">KNITTING</option>
                <option value="dyeing">SCR/DYEING</option>
                <option value="relax-dryer">RELAX DRYER</option>
                <option value="finishing">FINISHING</option>
                <option value="stenter">STENTER</option>
                <option value="tumbler">TUMBLER DRY</option>
                <option value="fleece">FLEECE</option>
                <option value="pengujian">PENGUJIAN (QC & LAB)</option>
                <option value="qe">QE / QC</option>
            </select>

            <button wire:click="exportPDF" class="bg-red-600 text-white px-5 py-2.5 rounded-2xl text-[9px] font-black uppercase italic hover:bg-black transition-all shadow-lg">
                EXPORT PDF
            </button>
        </div>
    </div>

    {{-- 2. MAIN CHART (Daily Activity Line) --}}
    <div id="chartContainer" 
        class="hidden" 
        data-chart="{{ json_encode($chartData ?? array_fill(0, 24, 0)) }}" 
        data-max="{{ $maxCapacity ?? 1000 }}">
    </div>

    {{-- Canvas tempat grafik muncul --}}
    <div class="flex justify-end mb-2">
        <div onclick="window.toggleProductionLine()" 
            class="flex items-center gap-2 bg-slate-800/40 px-3 py-1 rounded-full border border-slate-700/50 shadow-sm cursor-pointer hover:bg-slate-700/50 transition-all">
            
            <svg xmlns="http://www.w3.org/2000/svg" id="eye-icon-svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="transition-opacity duration-300">
                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
            </svg>
            
            <span id="btn-text" class="text-[9px] font-black text-slate-400 uppercase italic transition-colors duration-300">
                Produksi (KG)
            </span>
        </div>
    </div>

    <div class="mb-12 h-64 relative bg-slate-950/50 rounded-3xl p-6 border border-slate-800 shadow-inner">
        <canvas id="productionChart"></canvas>
    </div>

    {{-- 3. HEATMAP SECTION --}}
    <div class="mt-10 border-t border-slate-800 pt-8 mb-10 relative">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-[10px] font-black uppercase italic text-slate-400 tracking-widest">
                Hourly Activity Heatmap ({{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }})
            </h3>
        </div>

        @php 
            $safeHourlyData = $hourlyActivity ?? []; 
            $maxCount = collect($safeHourlyData)->max() ?: 1; 
        @endphp

        {{-- Gunakan variabel $totalInput yang dikirim dari with() --}}
        @if(($totalInput ?? 0) == 0)
            <div class="absolute inset-x-0 bottom-0 top-16 flex items-center justify-center bg-slate-900/60 backdrop-blur-[2px] rounded-xl z-10 border border-slate-800/50">
                <div class="text-center">
                    <span class="text-[10px] font-black text-red-500 uppercase tracking-widest italic animate-pulse">Waiting for production data...</span>
                    <p class="text-[8px] text-slate-500 uppercase mt-1 font-bold">Tidak ada aktivitas tercatat pada tanggal terpilih</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-6 md:grid-cols-12 lg:grid-cols-24 gap-2">
            @for($i = 0; $i < 24; $i++)
                @php 
                    $count = $safeHourlyData[$i] ?? 0;
                    $intensity = ($count / $maxCount) * 100;
                    $colorClass = $count == 0 ? 'bg-slate-800/30' : 'bg-red-600';
                @endphp
                <div class="group relative">
                    {{-- Kotak Heatmap: Tambahkan flex agar teks KG berada di tengah --}}
                    <div class="h-12 w-full rounded-lg {{ $colorClass }} transition-all border border-white/5 flex items-center justify-center overflow-hidden" 
                        style="opacity: {{ $count == 0 ? 100 : max($intensity, 45) }}%">
                        
                        {{-- Tampilkan angka KG jika data > 0 --}}
                        @if($count > 0)
                            <span class="text-[10px] font-black text-white leading-none tracking-tighter">
                                {{ number_format($count, 1) }} KG
                            </span>
                        @endif
                    </div>
                    
                    {{-- Tooltip tetap dipertahankan untuk detail presisi --}}
                    <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 hidden group-hover:block z-20">
                        <div class="bg-slate-950 text-white text-[8px] font-black p-2 rounded-lg shadow-2xl border border-slate-800 whitespace-nowrap uppercase">
                            JAM {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:00 — {{ number_format($count, 2) }} KG
                        </div>
                    </div>
                    <p class="text-[8px] font-bold text-slate-500 mt-2 text-center">{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</p>
                </div>
            @endfor
        </div>
    </div>

    {{-- 4. TREND PRODUCTION GRAPH --}}
    <div class="h-64 w-full bg-slate-950 rounded-[2rem] p-8 relative border border-slate-800 shadow-inner group">
        <h3 class="text-[10px] font-black uppercase italic text-slate-400 tracking-widest mb-4">Production Trend ({{ strtoupper($period) }})</h3>
        
        @php
            $maxValTrend = collect($trends)->max('total') ?: 1000; // Berikan minimal target kapasitas (misal 1000) agar skala lebih proporsional
            $points = "";
            $countTrends = count($trends);
            foreach($trends as $i => $d) {
                $x = ($i / (max($countTrends - 1, 1))) * 100;
                $y = 100 - (($d['total'] / $maxValTrend) * 70 + 15);
                $points .= "$x,$y ";
            }
        @endphp

        <div class="absolute inset-8 pointer-events-none">
            <svg class="w-full h-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 100 100">
                {{-- 1. Area Fill (Bayangan sangat tipis agar garis utama terlihat menonjol) --}}
                <path d="M 0,100 L {{ $points }} L 100,100 Z" fill="url(#grad)" opacity="0.03" />
                
                <defs>
                    <linearGradient id="grad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color:#ef4444;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#ef4444;stop-opacity:0" />
                    </linearGradient>
                </defs>

                {{-- 2. Garis Utama (Sangat Ramping: stroke-width="0.8") --}}
                <polyline 
                    fill="none" 
                    stroke="#ef4444" 
                    stroke-width="0.8" 
                    stroke-linecap="round" 
                    stroke-linejoin="round" 
                    points="{{ $points }}" 
                />
                
                {{-- 3. Titik Interaktif & Tooltip --}}
                @foreach($trends as $i => $d)
                    @php 
                        $cx = ($i / (max($countTrends - 1, 1))) * 100;
                        $cy = 100 - (($d['total'] / $maxValTrend) * 70 + 15);
                    @endphp
                    <g class="pointer-events-auto cursor-help group/point">
                        {{-- Sensor Kursor (Lingkaran besar tak terlihat) --}}
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="6" fill="transparent" />
                        
                        {{-- Titik Visual (Putih kecil tajam) --}}
                        <circle 
                            cx="{{ $cx }}" 
                            cy="{{ $cy }}" 
                            r="0.8" 
                            fill="white" 
                            stroke="#ef4444" 
                            stroke-width="0.3" 
                            class="transition-all duration-200 group-hover/point:r-2" 
                        />
                        
                        {{-- INFORMASI MUNCUL SAAT KURSOR DI ATAS TITIK --}}
                        <title>{{ $d['label'] ?? 'Data' }} ({{ $d['day'] ?? '-' }}): {{ number_format($d['total'] ?? 0, 1) }} KG</title>
                    </g>
                @endforeach
            </svg>
        </div>

        {{-- Labels Hari & Tanggal --}}
        <div class="flex justify-between absolute bottom-4 inset-x-8">
            @foreach($trends as $d)
                <div class="flex flex-col items-center">
                    {{-- Tambahkan ?? '' untuk mencegah error "Undefined array key" --}}
                    <span class="text-[7px] font-black text-slate-700 uppercase leading-none">
                        {{ $d['label'] ?? '' }}
                    </span>
                    <span class="text-[8px] font-black text-slate-500 uppercase mt-1">
                        {{ $d['day'] ?? '' }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>