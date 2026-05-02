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
    public $selectedSap = null;
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
            'activities' => ProductionActivity::with(['marketingOrder', 'operator'])
                ->when($this->viewMode === 'RAJUT', fn($q) => $q->where('division_name', 'knitting'))
                ->when($this->viewMode === 'WARNA', fn($q) => $q->whereIn('division_name', ['dyeing', 'finishing']))
                ->when($this->selectedUnit !== 'SEMUA', function($q) {
                    $q->whereHas('marketingOrder', fn($sq) => $sq->where('kelompok_kain', $this->selectedUnit));
                })
                ->when($this->startDate, fn($q) => $q->whereDate('created_at', '>=', $this->startDate))
                ->when($this->endDate, fn($q) => $q->whereDate('created_at', '<=', $this->endDate))
                ->latest()
                ->paginate(10),
            
            // Statistik Stuck Orders (> 2 Hari) dari modul Marketing
            'stuckOrders' => \App\Models\MarketingOrder::where('status', 'knitting')
                ->where('created_at', '<=', now()->subDays(2))
                ->count()
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

    public function openTracking($sap) {
        $this->selectedSap = $sap;
        // Ambil detail spesifikasi marketing
        $this->trackingData = \App\Models\MarketingOrder::where('sap_no', $sap)->first();
        
        // Ambil jejak produksi lengkap dengan data operator
        $this->trackingLogs = ProductionActivity::with('operator')
            ->whereHas('marketingOrder', fn($q) => $q->where('sap_no', $sap))
            ->orderBy('created_at', 'asc')
            ->get();
                
        $this->showTrackingModal = true;
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
                <h1 class="text-4xl md:text-6xl font-black text-red-600 uppercase tracking-tighter leading-none">
                    UNIT <span class="mkt-text">MONITORING</span>
                </h1>
                <p class="mkt-text-muted font-bold uppercase text-[10px] md:text-xs mt-3 tracking-[0.2em] italic opacity-70">
                    MODE: MONITORING {{ $viewMode }} | LIVE DUNIATEX RND
                </p>
                <div class="mt-3 flex items-center gap-3">
                    <div class="bg-slate-900 dark:bg-slate-800 text-white px-4 py-1.5 rounded-xl shadow-lg border border-white/5">
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
                class="flex-grow sm:flex-grow-0 px-12 py-5 rounded-3xl font-black uppercase italic transition-all tracking-widest text-xs {{ $viewMode === 'RAJUT' ? 'bg-red-600 shadow-xl shadow-red-900/30 text-white' : 'mkt-surface-alt mkt-text-muted border mkt-border hover:mkt-text' }}">
                MONITORING RAJUT
            </button>
            <button wire:click="$set('viewMode', 'WARNA')" 
                class="flex-grow sm:flex-grow-0 px-12 py-5 rounded-3xl font-black uppercase italic transition-all tracking-widest text-xs {{ $viewMode === 'WARNA' ? 'bg-emerald-600 shadow-xl shadow-emerald-900/30 text-white' : 'mkt-surface-alt mkt-text-muted border mkt-border hover:mkt-text' }}">
                MONITORING WARNA
            </button>
        </div>

        {{-- TABLE DATA --}}
        <div class="mkt-surface rounded-[2.5rem] border mkt-border shadow-2xl overflow-hidden transition-all">
            <div class="overflow-x-auto">
                <table class="w-full text-left font-bold min-w-[1000px]">
                    <thead>
                        <tr class="mkt-surface-alt text-[9px] font-black uppercase mkt-text-muted tracking-[0.2em] border-b mkt-border">
                            @if($viewMode === 'RAJUT')
                                <th class="px-8 py-6">SAP / ART</th>
                                <th class="px-8 py-6">PELANGGAN / MKT</th>
                                <th class="px-8 py-6">KONSTRUKSI / GRAMASI</th>
                                <th class="px-8 py-6">MESIN / KELOMPOK</th>
                                <th class="px-8 py-6">LEBAR / TARGET</th>
                                <th class="px-8 py-6 text-center">HASIL PRODUKSI</th>
                                <th class="px-8 py-6 text-right">STATUS</th>
                            @else
                                <th class="px-8 py-6">SAP / ART</th>
                                <th class="px-8 py-6">MKT & PELANGGAN</th>
                                <th class="px-8 py-6">KONSTRUKSI</th>
                                <th class="px-8 py-6">WARNA / HANDFEEL</th>
                                <th class="px-8 py-6">TARGET (L/G)</th>
                                <th class="px-8 py-6 text-center">HASIL PRODUKSI</th>
                                <th class="px-8 py-6 text-right">TIMELINE</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                        @forelse($activities as $act)
                            <tr class="hover:mkt-surface-alt/50 transition-colors border-b mkt-border last:border-0 italic">
                                {{-- KOLOM COMMON --}}
                                <td class="px-8 py-7 cursor-pointer group" wire:click="openTracking('{{ $act->marketingOrder->sap_no }}')">
                                    <span class="text-red-600 block text-sm font-black tracking-tighter group-hover:underline">
                                        #{{ $act->marketingOrder->sap_no }}
                                    </span>
                                    <span class="mkt-text mt-1 block text-[11px] font-bold uppercase opacity-60 group-hover:opacity-100">
                                        {{ $act->marketingOrder->art_no }}
                                    </span>
                                </td>

                                @if($viewMode === 'RAJUT')
                                    {{-- DATA KHUSUS RAJUT --}}
                                    <td class="px-8 py-7">
                                        <span class="mkt-text block text-sm tracking-tight">{{ $act->marketingOrder->pelanggan }}</span>
                                        <span class="text-[9px] mkt-text-muted font-black uppercase tracking-widest mt-1 opacity-50">MKT: {{ $act->marketingOrder->marketing_name ?? 'DD' }}</span>
                                    </td>
                                    <td class="px-8 py-7">
                                        <span class="mkt-text-muted block text-[11px] font-bold">{{ $act->marketingOrder->konstruksi_greige }}</span>
                                        <span class="text-blue-500 text-[10px] font-black uppercase mt-1 block">{{ $act->marketingOrder->gramasi_greige }} GSM</span>
                                    </td>
                                    <td class="px-8 py-7">
                                        <span class="text-blue-600 dark:text-blue-400 block font-black text-sm tracking-tighter">{{ $act->machine_no ?? 'MESIN 01' }}</span>
                                        <span class="text-[9px] mkt-text-muted font-black uppercase tracking-widest mt-1 opacity-50">KEL: {{ $act->marketingOrder->kelompok_kain }}</span>
                                    </td>
                                    <td class="px-8 py-7">
                                        <span class="mkt-text block text-[11px] font-bold leading-tight">{{ $act->marketingOrder->target_lebar }} "</span>
                                        <span class="mkt-text-muted text-[10px] font-black uppercase block mt-1">{{ $act->marketingOrder->target_gramasi }} GSM</span>
                                    </td>
                                @else
                                    {{-- DATA KHUSUS WARNA --}}
                                    <td class="px-8 py-7">
                                        <span class="mkt-text block text-sm tracking-tight">{{ $act->marketingOrder->pelanggan }}</span>
                                    </td>
                                    <td class="px-8 py-7 text-[11px] font-bold mkt-text-muted">
                                        {{ $act->marketingOrder->konstruksi_greige }}
                                    </td>
                                    <td class="px-8 py-7">
                                        <span class="text-emerald-600 dark:text-emerald-400 block font-black text-sm tracking-tight">{{ $act->marketingOrder->warna ?? 'TBD' }}</span>
                                        <span class="text-[9px] mkt-text-muted font-black uppercase tracking-widest mt-1 opacity-50">HANDFEEL: {{ $act->marketingOrder->handfeel ?? '-' }}</span>
                                    </td>
                                    <td class="px-8 py-7 text-[11px] font-bold mkt-text-muted">
                                        {{ $act->marketingOrder->target_lebar }} / {{ $act->marketingOrder->target_gramasi }}
                                    </td>
                                @endif

                                {{-- HASIL PRODUKSI (COMMON) --}}
                                <td class="px-8 py-7 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-emerald-500 font-black text-xl tracking-tighter leading-none">{{ $act->kg }} KG</span>
                                        <span class="text-[10px] mkt-text-muted font-black uppercase tracking-widest mt-1 opacity-50">{{ $act->roll }} ROLL</span>
                                    </div>
                                </td>

                                {{-- TIMELINE --}}
                                <td class="px-8 py-7 text-right">
                                    @if($viewMode === 'RAJUT')
                                        <span class="px-4 py-2 bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20 rounded-xl text-[9px] font-black uppercase tracking-widest shadow-sm inline-block">
                                            EST. KIRIM: {{ $act->created_at->addDays(1)->format('d/m') }}
                                        </span>
                                    @else
                                        <span class="px-4 py-2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 rounded-xl text-[9px] font-black uppercase tracking-widest shadow-sm inline-block">
                                            DEADLINE: {{ $act->marketingOrder->tgl_selesai ?? 'TBD' }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-24 text-center mkt-text-muted font-black uppercase text-xs tracking-[0.3em] opacity-30 italic">TIDAK ADA DATA {{ $viewMode }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Modal Tracking SAP --}}
            @if($showTrackingModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-md px-4">
                <div class="mkt-surface border mkt-border w-full max-w-6xl rounded-[3.5rem] overflow-hidden shadow-2xl animate-in fade-in zoom-in duration-300">
                    
                    {{-- 1. HEADER MODAL --}}
                    <div class="p-10 bg-slate-800/50 flex justify-between items-center border-b border-slate-700">
                            <div>
                                <h2 class="mkt-text font-black italic uppercase text-4xl leading-none">Industrial Order <span class="text-red-600">Detail</span></h2>
                                <p class="text-slate-400 text-[10px] mt-2 font-bold uppercase tracking-widest">SAP NO: {{ $selectedSap }} | PELANGGAN: {{ $trackingData->pelanggan ?? '-' }}</p>
                            </div>
                            <button wire:click="$set('showTrackingModal', false)" class="bg-slate-700 hover:bg-red-600 p-4 rounded-2xl text-white transition-all shadow-xl">✕ CLOSE</button>
                        </div>

                        <div class="p-6 md:p-10 grid grid-cols-12 gap-6 md:gap-8 overflow-y-auto max-h-[85vh]">
                            {{-- SISI KIRI: DATA TEKNIS (Identity & Specs dari Marketing) --}}
                            <div class="col-span-12 lg:col-span-7 space-y-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6">    
                                    {{-- I. Identity & Sales --}}
                                    <div class="mkt-surface p-6 rounded-[2.5rem] border-l-4 border-red-600 shadow-inner">
                                        <h4 class="text-red-600 mb-4 font-black italic border-b border-red-900/30 pb-2">I. Identity & Sales</h4>
                                        <p class="mkt-text-muted mb-1 tracking-widest uppercase">Artikel No:</p>
                                        <p class="mkt-text text-base mb-3 font-black">{{ $trackingData->art_no ?? '-' }}</p>
                                        <p class="text-slate-500 mb-1 tracking-widest uppercase">Representative:</p>
                                        <p class="mkt-text text-base font-black italic">{{ $trackingData->mkt ?? '-' }}</p>
                                    </div>

                                    {{-- II. Technical Specs --}}
                                    <div class="mkt-surface p-6 rounded-[2.5rem] border-l-4 border-slate-600 shadow-inner">
                                        <h4 class="mkt-text mb-4 font-black italic border-b mkt-border pb-2">II. Technical Specs</h4>
                                        <p class="mkt-text-muted mb-1 tracking-widest uppercase">Material / Benang:</p>
                                        <p class="mkt-text text-base mb-3 font-black">{{ $trackingData->material ?? '-' }} / {{ $trackingData->benang ?? '-' }}</p>
                                        <p class="text-slate-500 mb-1 tracking-widest uppercase">Finishing Warna:</p>
                                        <p class="text-emerald-400 text-base font-black italic uppercase tracking-tighter">{{ $trackingData->warna ?? '-' }}</p>
                                    </div>
                                </div>

                                {{-- Production Specification Matrix --}}
                                <div class="mkt-surface p-8 rounded-[3rem] text-center border mkt-border shadow-lg">
                                    <p class="mkt-text-muted text-[9px] font-black uppercase tracking-widest mb-6 italic">Production Specification Matrix</p>
                                    <div class="grid grid-cols-4 gap-4 divide-x mkt-border">
                                        <div>
                                            <p class="text-slate-500 text-[8px] uppercase font-black mb-1">Kelompok</p>
                                            <p class="text-xs font-black mkt-text italic">{{ $trackingData->kelompok_kain ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-slate-500 text-[8px] uppercase font-black mb-1">Lebar / Gramasi</p>
                                            <p class="text-xs font-black mkt-text italic">{{ $trackingData->target_lebar }}" / {{ $trackingData->target_gramasi }}</p>
                                        </div>
                                        <div>
                                            <p class="text-slate-500 text-[8px] uppercase font-black mb-1">Handfeel</p>
                                            <p class="text-xs font-black mkt-text italic">{{ $trackingData->handfeel ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-slate-500 text-[8px] uppercase font-black mb-1">Treatment</p>
                                            <p class="text-xs font-black text-red-500 italic">{{ $trackingData->treatment_khusus ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Notes --}}
                                <div class="mkt-surface-alt p-6 rounded-[2rem] border mkt-border shadow-sm">
                                    <p class="text-[9px] mkt-text-muted uppercase font-black italic mb-2 tracking-widest">Internal Marketing Notes:</p>
                                    <p class="mkt-text text-xs italic font-bold uppercase leading-relaxed">{{ $trackingData->keterangan_artikel ?? 'PROSES SESUAI STANDAR...' }}</p>
                                </div>
                            </div>

                            {{-- SISI KANAN: REAL-TIME MILESTONE (Detail Produksi & Operator) --}}
                            <div class="col-span-12 lg:col-span-5 mkt-surface-alt p-6 md:p-8 rounded-[2.5rem] border mkt-border">
                                <h3 class="mkt-text font-black italic text-xs mb-8 uppercase flex items-center gap-2">
                                    <span class="text-red-600 text-xl">III.</span> Real-time Production Milestone
                                </h3>
                                
                                <div class="space-y-4 relative max-h-[450px] overflow-y-auto pr-4 custom-scrollbar">
                                    <div class="absolute left-4 top-0 bottom-0 w-0.5 mkt-border border-l-2 border-dashed"></div>

                                    @php
                                        $steps = [
                                            ['name' => 'MARKETING', 'div' => 'marketing', 'label' => 'ORDER CREATED'],
                                            ['name' => 'KNITTING', 'div' => 'knitting', 'label' => 'PROSES RAJUT'],
                                            ['name' => 'SCR / DYEING', 'div' => 'dyeing', 'label' => 'PEWARNAAN'],
                                            ['name' => 'RELAX DRYER', 'div' => 'relax_dryer', 'label' => 'PENGERINGAN'],
                                            ['name' => 'FINISHING', 'div' => 'finishing', 'label' => 'CHEMICAL FINISH'],
                                            ['name' => 'STENTER', 'div' => 'stenter', 'label' => 'SETTING LEBAR'],
                                            ['name' => 'TUMBLER', 'div' => 'tumbler', 'label' => 'PROSES BULKING'],
                                            ['name' => 'FLEECE', 'div' => 'fleece', 'label' => 'GARUK / BRUSHING'],
                                            ['name' => 'QC & LAB', 'div' => 'pengujian', 'label' => 'PENGUJIAN FISIK'],
                                            ['name' => 'QE', 'div' => 'qe', 'label' => 'FINAL APPROVAL']
                                        ];
                                    @endphp

                                    @foreach($steps as $index => $step)
                                        @php
                                            $log = $trackingLogs->where('division_name', $step['div'])->first();
                                            $isDone = $log || ($step['div'] == 'marketing' && $trackingData);
                                            $techData = $log ? (is_array($log->technical_data) ? $log->technical_data : json_decode($log->technical_data, true)) : [];
                                            $isExpanded = $expandedLog === $step['div'];
                                        @endphp

                                        <div class="flex items-start gap-6 relative z-10 group">
                                            {{-- Icon Status --}}
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-[11px] font-black transition-all duration-500 
                                                {{ $isDone ? 'bg-emerald-500 text-white shadow-[0_0_20px_rgba(16,185,129,0.3)]' : 'mkt-surface-alt mkt-text-muted border mkt-border' }}">
                                                @if($isDone) ✓ @else {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }} @endif
                                            </div>

                                            <div class="flex-1 pb-6">
                                                <div class="flex justify-between items-center mb-1">
                                                    <div class="cursor-pointer" wire:click="toggleDetail('{{ $step['div'] }}')">
                                                        <h4 class="{{ $isDone ? 'mkt-text' : 'mkt-text-muted' }} text-[12px] font-black italic uppercase tracking-tighter flex items-center gap-2">
                                                            {{ $step['name'] }}
                                                            @if($log && $step['div'] !== 'marketing')
                                                                <span class="text-[8px] px-2 py-0.5 rounded mkt-surface mkt-text-muted font-bold tracking-widest uppercase">
                                                                    {{ $isExpanded ? '▲ Hide' : '▼ View Detail' }}
                                                                </span>
                                                            @endif
                                                        </h4>
                                                        <p class="text-[9px] font-bold italic uppercase mkt-text-muted">{{ $step['label'] }}</p>
                                                    </div>
                                                    
                                                    @if($log)
                                                        <div class="text-right">
                                                            <span class="text-[9px] text-emerald-400 font-black block">{{ $log->created_at->format('d/m/y H:i') }}</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- KARTU DETAIL: Hanya muncul jika $isExpanded adalah true --}}
                                                @if($isDone && $log && $step['div'] !== 'marketing' && $isExpanded)
                                                    <div class="mt-4 mkt-surface border mkt-border p-6 rounded-[2.5rem] shadow-2xl animate-in zoom-in duration-300 ring-1 ring-blue-500/20">
                                                        <div class="flex items-center gap-3 mb-5 pb-4 border-b mkt-border">
                                                            <div class="w-8 h-8 rounded-xl bg-blue-600/20 text-blue-400 flex items-center justify-center text-[10px] font-black border border-blue-600/30 uppercase">OP</div>
                                                            <div class="flex-1">
                                                                <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest">Operator In-Charge</p>
                                                                <p class="text-[11px] mkt-text font-black italic uppercase leading-none">{{ $log->operator->name ?? 'UNKNOWN' }}</p>
                                                            </div>
                                                            <div class="text-right">
                                                                <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest">Machine Unit</p>
                                                                <p class="text-[11px] text-red-500 font-black italic leading-none">{{ $log->machine_no ?? 'N/A' }}</p>
                                                            </div>
                                                        </div>

                                                        {{-- Data Grid --}}
                                                        <div class="grid grid-cols-3 gap-6">
                                                            <div class="col-span-1 p-3 mkt-surface-alt rounded-2xl border mkt-border">
                                                                <p class="text-[8px] mkt-text-muted uppercase font-black mb-1">Production Result</p>
                                                                <p class="text-[11px] text-emerald-400 font-black italic leading-none">{{ $log->kg }} KG / {{ $log->roll }} ROLL</p>
                                                            </div>

                                                            {{-- Loop techData dari database --}}
                                                            @if(!empty($techData))
                                                                @foreach($techData as $key => $value)
                                                                    @if(!in_array($key, ['kg', 'roll', 'machine_no', 'operator']))
                                                                        <div class="col-span-1 p-3 mkt-surface-alt rounded-2xl border mkt-border">
                                                                            <p class="text-[8px] mkt-text-muted uppercase font-black mb-1 tracking-tighter">{{ strtoupper(str_replace('_', ' ', $key)) }}</p>
                                                                            <p class="text-[11px] mkt-text font-black italic uppercase leading-none">{{ $value ?? '-' }}</p>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div> 
                            </div> 
                        </div> 
                    </div>
                </div> 
            @endif 
    </div> 
</div> 