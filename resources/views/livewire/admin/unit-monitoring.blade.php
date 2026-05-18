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

    public $showExportMenu = false;
    public $selectedIdentifier = null;
    public $showTrackingModal = false;
    public $trackingLogs = [];
    public $trackingData = null;
    public $expandedLog = null;

    public function mount() {
        $this->startDate = date('Y-m-d');
        $this->endDate = date('Y-m-d');
    }

    public function updated($property) {
        if (in_array($property, ['search', 'viewMode', 'selectedUnit', 'startDate', 'endDate'])) {
            $this->resetPage();
        }
    }

    public function with() {
        return [
            'orders' => \App\Models\MarketingOrder::with(['productionActivities', 'processingBy'])
                ->whereNotNull('processing_by')
                ->when($this->viewMode === 'RAJUT', function($q) {
                    $q->where('status', 'knitting');
                })
                ->when($this->viewMode === 'WARNA', function($q) {
                    $q->whereIn('status', ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'finishing', 'stenter', 'tumbler', 'fleece']);
                })
                ->when($this->viewMode === 'SELESAI', function($q) {
                    $q->where('status', 'finished');
                })
                ->when($this->selectedUnit !== 'SEMUA', function($q) {
                    $q->where('kelompok_kain', $this->selectedUnit);
                })
                ->when($this->search, function($q) {
                    $q->where(function($sq) {
                        $sq->where('art_no', 'like', "%{$this->search}%")
                           ->orWhere('sap_no', 'like', "%{$this->search}%");
                    });
                })
                ->when($this->startDate, function($q) {
                    $q->where(function($sq) {
                        $sq->whereHas('productionActivities', fn($ssq) => $ssq->whereDate('created_at', '>=', $this->startDate))
                           ->orWhere(function($ssq) {
                               if ($this->viewMode === 'RAJUT') {
                                   $ssq->where('status', 'knitting');
                               } else {
                                   $ssq->whereIn('status', ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'finishing', 'stenter', 'tumbler', 'fleece']);
                               }
                           });
                    });
                })
                ->when($this->endDate, function($q) {
                    $q->where(function($sq) {
                        $sq->whereHas('productionActivities', fn($ssq) => $ssq->whereDate('created_at', '<=', $this->endDate))
                           ->orWhere(function($ssq) {
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


    public function export($format)
    {
        $this->showExportMenu = false; // Tutup menu setelah klik
        
        // Redirect ke route export sesuai format
        return redirect()->route('admin.export', [
            'format' => $format, // 'pdf' atau 'excel'
            'mode' => $this->viewMode,
            'start' => $this->startDate,
            'end' => $this->endDate,
            'unit' => $this->selectedUnit
        ]);
    }

    public function openTracking($identifier) {
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
                <h1 class="text-4xl md:text-6xl font-black text-indigo-600 uppercase tracking-tighter leading-none">
                    UNIT <span class="mkt-text">MONITORING</span>
                </h1>
                <p class="mkt-text-muted font-bold uppercase text-[10px] md:text-xs mt-3 tracking-[0.2em] italic opacity-70">
                    MODE: MONITORING {{ $viewMode }} | LIVE DUNIATEX RND
                </p>
                <div class="mt-3 flex items-center gap-3">
                    <div class="mkt-surface-alt mkt-text px-4 py-1.5 rounded-xl shadow-lg border mkt-border">
                        <p class="real-time-clock text-sm font-black tracking-widest leading-none">00:00:00</p>
                    </div>
                    <p class="real-time-date text-[11px] font-bold mkt-text-muted uppercase tracking-widest italic"></p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row flex-wrap gap-4 items-stretch sm:items-center w-full xl:w-auto">
                {{-- STUCK ORDERS CARD --}}
                <div class="bg-red-500/10 border border-red-500/20 p-4 rounded-3xl flex items-center gap-4 px-6 shadow-sm transition-all hover:bg-red-500/20">
                    <div class="text-red-500 animate-pulse text-2xl font-black">⚠️</div>
                    <div>
                        <p class="text-[9px] font-black text-red-500 uppercase tracking-widest leading-tight">Stuck Orders<br>(>2 Days)</p>
                        <h4 class="text-2xl font-black mkt-text leading-none mt-1">{{ $stuckOrders }}</h4>
                    </div>
                </div>

                {{-- SEARCH INPUT --}}
                <div class="relative w-full sm:w-64 group">
                    <input type="text" wire:model.live="search" placeholder="CARI ARTIKEL / SAP..." 
                        class="w-full mkt-input border-2 mkt-border rounded-3xl pl-12 pr-6 py-4 text-[10px] font-black focus:border-indigo-600 focus:ring-0 transition-all placeholder:text-slate-400 italic uppercase">
                    <span class="absolute left-5 top-1/2 -translate-y-1/2 mkt-text-muted opacity-50 group-focus-within:opacity-100 group-focus-within:text-indigo-600 transition-colors">🔍</span>
                </div>

                <div class="relative flex-grow sm:flex-grow-0">
                    <button wire:click="$toggle('showExportMenu')" 
                        class="w-full flex items-center justify-center gap-3 bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-4 rounded-3xl font-black uppercase italic shadow-xl shadow-emerald-900/20 transition-all transform hover:scale-[1.02] text-[10px] tracking-widest">
                        GENERATE REPORT
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    {{-- DROPDOWN MENU --}}
                    @if($showExportMenu)
                        <div class="absolute right-0 mt-3 w-full sm:w-64 mkt-surface border mkt-border rounded-3xl shadow-2xl z-50 overflow-hidden backdrop-blur-md">
                            <button wire:click="export('pdf')" class="w-full text-left px-6 py-4 text-[10px] font-black italic hover:mkt-surface-alt mkt-text transition-colors flex items-center gap-3 border-b mkt-border">
                                <span class="p-2 bg-red-500/10 text-red-500 rounded-lg">PDF</span> DOWNLOAD DOCUMENT
                            </button>
                            <button wire:click="export('excel')" class="w-full text-left px-6 py-4 text-[10px] font-black italic hover:mkt-surface-alt mkt-text transition-colors flex items-center gap-3">
                                <span class="p-2 bg-emerald-500/10 text-emerald-500 rounded-lg">XLS</span> DOWNLOAD SPREADSHEET
                            </button>
                        </div>
                    @endif
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
                class="flex-grow sm:flex-grow-0 px-12 py-5 rounded-3xl font-black uppercase italic transition-all tracking-widest text-xs {{ $viewMode === 'RAJUT' ? 'bg-indigo-600 shadow-xl shadow-indigo-900/30 text-white' : 'mkt-surface-alt mkt-text-muted border mkt-border hover:mkt-text' }}">
                MONITORING RAJUT
            </button>
            <button wire:click="$set('viewMode', 'WARNA')" 
                class="flex-grow sm:flex-grow-0 px-12 py-5 rounded-3xl font-black uppercase italic transition-all tracking-widest text-xs {{ $viewMode === 'WARNA' ? 'bg-emerald-600 shadow-xl shadow-emerald-900/30 text-white' : 'mkt-surface-alt mkt-text-muted border mkt-border hover:mkt-text' }}">
                MONITORING WARNA
            </button>
            <button wire:click="$set('viewMode', 'SELESAI')" 
                class="flex-grow sm:flex-grow-0 px-12 py-5 rounded-3xl font-black uppercase italic transition-all tracking-widest text-xs {{ $viewMode === 'SELESAI' ? 'bg-blue-600 shadow-xl shadow-blue-900/30 text-white' : 'mkt-surface-alt mkt-text-muted border mkt-border hover:mkt-text' }}">
                ORDER SELESAI
            </button>
        </div>


        {{-- TABLE DATA --}}
        <div class="mkt-surface rounded-[2.5rem] border mkt-border shadow-2xl overflow-hidden transition-all">
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
                            <tr class="bg-indigo-500/5 border-l-4 border-indigo-600 hover:bg-indigo-500/10 transition-colors border-b mkt-border italic group">
                                <td class="px-8 py-7">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-2 w-2 relative">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                                        </span>
                                        <div>
                                            <span class="text-indigo-600 block text-sm font-black tracking-tighter">{{ $wip->art_no }}</span>
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
                                        <span class="text-blue-500 text-[10px] font-black uppercase mt-1 block">{{ $wip->rnd_gramasi_greige ?? $wip->target_gramasi }} GSM</span>
                                    @else
                                        <span class="text-emerald-500 block text-xs font-black uppercase italic">{{ $wip->warna }}</span>
                                        <span class="mkt-text-muted block text-[10px] font-bold mt-1 uppercase">{{ $wip->konstruksi_greige }}</span>
                                    @endif
                                </td>
                                <td class="px-8 py-7 text-center">
                                    @if($viewMode === 'RAJUT')
                                        <span class="text-blue-600 dark:text-blue-400 block font-black text-sm tracking-tighter">{{ $wip->rnd_mesin_rajut ?? 'TBD' }}</span>
                                        <span class="text-[9px] mkt-text-muted font-black uppercase tracking-widest mt-1 opacity-50">{{ $wip->kelompok_kain }}</span>
                                    @else
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-emerald-400 block font-black text-xs">{{ $wip->target_lebar }}" / {{ $wip->target_gramasi }}</span>
                                            <span class="text-[8px] mkt-text-muted font-black uppercase tracking-tighter">{{ $wip->handfeel ?? '-' }} | {{ $wip->belah_bulat }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-8 py-7 text-center">
                                    <span class="text-red-500 animate-pulse font-black text-[10px] tracking-widest uppercase">0</span>
                                </td>
                                <td class="px-8 py-7 text-center">
                                    <span class="text-red-500 animate-pulse font-black text-[10px] tracking-widest uppercase">ONGOING</span>
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
                                        <span class="text-[7px] mkt-text-muted font-black uppercase opacity-50 mt-1">STATUS: {{ strtoupper($wip->status) }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-7 text-center">
                                    <button wire:click="openTracking('{{ $wip->art_no }}')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-xl text-[9px] font-black uppercase transition-all shadow-lg shadow-indigo-600/20">
                                        DETAIL 👁️
                                    </button>
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
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-xl px-4 py-6">
                <div class="mkt-surface border mkt-border w-full max-w-7xl rounded-[4rem] overflow-hidden shadow-[0_0_100px_rgba(0,0,0,0.5)] animate-in fade-in zoom-in duration-500 max-h-[95vh] flex flex-col">
                    
                    {{-- HEADER MODAL --}}
                    <div class="p-10 mkt-surface-alt flex justify-between items-center border-b mkt-border backdrop-blur-md">
                        <div class="flex items-center gap-6">
                            <div class="h-16 w-16 bg-indigo-600 rounded-3xl flex items-center justify-center shadow-lg shadow-indigo-600/40">
                                <span class="text-white text-3xl font-black italic">!</span>
                            </div>
                            <div>
                                <h2 class="mkt-text font-black italic uppercase text-4xl leading-none tracking-tighter">
                                    MONITORING <span class="text-indigo-600">{{ $viewMode === 'WARNA' || in_array($trackingData->status, ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'finishing', 'stenter', 'tumbler', 'fleece']) ? 'WARNA' : 'RAJUT' }}</span>
                                </h2>
                                <p class="mkt-text-muted text-[10px] mt-2 font-black uppercase tracking-[0.3em] flex items-center gap-2">
                                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    NO ARTIKEL: {{ $trackingData->art_no }} | LEGACY SAP: {{ $trackingData->sap_no }} | INDUSTRIAL TRACEABILITY LIVE
                                </p>
                            </div>
                        </div>
                        <button wire:click="$set('showTrackingModal', false)" 
                            class="bg-white dark:bg-indigo-600 mkt-text-muted dark:text-white px-8 py-4 rounded-3xl text-[10px] font-black uppercase transition-all border mkt-border dark:border-none shadow-xl flex items-center gap-3 group">
                            CLOSE DOCUMENT <span class="group-hover:rotate-90 transition-transform">✕</span>
                        </button>
                    </div>

                    <div class="p-10 overflow-y-auto custom-scrollbar flex-1 min-h-0">
                        <div class="grid grid-cols-12 gap-8">
                            
                            {{-- COLUMN LEFT: SPECIFICATIONS (17 POINTS) --}}
                            <div class="col-span-12 lg:col-span-8 space-y-8">
                                
                                {{-- CARD 1: IDENTITY & SALES --}}
                                <div class="grid grid-cols-3 gap-6">
                                    <div class="col-span-1 mkt-surface-alt p-6 rounded-[2.5rem] border mkt-border shadow-inner">
                                        <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-3">ARTIKEL NO</p>
                                        <p class="text-xl font-black mkt-text tracking-tight uppercase leading-none">{{ $trackingData->art_no ?? '-' }}</p>
                                    </div>
                                    <div class="col-span-1 mkt-surface-alt p-6 rounded-[2.5rem] border mkt-border shadow-inner">
                                        <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-3">WARNA KAIN</p>
                                        <p class="text-xl font-black text-emerald-500 tracking-tight uppercase leading-none italic">{{ $trackingData->warna ?? '-' }}</p>
                                    </div>
                                    <div class="col-span-1 mkt-surface-alt p-6 rounded-[2.5rem] border mkt-border shadow-inner">
                                        <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-3">PELANGGAN</p>
                                        <p class="text-xl font-black mkt-text tracking-tight uppercase leading-none">{{ $trackingData->pelanggan ?? '-' }}</p>
                                    </div>
                                    <div class="col-span-1 mkt-surface-alt p-6 rounded-[2.5rem] border mkt-border shadow-inner">
                                        <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-3">SALES / MKT</p>
                                        <p class="text-xl font-black text-red-500 tracking-tight uppercase leading-none italic">{{ $trackingData->mkt ?? '-' }}</p>
                                    </div>
                                </div>

                                {{-- CARD 2: GREIGE & MACHINE SPECS --}}
                                <div class="mkt-surface p-8 rounded-[3.5rem] border-2 border-white/5 shadow-2xl relative overflow-hidden group">
                                    <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:opacity-20 transition-opacity">
                                        <svg class="w-24 h-24 mkt-text" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                                    </div>
                                    <h4 class="text-xs font-black mkt-text italic uppercase tracking-widest mb-8 flex items-center gap-3">
                                        <span class="w-6 h-[2px] bg-red-600"></span> GREIGE TECHNICAL SPECIFICATIONS
                                    </h4>
                                    <div class="grid grid-cols-4 gap-10">
                                        <div>
                                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-2">Konstruksi Greige</p>
                                            <p class="text-sm font-black mkt-text italic leading-tight">{{ $trackingData->konstruksi_greige ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-2">Gramasi Greige</p>
                                            <p class="text-2xl font-black text-blue-500 tracking-tighter leading-none">{{ $trackingData->rnd_gramasi_greige ?? '-' }} <span class="text-[10px] opacity-50 uppercase">GSM</span></p>
                                        </div>
                                        <div>
                                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-2">Kelompok Mesin</p>
                                            <p class="text-sm font-black mkt-text italic uppercase leading-tight">{{ $trackingData->kelompok_kain ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-2">Material Utama</p>
                                            <p class="text-sm font-black text-emerald-400 italic uppercase leading-tight">
                                                {{ $trackingData->material ?? '-' }}
                                                @if($trackingData->benang_percent)
                                                    <span class="text-red-500">({{ $trackingData->benang_percent }}%)</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- CARD 3: FINISHING TARGETS (GRID 2x2) --}}
                                <div class="grid grid-cols-2 gap-8">
                                    <div class="mkt-surface p-8 rounded-[3.5rem] border mkt-border shadow-xl">
                                        <h4 class="text-[9px] font-black mkt-text-muted uppercase tracking-widest mb-6">🎯 Finishing Targets</h4>
                                        <div class="grid grid-cols-2 gap-8">
                                            <div>
                                                <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">Target Lebar</p>
                                                <p class="text-xl font-black mkt-text italic leading-none">{{ $trackingData->target_lebar ?? '-' }}"</p>
                                            </div>
                                            <div>
                                                <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">Target Gramasi</p>
                                                <p class="text-xl font-black mkt-text italic leading-none">{{ $trackingData->target_gramasi ?? '-' }} GSM</p>
                                            </div>
                                            <div>
                                                <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">Belah / Bulat</p>
                                                <p class="text-xl font-black text-emerald-500 italic leading-none uppercase">{{ $trackingData->belah_bulat ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">Handfeel</p>
                                                <p class="text-xl font-black text-blue-500 italic leading-none uppercase">{{ $trackingData->handfeel ?? '-' }}</p>
                                            </div>
                                            <div class="col-span-2">
                                                <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">Treatment Khusus</p>
                                                <p class="text-xl font-black text-red-500 italic leading-none uppercase">{{ $trackingData->treatment_khusus ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- CARD 4: ACTUAL PRODUCTION (RAJUT) --}}
                                    @php $knitLog = $trackingLogs->where('division_name', 'knitting')->first(); @endphp
                                    <div class="mkt-surface p-8 rounded-[3.5rem] border-2 {{ $knitLog ? 'border-emerald-500/20 bg-emerald-500/5' : 'border-slate-800' }} shadow-xl relative overflow-hidden">
                                        @if(!$knitLog)
                                            <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center z-10">
                                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] italic">WAITING FOR PRODUCTION...</p>
                                            </div>
                                        @endif
                                        <h4 class="text-[9px] font-black {{ $knitLog ? 'text-emerald-500' : 'mkt-text-muted' }} uppercase tracking-widest mb-6 flex items-center justify-between">
                                            <span>⚡ Actual Knitting Result</span>
                                            @if($knitLog) <span class="bg-emerald-500 text-white px-2 py-0.5 rounded text-[7px]">DONE</span> @endif
                                        </h4>
                                        <div class="grid grid-cols-3 gap-4 text-center">
                                            <div>
                                                <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">Mesin Rajut</p>
                                                <p class="text-xl font-black mkt-text italic leading-none">{{ $knitLog->machine_no ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">ROLL</p>
                                                <p class="text-2xl font-black text-emerald-500 leading-none">{{ $knitLog->roll ?? '0' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">KG</p>
                                                <p class="text-2xl font-black text-emerald-500 leading-none">{{ $knitLog->kg ?? '0' }}</p>
                                            </div>
                                        </div>
                                        @if($knitLog)
                                        <div class="mt-6 pt-4 border-t border-white/5 flex justify-between items-center text-[8px] font-bold mkt-text-muted italic uppercase">
                                            <span>OP: {{ $knitLog->operator->name ?? 'N/A' }}</span>
                                            <span class="text-emerald-500">{{ $knitLog->created_at->format('H:i | d/m') }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- CARD 5: NEXT STEP & LOGISTICS --}}
                                <div class="grid grid-cols-3 gap-6">
                                    <div class="col-span-1 bg-blue-600 p-8 rounded-[3rem] shadow-xl shadow-blue-900/30">
                                        <p class="text-[8px] text-blue-200 font-black uppercase tracking-widest mb-2">KIRIM KE UNIT</p>
                                        <h3 class="text-3xl font-black text-white italic leading-none tracking-tighter">DPF 3</h3>
                                    </div>
                                    <div class="col-span-2 mkt-surface p-8 rounded-[3rem] border mkt-border shadow-xl">
                                        <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-2 italic">KETERANGAN ARTIKEL / NOTES</p>
                                        <p class="text-xs font-black mkt-text italic uppercase leading-relaxed">{{ $trackingData->keterangan_artikel ?? 'PROSES SESUAI STANDAR PRODUKSI RND.' }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- COLUMN RIGHT: TIMELINE & MILESTONES --}}
                            <div class="col-span-12 lg:col-span-4 flex flex-col space-y-8">
                                <div class="mkt-surface p-8 rounded-[4rem] border mkt-border shadow-2xl flex-1 flex flex-col">
                                    <h3 class="text-xs font-black mkt-text italic uppercase tracking-[0.2em] mb-10 flex items-center gap-4">
                                        <span class="w-8 h-[2px] bg-red-600"></span> TIMELINE TRACKING
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
                                            
                                            <div class="relative pl-16 pb-12 last:pb-0 group">
                                                {{-- STEP INDICATOR --}}
                                                <div class="absolute left-0 top-0 flex flex-col items-center">
                                                    @if($isDone)
                                                        <div class="h-10 w-10 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-[0_0_20px_rgba(79,70,229,0.4)] z-10 transition-all group-hover:scale-110">
                                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                        </div>
                                                    @elseif($isCurrent)
                                                        <div class="h-10 w-10 bg-indigo-500 rounded-2xl flex items-center justify-center shadow-[0_0_20px_rgba(79,70,229,0.4)] z-10 animate-pulse border-2 border-white/20">
                                                            <span class="text-white text-xs font-black italic">{{ $index + 1 }}</span>
                                                        </div>
                                                    @else
                                                        <div class="h-10 w-10 bg-slate-800 border-2 border-slate-700 rounded-2xl flex items-center justify-center z-10 opacity-30">
                                                            <span class="mkt-text-muted text-[10px] font-black italic">{{ $index + 1 }}</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- STEP CONTENT --}}
                                                <div class="flex items-start justify-between gap-4">
                                                    <div class="space-y-1">
                                                        <h4 class="text-xs font-black {{ $isDone ? 'mkt-text' : ($isCurrent ? 'text-indigo-600' : 'mkt-text-muted opacity-30') }} italic uppercase tracking-widest leading-none">
                                                            {{ $step['name'] }}
                                                        </h4>
                                                        <p class="text-[8px] font-bold {{ $isDone ? 'mkt-text-muted' : ($isCurrent ? 'text-indigo-400/50' : 'mkt-text-muted opacity-20') }} uppercase tracking-tighter">
                                                            {{ $step['label'] }}
                                                        </p>
                                                        
                                                        @if($log && $log->operator)
                                                            <div class="flex items-center gap-2 mt-3 mkt-surface-alt px-3 py-1.5 rounded-lg border mkt-border">
                                                                <div class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
                                                                <p class="text-[7px] mkt-text-muted font-black uppercase tracking-widest">
                                                                    OP: <span class="mkt-text">{{ $operatorActual }}</span>
                                                                </p>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    {{-- ACTIONS & TIME --}}
                                                    <div class="flex flex-col items-end gap-2 shrink-0">
                                                        @if($isDone)
                                                            <div class="bg-emerald-500/10 border border-emerald-500/10 px-3 py-2 rounded-xl text-right">
                                                                <p class="text-[7px] text-emerald-500 font-black uppercase tracking-widest mb-0.5 opacity-60">COMPLETED</p>
                                                                <p class="text-[10px] text-emerald-400 font-black italic leading-none">
                                                                    {{ ($step['div'] === 'marketing') ? $trackingData->created_at->format('d/m/Y H:i') : $log->created_at->format('d/m/Y H:i') }}
                                                                </p>
                                                            </div>
                                                            
                                                            <button wire:click="toggleDetail('{{ $step['div'] }}')" 
                                                                class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-xl border mkt-border transition-all shadow-lg group/btn shadow-indigo-600/20">
                                                                <span class="text-[8px] text-white font-black uppercase tracking-widest">LIHAT HASIL</span>
                                                                <svg class="w-3 h-3 text-white/70 group-hover/btn:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                            </button>
                                                        @elseif($isCurrent)
                                                            <div class="bg-indigo-600/10 border border-indigo-600/30 px-4 py-2 rounded-xl text-center">
                                                                <p class="text-[9px] text-indigo-500 font-black animate-pulse tracking-[0.2em]">ON PROGRESS</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- NESTED POPUP FOR LOG DETAIL --}}
                                    @if($expandedLog)
                                        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-md px-4 animate-in fade-in duration-300">
                                            <div class="mkt-surface border mkt-border w-full max-w-2xl rounded-[3rem] shadow-[0_0_100px_rgba(0,0,0,0.8)] animate-in zoom-in slide-in-from-bottom-10 duration-500 relative overflow-hidden flex flex-col max-h-[80vh]">
                                                
                                                {{-- MODAL HEADER --}}
                                                <div class="p-8 border-b mkt-border flex justify-between items-center bg-slate-900/50">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-12 h-12 {{ $expandedLog == 'marketing' ? 'bg-red-600' : 'bg-emerald-600' }} rounded-2xl flex items-center justify-center shadow-lg shadow-black/40">
                                                            <span class="text-white text-lg font-black italic">{{ $expandedLog == 'marketing' ? 'MO' : 'OP' }}</span>
                                                        </div>
                                                        <div>
                                                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-[0.3em]">
                                                                {{ $expandedLog == 'marketing' ? 'MARKETING SPECIFICATIONS' : 'ACTUAL PRODUCTION DATA' }}
                                                            </p>
                                                            <h3 class="text-xl font-black mkt-text italic uppercase">
                                                                {{ strtoupper(str_replace('_', ' ', $expandedLog)) }} RESULT
                                                            </h3>
                                                        </div>
                                                    </div>
                                                    <button wire:click="toggleDetail('{{ $expandedLog }}')" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-white hover:bg-red-600 transition-all">
                                                        ✕
                                                    </button>
                                                </div>

                                                {{-- MODAL CONTENT --}}
                                                <div class="p-8 overflow-y-auto custom-scrollbar">
                                                    @if($expandedLog == 'marketing')
                                                        <div class="space-y-10 custom-scrollbar pr-4">
                                                            {{-- I. IDENTITAS ORDER --}}
                                                            <div class="space-y-4">
                                                                <p class="text-[9px] font-black text-red-500 uppercase tracking-[0.3em] border-l-4 border-red-500 pl-3">I. IDENTITAS ORDER</p>
                                                                <div class="grid grid-cols-2 gap-6 bg-white/5 p-5 rounded-2xl">
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
                                                            <div class="space-y-4">
                                                                <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">II. KLASIFIKASI & MATERIAL</p>
                                                                <div class="grid grid-cols-2 gap-6 bg-white/5 p-5 rounded-2xl">
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
                                                                                <span class="text-red-500">({{ $trackingData->benang_percent }}%)</span>
                                                                            @endif
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            {{-- III. SPESIFIKASI TEKNIS --}}
                                                            <div class="space-y-4">
                                                                <p class="text-[9px] font-black text-blue-500 uppercase tracking-[0.3em] border-l-4 border-blue-500 pl-3">III. SPESIFIKASI TEKNIS</p>
                                                                <div class="grid grid-cols-2 gap-6 bg-white/5 p-5 rounded-2xl">
                                                                    <div>
                                                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TARGET GRAMASI</p>
                                                                        <p class="text-[11px] font-black text-blue-400">{{ $trackingData->target_gramasi }} GSM</p>
                                                                    </div>
                                                                    <div>
                                                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HANDFEEL</p>
                                                                        <p class="text-[11px] font-black mkt-text uppercase">{{ $trackingData->handfeel ?? '-' }}</p>
                                                                    </div>
                                                                    <div class="col-span-2">
                                                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TREATMENT KHUSUS</p>
                                                                        <p class="text-[11px] font-black text-red-500 uppercase italic">{{ $trackingData->treatment_khusus }}</p>
                                                                    </div>
                                                                    <div class="col-span-2">
                                                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">KONSTRUKSI GREIGE</p>
                                                                        <p class="text-[11px] font-black mkt-text italic uppercase">{{ $trackingData->konstruksi_greige }}</p>
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
                                                            <div class="space-y-4">
                                                                <p class="text-[9px] font-black text-orange-500 uppercase tracking-[0.3em] border-l-4 border-orange-500 pl-3">IV. QUANTITY & KETERANGAN</p>
                                                                <div class="grid grid-cols-4 gap-6 bg-white/5 p-5 rounded-2xl">
                                                                    <div>
                                                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">QUANTITY (ROLL)</p>
                                                                        <p class="text-[11px] font-black mkt-text uppercase">{{ $trackingData->roll_target }} RL</p>
                                                                    </div>
                                                                    <div>
                                                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">QUANTITY (KG)</p>
                                                                        <p class="text-[11px] font-black mkt-text uppercase">{{ $trackingData->kg_target }} KG</p>
                                                                    </div>
                                                                    <div class="col-span-2">
                                                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">KETERANGAN ARTIKEL</p>
                                                                        <p class="text-[11px] font-black mkt-text italic uppercase">{{ $trackingData->keterangan_artikel ?? '-' }}</p>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            {{-- V. DATA R&D --}}
                                                            <div class="space-y-4">
                                                                <p class="text-[9px] font-black text-purple-500 uppercase tracking-[0.3em] border-l-4 border-purple-500 pl-3">V. DATA R&D</p>
                                                                <div class="grid grid-cols-3 gap-6 bg-white/5 p-5 rounded-2xl">
                                                                    <div>
                                                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">GRAMASI GREIGE</p>
                                                                        <p class="text-[11px] font-black text-purple-400 italic">{{ $trackingData->rnd_gramasi_greige }} GSM</p>
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

                                                            {{-- VI. WORKFLOW & PROSES PRODUKSI --}}
                                                            <div class="space-y-4">
                                                                <p class="text-[9px] font-black text-yellow-500 uppercase tracking-[0.3em] border-l-4 border-yellow-500 pl-3">VI. WORKFLOW & PROSES PRODUKSI</p>
                                                                <div class="flex flex-wrap gap-4 bg-white/5 p-6 rounded-2xl">
                                                                    @php
                                                                        $workflows = [
                                                                            'req_compactor' => 'COMPACTOR',
                                                                            'req_heat_setting' => 'HEAT SETTING',
                                                                            'req_stenter' => 'STENTER',
                                                                            'req_tumbler' => 'TUMBLER',
                                                                            'req_fleece' => 'FLEECE'
                                                                        ];
                                                                    @endphp
                                                                    <div class="bg-red-600/20 border border-red-600/40 px-4 py-2 rounded-xl flex items-center gap-2">
                                                                        <span class="text-white text-[9px] font-black italic">KNITTING</span>
                                                                    </div>
                                                                    <div class="text-slate-600 self-center">→</div>
                                                                    <div class="bg-red-600/20 border border-red-600/40 px-4 py-2 rounded-xl flex items-center gap-2">
                                                                        <span class="text-white text-[9px] font-black italic">DYEING</span>
                                                                    </div>
                                                                    @foreach($workflows as $key => $label)
                                                                        @if($trackingData->$key)
                                                                            <div class="text-slate-600 self-center">→</div>
                                                                            <div class="bg-emerald-500/20 border border-emerald-500/40 px-4 py-2 rounded-xl flex items-center gap-2">
                                                                                <span class="text-white text-[9px] font-black italic">{{ $label }}</span>
                                                                            </div>
                                                                        @endif
                                                                    @endforeach
                                                                    <div class="text-slate-600 self-center">→</div>
                                                                    <div class="bg-red-600/20 border border-red-600/40 px-4 py-2 rounded-xl flex items-center gap-2">
                                                                        <span class="text-white text-[9px] font-black italic">QE/LAB</span>
                                                                    </div>
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
                                                                            <p class="text-xl font-black text-white italic tracking-tighter">{{ strtoupper($operatorActual) }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-right">
                                                                        <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">MACHINE UNIT</p>
                                                                        <p class="text-3xl font-black text-red-600 italic leading-none">{{ $log->machine_no ?? 'M-01' }}</p>
                                                                    </div>
                                                                </div>

                                                                @if($expandedLog === 'knitting')
                                                                    {{-- HIGH FIDELITY LAYOUT FOR KNITTING (UNIFIED WITH MARKETING STYLE) --}}
                                                                    <div class="space-y-10 animate-in fade-in duration-700">
                                                                        
                                                                        {{-- I. SPESIFIKASI MESIN --}}
                                                                        <div class="space-y-4">
                                                                            <p class="text-[9px] font-black text-blue-500 uppercase tracking-[0.3em] border-l-4 border-blue-500 pl-3">I. IDENTITAS & SPESIFIKASI MESIN</p>
                                                                            <div class="grid grid-cols-2 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                                <div class="col-span-2 border-r mkt-border pr-6">
                                                                                    <div class="grid grid-cols-2 gap-4">
                                                                                        <div>
                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">LEGACY SAP ID</p>
                                                                                            <p class="text-[11px] font-black text-blue-400 italic opacity-60">{{ $techData['sap_no'] ?? ($trackingData->sap_no ?? '-') }}</p>
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
                                                                                    <p class="text-[11px] font-black mkt-text uppercase text-blue-400">{{ $techData['jml_feeder'] ?? '0' }} <span class="text-[8px] mkt-text-muted">FDR</span></p>
                                                                                </div>
                                                                                <div>
                                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">JML JARUM</p>
                                                                                    <p class="text-[11px] font-black mkt-text uppercase text-blue-400">{{ $techData['jml_jarum'] ?? '0' }} <span class="text-[8px] mkt-text-muted">JRM</span></p>
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
                                                                            <p class="text-[9px] font-black text-red-500 uppercase tracking-[0.3em] border-l-4 border-red-500 pl-3">III. PENGGUNAAN BENANG & YL</p>
                                                                            <div class="grid grid-cols-2 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                                @foreach(range(1, 4) as $i)
                                                                                    @if(!empty($techData['benang_'.$i]))
                                                                                        <div class="space-y-2 border-l border-red-500/20 pl-4 group/item hover:bg-white/5 p-2 rounded-lg transition-all">
                                                                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-0.5">SLOT {{ $i }}</p>
                                                                                            <p class="text-[10px] font-black mkt-text uppercase leading-tight truncate">
                                                                                                {{ $techData['benang_'.$i] }}
                                                                                            </p>
                                                                                            @if(!empty($techData['benang_'.$i.'_percent']))
                                                                                                <p class="text-[11px] font-black text-red-500 tracking-tighter">{{ $techData['benang_'.$i.'_percent'] }}</p>
                                                                                            @endif
                                                                                            <div class="pt-2 border-t border-white/5">
                                                                                                <p class="text-[7px] mkt-text-muted font-bold uppercase">YL</p>
                                                                                                <p class="text-[11px] font-bold text-blue-400 tracking-tighter">{{ $techData['yl_'.$i] ?? '-' }}</p>
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
                                                                                    <p class="text-2xl font-black text-white italic tracking-tighter">{{ $techData['produksi_per_day'] ?? '0' }} <span class="text-sm mkt-text-muted">KG</span></p>
                                                                                </div>
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
                                                                                <p class="text-[11px] font-black text-emerald-500 italic">{{ $log->roll }} RL</p>
                                                                            </div>
                                                                            <div>
                                                                                <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">MACHINE NO</p>
                                                                                <p class="text-[11px] font-black text-red-500 italic">{{ $log->machine_no ?? 'M-01' }}</p>
                                                                            </div>
                                                                        </div>

                                                                        <p class="text-[9px] font-black text-blue-500 uppercase tracking-[0.3em] border-l-4 border-blue-500 pl-3">II. TECHNICAL DATA</p>
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

                                    {{-- FINAL STATUS BADGE --}}
                                    <div class="mt-10 p-6 bg-slate-900/50 rounded-[2.5rem] border mkt-border text-center">
                                        <p class="text-[7px] mkt-text-muted font-black uppercase tracking-[0.4em] mb-2 italic">CURRENT PRODUCTION STATE</p>
                                        <h5 class="text-lg font-black text-red-600 italic uppercase tracking-tighter">
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
</div>