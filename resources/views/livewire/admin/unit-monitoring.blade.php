<?php

use Livewire\Volt\Component;
use App\Models\ProductionActivity;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $viewMode = 'RAJUT';
    public $selectedUnit = 'SEMUA';
    public $startDate;
    public $endDate;

    public $showExportModal = false;
    public $selectedIdentifier = null;
    public $showTrackingModal = false;
    public $trackingLogs = [];
    public $trackingData = null;
    public $expandedLog = null;

    public function mount()
    {
        $this->startDate = date('Y-m-d');
        $this->endDate = date('Y-m-d');
    }

    public function updated($property)
    {
        if (in_array($property, ['search', 'viewMode', 'selectedUnit', 'startDate', 'endDate'])) {
            $this->resetPage();
        }
    }

    public function with()
    {
        return [
            'orders' => \App\Models\MarketingOrder::with(['productionActivities', 'processingBy'])
                ->when($this->viewMode === 'RAJUT', function ($q) {
                    $q->where('status', 'knitting');
                })
                ->when($this->viewMode === 'WARNA', function ($q) {
                    $q->whereIn('status', ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'finishing', 'stenter', 'tumbler', 'fleece']);
                })
                ->when($this->viewMode === 'SELESAI', function ($q) {
                    $q->where('status', 'finished');
                })
                ->when($this->selectedUnit !== 'SEMUA', function ($q) {
                    $q->where('kelompok_kain', $this->selectedUnit);
                })
                ->when($this->search, function ($q) {
                    $q->where(function ($sq) {
                        $sq->where('art_no', 'like', "%{$this->search}%")
                            ->orWhere('sap_no', 'like', "%{$this->search}%");
                    });
                })
                ->when($this->startDate, function ($q) {
                    $q->where(function ($sq) {
                        $sq->whereHas('productionActivities', fn($ssq) => $ssq->whereDate('created_at', '>=', $this->startDate))
                            ->orWhere(function ($ssq) {
                                if ($this->viewMode === 'RAJUT') {
                                    $ssq->where('status', 'knitting');
                                } else {
                                    $ssq->whereIn('status', ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'finishing', 'stenter', 'tumbler', 'fleece']);
                                }
                            });
                    });
                })
                ->when($this->endDate, function ($q) {
                    $q->where(function ($sq) {
                        $sq->whereHas('productionActivities', fn($ssq) => $ssq->whereDate('created_at', '<=', $this->endDate))
                            ->orWhere(function ($ssq) {
                                if ($this->viewMode === 'RAJUT') {
                                    $ssq->where('status', 'knitting');
                                } else {
                                    $ssq->whereIn('status', ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'finishing', 'stenter', 'tumbler', 'fleece']);
                                }
                            });
                    });
                })
                ->latest()
                ->paginate(10),

            // Statistik Stuck Orders (> 2 Hari) dari modul Marketing
            'stuckOrders' => \App\Models\MarketingOrder::where('status', 'knitting')
                ->where('created_at', '<=', now()->subDays(2))
                ->count(),
        ];
    }


    public function export($format, $modeOverride = null)
    {
        $this->showExportModal = false;

        // Redirect ke route export sesuai format
        return redirect()->route('admin.export', [
            'format' => $format,
            'mode' => $modeOverride ?: strtolower($this->viewMode),
            'start' => $this->startDate,
            'end' => $this->endDate,
            'unit' => $this->selectedUnit
        ]);
    }

    public function openTracking($identifier)
    {
        $this->selectedIdentifier = $identifier;
        // Ambil detail spesifikasi marketing menggunakan repository agar mendukung Art No maupun SAP No
        $repo = app(\App\Repositories\OrderRepository::class);
        $this->trackingData = $repo->findByIdentifier($identifier);

        if ($this->trackingData) {
            // Ambil jejak produksi lengkap dengan data operator
            $this->trackingLogs = ProductionActivity::with('operator')
                ->where('marketing_order_id', $this->trackingData->id)
                ->orderBy('created_at', 'asc')
                ->get();

            $this->showTrackingModal = true;
        }
    }

    public function toggleDetail($divName)
    {
        // Jika mengklik divisi yang sama, maka tutup. Jika berbeda, buka detailnya.
        $this->expandedLog = ($this->expandedLog === $divName) ? null : $divName;
    }
};
?>

<div class="min-h-screen mkt-bg mkt-text font-sans italic p-8 transition-colors duration-300" wire:poll.10s>
    <div class="container mx-auto">
        {{-- HEADER & ANALYTICS CARDS --}}
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-end mb-10 border-b mkt-border pb-10 gap-8">
            <div>
                <h1 class="text-4xl md:text-6xl font-black text-brand-600 uppercase tracking-tighter leading-none">
                    UNIT <span class="mkt-text">MONITORING</span>
                </h1>
                <p class="mkt-text-muted font-bold uppercase text-[10px] md:text-xs mt-3 tracking-[0.2em] italic opacity-70">
                    MODE: MONITORING {{ $viewMode }} | LIVE DUNIATEX RND
                </p>
                {{-- wire:ignore: jangan di-morph oleh wire:poll (cegah jam reset ke 00:00:00) --}}
                <div class="mt-3 flex items-center gap-3" wire:ignore id="unit-monitoring-live-clock">
                    <div class="mkt-surface-alt mkt-text px-4 py-1.5 rounded-xl shadow-lg border mkt-border">
                        <p class="real-time-clock text-sm font-black tracking-widest leading-none tabular-nums font-mono">{{ now()->format('H:i:s') }}</p>
                    </div>
                    <p class="real-time-date text-[11px] font-bold mkt-text-muted uppercase tracking-widest italic">{{ now()->locale('id')->translatedFormat('d M Y') }}</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row flex-wrap gap-4 items-stretch sm:items-center w-full xl:w-auto">
                {{-- STUCK ORDERS CARD --}}
                <div class="bg-red-500/10 border border-red-500/20 p-4 rounded-3xl flex items-center gap-4 px-6 shadow-sm transition-all hover:bg-red-500/20">
                    <div class="text-red-500 animate-pulse text-2xl font-black"></div>
                    <div>
                        <p class="text-[9px] font-black text-red-500 uppercase tracking-widest leading-tight">Stuck Orders<br>(>2 Days)</p>
                        <h4 class="text-2xl font-black mkt-text leading-none mt-1">{{ $stuckOrders }}</h4>
                    </div>
                </div>

                {{-- SEARCH INPUT --}}
                <div class="relative w-full sm:w-64 group">
                    <input type="text" wire:model.live="search" placeholder="CARI ARTIKEL / SAP..." 
                        class="w-full mkt-input border-2 mkt-border rounded-3xl pl-12 pr-6 py-4 text-[10px] font-black focus:border-brand-600 focus:ring-0 transition-all placeholder:text-slate-400 italic uppercase">
                    <span class="absolute left-5 top-1/2 -translate-y-1/2 mkt-text-muted opacity-50 group-focus-within:opacity-100 group-focus-within:text-brand-600 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                </div>

                <div class="relative flex-grow sm:flex-grow-0">
                    <button wire:click="$toggle('showExportModal')" 
                        class="w-full flex items-center justify-center gap-3 bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-4 rounded-3xl font-black uppercase italic shadow-xl shadow-emerald-900/20 transition-all transform hover:scale-[1.02] text-[10px] tracking-widest">
                        GENERATE REPORT
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    </button>
                </div>

                {{-- FILTER TANGGAL --}}
                <div class="flex items-center gap-2 mkt-surface p-1.5 rounded-3xl border mkt-border shadow-lg">
                    <input type="date" wire:model.live="startDate" class="bg-transparent border-none mkt-text font-black text-[10px] focus:ring-0 uppercase py-2">
                    <div class="w-px h-4 bg-slate-500/30"></div>
                    <input type="date" wire:model.live="endDate" class="bg-transparent border-none mkt-text font-black text-[10px] focus:ring-0 uppercase py-2">
                </div>
            </div>
        </div>

        {{-- TAB SWITCHER --}}
        <div class="flex flex-wrap gap-4 mb-10">
            <button wire:click="$set('viewMode', 'RAJUT')" 
                class="flex-grow sm:flex-grow-0 px-12 py-5 rounded-3xl font-black uppercase italic transition-all tracking-widest text-xs {{ $viewMode === 'RAJUT' ? 'bg-blue-600 shadow-xl shadow-blue-900/30 text-white' : 'mkt-surface-alt mkt-text-muted border mkt-border hover:mkt-text' }}">
                MONITORING RAJUT
            </button>
            <button wire:click="$set('viewMode', 'WARNA')" 
                class="flex-grow sm:flex-grow-0 px-12 py-5 rounded-3xl font-black uppercase italic transition-all tracking-widest text-xs {{ $viewMode === 'WARNA' ? 'bg-indigo-600 shadow-xl shadow-indigo-900/30 text-white' : 'mkt-surface-alt mkt-text-muted border mkt-border hover:mkt-text' }}">
                MONITORING WARNA
            </button>
            <button wire:click="$set('viewMode', 'SELESAI')" 
                class="flex-grow sm:flex-grow-0 px-12 py-5 rounded-3xl font-black uppercase italic transition-all tracking-widest text-xs {{ $viewMode === 'SELESAI' ? 'bg-emerald-600 shadow-xl shadow-emerald-900/30 text-white' : 'mkt-surface-alt mkt-text-muted border mkt-border hover:mkt-text' }}">
                ORDER SELESAI
            </button>
        </div>


        {{-- MOBILE WIP LIST (Visible only on mobile/tablet < md) --}}
        <div class="block md:hidden space-y-4 mb-6">
            @forelse($orders as $wip)
                @php
                    $latestActivity = $wip->productionActivities->sortByDesc('created_at')->first();
                    $actualRoll = $latestActivity ? $latestActivity->roll : 0;
                    $actualKg = $latestActivity ? $latestActivity->kg : 0;
                @endphp
                <div class="mkt-surface p-4 rounded-2xl border mkt-border shadow-md relative overflow-hidden flex flex-col gap-3">
                    <div class="absolute left-0 top-0 w-1.5 h-full bg-brand-600"></div>

                    <div class="flex justify-between items-start pl-2">
                        <div class="flex flex-col">
                            <span class="mkt-text block text-xs font-black tracking-tighter leading-none">{{ $wip->art_no }}</span>
                            <span class="mkt-text mt-1 block text-[9px] font-bold uppercase opacity-65">LEGACY #{{ $wip->sap_no }}</span>
                        </div>
                        <button wire:click="openTracking('{{ $wip->art_no }}')" class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-1.5 rounded-lg text-[8px] font-black uppercase transition-all shadow-sm">
                            DETAIL
                        </button>
                    </div>

                    <div class="border-t border-dashed mkt-border pl-2 pt-2 flex flex-col gap-2">
                        <div class="flex justify-between items-baseline">
                            <span class="text-[8px] font-black text-slate-500 uppercase italic">PELANGGAN</span>
                            <span class="mkt-text text-xs tracking-tight leading-none">{{ $wip->pelanggan }} <span class="text-[8px] mkt-text-muted font-black opacity-55 ml-1">MKT: {{ $wip->mkt ?? 'DD' }}</span></span>
                        </div>

                        <div class="flex justify-between items-baseline">
                            <span class="text-[8px] font-black text-slate-500 uppercase italic">
                                @if($viewMode === 'RAJUT') KONSTRUKSI @else WARNA @endif
                            </span>
                            @if($viewMode === 'RAJUT')
                                <div class="text-right leading-none">
                                    <span class="mkt-text-muted text-[10px] font-bold">{{ $wip->konstruksi_greige }}</span>
                                    <span class="mkt-text text-[9px]] font-black uppercase ml-1">{{ $wip->rnd_gramasi_greige ?? $wip->target_gramasi }} GSM</span>
                                </div>
                            @else
                                <div class="text-right leading-none">
                                    <span class="text-emerald-500 text-xs font-black uppercase italic">{{ $wip->warna }}</span>
                                    <span class="mkt-text-muted text-[9px] font-bold block mt-0.5">{{ $wip->konstruksi_greige }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-[8px] font-black text-slate-500 uppercase italic">
                                @if($viewMode === 'RAJUT') MESIN @else SPECS (L/G/H) @endif
                            </span>
                            @if($viewMode === 'RAJUT')
                                <span class="mkt-text font-black text-xs leading-none">{{ $wip->rnd_mesin_rajut ?? 'TBD' }}</span>
                            @else
                                <div class="text-right leading-none">
                                    <span class="text-emerald-400 font-black text-[10px]">{{ $wip->target_lebar }}" / {{ $wip->target_gramasi }}</span>
                                    <span class="text-[8px] mkt-text-muted font-bold block mt-0.5">{{ $wip->handfeel ?? '-' }} | {{ $wip->belah_bulat }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="flex justify-between items-center bg-slate-950/10 dark:bg-slate-950/20 p-2 rounded-xl border mkt-border mt-1">
                            <div class="flex flex-col">
                                <span class="text-[8px] font-black text-slate-500 uppercase italic">HASIL (ROLL / KG)</span>
                                <span class="mkt-text font-black text-xs mt-0.5">{{ $actualRoll ?: '0' }} ROLL / {{ $actualKg ? (float)$actualKg . ' KG' : '0.0 KG' }}</span>
                            </div>
                            <div class="text-right">
                                <div class="flex gap-1 items-center justify-end flex-wrap max-w-[120px]">
                                    @php
                                        $allSteps = [
                                            'knitting' => ['label' => 'KNT', 'flag' => null],
                                            'dyeing' => ['label' => 'DYE', 'flag' => null],
                                            'relax-dryer' => ['label' => 'RLX', 'flag' => null],
                                            'compactor' => ['label' => 'CMP', 'flag' => 'req_compactor'],
                                            'heat-setting' => ['label' => 'HT', 'flag' => 'req_heat_setting'],
                                            'stenter' => ['label' => 'STN', 'flag' => 'req_stenter'],
                                            'tumbler' => ['label' => 'TMB', 'flag' => 'req_tumbler'],
                                            'fleece' => ['label' => 'FLC', 'flag' => 'req_fleece']
                                        ];

                                        if ($viewMode === 'RAJUT') {
                                            $allSteps = ['knitting' => ['label' => 'KNT', 'flag' => null]];
                                        }

                                        $completedDivisions = $wip->productionActivities->pluck('division_name')->toArray();
                                    @endphp
                                    @foreach($allSteps as $key => $step)
                                        @php
                                            $showStep = $step['flag'] === null || (bool) $wip->{$step['flag']};
                                        @endphp
                                        @if($showStep)
                                            @php
                                                $isCompleted = in_array($key, $completedDivisions);
                                                $isCurrent = $wip->status === $key;
                                            @endphp
                                            <span class="px-1 py-0.5 rounded text-[7px] font-black {{ $isCompleted ? 'bg-emerald-500/20 text-emerald-500' : ($isCurrent ? 'bg-yellow-500/20 text-yellow-500 animate-pulse' : 'bg-white/5 text-slate-500') }}">
                                                {{ $step['label'] }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="mt-1 text-[7px] font-black">
                                    @if($wip->processing_by)
                                        <span class="text-amber-500 uppercase">OP: {{ $wip->processingBy->name }}</span>
                                    @elseif($wip->status === 'finished')
                                        <span class="text-emerald-500 uppercase">COMPLETED</span>
                                    @else
                                        <span class="mkt-text uppercase">PENDING IN {{ strtoupper($wip->status) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="mkt-surface p-12 rounded-2xl border mkt-border text-center mkt-text-muted font-black uppercase text-xs tracking-widest italic opacity-40">
                    TIDAK ADA DATA {{ $viewMode }}
                </div>
            @endforelse

            {{-- PAGINATION MOBILE --}}
            @if($orders->hasPages())
                <div class="p-4 mkt-surface rounded-2xl border mkt-border shadow-sm">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

        {{-- DESKTOP TABLE DATA (Visible only on md and larger) --}}
        <div class="hidden md:block mkt-surface rounded-[2.5rem] border mkt-border shadow-2xl overflow-hidden transition-all">
            <div class="overflow-x-auto">
                <table class="w-full text-left font-bold min-w-[1000px]">
                    <thead>
                        <tr class="mkt-surface-alt text-[9px] font-black uppercase mkt-text-muted tracking-[0.2em] border-b mkt-border">
                            <th class="px-8 py-6">NO ARTIKEL</th>
                            <th class="px-8 py-6">PELANGGAN</th>
                            @if($viewMode === 'RAJUT')
                                <th class="px-8 py-6">KONSTRUKSI</th>
                                <th class="px-8 py-6 text-center">MESIN RAJUT</th>
                            @else
                                <th class="px-8 py-6">WARNA & KONSTRUKSI</th>
                                <th class="px-8 py-6 text-center">SPECS (L/G/H)</th>
                            @endif
                            <th class="px-8 py-6 text-center">ROLL</th>
                            <th class="px-8 py-6 text-center">KG</th>
                            <th class="px-8 py-6 text-right">@if($viewMode === 'RAJUT') STATUS @else LOGISTICS @endif</th>
                            <th class="px-8 py-6 text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        {{-- WIP ROWS --}}
                        @forelse($orders as $wip)
                            <tr class="bg-brand-500/5 border-l-4 border-brand-600 hover:bg-brand-500/10 transition-colors border-b mkt-border italic group">
                                <td class="px-8 py-7">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-2 w-2 relative">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-500"></span>
                                        </span>
                                        <div>
                                            <span class="mkt-text block text-sm font-black tracking-tighter">{{ $wip->art_no }}</span>
                                            <span class="mkt-text mt-1 block text-[11px] font-bold uppercase opacity-60">LEGACY ID #{{ $wip->sap_no }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-7">
                                    <span class="mkt-text block text-sm tracking-tight">{{ $wip->pelanggan }}</span>
                                    <span class="text-[9px] mkt-text-muted font-black uppercase tracking-widest mt-1 opacity-50">MKT: {{ $wip->mkt ?? 'DD' }}</span>
                                </td>
                                <td class="px-8 py-7">
                                    @if($viewMode === 'RAJUT')
                                        <span class="mkt-text-muted block text-[11px] font-bold">{{ $wip->konstruksi_greige }}</span>
                                        <span class="mkt-text text-[10px]] font-black uppercase mt-1 block">{{ $wip->rnd_gramasi_greige ?? $wip->target_gramasi }} GSM</span>
                                    @else
                                        <span class="text-emerald-500 block text-xs font-black uppercase italic">{{ $wip->warna }}</span>
                                        <span class="mkt-text-muted block text-[10px] font-bold mt-1 uppercase">{{ $wip->konstruksi_greige }}</span>
                                    @endif
                                </td>
                                <td class="px-8 py-7 text-center">
                                    @if($viewMode === 'RAJUT')
                                        <span class="mkt-text block font-black text-sm tracking-tighter">{{ $wip->rnd_mesin_rajut ?? 'TBD' }}</span>
                                        <span class="text-[9px] mkt-text-muted font-black uppercase tracking-widest mt-1 opacity-50">{{ $wip->kelompok_kain }}</span>
                                    @else
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-emerald-400 block font-black text-xs">{{ $wip->target_lebar }}" / {{ $wip->target_gramasi }}</span>
                                            <span class="text-[8px] mkt-text-muted font-black uppercase tracking-tighter">{{ $wip->handfeel ?? '-' }} | {{ $wip->belah_bulat }}</span>
                                        </div>
                                    @endif
                                @php
                                    $actualRoll = $wip->productionActivities->sum('roll');
                                    $actualKg = $wip->productionActivities->sum('kg');
                                @endphp
                                <td class="px-8 py-7 text-center">
                                    <span class="mkt-text font-black text-sm">{{ $actualRoll ?: '-' }}</span>
                                </td>
                                <td class="px-8 py-7 text-center">
                                    <span class="mkt-text font-black text-sm">{{ $actualKg ? (float)$actualKg . ' KG' : '-' }}</span>
                                </td>
                                <td class="px-8 py-7 text-right">
                                    <div class="flex flex-col items-end gap-1">
                                        <div class="flex gap-1 items-center flex-wrap justify-end">
                                            @php
                                                $allSteps = [
                                                    'knitting' => ['label' => 'KNT', 'flag' => null],
                                                    'dyeing' => ['label' => 'DYE', 'flag' => null],
                                                    'relax-dryer' => ['label' => 'RLX', 'flag' => null],
                                                    'compactor' => ['label' => 'CMP', 'flag' => 'req_compactor'],
                                                    'heat-setting' => ['label' => 'HT', 'flag' => 'req_heat_setting'],
                                                    'stenter' => ['label' => 'STN', 'flag' => 'req_stenter'],
                                                    'tumbler' => ['label' => 'TMB', 'flag' => 'req_tumbler'],
                                                    'fleece' => ['label' => 'FLC', 'flag' => 'req_fleece']
                                                ];

                                                if ($this->viewMode === 'RAJUT') {
                                                    $allSteps = ['knitting' => ['label' => 'KNT', 'flag' => null]];
                                                }

                                                $completedDivisions = $wip->productionActivities->pluck('division_name')->toArray();
                                            @endphp
                                            @foreach($allSteps as $key => $step)
                                                @php
                                                    $showStep = $step['flag'] === null || (bool) $wip->{$step['flag']};
                                                @endphp
                                                @if($showStep)
                                                    @php
                                                        $isCompleted = in_array($key, $completedDivisions);
                                                        $isCurrent = $wip->status === $key;
                                                    @endphp
                                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-black {{ $isCompleted ? 'bg-emerald-500/20 text-emerald-500' : ($isCurrent ? 'bg-yellow-500/20 text-yellow-500 animate-pulse' : 'bg-white/5 text-slate-500') }}">
                                                        {{ $step['label'] }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                        @if($wip->processing_by)
                                            <span class="text-[7px] text-amber-500 font-black uppercase opacity-90 mt-1">
                                                ACTIVE OP: {{ $wip->processingBy->name }}
                                            </span>
                                        @elseif($wip->status === 'finished')
                                            <span class="text-[7px] text-emerald-500 font-black uppercase opacity-90 mt-1">
                                                SYSTEM COMPLETED
                                            </span>
                                        @else
                                            <span class="text-[7px] mkt-text font-black uppercase opacity-75 mt-1 animate-pulse">
                                                PENDING IN {{ strtoupper($wip->status) }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-8 py-7 text-center">
                                    <button wire:click="openTracking('{{ $wip->art_no }}')" class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-2 rounded-xl text-[9px] font-black uppercase transition-all shadow-lg shadow-brand-600/20">
                                        DETAIL                                     </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="py-24 text-center mkt-text-muted font-black uppercase text-xs tracking-[0.3em] opacity-30 italic">TIDAK ADA DATA {{ $viewMode }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Modal Tracking Artikel --}}
            @if($showTrackingModal)
                @teleport('body')
                <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/90 backdrop-blur-xl p-0 sm:p-4 md:py-6">
                    <div class="mkt-surface border mkt-border w-full sm:max-w-7xl rounded-none sm:rounded-3xl md:rounded-[4rem] overflow-hidden shadow-xl sm:shadow-[0_0_100px_rgba(0,0,0,0.5)] animate-in fade-in zoom-in duration-500 max-h-[100dvh] sm:max-h-[95vh] flex flex-col">

                        {{-- HEADER MODAL --}}
                        <div class="p-3 sm:p-6 md:p-10 mkt-surface-alt flex flex-col gap-3 border-b mkt-border backdrop-blur-md shrink-0">
                            <div class="flex items-start gap-3 sm:gap-6 min-w-0">
                                <div class="h-10 w-10 sm:h-14 sm:w-14 md:h-16 md:w-16 bg-brand-600 rounded-xl sm:rounded-2xl md:rounded-3xl flex items-center justify-center shrink-0 shadow-lg shadow-brand-600/40">
                                    <span class="text-white text-lg sm:text-2xl md:text-3xl font-black italic">!</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    @php
                                        $modalMode = $viewMode === 'WARNA' || in_array($trackingData->status, ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'finishing', 'stenter', 'tumbler', 'fleece']) ? 'WARNA' : 'RAJUT';
                                    @endphp
                                    <h2 class="mkt-text font-black italic uppercase text-lg sm:text-2xl md:text-4xl leading-tight sm:leading-none tracking-tighter">
                                        MONITORING <span class="text-brand-600">{{ $modalMode }}</span>
                                    </h2>
                                    <div class="mt-1.5 sm:mt-2 space-y-0.5">
                                        <p class="mkt-text-muted text-[7px] sm:text-[10px] font-black uppercase tracking-wide flex items-center gap-1.5">
                                            <span class="inline-block w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                                            <span class="truncate">ART: {{ $trackingData->art_no }}</span>
                                        </p>
                                        <p class="mkt-text-muted text-[7px] sm:text-[10px] font-black uppercase tracking-wide truncate">
                                            SAP: {{ $trackingData->sap_no }} · <span class="text-emerald-500">LIVE</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <button wire:click="$set('showTrackingModal', false)" 
                                class="w-full sm:w-auto sm:self-end bg-white dark:bg-brand-600 text-slate-600 dark:text-white px-4 sm:px-8 py-2.5 sm:py-4 rounded-xl sm:rounded-3xl text-[8px] sm:text-[10px] font-black uppercase transition-all border mkt-border dark:border-none shadow-md sm:shadow-xl flex items-center justify-center gap-2 group">
                                TUTUP <span class="group-hover:rotate-90 transition-transform">✕</span>
                            </button>
                        </div>

                        <div class="p-3 sm:p-6 md:p-10 overflow-y-auto custom-scrollbar flex-1 min-h-0">
                            <div class="grid grid-cols-12 gap-4 sm:gap-6 md:gap-8">

                                {{-- COLUMN LEFT: SPECIFICATIONS (17 POINTS) --}}
                                <div class="col-span-12 lg:col-span-8 space-y-4 sm:space-y-6 md:space-y-8">

                                    {{-- CARD 1: IDENTITY & SALES --}}
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-4 md:gap-6">
                                        <div class="mkt-surface-alt p-3 sm:p-4 md:p-6 rounded-xl sm:rounded-2xl md:rounded-[2.5rem] border mkt-border shadow-inner min-w-0">
                                            <p class="text-[7px] sm:text-[8px] mkt-text-muted font-black uppercase tracking-wide mb-1 sm:mb-3">ARTIKEL NO</p>
                                            <p class="text-sm sm:text-lg md:text-xl font-black mkt-text tracking-tight uppercase leading-tight break-all">{{ $trackingData->art_no ?? '-' }}</p>
                                        </div>
                                        <div class="mkt-surface-alt p-3 sm:p-4 md:p-6 rounded-xl sm:rounded-2xl md:rounded-[2.5rem] border mkt-border shadow-inner min-w-0">
                                            <p class="text-[7px] sm:text-[8px] mkt-text-muted font-black uppercase tracking-wide mb-1 sm:mb-3">WARNA KAIN</p>
                                            <p class="text-sm sm:text-lg md:text-xl font-black text-emerald-500 tracking-tight uppercase leading-tight italic break-words">{{ $trackingData->warna ?? '-' }}</p>
                                        </div>
                                        <div class="mkt-surface-alt p-3 sm:p-4 md:p-6 rounded-xl sm:rounded-2xl md:rounded-[2.5rem] border mkt-border shadow-inner min-w-0 col-span-2 md:col-span-1">
                                            <p class="text-[7px] sm:text-[8px] mkt-text-muted font-black uppercase tracking-wide mb-1 sm:mb-3">PELANGGAN</p>
                                            <p class="text-sm sm:text-lg md:text-xl font-black mkt-text tracking-tight uppercase leading-tight break-words">{{ $trackingData->pelanggan ?? '-' }}</p>
                                        </div>
                                        <div class="mkt-surface-alt p-3 sm:p-4 md:p-6 rounded-xl sm:rounded-2xl md:rounded-[2.5rem] border mkt-border shadow-inner min-w-0 col-span-2 md:col-span-1">
                                            <p class="text-[7px] sm:text-[8px] mkt-text-muted font-black uppercase tracking-wide mb-1 sm:mb-3">SALES / MKT</p>
                                            <p class="text-sm sm:text-lg md:text-xl font-black mkt-text tracking-tight uppercase leading-tight italic">{{ $trackingData->mkt ?? '-' }}</p>
                                        </div>
                                    </div>

                                    {{-- CARD 2: GREIGE & MACHINE SPECS --}}
                                    <div class="mkt-surface p-3 sm:p-6 md:p-8 rounded-xl sm:rounded-2xl md:rounded-[3.5rem] border mkt-border shadow-sm sm:shadow-2xl relative overflow-hidden group">
                                        <div class="absolute top-0 right-0 p-4 sm:p-8 opacity-5 sm:opacity-10 group-hover:opacity-20 transition-opacity pointer-events-none hidden sm:block">
                                            <svg class="w-16 sm:w-24 h-16 sm:h-24 mkt-text" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                                        </div>
                                        <h4 class="text-[9px] sm:text-xs font-black mkt-text italic uppercase tracking-widest mb-3 sm:mb-6 md:mb-8 flex items-center gap-2 relative z-10">
                                            <span class="w-4 sm:w-6 h-[2px] bg-brand shrink-0"></span> GREIGE TECHNICAL SPECIFICATIONS
                                        </h4>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6 md:gap-10 relative z-10">
                                            <div class="col-span-2 min-w-0">
                                                <p class="text-[7px] sm:text-[8px] mkt-text-muted font-black uppercase tracking-wide mb-1 sm:mb-2">Konstruksi Greige</p>
                                                <p class="text-xs sm:text-sm font-black mkt-text italic leading-snug break-words">{{ $trackingData->konstruksi_greige ?? '-' }}</p>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-[7px] sm:text-[8px] mkt-text-muted font-black uppercase tracking-wide mb-1 sm:mb-2">Gramasi Greige</p>
                                                <p class="text-lg sm:text-2xl font-black mkt-text tracking-tighter leading-none">{{ $trackingData->rnd_gramasi_greige ?? '-' }} <span class="text-[9px] sm:text-[10px] opacity-50 uppercase">GSM</span></p>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-[7px] sm:text-[8px] mkt-text-muted font-black uppercase tracking-wide mb-1 sm:mb-2">Kelompok Mesin</p>
                                                <p class="text-xs sm:text-sm font-black mkt-text italic uppercase leading-tight break-words">{{ $trackingData->kelompok_kain ?? '-' }}</p>
                                            </div>
                                            <div class="col-span-2 md:col-span-1 min-w-0">
                                                <p class="text-[7px] sm:text-[8px] mkt-text-muted font-black uppercase tracking-wide mb-1 sm:mb-2">Material Utama</p>
                                                <p class="text-xs sm:text-sm font-black text-emerald-600 dark:text-emerald-400 italic uppercase leading-tight break-words">
                                                    {{ $trackingData->material ?? '-' }}
                                                    @if($trackingData->benang_percent)
                                                        <span class="mkt-text">({{ $trackingData->benang_percent }}%)</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- CARD 3: FINISHING TARGETS + KNITTING --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-6 md:gap-8">
                                        <div class="mkt-surface p-3 sm:p-6 md:p-8 rounded-xl sm:rounded-2xl md:rounded-[3.5rem] border mkt-border shadow-sm sm:shadow-xl">
                                            <h4 class="text-[8px] sm:text-[9px] font-black mkt-text-muted uppercase tracking-widest mb-3 sm:mb-6">Finishing Targets</h4>
                                            <div class="grid grid-cols-2 gap-3 sm:gap-6 md:gap-8">
                                                <div class="min-w-0">
                                                    <p class="text-[7px] sm:text-[8px] mkt-text-muted font-black uppercase tracking-wide mb-0.5 sm:mb-1">Target Lebar</p>
                                                    <p class="text-sm sm:text-lg md:text-xl font-black mkt-text italic leading-tight">{{ $trackingData->target_lebar ?? '-' }}"</p>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-[7px] sm:text-[8px] mkt-text-muted font-black uppercase tracking-wide mb-0.5 sm:mb-1">Target Gramasi</p>
                                                    <p class="text-sm sm:text-lg md:text-xl font-black mkt-text italic leading-tight">{{ $trackingData->target_gramasi ?? '-' }} GSM</p>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-[7px] sm:text-[8px] mkt-text-muted font-black uppercase tracking-wide mb-0.5 sm:mb-1">Belah / Bulat</p>
                                                    <p class="text-sm sm:text-lg md:text-xl font-black text-emerald-500 italic leading-tight uppercase">{{ $trackingData->belah_bulat ?? '-' }}</p>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-[7px] sm:text-[8px] mkt-text-muted font-black uppercase tracking-wide mb-0.5 sm:mb-1">Handfeel</p>
                                                    <p class="text-sm sm:text-lg md:text-xl font-black mkt-text italic leading-tight uppercase">{{ $trackingData->handfeel ?? '-' }}</p>
                                                </div>
                                                <div class="col-span-2 min-w-0">
                                                    <p class="text-[7px] sm:text-[8px] mkt-text-muted font-black uppercase tracking-wide mb-0.5 sm:mb-1">Treatment Khusus</p>
                                                    <p class="text-sm sm:text-lg md:text-xl font-black mkt-text italic leading-tight uppercase break-words">{{ $trackingData->treatment_khusus ?? '-' }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- CARD 4: ACTUAL PRODUCTION (RAJUT) --}}
                                        @php $knitLog = $trackingLogs->where('division_name', 'knitting')->first(); @endphp
                                        <div class="mkt-surface p-3 sm:p-6 md:p-8 rounded-xl sm:rounded-2xl md:rounded-[3.5rem] border-2 {{ $knitLog ? 'border-emerald-500/20 bg-emerald-500/5' : 'mkt-border' }} shadow-sm sm:shadow-xl relative overflow-hidden">
                                            @if(!$knitLog)
                                                <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center z-10">
                                                    <p class="text-[9px] sm:text-[10px] font-black text-slate-500 uppercase tracking-widest italic px-4 text-center">WAITING FOR PRODUCTION...</p>
                                                </div>
                                            @endif
                                            <h4 class="text-[8px] sm:text-[9px] font-black {{ $knitLog ? 'text-emerald-500' : 'mkt-text-muted' }} uppercase tracking-widest mb-3 sm:mb-6 flex items-center justify-between gap-2">
                                                <span>Actual Knitting</span>
                                                @if($knitLog) <span class="bg-emerald-500 text-white px-2 py-0.5 rounded text-[7px] shrink-0">DONE</span> @endif
                                            </h4>
                                            <div class="grid grid-cols-3 gap-2 sm:gap-4 text-center">
                                                <div class="min-w-0">
                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-0.5 sm:mb-1">Mesin</p>
                                                    <p class="text-sm sm:text-lg md:text-xl font-black mkt-text italic leading-none truncate">{{ $knitLog->machine_no ?? '-' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-0.5 sm:mb-1">ROLL</p>
                                                    <p class="text-lg sm:text-2xl font-black text-emerald-500 leading-none">{{ $knitLog->roll ?? '0' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-0.5 sm:mb-1">KG</p>
                                                    <p class="text-lg sm:text-2xl font-black text-emerald-500 leading-none">{{ $knitLog->kg ?? '0' }}</p>
                                                </div>
                                            </div>
                                            @if($knitLog)
                                                <div class="mt-3 sm:mt-6 pt-3 sm:pt-4 border-t mkt-border flex flex-col sm:flex-row sm:justify-between gap-1 text-[7px] sm:text-[8px] font-bold mkt-text-muted italic uppercase">
                                                    <span class="truncate">OP: {{ $knitLog->operator->name ?? 'N/A' }}</span>
                                                    <span class="text-emerald-500 shrink-0">{{ $knitLog->created_at->format('H:i | d/m') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- CARD 5: NEXT STEP & LOGISTICS --}}
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 sm:gap-4 md:gap-6">
                                        <div class="col-span-2 md:col-span-1 bg-brand p-4 sm:p-6 md:p-8 rounded-xl sm:rounded-2xl md:rounded-[3rem] shadow-lg shadow-brand-900/30">
                                            <p class="text-[7px] sm:text-[8px] text-brand-200 font-black uppercase tracking-wide mb-1 sm:mb-2">KIRIM KE UNIT</p>
                                            <h3 class="text-2xl sm:text-3xl font-black text-white italic leading-none tracking-tighter">DPF 3</h3>
                                        </div>
                                        <div class="col-span-2 mkt-surface p-4 sm:p-6 md:p-8 rounded-xl sm:rounded-2xl md:rounded-[3rem] border mkt-border shadow-sm sm:shadow-xl min-w-0">
                                            <p class="text-[7px] sm:text-[8px] mkt-text-muted font-black uppercase tracking-wide mb-1 sm:mb-2 italic">KETERANGAN ARTIKEL</p>
                                            <p class="text-[10px] sm:text-xs font-black mkt-text italic uppercase leading-relaxed break-words">{{ $trackingData->keterangan_artikel ?? 'PROSES SESUAI STANDAR PRODUKSI RND.' }}</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- COLUMN RIGHT: TIMELINE & MILESTONES --}}
                                <div class="col-span-12 lg:col-span-4 flex flex-col space-y-4 sm:space-y-6 md:space-y-8">
                                    <div class="mkt-surface p-3 sm:p-6 md:p-8 rounded-xl sm:rounded-2xl md:rounded-[4rem] border mkt-border shadow-sm sm:shadow-2xl flex-1 flex flex-col min-h-0">
                                        <h3 class="text-[10px] sm:text-xs font-black mkt-text italic uppercase tracking-[0.15em] sm:tracking-[0.2em] mb-4 sm:mb-6 md:mb-10 flex items-center gap-2 sm:gap-4">
                                            <span class="w-5 sm:w-8 h-[2px] bg-red-600 shrink-0"></span> TIMELINE TRACKING
                                        </h3>

                                        <div class="space-y-6 relative flex-1 overflow-y-auto pr-2 custom-scrollbar">
                                            <div class="absolute left-5 top-0 bottom-10 w-[2px] bg-slate-800 border-l border-dashed border-slate-700"></div>

                                            @php
                                                $baseSteps = [
                                                    ['name' => 'MARKETING', 'div' => 'marketing', 'label' => 'ORDER CREATED', 'flag' => null],
                                                    ['name' => 'KNITTING', 'div' => 'knitting', 'label' => 'PROSES RAJUT', 'flag' => null],
                                                    ['name' => 'DYEING', 'div' => 'dyeing', 'label' => 'PEWARNAAN', 'flag' => null],
                                                    ['name' => 'RELAX DRYER', 'div' => 'relax-dryer', 'label' => 'RELAX DRYER', 'flag' => null],
                                                    ['name' => 'COMPACTOR', 'div' => 'compactor', 'label' => 'COMPACTOR', 'flag' => 'req_compactor'],
                                                    ['name' => 'HEAT SETTING', 'div' => 'heat-setting', 'label' => 'HEAT SETTING', 'flag' => 'req_heat_setting'],
                                                    ['name' => 'STENTER', 'div' => 'stenter', 'label' => 'STENTER', 'flag' => 'req_stenter'],
                                                    ['name' => 'TUMBLER', 'div' => 'tumbler', 'label' => 'TUMBLER', 'flag' => 'req_tumbler'],
                                                    ['name' => 'FLEECE', 'div' => 'fleece', 'label' => 'FLEECE', 'flag' => 'req_fleece'],
                                                    ['name' => 'QE / LAB', 'div' => 'qe', 'label' => 'QUALITY APPROVAL', 'flag' => 'req_qe']
                                                ];

                                                $steps = [];
                                                foreach ($baseSteps as $bs) {
                                                    if ($bs['flag'] === null || (bool) $trackingData->{$bs['flag']} === true) {
                                                        if ($this->viewMode === 'RAJUT' && in_array($bs['div'], ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'stenter', 'tumbler', 'fleece', 'qe'])) {
                                                            continue;
                                                        }
                                                        $steps[] = $bs;
                                                    }
                                                }
                                            @endphp

                                            @foreach($steps as $index => $step)
                                                @php 
                                                                                            $log = $trackingLogs->where('division_name', $step['div'])->first();
                                                    // Marketing is always "done" if we are here
                                                    $isDone = ($step['div'] === 'marketing') ? true : ($log ? true : false);
                                                    $isCurrent = $trackingData->status === $step['div'];
                                                    $techData = $log ? (is_array($log->technical_data) ? $log->technical_data : json_decode($log->technical_data, true)) : [];
                                                    $operatorActual = $techData['nama_input'] ?? $techData['operator'] ?? $log->operator->name ?? 'UNKNOWN';
                                                @endphp

                                                <div class="relative pl-12 sm:pl-16 pb-8 sm:pb-12 last:pb-0 group">
                                                    {{-- STEP INDICATOR --}}
                                                    <div class="absolute left-0 top-0 flex flex-col items-center">
                                                        @if($isDone)
                                                            <div class="h-8 w-8 sm:h-10 sm:w-10 bg-brand-600 rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg shadow-brand-600/30 z-10">
                                                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                            </div>
                                                        @elseif($isCurrent)
                                                            <div class="h-8 w-8 sm:h-10 sm:w-10 bg-brand-500 rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg z-10 animate-pulse border-2 border-white/20">
                                                                <span class="text-white text-[10px] sm:text-xs font-black italic">{{ $index + 1 }}</span>
                                                            </div>
                                                        @else
                                                            <div class="h-8 w-8 sm:h-10 sm:w-10 bg-slate-800 border-2 border-slate-700 rounded-xl sm:rounded-2xl flex items-center justify-center z-10 opacity-30">
                                                                <span class="mkt-text-muted text-[9px] sm:text-[10px] font-black italic">{{ $index + 1 }}</span>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    {{-- STEP CONTENT --}}
                                                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 sm:gap-4 min-w-0">
                                                        <div class="space-y-1 min-w-0 flex-1">
                                                            <h4 class="text-[10px] sm:text-xs font-black {{ $isDone ? 'mkt-text' : ($isCurrent ? 'text-brand-600' : 'mkt-text-muted opacity-30') }} italic uppercase tracking-wide sm:tracking-widest leading-tight">
                                                                {{ $step['name'] }}
                                                            </h4>
                                                            <p class="text-[7px] sm:text-[8px] font-bold {{ $isDone ? 'mkt-text-muted' : ($isCurrent ? 'mkt-text/50' : 'mkt-text-muted opacity-20') }} uppercase tracking-tighter">
                                                                {{ $step['label'] }}
                                                            </p>

                                                            @if($log && $log->operator)
                                                                <div class="flex items-center gap-2 mt-2 sm:mt-3 mkt-surface-alt px-2 sm:px-3 py-1 sm:py-1.5 rounded-lg border mkt-border max-w-full">
                                                                    <div class="w-1.5 h-1.5 rounded-full bg-brand-500 shrink-0"></div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase tracking-wide truncate">
                                                                        OP: <span class="mkt-text">{{ $operatorActual }}</span>
                                                                    </p>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        {{-- ACTIONS & TIME --}}
                                                        <div class="flex flex-row sm:flex-col items-center sm:items-end gap-2 shrink-0 flex-wrap">
                                                            @if($isDone)
                                                                <div class="bg-emerald-500/10 border border-emerald-500/10 px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg sm:rounded-xl text-left sm:text-right">
                                                                    <p class="text-[6px] sm:text-[7px] text-emerald-500 font-black uppercase tracking-wide mb-0.5 opacity-60">DONE</p>
                                                                    <p class="text-[9px] sm:text-[10px] text-emerald-400 font-black italic leading-none whitespace-nowrap">
                                                                        {{ ($step['div'] === 'marketing') ? $trackingData->created_at->format('d/m H:i') : $log->created_at->format('d/m H:i') }}
                                                                    </p>
                                                                </div>

                                                                <button wire:click="toggleDetail('{{ $step['div'] }}')" 
                                                                    class="flex items-center gap-1.5 bg-brand-600 hover:bg-brand-700 px-2.5 sm:px-4 py-1.5 sm:py-2 rounded-lg sm:rounded-xl transition-all shadow-md">
                                                                    <span class="text-[7px] sm:text-[8px] text-white font-black uppercase">HASIL</span>
                                                                    <svg class="w-3 h-3 text-white/70 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                                </button>
                                                            @elseif($isCurrent)
                                                                <div class="bg-brand-600/10 border border-brand-600/30 px-3 py-1.5 sm:py-2 rounded-lg sm:rounded-xl">
                                                                    <p class="text-[8px] sm:text-[9px] mkt-text-muted font-black animate-pulse tracking-wider">ON PROGRESS</p>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- NESTED POPUP FOR LOG DETAIL --}}


                                        {{-- FINAL STATUS BADGE --}}
                                        <div class="mt-4 sm:mt-6 md:mt-10 p-4 sm:p-6 mkt-surface-alt rounded-xl sm:rounded-2xl md:rounded-[2.5rem] border mkt-border text-center">
                                            <p class="text-[7px] mkt-text-muted font-black uppercase tracking-[0.2em] sm:tracking-[0.4em] mb-1 sm:mb-2 italic">CURRENT PRODUCTION STATE</p>
                                            <h5 class="text-sm sm:text-lg font-black mkt-text italic uppercase tracking-tighter">
                                                @if($trackingLogs->last())
                                                    {{ strtoupper($trackingLogs->last()->division_name) }} COMPLETED
                                                @else
                                                    MARKETING STAGE
                                                @endif
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endteleport
            @endif
    </div> 

    @if($expandedLog)
        <div class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-md p-0 sm:p-4 animate-in fade-in duration-300">
            <div class="mkt-surface border mkt-border w-full sm:max-w-2xl rounded-t-2xl sm:rounded-[3rem] shadow-xl sm:shadow-[0_0_100px_rgba(0,0,0,0.8)] animate-in zoom-in slide-in-from-bottom-10 duration-500 relative overflow-hidden flex flex-col max-h-[90dvh] sm:max-h-[80vh]">

                {{-- MODAL HEADER --}}
                <div class="p-4 sm:p-6 md:p-8 border-b mkt-border flex justify-between items-start gap-3 mkt-surface-alt shrink-0">
                    <div class="flex items-start gap-3 min-w-0 flex-1">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 {{ $expandedLog == 'marketing' ? 'bg-red-600' : 'bg-emerald-600' }} rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg shrink-0">
                            <span class="text-white text-sm sm:text-lg font-black italic">{{ $expandedLog == 'marketing' ? 'MO' : 'OP' }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[7px] sm:text-[8px] mkt-text-muted font-black uppercase tracking-wide sm:tracking-[0.3em]">
                                {{ $expandedLog == 'marketing' ? 'MARKETING SPECS' : 'PRODUCTION DATA' }}
                            </p>
                            <h3 class="text-base sm:text-xl font-black mkt-text italic uppercase leading-tight">
                                {{ strtoupper(str_replace('_', ' ', $expandedLog)) }} RESULT
                            </h3>
                        </div>
                    </div>
                    <button wire:click="toggleDetail('{{ $expandedLog }}')" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-slate-800 flex items-center justify-center text-white hover:bg-red-600 transition-all shrink-0">
                        ✕
                    </button>
                </div>

                {{-- MODAL CONTENT --}}
                <div class="p-4 sm:p-6 md:p-8 overflow-y-auto custom-scrollbar flex-1 min-h-0">
                    @if($expandedLog == 'marketing')
                        <div class="space-y-5 sm:space-y-8 md:space-y-10 custom-scrollbar sm:pr-4">
                            {{-- I. IDENTITAS ORDER --}}
                            <div class="space-y-2 sm:space-y-4">
                                <p class="text-[8px] sm:text-[9px] font-black mkt-text-muted uppercase tracking-wide sm:tracking-[0.3em] border-l-4 border-mkt-border pl-2 sm:pl-3">I. IDENTITAS ORDER</p>
                                <div class="grid grid-cols-2 gap-3 sm:gap-6 mkt-surface-alt p-3 sm:p-5 rounded-xl sm:rounded-2xl border mkt-border">
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">LEGACY SAP ID</p>
                                        <p class="text-[11px] font-black mkt-text opacity-60">{{ $trackingData->sap_no ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">ART NO</p>
                                        <p class="text-[11px] font-black mkt-text">{{ $trackingData->art_no }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TANGGAL ORDER</p>
                                        <p class="text-[11px] font-black mkt-text">{{ $trackingData->created_at->format('d/m/Y') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">PELANGGAN</p>
                                        <p class="text-[11px] font-black mkt-text uppercase">{{ $trackingData->pelanggan }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- II. KLASIFIKASI & MATERIAL --}}
                            <div class="space-y-2 sm:space-y-4">
                                <p class="text-[8px] sm:text-[9px] font-black text-emerald-500 uppercase tracking-wide sm:tracking-[0.3em] border-l-4 border-emerald-500 pl-2 sm:pl-3">II. KLASIFIKASI & MATERIAL</p>
                                <div class="grid grid-cols-2 gap-3 sm:gap-6 mkt-surface-alt p-3 sm:p-5 rounded-xl sm:rounded-2xl border mkt-border">
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">MKT (SALES)</p>
                                        <p class="text-[11px] font-black mkt-text uppercase">{{ $trackingData->mkt }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">KEPERLUAN</p>
                                        <p class="text-[11px] font-black mkt-text uppercase">{{ $trackingData->keperluan ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">MATERIAL</p>
                                        <p class="text-[11px] font-black mkt-text uppercase">{{ $trackingData->material }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">BENANG</p>
                                        <p class="text-[11px] font-black mkt-text uppercase">
                                            {{ $trackingData->benang ?? '-' }}
                                            @if($trackingData->benang_percent)
                                                <span class="mkt-text">({{ $trackingData->benang_percent }}%)</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- III. SPESIFIKASI TEKNIS --}}
                            <div class="space-y-2 sm:space-y-4">
                                <p class="text-[8px] sm:text-[9px] font-black mkt-text-muted uppercase tracking-wide sm:tracking-[0.3em] border-l-4 border-mkt-border pl-2 sm:pl-3">III. SPESIFIKASI TEKNIS</p>
                                <div class="grid grid-cols-2 gap-3 sm:gap-6 mkt-surface-alt p-3 sm:p-5 rounded-xl sm:rounded-2xl border mkt-border">
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TARGET GRAMASI</p>
                                        <p class="text-[11px] font-black mkt-text">{{ $trackingData->target_gramasi }} GSM</p>
                                    </div>
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HANDFEEL</p>
                                        <p class="text-[11px] font-black mkt-text uppercase">{{ $trackingData->handfeel ?? '-' }}</p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TREATMENT KHUSUS</p>
                                        <p class="text-[11px] font-black mkt-text uppercase italic">{{ $trackingData->treatment_khusus }}</p>
                                    </div>
                                    <div class="col-span-2 min-w-0">
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">KONSTRUKSI GREIGE</p>
                                        <p class="text-[11px] font-black mkt-text italic uppercase break-words leading-snug">{{ $trackingData->konstruksi_greige }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">KELOMPOK KAIN</p>
                                        <p class="text-[11px] font-black mkt-text uppercase">{{ $trackingData->kelompok_kain }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TARGET LEBAR</p>
                                        <p class="text-[11px] font-black mkt-text">{{ $trackingData->target_lebar }}"</p>
                                    </div>
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">BELAH/BULAT</p>
                                        <p class="text-[11px] font-black mkt-text uppercase">{{ $trackingData->belah_bulat }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">WARNA KAIN</p>
                                        <p class="text-[11px] font-black text-emerald-400 uppercase">{{ $trackingData->warna }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- IV. QUANTITY & KETERANGAN --}}
                            <div class="space-y-2 sm:space-y-4">
                                <p class="text-[8px] sm:text-[9px] font-black text-orange-500 uppercase tracking-wide sm:tracking-[0.3em] border-l-4 border-orange-500 pl-2 sm:pl-3">IV. QUANTITY & KETERANGAN</p>
                                <div class="grid grid-cols-2 gap-3 sm:gap-6 mkt-surface-alt p-3 sm:p-5 rounded-xl sm:rounded-2xl border mkt-border">
                                    <div class="min-w-0">
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">ROLL</p>
                                        <p class="text-[11px] font-black mkt-text uppercase">{{ $trackingData->roll_target }} ROLL</p>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">KG</p>
                                        <p class="text-[11px] font-black mkt-text uppercase">{{ $trackingData->kg_target }} KG</p>
                                    </div>
                                    <div class="col-span-2 min-w-0">
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">KETERANGAN</p>
                                        <p class="text-[11px] font-black mkt-text italic uppercase break-words">{{ $trackingData->keterangan_artikel ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- V. DATA R&D --}}
                            <div class="space-y-2 sm:space-y-4">
                                <p class="text-[8px] sm:text-[9px] font-black mkt-text-muted uppercase tracking-wide sm:tracking-[0.3em] border-l-4 border-mkt-border pl-2 sm:pl-3">V. DATA R&D</p>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-6 mkt-surface-alt p-3 sm:p-5 rounded-xl sm:rounded-2xl border mkt-border">
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">GRAMASI GREIGE</p>
                                        <p class="text-[11px] font-black mkt-text italic">{{ $trackingData->rnd_gramasi_greige }} GSM</p>
                                    </div>
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">MESIN RAJUT</p>
                                        <p class="text-[11px] font-black mkt-text uppercase">{{ $trackingData->rnd_mesin_rajut ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">JENIS MESIN RAJUT</p>
                                        <p class="text-[11px] font-black mkt-text uppercase">{{ $trackingData->rnd_jenis_mesin_rajut ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                      @else
                                                    @php 
                                                                                                            $log = $trackingLogs->where('division_name', $expandedLog)->first();
                                                        $techData = $log ? (is_array($log->technical_data) ? $log->technical_data : json_decode($log->technical_data, true)) : [];
                                                        $operatorActual = $techData['nama_input'] ?? $techData['operator'] ?? $log->operator->name ?? 'UNKNOWN';
                                                    @endphp
                                                    @if($log)
                                                                                                                    <div class="space-y-8">
                                                                                                                        <div class="flex items-center justify-between p-6 mkt-surface-alt border mkt-border rounded-3xl group hover:border-emerald-500/50 transition-all duration-500">
                                                                                                                            <div class="flex items-center gap-5">
                                                                                                                                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center border border-emerald-500/20">
                                                                                                                                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                                                                                                </div>
                                                                                                                                <div>
                                                                                                                                    <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1 italic">ACTUAL OPERATOR</p>
                                                                                                                                    <p class="text-xl font-black mkt-text italic tracking-tighter">{{ strtoupper($operatorActual) }}</p>
                                                                                                                                </div>
                                                                                                                            </div>
                                                                                                                            <div class="text-right">
                                                                                                                                <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">MACHINE UNIT</p>
                                                                                                                                <p class="text-3xl font-black mkt-text italic leading-none">{{ $log->machine_no ?? 'M-01' }}</p>
                                                                                                                            </div>
                                                                                                                        </div>

                                                                                                                        @if($expandedLog === 'knitting')
                                                                                                                            {{-- HIGH FIDELITY LAYOUT FOR KNITTING (UNIFIED WITH MARKETING STYLE) --}}
                                                                                                                            <div class="space-y-10 animate-in fade-in duration-700">

                                                                                                                                {{-- I. SPESIFIKASI MESIN --}}
                                                                                                                                <div class="space-y-4">
                                                                                                                                    <p class="text-[9px] font-black mkt-text-muted uppercase tracking-[0.3em] border-l-4 border-mkt-border pl-3">I. IDENTITAS & SPESIFIKASI MESIN</p>
                                                                                                                                    <div class="grid grid-cols-2 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                                                                                        <div class="col-span-2 border-r mkt-border pr-6">
                                                                                                                                            <div class="grid grid-cols-2 gap-4">
                                                                                                                                                <div>
                                                                                                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">LEGACY SAP ID</p>
                                                                                                                                                    <p class="text-[11px] font-black mkt-text italic opacity-60">{{ $techData['sap_no'] ?? ($trackingData->sap_no ?? '-') }}</p>
                                                                                                                                                </div>
                                                                                                                                                <div>
                                                                                                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TGL PRODUKSI</p>
                                                                                                                                                    <p class="text-[11px] font-black mkt-text">{{ !empty($techData['tgl_input']) ? date('d/m/Y', strtotime($techData['tgl_input'])) : $log->created_at->format('d/m/Y') }}</p>
                                                                                                                                                </div>
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                        <div>
                                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">NO MESIN / TYPE</p>
                                                                                                                                            <p class="text-[11px] font-black mkt-text uppercase italic">{{ $techData['no_mesin'] ?? '-' }} / {{ $techData['type_mesin'] ?? '-' }}</p>
                                                                                                                                        </div>
                                                                                                                                        <div>
                                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">GAUGE / INCH</p>
                                                                                                                                            <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['gauge_inch'] ?? '-' }}</p>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-span-2">
                                                                                                                                        </div>
                                                                                                                                        <div>
                                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">JML FEEDER</p>
                                                                                                                                            <p class="text-[11px] font-black mkt-text uppercase mkt-text">{{ $techData['jml_feeder'] ?? '0' }} <span class="text-[8px] mkt-text-muted">FDR</span></p>
                                                                                                                                        </div>
                                                                                                                                        <div>
                                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">JML JARUM</p>
                                                                                                                                            <p class="text-[11px] font-black mkt-text uppercase mkt-text">{{ $techData['jml_jarum'] ?? '0' }} <span class="text-[8px] mkt-text-muted">JRM</span></p>
                                                                                                                                        </div>
                                                                                                                                    </div>
                                                                                                                                </div>

                                                                                                                                {{-- II. HASIL PRODUKSI --}}
                                                                                                                                <div class="space-y-4">
                                                                                                                                    <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">II. HASIL PRODUKSI GREIGE</p>
                                                                                                                                    <div class="grid grid-cols-2 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                                                                                        <div>
                                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">LEBAR / GRAMASI</p>
                                                                                                                                            <p class="text-[11px] font-black mkt-text uppercase italic">{{ $techData['lebar'] ?? '-' }} x {{ $techData['gramasi'] ?? '-' }}</p>
                                                                                                                                        </div>
                                                                                                                                        <div>
                                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TOTAL OUTPUT</p>
                                                                                                                                            <p class="text-[11px] font-black text-emerald-500 uppercase">{{ $log->roll }} ROLL</p>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-span-2 bg-emerald-500/5 p-4 rounded-xl border border-emerald-500/10">
                                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1 italic">ACTUAL WEIGHT (KG)</p>
                                                                                                                                            <p class="text-2xl font-black text-emerald-500 italic">{{ $log->kg }} <span class="text-[10px] mkt-text-muted">KG</span></p>
                                                                                                                                        </div>
                                                                                                                                    </div>
                                                                                                                                </div>

                                                                                                                                {{-- III. PENGGUNAAN BENANG & YL --}}
                                                                                                                                <div class="space-y-4">
                                                                                                                                    <p class="text-[9px] font-black mkt-text-muted uppercase tracking-[0.3em] border-l-4 border-mkt-border pl-3">III. PENGGUNAAN BENANG & YL</p>
                                                                                                                                    <div class="grid grid-cols-2 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                                                                                        @foreach(range(1, 4) as $i)
                                                                                                                                            @if(!empty($techData['benang_' . $i]))
                                                                                                                                                <div class="space-y-2 border-l border-red-500/20 pl-4 group/item hover:bg-white/5 p-2 rounded-lg transition-all">
                                                                                                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-0.5">SLOT {{ $i }}</p>
                                                                                                                                                    <p class="text-[10px] font-black mkt-text uppercase leading-tight truncate">
                                                                                                                                                        {{ $techData['benang_' . $i] }}
                                                                                                                                                    </p>
                                                                                                                                                    @if(!empty($techData['benang_' . $i . '_lot']))
                                                                                                                                                        <p class="text-[9px] font-black text-slate-500 uppercase leading-none">LOT: {{ $techData['benang_' . $i . '_lot'] }}</p>
                                                                                                                                                    @endif
                                                                                                                                                    @if(!empty($techData['benang_' . $i . '_percent']))
                                                                                                                                                        <p class="text-[11px] font-black mkt-text tracking-tighter">{{ $techData['benang_' . $i . '_percent'] }}</p>
                                                                                                                                                    @endif
                                                                                                                                                    <div class="pt-2 border-t border-white/5">
                                                                                                                                                        <p class="text-[7px] mkt-text-muted font-bold uppercase">YL</p>
                                                                                                                                                        <p class="text-[11px] font-bold mkt-text tracking-tighter">{{ $techData['yl_' . $i] ?? '-' }}</p>
                                                                                                                                                    </div>
                                                                                                                                                </div>
                                                                                                                                            @endif
                                                                                                                                        @endforeach
                                                                                                                                    </div>
                                                                                                                                </div>

                                                                                                                                {{-- IV. NOTE & TARGET --}}
                                                                                                                                <div class="space-y-4">
                                                                                                                                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-[0.3em] border-l-4 border-slate-500 pl-3">IV. NOTE & TARGET</p>
                                                                                                                                    <div class="grid grid-cols-3 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                                                                                        <div class="col-span-2">
                                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OPERATOR NOTES / KETERANGAN</p>
                                                                                                                                            <div class="bg-black/20 p-4 rounded-xl border border-white/5">
                                                                                                                                                <p class="text-[10px] font-bold text-slate-400 italic leading-relaxed">"{{ $techData['note'] ?? 'Tidak ada catatan tambahan dari operator.' }}"</p>
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                        <div class="text-right flex flex-col justify-center">
                                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TARGET PRODUKSI / DAY</p>
                                                                                                                                            <p class="text-2xl font-black text-emerald-400 italic tracking-tighter">{{ $techData['produksi_per_day'] ?? '0' }} <span class="text-sm mkt-text-muted">KG</span></p>
                                                                                                                                        </div>
                                                                                                                                    </div>
                                                                                                                                </div>
                                                                                                                            </div>
                                                                                                                        @elseif($expandedLog === 'stenter')
                                                            @php
                                                                $preset = $techData['preset'] ?? [];
                                                                $drying = $techData['drying'] ?? [];
                                                                $finishing = $techData['finishing'] ?? [];

                                                                $parameters = [
                                                                    ['label' => 'TANGGAL', 'key' => 'tanggal', 'suffix' => '', 'format' => 'date'],
                                                                    ['label' => 'TEMPERATURE', 'key' => 'suhu', 'suffix' => ' °C', 'format' => 'string'],
                                                                    ['label' => 'SPEED', 'key' => 'speed', 'suffix' => ' m/min', 'format' => 'string'],
                                                                    ['label' => 'PADDER', 'key' => 'padder', 'suffix' => '', 'format' => 'string'],
                                                                    ['label' => 'RANGKA', 'key' => 'rangka', 'suffix' => '', 'format' => 'string'],
                                                                    ['label' => 'OVERFEED A', 'key' => 'overfeed_a', 'suffix' => '', 'format' => 'string'],
                                                                    ['label' => 'OVERFEED B', 'key' => 'overfeed_b', 'suffix' => '', 'format' => 'string'],
                                                                    ['label' => 'FAN/BLOWER', 'key' => 'fan', 'suffix' => '', 'format' => 'string'],
                                                                    ['label' => 'DELIVERY SPEED', 'key' => 'delivery', 'suffix' => ' m/min', 'format' => 'string'],
                                                                    ['label' => 'FOLDING SPEED', 'key' => 'folding', 'suffix' => ' m/min', 'format' => 'string'],
                                                                    ['label' => 'CHEMICAL 1', 'key' => 'chem1', 'suffix' => '', 'format' => 'string'],
                                                                    ['label' => 'CHEMICAL 2', 'key' => 'chem2', 'suffix' => '', 'format' => 'string'],
                                                                    ['label' => 'HASIL LEBAR', 'key' => 'lebar', 'suffix' => ' cm', 'format' => 'string'],
                                                                    ['label' => 'HASIL GRAMASI', 'key' => 'gramasi', 'suffix' => ' gsm', 'format' => 'string'],
                                                                    ['label' => 'SHRINKAGE', 'key' => 'shrinkage', 'suffix' => ' %', 'format' => 'string'],
                                                                ];
                                                            @endphp
                                                            <div class="space-y-10 animate-in fade-in duration-700">
                                                                {{-- I. GLOBAL IDENTITAS & WAKTU --}}
                                                                <div class="space-y-4">
                                                                    <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">I. IDENTITAS GLOBAL</p>
                                                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                        <div>
                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OPERATOR</p>
                                                                            <p class="text-[11px] font-black text-slate-100 uppercase">{{ $techData['operator'] ?? $log->operator->name ?? $log->operator ?? '-' }}</p>
                                                                        </div>
                                                                        <div>
                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">NO MESIN</p>
                                                                            <p class="text-[11px] font-black mkt-text uppercase italic">{{ $techData['no_mesin'] ?? $log->machine_no ?? '-' }}</p>
                                                                        </div>
                                                                        <div>
                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TANGGAL SUBMIT</p>
                                                                            <p class="text-[11px] font-black text-slate-100">{{ !empty($log->created_at) ? date('d/m/Y H:i', strtotime($log->created_at)) : '-' }}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                {{-- II. TECHNICAL PARAMETERS LIST --}}
                                                                <div class="space-y-6">
                                                                    <p class="text-[9px] font-black mkt-text-muted uppercase tracking-[0.3em] border-l-4 border-mkt-border pl-3">II. TECHNICAL PARAMETERS LOG</p>

                                                                    <!-- Table Header -->
                                                                    <div class="bg-white/5 p-6 rounded-[2rem] border border-white/5 shadow-md">
                                                                        <div class="hidden md:grid grid-cols-12 gap-6 font-black text-[10px] mkt-text-muted uppercase tracking-wider text-center">
                                                                            <div class="col-span-3 text-left pl-4">Parameter</div>
                                                                            <div class="col-span-3 text-amber-500">Preset</div>
                                                                            <div class="col-span-3 text-sky-500">Drying</div>
                                                                            <div class="col-span-3 text-emerald-500">Finishing</div>
                                                                        </div>
                                                                        <div class="block md:hidden font-black text-[10px] mkt-text-muted uppercase text-center">
                                                                            Detail Parameter (Preset / Drying / Finishing)
                                                                        </div>
                                                                    </div>

                                                                    <!-- Parameter list -->
                                                                    <div class="space-y-4">
                                                                        @foreach($parameters as $param)
                                                                            @php
                                                                                $pVal = $preset[$param['key']] ?? '';
                                                                                $dVal = $drying[$param['key']] ?? '';
                                                                                $fVal = $finishing[$param['key']] ?? '';

                                                                                if ($param['format'] === 'date') {
                                                                                    $pDisplay = !empty($pVal) ? date('d/m/Y', strtotime($pVal)) : '-';
                                                                                    $dDisplay = !empty($dVal) ? date('d/m/Y', strtotime($dVal)) : '-';
                                                                                    $fDisplay = !empty($fVal) ? date('d/m/Y', strtotime($fVal)) : '-';
                                                                                } else {
                                                                                    $pDisplay = ($pVal !== '') ? $pVal . $param['suffix'] : '-';
                                                                                    $dDisplay = ($dVal !== '') ? $dVal . $param['suffix'] : '-';
                                                                                    $fDisplay = ($fVal !== '') ? $fVal . $param['suffix'] : '-';
                                                                                }
                                                                            @endphp

                                                                            <div class="bg-white/5 border border-white/5 p-6 rounded-[2rem] shadow-sm hover:border-brand-500/50 transition-all duration-300">
                                                                                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                                                                    <!-- Parameter Title -->
                                                                                    <div class="col-span-3 flex items-center gap-3">
                                                                                        <div class="w-2 h-2 rounded-full bg-brand-500"></div>
                                                                                        <span class="text-xs font-black uppercase mkt-text tracking-wider font-semibold">
                                                                                            {{ $param['label'] }}
                                                                                        </span>
                                                                                    </div>

                                                                                    <!-- Preset Value -->
                                                                                    <div class="col-span-3 space-y-1 text-center">
                                                                                        <span class="block md:hidden text-[9px] font-black uppercase text-amber-500">Preset</span>
                                                                                        <span class="text-xs font-black text-amber-400 italic">
                                                                                            {{ $pDisplay }}
                                                                                        </span>
                                                                                    </div>

                                                                                    <!-- Drying Value -->
                                                                                    <div class="col-span-3 space-y-1 text-center">
                                                                                        <span class="block md:hidden text-[9px] font-black uppercase text-sky-500">Drying</span>
                                                                                        <span class="text-xs font-black text-sky-400 italic">
                                                                                            {{ $dDisplay }}
                                                                                        </span>
                                                                                    </div>

                                                                                    <!-- Finishing Value -->
                                                                                    <div class="col-span-3 space-y-1 text-center">
                                                                                        <span class="block md:hidden text-[9px] font-black uppercase text-emerald-500">Finishing</span>
                                                                                        <span class="text-xs font-black text-emerald-400 italic">
                                                                                            {{ $fDisplay }}
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @elseif($expandedLog === 'tumbler')
                                                                                                            <div class="space-y-10 animate-in fade-in duration-700">
                                                                                                                {{-- I. IDENTITAS & WAKTU --}}
                                                                                                                <div class="space-y-4">
                                                                                                                    <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">I. IDENTITAS & WAKTU (TUMBLER DRY)</p>
                                                                                                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OPERATOR</p>
                                                                                                                            <p class="text-[11px] font-black text-slate-100 uppercase">{{ $techData['operator'] ?? '-' }}</p>
                                                                                                                        </div>
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TANGGAL</p>
                                                                                                                            <p class="text-[11px] font-black text-slate-100">{{ !empty($techData['tanggal']) ? date('d/m/Y', strtotime($techData['tanggal'])) : '-' }}</p>
                                                                                                                        </div>
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">NO MESIN</p>
                                                                                                                            <p class="text-[11px] font-black text-slate-100 uppercase italic">{{ $techData['no_mesin'] ?? '-' }}</p>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </div>

                                                                                                                {{-- II. PARAMETER MESIN --}}
                                                                                                                <div class="space-y-4">
                                                                                                                    <p class="text-[9px] font-black mkt-text-muted uppercase tracking-[0.3em] border-l-4 border-mkt-border pl-3">II. PARAMETER SETTING MESIN</p>
                                                                                                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TEMPERATURE</p>
                                                                                                                            <p class="text-[11px] font-black mkt-text">{{ $techData['suhu'] ?? '-' }}°C</p>
                                                                                                                        </div>
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">STEAM INJECT</p>
                                                                                                                            <p class="text-[11px] font-black mkt-text">{{ $techData['steam_inject'] ?? '-' }}</p>
                                                                                                                        </div>
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HOTWIND</p>
                                                                                                                            <p class="text-[11px] font-black mkt-text">{{ $techData['hotwind'] ?? '-' }}</p>
                                                                                                                        </div>
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">COLDWIND</p>
                                                                                                                            <p class="text-[11px] font-black mkt-text">{{ $techData['coldwind'] ?? '-' }}</p>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </div>

                                                                                                                {{-- III. HASIL FISIK & OUTCOME --}}
                                                                                                                <div class="space-y-4">
                                                                                                                    <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">III. HASIL FISIK & OUTCOME</p>
                                                                                                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">LEBAR</p>
                                                                                                                            <p class="text-[11px] font-black text-emerald-400 italic">{{ $techData['lebar'] ?? '-' }}</p>
                                                                                                                        </div>
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">GRAMASI</p>
                                                                                                                            <p class="text-[11px] font-black text-emerald-400 italic">{{ $techData['gramasi'] ?? '-' }}</p>
                                                                                                                        </div>
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">SHRINKAGE (V x H)</p>
                                                                                                                            <p class="text-[11px] font-black text-emerald-400 italic">{{ $techData['shrinkage'] ?? '-' }}</p>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        @elseif($expandedLog === 'fleece')
                                                                                                            <div class="space-y-10 animate-in fade-in duration-700">
                                                                                                                {{-- I. GLOBAL INFO --}}
                                                                                                                <div class="space-y-4">
                                                                                                                    <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">I. IDENTITAS MESIN (FLEECE)</p>
                                                                                                                    <div class="bg-white/5 p-6 rounded-2xl">
                                                                                                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">NO MESIN</p>
                                                                                                                        <p class="text-[11px] font-black text-slate-100 uppercase italic">{{ $techData['no_mesin'] ?? '-' }}</p>
                                                                                                                    </div>
                                                                                                                </div>

                                                                                                                {{-- II. DETIL PROSES SIDE-BY-SIDE --}}
                                                                                                                <div class="space-y-4">
                                                                                                                    <p class="text-[9px] font-black mkt-text-muted uppercase tracking-[0.3em] border-l-4 border-mkt-border pl-3">II. DETAIL PARAMETER PROSES (SIDE-BY-SIDE)</p>
                                                                                                                    <div class="overflow-x-auto">
                                                                                                                        <div class="min-w-[800px] space-y-4">
                                                                                                                            {{-- TABLE HEADER --}}
                                                                                                                            <div class="grid grid-cols-12 gap-6 bg-white/5 px-6 py-4 rounded-2xl items-center text-[10px] font-black uppercase tracking-widest text-slate-400">
                                                                                                                                <div class="col-span-3">PARAMETER</div>
                                                                                                                                <div class="col-span-3 text-center text-amber-400">RAISING</div>
                                                                                                                                <div class="col-span-3 text-center text-sky-400">BRUSHING</div>
                                                                                                                                <div class="col-span-3 text-center text-emerald-400">SHEARING</div>
                                                                                                                            </div>

                                                                                                                            @php
                                                                                                                                $raising = $techData['raising'] ?? [];
                                                                                                                                $brushing = $techData['brushing'] ?? [];
                                                                                                                                $shearing = $techData['shearing'] ?? [];

                                                                                                                                $fleeceParams = [
                                                                                                                                    ['label' => 'OPERATOR', 'r' => $raising['operator'] ?? '-', 'b' => $brushing['operator'] ?? '-', 's' => $shearing['operator'] ?? '-'],
                                                                                                                                    ['label' => 'TANGGAL', 'r' => !empty($raising['tanggal']) ? date('d/m/Y', strtotime($raising['tanggal'])) : '-', 'b' => !empty($brushing['tanggal']) ? date('d/m/Y', strtotime($brushing['tanggal'])) : '-', 's' => !empty($shearing['tanggal']) ? date('d/m/Y', strtotime($shearing['tanggal'])) : '-'],
                                                                                                                                    ['label' => 'STANDAR BULU', 'r' => $raising['standar_bulu'] ?? '-', 'b' => $brushing['standar_bulu'] ?? '-', 's' => '-'],
                                                                                                                                    ['label' => 'SPEED / CLOTH SPEED', 'r' => $raising['speed'] ?? '-', 'b' => $brushing['cloth_speed'] ?? '-', 's' => $shearing['speed'] ?? '-'],
                                                                                                                                    ['label' => 'CLOTH OUT', 'r' => $raising['cloth_out'] ?? '-', 'b' => $brushing['cloth_out'] ?? '-', 's' => $shearing['cloth_out'] ?? '-'],
                                                                                                                                    ['label' => 'BEND PIN', 'r' => $raising['bend_pin'] ?? '-', 'b' => '-', 's' => '-'],
                                                                                                                                    ['label' => 'STRIGHT PIN', 'r' => $raising['stright_pin'] ?? '-', 'b' => '-', 's' => '-'],
                                                                                                                                    ['label' => 'RPM DRUM', 'r' => $raising['rpm_drum'] ?? '-', 'b' => $brushing['rpm_drum'] ?? '-', 's' => '-'],
                                                                                                                                    ['label' => 'DRUM BRUSH', 'r' => $raising['drum_brush'] ?? '-', 'b' => '-', 's' => '-'],
                                                                                                                                    ['label' => 'LEFT BRUSH', 'r' => '-', 'b' => $brushing['left_brush'] ?? '-', 's' => '-'],
                                                                                                                                    ['label' => 'RIGHT BRUSH', 'r' => '-', 'b' => $brushing['right_brush'] ?? '-', 's' => '-'],
                                                                                                                                    ['label' => 'TENSION 1/2/3', 'r' => '-', 'b' => $brushing['tension'] ?? '-', 's' => '-'],
                                                                                                                                    ['label' => 'EXPENDING', 'r' => '-', 'b' => '-', 's' => $shearing['expending'] ?? '-'],
                                                                                                                                    ['label' => 'SHEAR', 'r' => '-', 'b' => '-', 's' => $shearing['shear'] ?? '-'],
                                                                                                                                    ['label' => 'LEBAR / GRAMASI', 'r' => $raising['lebar_gsm'] ?? '-', 'b' => $brushing['lebar_gramasi'] ?? '-', 's' => $shearing['lebar_gramasi'] ?? '-'],
                                                                                                                                ];
                                                                                                                            @endphp

                                                                                                                            <div class="space-y-3">
                                                                                                                                @foreach($fleeceParams as $param)
                                                                                                                                    <div class="grid grid-cols-12 gap-6 bg-white/[0.02] border border-white/5 hover:border-brand-500/30 px-6 py-5 rounded-[1.5rem] items-center transition-all duration-300">
                                                                                                                                        <div class="col-span-3">
                                                                                                                                            <span class="text-xs font-black text-slate-300 tracking-wider">
                                                                                                                                                {{ $param['label'] }}
                                                                                                                                            </span>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-span-3 text-center">
                                                                                                                                            <span class="text-xs font-black text-amber-400 italic">
                                                                                                                                                {{ $param['r'] }}
                                                                                                                                            </span>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-span-3 text-center">
                                                                                                                                            <span class="text-xs font-black text-sky-400 italic">
                                                                                                                                                {{ $param['b'] }}
                                                                                                                                            </span>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-span-3 text-center">
                                                                                                                                            <span class="text-xs font-black text-emerald-400 italic">
                                                                                                                                                {{ $param['s'] }}
                                                                                                                                            </span>
                                                                                                                                        </div>
                                                                                                                                    </div>
                                                                                                                                @endforeach
                                                                                                                            </div>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        @elseif($expandedLog === 'pengujian')
                                                                                                            <div class="space-y-10 animate-in fade-in duration-700">
                                                                                                                {{-- I. IDENTITAS & WAKTU --}}
                                                                                                                <div class="space-y-4">
                                                                                                                    <p class="text-[9px] font-black text-cyan-500 uppercase tracking-[0.3em] border-l-4 border-cyan-500 pl-3">I. IDENTITAS & WAKTU (PENGUJIAN QC & LAB)</p>
                                                                                                                    <div class="grid grid-cols-2 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OPERATOR PENGUJI</p>
                                                                                                                            <p class="text-[11px] font-black text-slate-100 uppercase italic">{{ $techData['operator'] ?? '-' }}</p>
                                                                                                                        </div>
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TANGGAL UJI</p>
                                                                                                                            <p class="text-[11px] font-black text-slate-100 italic">{{ !empty($techData['tanggal']) ? date('d/m/Y', strtotime($techData['tanggal'])) : '-' }}</p>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </div>

                                                                                                                {{-- II. HASIL PENGUJIAN FISIK --}}
                                                                                                                <div class="space-y-4">
                                                                                                                    <p class="text-[9px] font-black mkt-text-muted uppercase tracking-[0.3em] border-l-4 border-mkt-border pl-3">II. HASIL PENGUJIAN FISIK</p>
                                                                                                                    <div class="grid grid-cols-2 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HASIL LEBAR</p>
                                                                                                                            <p class="text-[11px] font-black mkt-text italic">{{ $techData['lebar'] ?? '-' }} cm</p>
                                                                                                                        </div>
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HASIL GRAMASI</p>
                                                                                                                            <p class="text-[11px] font-black mkt-text italic">{{ $techData['gramasi'] ?? '-' }} gsm</p>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </div>

                                                                                                                {{-- III. METRIK PENGUJIAN KUALITAS --}}
                                                                                                                <div class="space-y-4">
                                                                                                                    <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">III. METRIK PENGUJIAN KUALITAS</p>
                                                                                                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">SHRINKAGE</p>
                                                                                                                            <p class="text-[11px] font-black text-emerald-400 italic">{{ $techData['shrinkage'] ?? '-' }}%</p>
                                                                                                                        </div>
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">SPIRALITY</p>
                                                                                                                            <p class="text-[11px] font-black text-emerald-400 italic">{{ $techData['spirality'] ?? '-' }}%</p>
                                                                                                                        </div>
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">SKEWNESS</p>
                                                                                                                            <p class="text-[11px] font-black text-emerald-400 italic">{{ $techData['skewness'] ?? '-' }}%</p>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        @elseif($expandedLog === 'qe')
                                                                                                            <div class="space-y-10 animate-in fade-in duration-700">
                                                                                                                {{-- I. IDENTITAS & OPERATOR --}}
                                                                                                                <div class="space-y-4">
                                                                                                                    <p class="text-[9px] font-black mkt-text-muted uppercase tracking-[0.3em] border-l-4 border-mkt-border pl-3">I. IDENTITAS KAIN & OPERATOR (QE)</p>
                                                                                                                    <div class="grid grid-cols-2 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OPERATOR QE</p>
                                                                                                                            <p class="text-[11px] font-black text-slate-100 uppercase italic">{{ $techData['operator'] ?? '-' }}</p>
                                                                                                                        </div>
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">FABRIC NAME</p>
                                                                                                                            <p class="text-[11px] font-black text-slate-100 uppercase italic">{{ $techData['fabric_name'] ?? '-' }}</p>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </div>

                                                                                                                {{-- II. HASIL VALIDASI FISIK --}}
                                                                                                                <div class="space-y-4">
                                                                                                                    <p class="text-[9px] font-black text-cyan-500 uppercase tracking-[0.3em] border-l-4 border-cyan-500 pl-3">II. HASIL VALIDASI FISIK (FINAL SPECIFICATION)</p>
                                                                                                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">LEBAR</p>
                                                                                                                            <p class="text-[11px] font-black text-cyan-400 italic">{{ $techData['lebar'] ?? '-' }} cm</p>
                                                                                                                        </div>
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">GRAMASI</p>
                                                                                                                            <p class="text-[11px] font-black text-cyan-400 italic">{{ $techData['gramasi'] ?? '-' }} gsm</p>
                                                                                                                        </div>
                                                                                                                        <div>
                                                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">SHRINKAGE</p>
                                                                                                                            <p class="text-[11px] font-black text-cyan-400 italic">{{ $techData['shrinkage'] ?? '-' }}%</p>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </div>

                                                                                                                {{-- III. REKOMENDASI & CATATAN (NOTE) --}}
                                                                                                                <div class="space-y-4">
                                                                                                                    <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">III. REKOMENDASI & CATATAN (NOTE)</p>
                                                                                                                    <div class="bg-white/5 p-6 rounded-2xl">
                                                                                                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">FINAL NOTE</p>
                                                                                                                        <p class="text-[11px] font-black text-emerald-400 uppercase italic">{{ $techData['note'] ?? '-' }}</p>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        @else
                                                                                                             {{-- GENERIC LAYOUT IN MARKETING STYLE --}}
                                                                                                                            <div class="space-y-4">
                                                                                                                                <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">I. HASIL PRODUKSI ({{ strtoupper($expandedLog) }})</p>
                                                                                                                                <div class="grid grid-cols-2 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                                                                                    <div>
                                                                                                                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">ACTUAL WEIGHT</p>
                                                                                                                                        <p class="text-[11px] font-black text-emerald-500 italic">{{ $log->kg }} KG</p>
                                                                                                                                    </div>
                                                                                                                                    <div>
                                                                                                                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">ACTUAL ROLL</p>
                                                                                                                                        <p class="text-[11px] font-black text-emerald-500 italic">{{ $log->roll }} ROLL</p>
                                                                                                                                    </div>
                                                                                                                                    <div>
                                                                                                                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">MACHINE NO</p>
                                                                                                                                        <p class="text-[11px] font-black mkt-text italic">{{ $log->machine_no ?? 'M-01' }}</p>
                                                                                                                                    </div>
                                                                                                                                </div>

                                                                                                                                <p class="text-[9px] font-black mkt-text-muted uppercase tracking-[0.3em] border-l-4 border-mkt-border pl-3">II. TECHNICAL DATA</p>
                                                                                                                                <div class="grid grid-cols-2 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                                                                                    @foreach($techData as $key => $value)
                                                                                                                                        @if(!in_array($key, ['kg', 'roll', 'machine_no', 'operator', 'nama_input', 'updated_at', 'created_at', 'preset', 'drying', 'finishing', 'raising', 'brushing', 'shearing']))
                                                                                                                                            <div>
                                                                                                                                                <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">{{ strtoupper(str_replace('_', ' ', $key)) }}</p>
                                                                                                                                                <p class="text-[11px] font-black mkt-text italic uppercase leading-none">{{ $value ?? '-' }}</p>
                                                                                                                                            </div>
                                                                                                                                        @endif
                                                                                                                                    @endforeach
                                                                                                                                </div>
                                                                                                                            </div>
                                                                                                        @endif
                                                                                                                    </div>
                                                    @endif
                                                @endif
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL EXPORT REPORT --}}
    @if($showExportModal)
        <div class="fixed inset-0 z-[200] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" wire:click="$set('showExportModal', false)"></div>
            <div class="relative bg-white dark:bg-slate-900 w-full max-w-4xl rounded-[2rem] sm:rounded-[3rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
                
                {{-- Modal Header --}}
                <div class="p-6 md:p-8 border-b mkt-border flex justify-between items-center bg-slate-50 dark:bg-slate-900">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-emerald-500 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/30">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black italic mkt-text dark:text-white uppercase tracking-tighter leading-none">EXPORT CENTER</h3>
                            <p class="text-[10px] font-bold mkt-text-muted dark:text-slate-400 uppercase tracking-widest mt-1">Pilih Cakupan Laporan</p>
                        </div>
                    </div>
                    <button wire:click="$set('showExportModal', false)" class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-red-500 hover:text-white dark:hover:text-white transition-all">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {{-- Modal Body - Export Options --}}
                <div class="p-6 md:p-8 bg-slate-50/50 dark:bg-slate-950">
                    <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-6 italic flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Export akan menggunakan filter tanggal: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                        
                        {{-- 1. MASTER PIPELINE --}}
                        <div class="mkt-surface border mkt-border p-6 rounded-3xl hover:border-amber-500 transition-colors group flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center font-black text-xs">M</span>
                                    <h4 class="text-sm font-black mkt-text dark:text-slate-200 uppercase tracking-wider">SELURUHNYA (MASTER PIPELINE)</h4>
                                </div>
                                <p class="text-[10px] font-bold mkt-text-muted dark:text-slate-400 leading-relaxed mb-6">Mencakup data dari awal Marketing hingga produk selesai di Packing.</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <button wire:click="export('pdf', 'master')" class="w-full py-2.5 rounded-xl border-2 border-red-500 text-red-500 font-black text-[9px] uppercase tracking-wider hover:bg-red-500 hover:text-white transition-all">PDF</button>
                                <button wire:click="export('excel', 'master')" class="w-full py-2.5 rounded-xl bg-emerald-600 text-white font-black text-[9px] uppercase tracking-wider hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/20">EXCEL (XLS)</button>
                            </div>
                        </div>

                        {{-- 2. MARKETING --}}
                        <div class="mkt-surface border mkt-border p-6 rounded-3xl hover:border-blue-500 transition-colors group flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center font-black text-xs">MK</span>
                                    <h4 class="text-sm font-black mkt-text dark:text-slate-200 uppercase tracking-wider">DIVISI MARKETING</h4>
                                </div>
                                <p class="text-[10px] font-bold mkt-text-muted dark:text-slate-400 leading-relaxed mb-6">Data input awal pesanan, target produksi, dan spesifikasi R&D.</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <button disabled class="w-full py-2.5 rounded-xl border-2 border-slate-300 dark:border-slate-700 text-slate-400 dark:text-slate-600 font-black text-[9px] uppercase tracking-wider cursor-not-allowed opacity-50">PDF (N/A)</button>
                                <button wire:click="export('excel', 'marketing')" class="w-full py-2.5 rounded-xl bg-emerald-600 text-white font-black text-[9px] uppercase tracking-wider hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/20">EXCEL (XLS)</button>
                            </div>
                        </div>

                        {{-- 3. KNITTING --}}
                        <div class="mkt-surface border mkt-border p-6 rounded-3xl hover:border-brand-600 transition-colors group flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="w-8 h-8 rounded-lg bg-brand-600/10 text-brand-600 flex items-center justify-center font-black text-xs">KN</span>
                                    <h4 class="text-sm font-black mkt-text dark:text-slate-200 uppercase tracking-wider">DIVISI KNITTING (RAJUT)</h4>
                                </div>
                                <p class="text-[10px] font-bold mkt-text-muted dark:text-slate-400 leading-relaxed mb-6">Riwayat rajut kain, aktual berat/roll, dan detail mesin rajut.</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <button wire:click="export('pdf', 'rajut')" class="w-full py-2.5 rounded-xl border-2 border-red-500 text-red-500 font-black text-[9px] uppercase tracking-wider hover:bg-red-500 hover:text-white transition-all">PDF</button>
                                <button wire:click="export('excel', 'rajut')" class="w-full py-2.5 rounded-xl bg-emerald-600 text-white font-black text-[9px] uppercase tracking-wider hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/20">EXCEL (XLS)</button>
                            </div>
                        </div>

                        {{-- 4. DYEING & FINISHING --}}
                        <div class="mkt-surface border mkt-border p-6 rounded-3xl hover:border-indigo-500 transition-colors group flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center font-black text-xs">DF</span>
                                    <h4 class="text-sm font-black mkt-text dark:text-slate-200 uppercase tracking-wider">DIVISI DYEING (WARNA & FINISHING)</h4>
                                </div>
                                <p class="text-[10px] font-bold mkt-text-muted dark:text-slate-400 leading-relaxed mb-6">Seluruh inputan pasca rajut (Warna, Stenter, Compactor, Fleece, dll).</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <button wire:click="export('pdf', 'warna')" class="w-full py-2.5 rounded-xl border-2 border-red-500 text-red-500 font-black text-[9px] uppercase tracking-wider hover:bg-red-500 hover:text-white transition-all">PDF</button>
                                <button wire:click="export('excel', 'warna')" class="w-full py-2.5 rounded-xl bg-emerald-600 text-white font-black text-[9px] uppercase tracking-wider hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/20">EXCEL (XLS)</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endif
</div>