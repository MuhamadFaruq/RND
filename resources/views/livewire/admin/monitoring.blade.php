<?php

use Livewire\Volt\Component;
use App\Models\ProductionActivity;
use App\Models\MarketingOrder; 
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
    public $selectedOperator = 'SEMUA';
    

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
            default => 'mkt-text-muted mkt-surface border mkt-border',
        };
    }

    public function exportExcel()
    {
        $date = $this->filterDate;
        $mode = $this->activeTab;
        $operator = $this->selectedOperator;

        $exists = \App\Models\ProductionActivity::whereDate('created_at', $date)
            ->when($mode === 'rajut', function($q) {
                $q->where('division_name', 'KNITTING');
            })
            ->when($mode === 'warna', function($q) {
                $q->whereIn('division_name', ['DYEING', 'FINISHING']);
            })
            ->when($operator !== 'SEMUA', function($q) use ($operator) {
                $q->where('operator_id', $operator); 
            })
            ->exists();

        if (!$exists) {
            $this->dispatch('notify', [
                'message' => 'Data produksi untuk tanggal ' . $date . ' dengan kriteria tersebut tidak ditemukan.', 
                'type' => 'error'
            ]);
            return;
        }

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'EXPORT',
            'model' => 'ProductionReport',
            'description' => "Export Excel tanggal $date untuk Operator ID: " . ($operator == 'SEMUA' ? 'Semua' : $operator),
            'ip_address' => request()->ip(),
        ]);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ProductionExport($date, $date, $mode, 'SEMUA', $operator), 
            'Produksi_' . $date . '.xlsx'
        );
    }

    public function with()
    {
        $operators = \App\Models\User::whereHas('productionActivities')
            ->where('name', 'NOT LIKE', '%admin%')
            ->where('name', 'NOT LIKE', '%super%')
            ->get();

        $selectedDate = \Carbon\Carbon::parse($this->filterDate ?? now());
        $targetDateString = $selectedDate->format('Y-m-d');
        $isToday = $selectedDate->isToday();

        // Query Marketing Order untuk monitoring Marketing
        $marketingOrderQuery = \App\Models\MarketingOrder::whereDate('created_at', $targetDateString);

        $maxCapacity = \App\Models\Setting::where('key', 'max_capacity')->value('value') ?? 1000;

        $query = \App\Models\ProductionActivity::whereDate('created_at', $targetDateString)
            ->when($this->search, function($q) {
                $q->where(function($sub) {
                    $sub->whereHas('marketingOrder', function($mo) {
                        $mo->where('sap_no', 'like', '%'.$this->search.'%');
                    })->orWhere('warna', 'like', '%'.$this->search.'%');
                });
            });

        return [
            'operators'       => $operators,
            'activeTab'       => $this->activeTab,
            'maxCapacity'     => $maxCapacity,
            'isToday'         => $isToday,
            'currentTime'     => \Carbon\Carbon::now()->format('H:i:s'),
            'todayProduction' => (clone $query)->sum('kg'),
            
            'summary' => [
                'rajut_kg'   => (clone $query)->where('division_name', 'KNITTING')->sum('kg'),
                'rajut_roll' => (clone $query)->where('division_name', 'KNITTING')->sum('roll'),
                'warna_kg'   => (clone $query)->whereIn('division_name', ['DYEING', 'FINISHING', 'STENTER'])->sum('kg'),
                'warna_roll' => (clone $query)->whereIn('division_name', ['DYEING', 'FINISHING', 'STENTER'])->sum('roll'),
                // Data Monitoring Marketing
                'marketing_mo' => $marketingOrderQuery->count(),
                'marketing_kg' => $marketingOrderQuery->sum('kg_target'),
            ],

            'latestActivities' => (clone $query)->with(['marketingOrder', 'user'])->latest()->take(10)->get(),

            'divisionStats' => \App\Models\Division::whereNotIn('name', ['ADMIN', 'MARKETING', 'IT']) 
                ->withCount(['productionActivities' => function($q) use ($targetDateString) {
                    $q->whereDate('created_at', $targetDateString);
                }])->get(),
        ];
    }
};
?>

<div class="min-h-screen w-full mkt-bg mkt-text font-sans italic flex flex-col transition-colors duration-300">
    <div @if($isToday) wire:poll.10s @endif class="p-4 md:p-8 flex-grow container mx-auto">
        
        {{-- HEADER --}}
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 border-b mkt-border pb-6 gap-6">
            <div>
                <h1 class="text-3xl md:text-5xl font-black italic tracking-tighter uppercase text-red-600 leading-none">
                    Production <span class="mkt-text">Monitor</span>
                </h1>
                <p class="mkt-text-muted font-bold tracking-widest uppercase text-[10px] md:text-xs mt-2 italic">Duniatex Group - Realtime Data</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
                <div class="mkt-surface p-2 rounded-2xl border mkt-border flex items-center gap-3 shadow-xl w-full sm:w-auto">
                    <input type="date" wire:model.live="filterDate" class="bg-transparent border-none text-red-500 font-black uppercase text-xs md:text-sm focus:ring-0 cursor-pointer flex-1">
                    <select wire:model.live="selectedOperator" class="bg-transparent border-none text-blue-400 font-black uppercase text-[10px] focus:ring-0 cursor-pointer">
                        <option value="SEMUA">SEMUA OPERATOR</option>
                        @foreach($operators as $op)
                            <option value="{{ $op->id }}">{{ strtoupper($op->name) }}</option>
                        @endforeach
                    </select>
                    <button wire:click.prevent="exportExcel" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase transition-all flex items-center gap-2 whitespace-nowrap">
                        Export
                    </button>
                </div>

                <div class="flex lg:flex-col items-center lg:items-end justify-between w-full lg:w-auto min-w-[120px]">
                    <div class="real-time-clock text-2xl md:text-4xl font-mono font-bold text-emerald-400 leading-none tabular-nums">00:00:00</div>
                    <div class="real-time-date text-[9px] md:text-[10px] font-black mkt-text-muted uppercase italic tracking-widest mt-1">Live Feed</div>
                </div>
            </div>
        </div>

        {{-- QUICK SUMMARY HEADER (3 COLUMNS) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            {{-- Rajut --}}
            <div class="mkt-surface border border-red-600/20 p-5 rounded-3xl flex justify-between items-center group hover:bg-red-950/10 transition-all">
                <div>
                    <p class="text-[10px] font-black text-red-500 uppercase tracking-[0.2em] mb-1 italic">Accumulation: RAJUT</p>
                    <h4 class="text-3xl font-black italic tracking-tighter mkt-text">
                        {{ number_format($summary['rajut_kg'], 2) }} <span class="text-xs mkt-text-muted uppercase">KG</span>
                    </h4>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-black italic text-slate-700 group-hover:text-red-600 transition-colors">
                        {{ number_format($summary['rajut_roll']) }}
                    </span>
                    <p class="text-[9px] font-bold text-slate-500 uppercase italic">Rolls</p>
                </div>
            </div>

            {{-- Warna --}}
            <div class="mkt-surface border border-blue-600/20 p-5 rounded-3xl flex justify-between items-center group hover:bg-blue-950/10 transition-all">
                <div>
                    <p class="text-[10px] font-black text-blue-500 uppercase tracking-[0.2em] mb-1 italic">Accumulation: WARNA</p>
                    <h4 class="text-3xl font-black italic tracking-tighter mkt-text">
                        {{ number_format($summary['warna_kg'], 2) }} <span class="text-xs mkt-text-muted uppercase">KG</span>
                    </h4>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-black italic text-slate-700 group-hover:text-blue-600 transition-colors">
                        {{ number_format($summary['warna_roll']) }}
                    </span>
                    <p class="text-[9px] font-bold text-slate-500 uppercase italic">Rolls</p>
                </div>
            </div>

            {{-- Marketing --}}
            <div class="mkt-surface border border-emerald-600/20 p-5 rounded-3xl flex justify-between items-center group hover:bg-emerald-950/10 transition-all shadow-xl">
                <div>
                    <p class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em] mb-1 italic">Accumulation: MARKETING</p>
                    <h4 class="text-3xl font-black italic tracking-tighter mkt-text">
                        {{ number_format($summary['marketing_kg'], 2) }} <span class="text-xs mkt-text-muted uppercase">KG</span>
                    </h4>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-black italic text-slate-700 group-hover:text-emerald-500 transition-colors">
                        {{ number_format($summary['marketing_mo']) }}
                    </span>
                    <p class="text-[9px] font-bold text-slate-500 uppercase italic">Orders</p>
                </div>
            </div>
        </div>

        {{-- WIDGETS STATISTIK --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-6 mb-10">
            {{-- Total Output Card --}}
            <div class="mkt-surface p-8 rounded-[2.5rem] border mkt-border shadow-2xl relative overflow-hidden group">
                <p class="text-[10px] font-black text-slate-500 uppercase mb-3 italic tracking-widest">Total Output (KG)</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-4xl font-black italic tracking-tighter mkt-text">{{ number_format($todayProduction, 2) }}</h3>
                    <span class="text-xs font-bold text-red-600 uppercase italic">Live</span>
                </div>
                <div class="absolute left-0 top-0 w-1.5 h-full bg-red-600 shadow-[0_0_20px_rgba(220,38,38,0.5)]"></div>
            </div>

            {{-- Marketing Unit Card --}}
            <div class="mkt-surface p-8 rounded-[2.5rem] border mkt-border shadow-2xl relative overflow-hidden group hover:border-emerald-500/50 transition-all">
                <p class="text-[10px] font-black text-slate-500 uppercase mb-3 italic tracking-widest">Marketing</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-4xl font-black italic tracking-tighter mkt-text">{{ $summary['marketing_mo'] }}</h3>
                    <span class="text-xs font-bold text-emerald-500 uppercase italic">Orders</span>
                </div>
                <div class="absolute left-0 top-0 w-1.5 h-full bg-emerald-600 shadow-[0_0_20px_rgba(16,185,129,0.5)]"></div>
            </div>

            @foreach($divisionStats as $stat)
            <div class="mkt-surface p-8 rounded-[2.5rem] border mkt-border shadow-2xl relative overflow-hidden">
                <p class="text-[10px] font-black text-slate-500 uppercase mb-3 italic tracking-widest">{{ $stat->name }}</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-4xl font-black italic tracking-tighter mkt-text">{{ $stat->production_activities_count }}</h3>
                    <span class="text-xs font-bold text-blue-500 uppercase italic">Units</span>
                </div>
                <div class="absolute left-0 top-0 w-1.5 h-full bg-blue-600 shadow-[0_0_20px_rgba(37,99,235,0.5)]"></div>
            </div>
            @endforeach
        </div>

        {{-- TABEL LIVE FEED --}}
        <div class="mkt-surface rounded-[2rem] md:rounded-[3rem] overflow-hidden shadow-2xl border mkt-border mt-8">
            <div class="p-6 md:p-8 border-b mkt-border flex justify-between items-center">
                <h3 class="text-lg md:text-xl font-black italic uppercase tracking-tighter mkt-text">Live Activity</h3>
                @if($isToday) <span class="animate-pulse h-3 w-3 rounded-full bg-emerald-500"></span> @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left italic font-bold min-w-[800px]">
                    <thead>
                        <tr class="mkt-surface-alt text-[9px] font-black uppercase mkt-text-muted tracking-[0.2em] border-b mkt-border">
                            <th class="px-8 py-6">Jam</th>
                            <th class="px-8 py-6">Divisi</th>
                            <th class="px-8 py-6">SAP NO</th>
                            <th class="px-8 py-6">Artikel</th>
                            <th class="px-8 py-6 text-center">Mesin</th>
                            <th class="px-8 py-6 text-right">Hasil (KG)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                        @forelse($latestActivities as $activity)
                        <tr class="hover:mkt-surface-alt/50 transition-colors border-b mkt-border last:border-0">
                            <td class="px-8 py-7 font-mono text-emerald-500 font-bold text-sm tracking-tight">{{ $activity->created_at->format('H:i') }}</td>
                            <td class="px-8 py-7">
                                <span class="px-4 py-1.5 bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 rounded-xl uppercase text-[9px] font-black tracking-widest inline-block shadow-sm">
                                    {{ $activity->division_name }}
                                </span>
                            </td>
                            <td class="px-8 py-7 font-black mkt-text text-sm tracking-tighter">#{{ $activity->marketingOrder->sap_no ?? 'N/A' }}</td>
                            <td class="px-8 py-7 mkt-text-muted uppercase text-[11px] font-bold tracking-tight truncate max-w-[150px] opacity-70">{{ $activity->marketingOrder->art_no ?? 'N/A' }}</td>
                            <td class="px-8 py-7 text-center">
                                <span class="mkt-surface-alt mkt-text px-3 py-1.5 rounded-xl border mkt-border text-[10px] font-black tracking-widest">
                                    {{ $activity->technical_data['no_mesin'] ?? '-' }}
                                </span>
                            </td>
                            <td class="px-8 py-7 text-right font-black text-2xl text-red-600 dark:text-red-500 tracking-tighter">{{ number_format($activity->kg, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="p-20 text-center mkt-text-muted font-black uppercase text-xs italic">Data Tidak Ditemukan</td></tr>
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


@script
<script>
    // Menggunakan API internal Livewire v3 untuk menangkap event 'notify'
    $wire.on('notify', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
            icon: payload.type || 'info',
            title: '<span class="text-xl font-black italic uppercase tracking-tighter">Sistem Produksi</span>',
            html: `<p class="text-sm font-bold opacity-80 italic uppercase">${payload.message}</p>`,
            
            // --- MODIFIKASI TAMPILAN AGAR FRIENDLY ---
            background: '#0f172a', 
            color: '#fff',
            
            padding: '2rem',
            customClass: {
                popup: 'rounded-[3rem] border border-slate-800 shadow-2xl backdrop-blur-md',
                confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-black italic uppercase px-8 py-3 rounded-2xl transition-all tracking-widest text-[10px]'
            },
            
            buttonsStyling: false,
            
            showClass: {
                popup: 'animate__animated animate__fadeInUp animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutDown animate__faster'
            }
        });

        } else {
            alert(payload.message);
        }
    });
</script>
@endscript
