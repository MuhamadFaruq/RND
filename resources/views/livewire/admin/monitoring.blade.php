<?php

use Livewire\Volt\Component;
use App\Models\ProductionActivity;
use App\Models\MarketingOrder; 
use App\Models\Division;
use App\Models\Setting;
use Carbon\Carbon;

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
            'ON PROGRESS' => 'mkt-text bg-brand-950/50 border-brand-800',
            'DONE' => 'text-emerald-400 bg-emerald-950/50 border-emerald-800',
            default => 'mkt-text-muted mkt-surface border mkt-border',
        };
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
            })
            ->when($this->selectedOperator !== 'SEMUA', function($q) {
                $q->where('operator_id', $this->selectedOperator);
            });

        // Ambil semua aktivitas untuk kalkulasi
        $activities = (clone $query)->get();

        // Alur Rajut (Knitting) - Unik per order
        $rajutActivities = $activities->filter(function ($act) {
            return strtoupper($act->division_name) === 'KNITTING';
        })->groupBy('marketing_order_id')->map(function ($group) {
            return $group->sortByDesc('created_at')->first();
        });
        $rajutKg = $rajutActivities->sum('kg');
        $rajutRoll = $rajutActivities->sum('roll');

        // Alur Warna (Dyeing & Finishing) - Unik per order
        $warnaDivs = ['DYEING', 'RELAX-DRYER', 'COMPACTOR', 'HEAT-SETTING', 'STENTER', 'TUMBLER', 'FLEECE', 'FINISHING'];
        $warnaActivities = $activities->filter(function ($act) use ($warnaDivs) {
            return in_array(strtoupper($act->division_name), $warnaDivs);
        })->groupBy('marketing_order_id')->map(function ($group) {
            return $group->sortByDesc('created_at')->first();
        });
        $warnaKg = $warnaActivities->sum('kg');
        $warnaRoll = $warnaActivities->sum('roll');

        // Total Output adalah gabungan unik dari Rajut dan Warna
        $todayProduction = $rajutKg + $warnaKg;

        // Hitung total KG dan Roll untuk order yang berstatus finished hari ini
        $finishedOrders = \App\Models\MarketingOrder::whereDate('created_at', $targetDateString)
            ->where('status', 'finished')
            ->get();

        $finishedKg = 0;
        $finishedRoll = 0;
        foreach ($finishedOrders as $fo) {
            $latestAct = \App\Models\ProductionActivity::where('marketing_order_id', $fo->id)
                ->whereNull('deleted_at')
                ->latest('id')
                ->first();
            if ($latestAct) {
                $finishedKg += $latestAct->kg;
                $finishedRoll += $latestAct->roll;
            } else {
                $finishedKg += $fo->kg_target;
                $finishedRoll += $fo->roll_target;
            }
        }

        return [
            'operators'       => $operators,
            'activeTab'       => $this->activeTab,
            'maxCapacity'     => $maxCapacity,
            'isToday'         => $isToday,
            'currentTime'     => \Carbon\Carbon::now()->format('H:i:s'),
            'todayProduction' => $todayProduction,
            
            'summary' => [
                'rajut_kg'   => $rajutKg,
                'rajut_roll' => $rajutRoll,
                'warna_kg'   => $warnaKg,
                'warna_roll' => $warnaRoll,
                'finished_kg' => $finishedKg,
                'finished_roll' => $finishedRoll,
                // Data Monitoring Marketing
                'marketing_mo' => $marketingOrderQuery->count(),
                'marketing_kg' => $marketingOrderQuery->sum('kg_target'),
            ],

            'latestActivities' => (clone $query)->with(['marketingOrder', 'user'])->latest()->take(10)->get(),

            'divisionStats' => collect([
                [
                    'name'  => 'KNITTING',
                    'count' => \App\Models\MarketingOrder::whereDate('created_at', $targetDateString)
                        ->where('status', 'knitting')
                        ->when($this->search, function($q) {
                            $q->where(function($sub) {
                                $sub->where('sap_no', 'like', '%'.$this->search.'%')
                                   ->orWhere('art_no', 'like', '%'.$this->search.'%')
                                   ->orWhere('warna', 'like', '%'.$this->search.'%');
                            });
                        })
                        ->count(),
                    'color' => 'bg-blue-600 shadow-[0_0_20px_rgba(37,99,235,0.5)]',
                    'label' => 'Orders',
                ],
                [
                    'name'  => 'DYEING',
                    'count' => \App\Models\MarketingOrder::whereDate('created_at', $targetDateString)
                        ->whereIn('status', ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'stenter', 'tumbler', 'fleece', 'finishing'])
                        ->when($this->search, function($q) {
                            $q->where(function($sub) {
                                $sub->where('sap_no', 'like', '%'.$this->search.'%')
                                   ->orWhere('art_no', 'like', '%'.$this->search.'%')
                                   ->orWhere('warna', 'like', '%'.$this->search.'%');
                            });
                        })
                        ->count(),
                    'color' => 'bg-indigo-600 shadow-[0_0_20px_rgba(79,70,229,0.5)]',
                    'label' => 'Orders',
                ],
                [
                    'name'  => 'PENGUJIAN',
                    'count' => \App\Models\MarketingOrder::whereDate('created_at', $targetDateString)
                        ->where('status', 'pengujian')
                        ->when($this->search, function($q) {
                            $q->where(function($sub) {
                                $sub->where('sap_no', 'like', '%'.$this->search.'%')
                                   ->orWhere('art_no', 'like', '%'.$this->search.'%')
                                   ->orWhere('warna', 'like', '%'.$this->search.'%');
                            });
                        })
                        ->count(),
                    'color' => 'bg-amber-600 shadow-[0_0_20px_rgba(217,119,6,0.5)]',
                    'label' => 'Orders',
                ],
                [
                    'name'  => 'QE',
                    'count' => \App\Models\MarketingOrder::whereDate('created_at', $targetDateString)
                        ->where('status', 'qe')
                        ->when($this->search, function($q) {
                            $q->where(function($sub) {
                                $sub->where('sap_no', 'like', '%'.$this->search.'%')
                                   ->orWhere('art_no', 'like', '%'.$this->search.'%')
                                   ->orWhere('warna', 'like', '%'.$this->search.'%');
                            });
                        })
                        ->count(),
                    'color' => 'bg-cyan-600 shadow-[0_0_20px_rgba(8,145,178,0.5)]',
                    'label' => 'Orders',
                ],
            ])->map(fn($item) => (object)$item),
        ];
    }
};
?>

<div class="min-h-screen w-full mkt-bg mkt-text font-sans italic flex flex-col transition-colors duration-300">
    <div @if($isToday) wire:poll.10s @endif class="p-4 md:p-8 flex-grow w-full max-w-full px-4 md:px-10">
        
        {{-- HEADER --}}
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 border-b mkt-border pb-6 gap-6">
            <div>
                <h1 class="text-3xl md:text-5xl font-black italic tracking-tighter uppercase text-brand-600 leading-none">
                    Production <span class="mkt-text">Monitor</span>
                </h1>
                <p class="mkt-text-muted font-bold tracking-widest uppercase text-[10px] md:text-xs mt-2 italic">Duniatex Group - Realtime Data</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
                <div class="mkt-surface p-2 rounded-2xl border mkt-border flex items-center gap-3 shadow-xl w-full sm:w-auto">
                    <input type="date" wire:model.live="filterDate" class="bg-transparent border-none text-brand-600 font-black uppercase text-xs md:text-sm focus:ring-0 cursor-pointer flex-1">
                    <select wire:model.live="selectedOperator" class="bg-transparent border-none mkt-text font-black uppercase text-[10px] focus:ring-0 cursor-pointer">
                        <option value="SEMUA">SEMUA OPERATOR</option>
                        @foreach($operators as $op)
                            <option value="{{ $op->id }}">{{ strtoupper($op->name) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex lg:flex-col items-center lg:items-end justify-between w-full lg:w-auto min-w-[120px]" wire:ignore id="admin-monitoring-live-clock">
                    <div class="real-time-clock text-2xl md:text-4xl font-mono font-bold text-emerald-400 leading-none tabular-nums">{{ now()->format('H:i:s') }}</div>
                    <div class="real-time-date text-[9px] md:text-[10px] font-black mkt-text-muted uppercase italic tracking-widest mt-1">{{ now()->locale('id')->translatedFormat('d M Y') }}</div>
                </div>
            </div>
        </div>

        {{-- QUICK SUMMARY HEADER (4 COLUMNS - RESPONSIVE) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6 mb-8">
            {{-- Marketing --}}
            <div class="mkt-surface border border-brand/20 p-3 md:p-5 rounded-2xl md:rounded-3xl flex flex-col lg:flex-row justify-between items-start lg:items-center gap-1 group hover:bg-brand-950/10 transition-all shadow-md">
                <div>
                    <p class="text-[7px] sm:text-[9px] md:text-[10px] font-black text-brand-500 uppercase tracking-wider md:tracking-[0.2em] mb-0.5 md:mb-1 italic">MARKETING</p>
                    <h4 class="text-sm sm:text-xl md:text-3xl font-black italic tracking-tighter mkt-text leading-none">
                        {{ (float)$summary['marketing_kg'] }}<span class="text-[7px] sm:text-[9px] md:text-xs mkt-text-muted uppercase ml-0.5">KG</span>
                    </h4>
                </div>
                <div class="lg:text-right mt-1 lg:mt-0 flex items-baseline lg:block gap-1">
                    <span class="text-xs sm:text-lg md:text-2xl font-black italic text-slate-700 dark:text-slate-200 group-hover:text-brand transition-colors leading-none">
                        {{ number_format($summary['marketing_mo']) }}
                    </span>
                    <span class="text-[7px] sm:text-[9px] font-bold text-slate-500 uppercase italic leading-none">Orders</span>
                </div>
            </div>

            {{-- Rajut --}}
            <div class="mkt-surface border border-blue-600/20 p-3 md:p-5 rounded-2xl md:rounded-3xl flex flex-col lg:flex-row justify-between items-start lg:items-center gap-1 group hover:bg-blue-950/10 transition-all shadow-sm">
                <div>
                    <p class="text-[7px] sm:text-[9px] md:text-[10px] font-black text-blue-500 uppercase tracking-wider md:tracking-[0.2em] mb-0.5 md:mb-1 italic">RAJUT</p>
                    <h4 class="text-sm sm:text-xl md:text-3xl font-black italic tracking-tighter mkt-text leading-none">
                        {{ (float)$summary['rajut_kg'] }}<span class="text-[7px] sm:text-[9px] md:text-xs mkt-text-muted uppercase ml-0.5">KG</span>
                    </h4>
                </div>
                <div class="lg:text-right mt-1 lg:mt-0 flex items-baseline lg:block gap-1">
                    <span class="text-xs sm:text-lg md:text-2xl font-black italic text-slate-700 dark:text-slate-200 group-hover:text-blue-600 transition-colors leading-none">
                        {{ number_format($summary['rajut_roll']) }}
                    </span>
                    <span class="text-[7px] sm:text-[9px] font-bold text-slate-500 uppercase italic leading-none">Rolls</span>
                </div>
            </div>

            {{-- Warna --}}
            <div class="mkt-surface border border-indigo-600/20 p-3 md:p-5 rounded-2xl md:rounded-3xl flex flex-col lg:flex-row justify-between items-start lg:items-center gap-1 group hover:bg-indigo-950/10 transition-all shadow-sm">
                <div>
                    <p class="text-[7px] sm:text-[9px] md:text-[10px] font-black text-indigo-500 uppercase tracking-wider md:tracking-[0.2em] mb-0.5 md:mb-1 italic">WARNA</p>
                    <h4 class="text-sm sm:text-xl md:text-3xl font-black italic tracking-tighter mkt-text leading-none">
                        {{ (float)$summary['warna_kg'] }}<span class="text-[7px] sm:text-[9px] md:text-xs mkt-text-muted uppercase ml-0.5">KG</span>
                    </h4>
                </div>
                <div class="lg:text-right mt-1 lg:mt-0 flex items-baseline lg:block gap-1">
                    <span class="text-xs sm:text-lg md:text-2xl font-black italic text-slate-700 dark:text-slate-200 group-hover:text-indigo-500 transition-colors leading-none">
                        {{ number_format($summary['warna_roll']) }}
                    </span>
                    <span class="text-[7px] sm:text-[9px] font-bold text-slate-500 uppercase italic leading-none">Rolls</span>
                </div>
            </div>

            {{-- Finished --}}
            <div class="mkt-surface border border-emerald-600/20 p-3 md:p-5 rounded-2xl md:rounded-3xl flex flex-col lg:flex-row justify-between items-start lg:items-center gap-1 group hover:bg-emerald-950/10 transition-all shadow-sm">
                <div>
                    <p class="text-[7px] sm:text-[9px] md:text-[10px] font-black text-emerald-500 uppercase tracking-wider md:tracking-[0.2em] mb-0.5 md:mb-1 italic">FINISHED</p>
                    <h4 class="text-sm sm:text-xl md:text-3xl font-black italic tracking-tighter mkt-text leading-none">
                        {{ (float)$summary['finished_kg'] }}<span class="text-[7px] sm:text-[9px] md:text-xs mkt-text-muted uppercase ml-0.5">KG</span>
                    </h4>
                </div>
                <div class="lg:text-right mt-1 lg:mt-0 flex items-baseline lg:block gap-1">
                    <span class="text-xs sm:text-lg md:text-2xl font-black italic text-slate-700 dark:text-slate-200 group-hover:text-emerald-500 transition-colors leading-none">
                        {{ number_format($summary['finished_roll']) }}
                    </span>
                    <span class="text-[7px] sm:text-[9px] font-bold text-slate-500 uppercase italic leading-none">Rolls</span>
                </div>
            </div>
        </div>

        {{-- WIDGETS STATISTIK (2-COLUMN GRID ON MOBILE FOR COMPACTNESS) --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-6 mb-10">
            {{-- Total Output Card --}}
            <div class="mkt-surface p-4 md:p-6 lg:p-8 rounded-2xl md:rounded-[2.5rem] border mkt-border shadow-lg relative overflow-hidden group">
                <p class="text-[8px] md:text-[10px] font-black text-slate-500 uppercase mb-1 md:mb-3 italic tracking-wider md:tracking-widest">Total Output (KG)</p>
                <div class="flex items-baseline gap-1 md:gap-2">
                    <h3 class="text-lg sm:text-2xl md:text-4xl font-black italic tracking-tighter mkt-text">{{ (float)$todayProduction }}</h3>
                    <span class="text-[8px] md:text-xs font-bold text-emerald-600 uppercase italic">Live</span>
                </div>
                <div class="absolute left-0 top-0 w-1 h-full bg-emerald-600 shadow-[0_0_20px_rgba(16,185,129,0.5)]"></div>
            </div>

            {{-- Marketing Unit Card --}}
            <div class="mkt-surface p-4 md:p-6 lg:p-8 rounded-2xl md:rounded-[2.5rem] border mkt-border shadow-lg relative overflow-hidden group hover:border-brand-500/50 transition-all">
                <p class="text-[8px] md:text-[10px] font-black text-slate-500 uppercase mb-1 md:mb-3 italic tracking-wider md:tracking-widest">Marketing</p>
                <div class="flex items-baseline gap-1 md:gap-2">
                    <h3 class="text-lg sm:text-2xl md:text-4xl font-black italic tracking-tighter mkt-text">{{ $summary['marketing_mo'] }}</h3>
                    <span class="text-[8px] md:text-xs font-bold text-brand uppercase italic">Orders</span>
                </div>
                <div class="absolute left-0 top-0 w-1 h-full bg-brand shadow-[0_0_20px_rgba(237,28,36,0.5)]"></div>
            </div>

            @foreach($divisionStats as $stat)
            <div class="mkt-surface p-4 md:p-6 lg:p-8 rounded-2xl md:rounded-[2.5rem] border mkt-border shadow-lg relative overflow-hidden">
                <p class="text-[8px] md:text-[10px] font-black text-slate-500 uppercase mb-1 md:mb-3 italic tracking-wider md:tracking-widest">{{ $stat->name }}</p>
                <div class="flex items-baseline gap-1 md:gap-2">
                    <h3 class="text-lg sm:text-2xl md:text-4xl font-black italic tracking-tighter mkt-text">{{ $stat->count }}</h3>
                    <span class="text-[8px] md:text-xs font-bold text-brand uppercase italic">{{ $stat->label }}</span>
                </div>
                <div class="absolute left-0 top-0 w-1 h-full {{ $stat->color }}"></div>
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
                    <tbody class="divide-y divide-border">
                        @forelse($latestActivities as $activity)
                        <tr class="hover:mkt-surface-alt/50 transition-colors border-b mkt-border last:border-0">
                            <td class="px-8 py-7 font-mono text-emerald-500 font-bold text-sm tracking-tight">{{ $activity->created_at->format('H:i') }}</td>
                            <td class="px-8 py-7">
                                @php
                                    $badgeColor = match(strtoupper($activity->division_name)) {
                                        'KNITTING' => 'bg-blue-600/10 text-blue-500 border-blue-600/20',
                                        'DYEING', 'WARNA', 'FINISHING', 'STENTER', 'RELAX DRYER', 'COMPACTOR' => 'bg-indigo-600/10 text-indigo-500 border-indigo-600/20',
                                        'MARKETING' => 'bg-brand/10 text-brand border-brand/20',
                                        'PENGUJIAN', 'QE' => 'bg-emerald-600/10 text-emerald-500 border-emerald-600/20',
                                        default => 'bg-brand/10 mkt-text border border-brand/20',
                                    };
                                @endphp
                                <span class="px-4 py-1.5 {{ $badgeColor }} rounded-xl uppercase text-[9px] font-black tracking-widest inline-block shadow-sm">
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
                            @php
                                $orderTargetKg = $activity->marketingOrder->kg_target ?? 0;
                                $orderTargetRoll = $activity->marketingOrder->roll_target ?? 0;
                                $kgDev = $orderTargetKg > 0 && is_numeric($activity->kg) && $activity->kg > 0 && (abs($activity->kg - $orderTargetKg) / $orderTargetKg) > 0.1;
                                $rollDev = $orderTargetRoll > 0 && is_numeric($activity->roll) && $activity->roll > 0 && (abs($activity->roll - $orderTargetRoll) / $orderTargetRoll) > 0.1;
                                $deviation = $kgDev || $rollDev;
                            @endphp
                            <td class="px-8 py-7 text-right font-black text-2xl mkt-text tracking-tighter">{{ (float)$activity->kg }} @if($deviation)<span class="ml-1 inline-block px-1.5 py-0.5 bg-red-100 text-red-600 rounded-full text-[8px] font-black uppercase">Deviasi</span>@endif</td>
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
