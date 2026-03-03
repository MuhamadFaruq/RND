<?php

use Livewire\Volt\Component;
use App\Models\ProductionActivity;
use App\Models\Division;
use App\Models\Setting;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductionExport;

new class extends Component
{
    public $activeTab = 'rajut';
    public $filterDate;
    public $search = '';
    

    public function mount()
    {
        $this->filterDate = $this->filterDate ?? now()->format('Y-m-d');
    }

    public function updatedFilterDate()
    {
        // Hook ini memicu Livewire untuk menjalankan render ulang
    }

    public function getStatusColor($timeline)
    {
        return match (strtoupper($timeline)) {
            'URGENT' => 'text-red-500 bg-red-950/50 border-red-800',
            'ON PROGRESS' => 'text-blue-400 bg-blue-950/50 border-blue-800',
            'DONE' => 'text-emerald-400 bg-emerald-950/50 border-emerald-800',
            default => 'text-slate-400 bg-slate-900 border-slate-800',
        };
    }

    public function exportExcel()
    {
        // Catat ke Audit Trail
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'action' => 'EXPORT',
            'model' => 'ProductionReport',
            'description' => 'Mengunduh laporan produksi tanggal ' . $this->filterDate,
            'ip_address' => request()->ip(),
        ]);

        $fileName = 'Produksi_' . $this->filterDate . '.xlsx';
        return Excel::download(new ProductionExport($this->filterDate), $fileName);
    }

    public function with()
    {
        // Pastikan parsing tanggal aman
        $selectedDate = Carbon::parse($this->filterDate ?? now());
        $targetDateString = $selectedDate->format('Y-m-d');
        $isToday = $selectedDate->isToday();

        // 1. Ambil Parameter Global dari Setting
        $maxCapacity = Setting::where('key', 'max_capacity')->value('value') ?? 1000;

        // 2. Query Dasar untuk Monitoring
        $query = ProductionActivity::whereDate('created_at', $targetDateString)
            ->when($this->search, function($q) {
                $q->where(function($sub) {
                    $sub->whereHas('marketingOrder', function($mo) {
                        $mo->where('sap_no', 'like', '%'.$this->search.'%')
                           ->orWhere('art_no', 'like', '%'.$this->search.'%');
                    })->orWhere('pelanggan', 'like', '%'.$this->search.'%')
                      ->orWhere('warna', 'like', '%'.$this->search.'%');
                });
            });

        

        // 3. Return Semua Data ke Blade dalam Satu Array
        return [
            'activeTab' => $this->activeTab,
            'maxCapacity' => $maxCapacity,
            'isToday' => $isToday,
            'currentTime' => Carbon::now()->format('H:i:s'),
            'todayProduction' => (clone $query)->sum('kg'),
            
            // Filter Divisi Non-Produksi
            'warnaData' => (clone $query)->whereIn('division_name', ['DYEING', 'FINISHING', 'STENTER'])->get(),
            'divisionStats' => Division::whereNotIn('name', ['ADMIN', 'MARKETING', 'IT']) 
                ->withCount(['productionActivities' => function($q) use ($selectedDate) {
                    $q->whereDate('created_at', $selectedDate);
                }])->get(),

            // Data Spesifik untuk Tab Rajut & Warna
            'rajutData' => (clone $query)->where('division_name', 'KNITTING')->get(),
            'warnaData' => (clone $query)->whereIn('division_name', ['DYEING', 'FINISHING'])->get(),

            'latestActivities' => (clone $query)->with('marketingOrder')
                ->latest()->take(10)->get(),

            'summary' => [
                // PERBAIKAN: Pastikan 'KNITTING' ditulis dengan HURUF KAPITAL
                'rajut_kg' => (clone $query)->where('division_name', 'KNITTING')->sum('kg'),
                'rajut_roll' => (clone $query)->where('division_name', 'KNITTING')->sum('roll'),
                
                // Samakan juga untuk bagian Warna agar konsisten
                'warna_kg' => (clone $query)->whereIn('division_name', ['DYEING', 'FINISHING', 'STENTER'])->sum('kg'),
                'warna_roll' => (clone $query)->whereIn('division_name', ['DYEING', 'FINISHING', 'STENTER'])->sum('roll'),
            ],
            
            // Periksa juga bagian ini jika angka pada kotak unit (0 UNITS) masih kosong
            'divisionStats' => Division::whereNotIn('name', ['ADMIN', 'MARKETING', 'IT']) 
                ->withCount(['productionActivities' => function($q) use ($targetDateString) {
                    $q->whereDate('created_at', $targetDateString);
                }])->get(),
        ];
    }
};
?>

{{-- Container Utama: h-screen & bg-slate-900 mencegah bocor putih --}}
<div class="min-h-screen w-full bg-slate-900 text-white font-sans italic flex flex-col">
    <div @if($isToday) wire:poll.10s @endif class="p-4 md:p-8 flex-grow container mx-auto">
        
        {{-- HEADER RESPONSIF --}}
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 border-b border-slate-800 pb-6 gap-6">
            
            <div>
                <h1 class="text-3xl md:text-5xl font-black italic tracking-tighter uppercase text-red-600 leading-none">
                    Production <span class="text-white">Monitor</span>
                </h1>
                <p class="text-slate-400 font-bold tracking-widest uppercase text-[10px] md:text-xs mt-2 italic">Duniatex Group - Realtime Data</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
                <div class="bg-slate-800 p-2 rounded-2xl border border-slate-700 flex items-center gap-3 shadow-xl w-full sm:w-auto">
                    <input type="date" wire:model.live="filterDate" 
                        class="bg-transparent border-none text-red-500 font-black uppercase text-xs md:text-sm focus:ring-0 cursor-pointer flex-1">
                    
                    <button wire:click="exportExcel" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase transition-all flex items-center gap-2 whitespace-nowrap">
                        📊 Export
                    </button>
                </div>

                <div class="flex lg:flex-col items-center lg:items-end justify-between w-full lg:w-auto">
                    <div class="text-2xl md:text-4xl font-mono font-bold text-emerald-400 leading-none">{{ $currentTime }}</div>
                    <div class="text-[9px] md:text-[10px] font-black text-slate-500 uppercase italic tracking-widest">
                        {{ $isToday ? 'Live Feed' : 'Historical' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- QUICK SUMMARY HEADER --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            {{-- Summary Rajut --}}
            <div class="bg-slate-900/50 border border-red-600/20 p-5 rounded-3xl flex justify-between items-center group hover:bg-red-950/10 transition-all">
                <div>
                    <p class="text-[10px] font-black text-red-500 uppercase tracking-[0.2em] mb-1 italic">Total Accumulation: RAJUT</p>
                    <h4 class="text-3xl font-black italic tracking-tighter text-white">
                        {{ number_format($summary['rajut_kg'], 2) }} <span class="text-xs text-slate-500 uppercase">KG</span>
                    </h4>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-black italic text-slate-700 group-hover:text-red-600 transition-colors">
                        {{ number_format($summary['rajut_roll']) }}
                    </span>
                    <p class="text-[9px] font-bold text-slate-500 uppercase italic">Total Rolls</p>
                </div>
            </div>

            {{-- Summary Warna --}}
            <div class="bg-slate-900/50 border border-blue-600/20 p-5 rounded-3xl flex justify-between items-center group hover:bg-blue-950/10 transition-all">
                <div>
                    <p class="text-[10px] font-black text-blue-500 uppercase tracking-[0.2em] mb-1 italic">Total Accumulation: WARNA</p>
                    <h4 class="text-3xl font-black italic tracking-tighter text-white">
                        {{ number_format($summary['warna_kg'], 2) }} <span class="text-xs text-slate-500 uppercase">KG</span>
                    </h4>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-black italic text-slate-700 group-hover:text-blue-600 transition-colors">
                        {{ number_format($summary['warna_roll']) }}
                    </span>
                    <p class="text-[9px] font-bold text-slate-500 uppercase italic">Total Rolls</p>
                </div>
            </div>
        </div>
        {{-- WIDGETS STATISTIK --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            {{-- Total Output Card --}}
            <div class="bg-slate-900 p-8 rounded-[2.5rem] border border-white/5 shadow-2xl relative overflow-hidden group">
                <p class="text-[10px] font-black text-slate-500 uppercase mb-3 italic tracking-widest">Total Output (KG)</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-4xl font-black italic tracking-tighter text-white">{{ number_format($todayProduction, 2) }}</h3>
                    <span class="text-xs font-bold text-red-600 uppercase italic">Live</span>
                </div>
                <div class="absolute left-0 top-0 w-1.5 h-full bg-red-600 shadow-[0_0_20px_rgba(220,38,38,0.5)]"></div>
            </div>

            @foreach($divisionStats as $stat)
            <div class="bg-slate-900 p-8 rounded-[2.5rem] border border-white/5 shadow-2xl relative overflow-hidden">
                <p class="text-[10px] font-black text-slate-500 uppercase mb-3 italic tracking-widest">{{ $stat->name }}</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-4xl font-black italic tracking-tighter text-white">{{ $stat->production_activities_count }}</h3>
                    <span class="text-xs font-bold text-blue-500 uppercase italic">Units</span>
                </div>
                <div class="absolute left-0 top-0 w-1.5 h-full bg-blue-600 shadow-[0_0_20px_rgba(37,99,235,0.5)]"></div>
            </div>
            @endforeach
        </div>

        {{-- TABEL LIVE FEED --}}
        <div class="bg-slate-800 rounded-[2rem] md:rounded-[3rem] overflow-hidden shadow-2xl border border-slate-700 mt-8">
            <div class="p-6 md:p-8 border-b border-slate-700 flex justify-between items-center">
                <h3 class="text-lg md:text-xl font-black italic uppercase tracking-tighter">Live Activity</h3>
                @if($isToday) <span class="animate-pulse h-3 w-3 rounded-full bg-emerald-500"></span> @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left italic font-bold min-w-[800px]">
                    <thead class="bg-slate-900/50 uppercase text-[10px] text-slate-500 tracking-widest">
                        <tr>
                            <th class="p-6">Jam</th>
                            <th class="p-6">Divisi</th>
                            <th class="p-6">SAP NO</th>
                            <th class="p-6">Artikel</th>
                            <th class="p-6 text-center">Mesin</th>
                            <th class="p-6 text-right">Hasil (KG)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($latestActivities as $activity)
                        <tr class="hover:bg-slate-700/30 transition duration-300">
                            <td class="p-6 font-mono text-emerald-400">{{ $activity->created_at->format('H:i') }}</td>
                            <td class="p-6"><span class="px-3 py-1 bg-blue-900/50 text-blue-400 rounded-lg border border-blue-800 uppercase text-[10px]">{{ $activity->division_name }}</span></td>
                            <td class="p-6 font-black text-white italic">#{{ $activity->marketingOrder->sap_no ?? 'N/A' }}</td>
                            <td class="p-6 text-slate-300 uppercase text-xs truncate max-w-[150px]">{{ $activity->marketingOrder->art_no ?? 'N/A' }}</td>
                            <td class="p-6 text-center"><span class="bg-slate-900 px-3 py-1 rounded-lg text-xs">{{ $activity->technical_data['no_mesin'] ?? '-' }}</span></td>
                            <td class="p-6 text-right font-black text-2xl text-red-500 tracking-tighter">{{ number_format($activity->kg, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="p-20 text-center text-slate-500 font-black uppercase text-xs italic">Data Tidak Ditemukan</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    {{-- Di monitoring.blade.php --}}
    <livewire:admin.production-chart 
        :selectedDate="$filterDate" 
        :wire:key="'prod-chart-v2-' . $filterDate" 
    />
</div>


