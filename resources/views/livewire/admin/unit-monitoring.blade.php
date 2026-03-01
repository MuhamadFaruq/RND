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

    public function exportReport()
    {
        return redirect()->route('admin.export-pdf', [
            'mode' => $this->viewMode,
            'start' => $this->startDate,
            'end' => $this->endDate,
            'unit' => $this->selectedUnit
        ]);
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

<div class="min-h-screen bg-slate-900 text-white font-sans italic p-8" wire:poll.10s>
    <div class="container mx-auto">
        {{-- HEADER & ANALYTICS CARDS --}}
        <div class="flex justify-between items-end mb-6 border-b border-slate-800 pb-8">
            <div>
                <h1 class="text-6xl font-black text-red-600 uppercase tracking-tighter">
                    UNIT <span class="text-white">MONITORING</span>
                </h1>
                <p class="text-slate-400 font-bold uppercase text-[10px] mt-2 tracking-widest italic">
                    MODE: MONITORING {{ $viewMode }} | LIVE DUNIATEX RND
                </p>
            </div>

            <div class="flex gap-4 items-center">
                {{-- SINGLE BUTTON REPORT WITH DROPDOWN --}}
                <div class="bg-red-600/20 border border-red-600/50 p-4 rounded-3xl flex items-center gap-4 px-8 shadow-lg shadow-red-900/20">
                    <div class="text-red-500 animate-pulse text-2xl font-black">⚠️</div>
                    <div>
                        <p class="text-[9px] font-black text-red-500 uppercase tracking-widest">Stuck Orders (>2 Days)</p>
                        <h4 class="text-2xl font-black text-white leading-none">{{ $stuckOrders }}</h4>
                    </div>
                </div>
                <div class="relative">
                    <button wire:click="$toggle('showExportMenu')" 
                        class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-2xl font-black uppercase italic shadow-lg transition-all transform hover:scale-105 text-[10px]">
                        GENERATE {{ $viewMode }} REPORT
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    {{-- DROPDOWN MENU --}}
                    @if($showExportMenu)
                        <div class="absolute right-0 mt-2 w-48 bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl z-50 overflow-hidden">
                            <button wire:click="export('pdf')" class="w-full text-left px-4 py-3 text-[10px] font-black italic hover:bg-slate-700 text-red-500 flex items-center gap-2 border-b border-slate-700">
                                <span>PDF</span> DOWNLOAD PDF DOCUMENT
                            </button>
                            <button wire:click="export('excel')" class="w-full text-left px-4 py-3 text-[10px] font-black italic hover:bg-slate-700 text-emerald-400 flex items-center gap-2">
                                <span>XLS</span> DOWNLOAD EXCEL SPREADSHEET
                            </button>
                        </div>
                    @endif
                </div>

                {{-- FILTER TANGGAL --}}
                <div class="flex gap-2 bg-slate-800 p-2 rounded-2xl border border-slate-700 shadow-xl">
                    <input type="date" wire:model.live="startDate" class="bg-transparent border-none text-white font-bold text-[10px] focus:ring-0 uppercase">
                    <span class="text-slate-600 self-center font-black">TO</span>
                    <input type="date" wire:model.live="endDate" class="bg-transparent border-none text-white font-bold text-[10px] focus:ring-0 uppercase">
                </div>
            </div>
        </div>

        {{-- TAB SWITCHER --}}
        <div class="flex gap-4 mb-8">
            <button wire:click="$set('viewMode', 'RAJUT')" 
                class="px-10 py-4 rounded-2xl font-black uppercase italic transition-all {{ $viewMode === 'RAJUT' ? 'bg-red-600 shadow-lg shadow-red-900/40' : 'bg-slate-800 text-slate-500' }}">
                MONITORING RAJUT
            </button>
            <button wire:click="$set('viewMode', 'WARNA')" 
                class="px-10 py-4 rounded-2xl font-black uppercase italic transition-all {{ $viewMode === 'WARNA' ? 'bg-emerald-600 shadow-lg shadow-emerald-900/40' : 'bg-slate-800 text-slate-500' }}">
                MONITORING WARNA
            </button>
        </div>

        {{-- TABLE DATA --}}
        <div class="bg-slate-800 rounded-[3rem] border border-slate-700 shadow-2xl overflow-x-auto">
            <div class="p-10 min-w-[1200px]">
                <table class="w-full text-left uppercase font-black italic tracking-tighter">
                    <thead>
                        <tr class="text-slate-500 text-[9px] border-b border-slate-700">
                            @if($viewMode === 'RAJUT')
                                <th class="pb-4">SAP / ART</th>
                                <th class="pb-4">PELANGGAN / MKT</th>
                                <th class="pb-4">KONSTRUKSI / GRAMASI</th>
                                <th class="pb-4">MESIN / KELOMPOK</th>
                                <th class="pb-4">LEBAR / GRAMASI (TARGET)</th>
                                <th class="pb-4">HASIL (KG/ROLL)</th>
                                <th class="pb-4">KIRIM DPF 3</th>
                            @else
                                <th class="pb-4">SAP / ART</th>
                                <th class="pb-4">MKT & PELANGGAN</th>
                                <th class="pb-4">KONSTRUKSI</th>
                                <th class="pb-4">WARNA / HANDFEEL</th>
                                <th class="pb-4">TARGET (L/G)</th>
                                <th class="pb-4">HASIL (KG/ROLL)</th>
                                <th class="pb-4">TIMELINE SELESAI</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="text-[11px]">
                        @forelse($activities as $act)
                            <tr class="border-b border-slate-700/50 hover:bg-slate-900/40 transition-all">
                                {{-- KOLOM COMMON --}}
                                <td class="py-6 px-2 cursor-pointer group" wire:click="openTracking('{{ $act->marketingOrder->sap_no }}')">
                                    <span class="text-red-600 block leading-none group-hover:underline font-black">
                                        #{{ $act->marketingOrder->sap_no }}
                                    </span>
                                    <span class="text-white mt-1 block opacity-70 group-hover:opacity-100">
                                        {{ $act->marketingOrder->art_no }}
                                    </span>
                                </td>

                                @if($viewMode === 'RAJUT')
                                    {{-- DATA KHUSUS RAJUT --}}
                                    <td class="py-6 px-2">
                                        <span class="text-white block">{{ $act->marketingOrder->pelanggan }}</span>
                                        <span class="text-slate-500 text-[9px]">MKT: {{ $act->marketingOrder->marketing_name ?? 'DD' }}</span>
                                    </td>
                                    <td class="py-6 px-2">
                                        <span class="text-slate-300 block">{{ $act->marketingOrder->konstruksi_greige }}</span>
                                        <span class="text-slate-500">{{ $act->marketingOrder->gramasi_greige }} GSM</span>
                                    </td>
                                    <td class="py-6 px-2">
                                        <span class="text-blue-400 block">{{ $act->machine_no ?? 'MESIN 01' }}</span>
                                        <span class="text-slate-500 text-[9px]">KEL: {{ $act->marketingOrder->kelompok_kain }}</span>
                                    </td>
                                    <td class="py-6 px-2">
                                        <span class="text-white block">{{ $act->marketingOrder->target_lebar }} "</span>
                                        <span class="text-slate-500">{{ $act->marketingOrder->target_gramasi }} GSM</span>
                                    </td>
                                @else
                                    {{-- DATA KHUSUS WARNA --}}
                                    <td class="py-6 px-2">
                                        <span class="text-white block">{{ $act->marketingOrder->pelanggan }}</span>
                                    </td>
                                    <td class="py-6 px-2 text-slate-300">
                                        {{ $act->marketingOrder->konstruksi_greige }}
                                    </td>
                                    <td class="py-6 px-2">
                                        <span class="text-emerald-400 block">{{ $act->marketingOrder->warna ?? 'TBD' }}</span>
                                        <span class="text-slate-500 text-[9px]">HANDFEEL: {{ $act->marketingOrder->handfeel ?? '-' }}</span>
                                    </td>
                                    <td class="py-6 px-2 text-slate-300">
                                        {{ $act->marketingOrder->target_lebar }} / {{ $act->marketingOrder->target_gramasi }}
                                    </td>
                                @endif

                                {{-- HASIL PRODUKSI (COMMON) --}}
                                <td class="py-6 px-2">
                                    <span class="text-emerald-400 font-bold">{{ $act->kg }} KG</span>
                                    <span class="text-slate-600 mx-1">/</span>
                                    <span class="text-white">{{ $act->roll }} ROLL</span>
                                </td>

                                {{-- TIMELINE --}}
                                <td class="py-6 px-2">
                                    @if($viewMode === 'RAJUT')
                                        <span class="px-3 py-1 bg-slate-900 border border-red-900/30 rounded-full text-red-500 text-[9px]">
                                            EST. KIRIM: {{ $act->created_at->addDays(1)->format('d/m') }}
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-slate-900 border border-emerald-900/30 rounded-full text-emerald-500 text-[9px]">
                                            DEADLINE: {{ $act->marketingOrder->tgl_selesai ?? 'TBD' }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-20 text-center text-slate-500">TIDAK ADA DATA {{ $viewMode }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Modal Tracking SAP --}}
            @if($showTrackingModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-md px-4">
                <div class="bg-slate-900 border border-slate-700 w-full max-w-6xl rounded-[3.5rem] overflow-hidden shadow-2xl animate-in fade-in zoom-in duration-300">
                    
                    {{-- 1. HEADER MODAL (Tetap di sini) --}}
                    <div class="p-10 bg-slate-800/50 flex justify-between items-center border-b border-slate-700">
                            <div>
                                <h2 class="text-white font-black italic uppercase text-4xl leading-none">Industrial Order <span class="text-red-600">Detail</span></h2>
                                <p class="text-slate-400 text-[10px] mt-2 font-bold uppercase tracking-widest">SAP NO: {{ $selectedSap }} | PELANGGAN: {{ $trackingData->pelanggan ?? '-' }}</p>
                            </div>
                            <button wire:click="$set('showTrackingModal', false)" class="bg-slate-700 hover:bg-red-600 p-4 rounded-2xl text-white transition-all shadow-xl">✕ CLOSE</button>
                        </div>

                        <div class="p-6 md:p-10 grid grid-cols-12 gap-6 md:gap-8 overflow-y-auto max-h-[85vh]">
                            {{-- SISI KIRI: DATA TEKNIS (Identity & Specs dari Marketing) --}}
                            <div class="col-span-12 lg:col-span-7 space-y-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6">    
                                    {{-- I. Identity & Sales --}}
                                    <div class="bg-slate-800 p-6 rounded-[2.5rem] border-l-4 border-red-600 shadow-inner">
                                        <h4 class="text-red-600 mb-4 font-black italic border-b border-red-900/30 pb-2">I. Identity & Sales</h4>
                                        <p class="text-slate-500 mb-1 tracking-widest uppercase">Artikel No:</p>
                                        <p class="text-white text-base mb-3 font-black">{{ $trackingData->art_no ?? '-' }}</p>
                                        <p class="text-slate-500 mb-1 tracking-widest uppercase">Representative:</p>
                                        <p class="text-white text-base font-black italic">{{ $trackingData->mkt ?? '-' }}</p>
                                    </div>

                                    {{-- II. Technical Specs --}}
                                    <div class="bg-slate-800 p-6 rounded-[2.5rem] border-l-4 border-slate-600 shadow-inner">
                                        <h4 class="text-white mb-4 font-black italic border-b border-slate-700 pb-2">II. Technical Specs</h4>
                                        <p class="text-slate-500 mb-1 tracking-widest uppercase">Material / Benang:</p>
                                        <p class="text-white text-base mb-3 font-black">{{ $trackingData->material ?? '-' }} / {{ $trackingData->benang ?? '-' }}</p>
                                        <p class="text-slate-500 mb-1 tracking-widest uppercase">Finishing Warna:</p>
                                        <p class="text-emerald-400 text-base font-black italic uppercase tracking-tighter">{{ $trackingData->warna ?? '-' }}</p>
                                    </div>
                                </div>

                                {{-- Production Specification Matrix --}}
                                <div class="bg-slate-800 p-8 rounded-[3rem] text-center border border-slate-700 shadow-lg">
                                    <p class="text-slate-500 text-[9px] font-black uppercase tracking-widest mb-6 italic">Production Specification Matrix</p>
                                    <div class="grid grid-cols-4 gap-4 divide-x divide-slate-700">
                                        <div>
                                            <p class="text-slate-500 text-[8px] uppercase font-black mb-1">Kelompok</p>
                                            <p class="text-xs font-black text-white italic">{{ $trackingData->kelompok_kain ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-slate-500 text-[8px] uppercase font-black mb-1">Lebar / Gramasi</p>
                                            <p class="text-xs font-black text-white italic">{{ $trackingData->target_lebar }}" / {{ $trackingData->target_gramasi }}</p>
                                        </div>
                                        <div>
                                            <p class="text-slate-500 text-[8px] uppercase font-black mb-1">Handfeel</p>
                                            <p class="text-xs font-black text-white italic">{{ $trackingData->handfeel ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-slate-500 text-[8px] uppercase font-black mb-1">Treatment</p>
                                            <p class="text-xs font-black text-red-500 italic">{{ $trackingData->treatment_khusus ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Notes --}}
                                <div class="bg-slate-800/50 p-6 rounded-[2rem] border border-slate-700 shadow-sm">
                                    <p class="text-[9px] text-slate-500 uppercase font-black italic mb-2 tracking-widest">Internal Marketing Notes:</p>
                                    <p class="text-white text-xs italic font-bold uppercase leading-relaxed">{{ $trackingData->keterangan_artikel ?? 'PROSES SESUAI STANDAR...' }}</p>
                                </div>
                            </div>

                            {{-- SISI KANAN: REAL-TIME MILESTONE (Detail Produksi & Operator) --}}
                            <div class="col-span-12 lg:col-span-5 bg-slate-800/30 p-6 md:p-8 rounded-[2.5rem] border border-slate-700">
                                <h3 class="text-white font-black italic text-xs mb-8 uppercase flex items-center gap-2">
                                    <span class="text-red-600 text-xl">III.</span> Real-time Production Milestone
                                </h3>
                                
                                <div class="space-y-4 relative max-h-[450px] overflow-y-auto pr-4 custom-scrollbar">
                                    <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-slate-700"></div>

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
                                                {{ $isDone ? 'bg-emerald-500 text-white shadow-[0_0_20px_rgba(16,185,129,0.3)]' : 'bg-slate-700 text-slate-500' }}">
                                                @if($isDone) ✓ @else {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }} @endif
                                            </div>

                                            <div class="flex-1 pb-6">
                                                <div class="flex justify-between items-center mb-1">
                                                    <div class="cursor-pointer" wire:click="toggleDetail('{{ $step['div'] }}')">
                                                        <h4 class="{{ $isDone ? 'text-white' : 'text-slate-500' }} text-[12px] font-black italic uppercase tracking-tighter flex items-center gap-2">
                                                            {{ $step['name'] }}
                                                            @if($log && $step['div'] !== 'marketing')
                                                                <span class="text-[8px] px-2 py-0.5 rounded bg-slate-800 text-slate-400 font-bold tracking-widest uppercase">
                                                                    {{ $isExpanded ? '▲ Hide' : '▼ View Detail' }}
                                                                </span>
                                                            @endif
                                                        </h4>
                                                        <p class="text-[9px] font-bold italic uppercase text-slate-600">{{ $step['label'] }}</p>
                                                    </div>
                                                    
                                                    @if($log)
                                                        <div class="text-right">
                                                            <span class="text-[9px] text-emerald-400 font-black block">{{ $log->created_at->format('d/m/y H:i') }}</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- KARTU DETAIL: Hanya muncul jika $isExpanded adalah true --}}
                                                @if($isDone && $log && $step['div'] !== 'marketing' && $isExpanded)
                                                    <div class="mt-4 bg-slate-900/90 border border-slate-700 p-6 rounded-[2.5rem] shadow-2xl animate-in zoom-in duration-300 ring-1 ring-blue-500/20">
                                                        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-800">
                                                            <div class="w-8 h-8 rounded-xl bg-blue-600/20 text-blue-400 flex items-center justify-center text-[10px] font-black border border-blue-600/30 uppercase">OP</div>
                                                            <div class="flex-1">
                                                                <p class="text-[8px] text-slate-500 font-black uppercase tracking-widest">Operator In-Charge</p>
                                                                <p class="text-[11px] text-white font-black italic uppercase leading-none">{{ $log->operator->name ?? 'UNKNOWN' }}</p>
                                                            </div>
                                                            <div class="text-right">
                                                                <p class="text-[8px] text-slate-500 font-black uppercase tracking-widest">Machine Unit</p>
                                                                <p class="text-[11px] text-red-500 font-black italic leading-none">{{ $log->machine_no ?? 'N/A' }}</p>
                                                            </div>
                                                        </div>

                                                        {{-- Data Grid --}}
                                                        <div class="grid grid-cols-3 gap-6">
                                                            <div class="col-span-1 p-3 bg-slate-800/40 rounded-2xl border border-slate-700/50">
                                                                <p class="text-[8px] text-slate-500 uppercase font-black mb-1">Production Result</p>
                                                                <p class="text-[11px] text-emerald-400 font-black italic leading-none">{{ $log->kg }} KG / {{ $log->roll }} ROLL</p>
                                                            </div>

                                                            {{-- Loop techData dari database --}}
                                                            @if(!empty($techData))
                                                                @foreach($techData as $key => $value)
                                                                    @if(!in_array($key, ['kg', 'roll', 'machine_no', 'operator']))
                                                                        <div class="col-span-1 p-3 bg-slate-800/40 rounded-2xl border border-slate-700/50">
                                                                            <p class="text-[8px] text-slate-500 uppercase font-black mb-1 tracking-tighter">{{ strtoupper(str_replace('_', ' ', $key)) }}</p>
                                                                            <p class="text-[11px] text-white font-black italic uppercase leading-none">{{ $value ?? '-' }}</p>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach {{-- Penutup Milestone loop --}}
                                </div> {{-- Penutup Custom Scrollbar --}}
                            </div> {{-- Penutup Kolom Milestone (lg:col-span-5) --}}
                        </div> {{-- Penutup Responsive Grid (grid-cols-12) --}}
                    </div> {{-- Penutup Bg-slate-900 (Modal Body) --}}
                </div> {{-- Penutup Fixed Inset (Modal Wrapper) --}}
            @endif {{-- HANYA SATU @endif DI SINI UNTUK MODAL --}}
    </div> {{-- Penutup Container mx-auto --}}
</div> {{-- Penutup Min-h-screen Layout Utama --}}