<?php

use Livewire\Volt\Component;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;

new class extends Component {
    public $orderId;
    public $trackingData;
    public $trackingLogs;
    public $expandedLog = null;

    public function mount($orderId)
    {
        $this->orderId = $orderId;
        $this->loadData();
    }

    public function loadData()
    {
        $this->trackingData = MarketingOrder::find($this->orderId);
        if ($this->trackingData) {
            $this->trackingLogs = ProductionActivity::with('operator')
                ->where('marketing_order_id', $this->trackingData->id)
                ->orderBy('created_at', 'asc')
                ->get();
        }
    }

    public function toggleDetail($divName)
    {
        $this->expandedLog = ($this->expandedLog === $divName) ? null : $divName;
    }
}; ?>

<div class="mkt-surface border mkt-border w-full rounded-3xl sm:rounded-[4rem] overflow-hidden shadow-[0_0_100px_rgba(0,0,0,0.5)] flex flex-col max-h-[95vh] text-white bg-[#111c44]">
    
    {{-- HEADER MODAL --}}
    <div class="p-5 sm:p-10 mkt-surface-alt flex flex-col sm:flex-row justify-between items-start sm:items-center border-b mkt-border backdrop-blur-md gap-4">
        <div class="flex items-center gap-4 sm:gap-6">
            <div class="h-12 w-12 sm:h-16 sm:w-16 bg-indigo-600 rounded-2xl sm:rounded-3xl flex items-center justify-center shrink-0 shadow-lg shadow-indigo-600/40">
                <span class="text-white text-xl sm:text-3xl font-black italic">!</span>
            </div>
            <div>
                @php
                    $isWarna = in_array($trackingData->status, ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'finishing', 'stenter', 'tumbler', 'fleece', 'qe', 'finished']);
                @endphp
                <h2 class="mkt-text font-black italic uppercase text-2xl sm:text-4xl leading-none tracking-tighter">
                    MONITORING <span class="text-indigo-600">{{ $isWarna ? 'WARNA' : 'RAJUT' }}</span>
                </h2>
                <p class="mkt-text-muted text-[8px] sm:text-[10px] mt-2 font-black uppercase tracking-[0.2em] sm:tracking-[0.3em] flex flex-wrap items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    NO ARTIKEL: {{ $trackingData->art_no }} <span class="hidden sm:inline">|</span> LEGACY SAP: {{ $trackingData->sap_no }} <span class="hidden sm:inline">|</span> LIVE
                </p>
            </div>
        </div>
        <button @click="$dispatch('close-log-detail')" 
            class="w-full sm:w-auto bg-white dark:bg-indigo-600 text-slate-600 dark:text-white px-6 sm:px-8 py-3 sm:py-4 rounded-2xl sm:rounded-3xl text-[9px] sm:text-[10px] font-black uppercase transition-all border mkt-border dark:border-none shadow-xl flex items-center justify-center gap-3 group">
            CLOSE DOCUMENT <span class="group-hover:rotate-90 transition-transform">✕</span>
        </button>
    </div>

    <div class="p-4 sm:p-10 overflow-y-auto custom-scrollbar flex-1 min-h-0 max-h-[75vh]">
        <div class="grid grid-cols-12 gap-6 sm:gap-8">
            
            {{-- COLUMN LEFT: SPECIFICATIONS (17 POINTS) --}}
            <div class="col-span-12 lg:col-span-8 space-y-6 sm:space-y-8">
                
                {{-- GROUP A: IDENTITY & TRANSACTION --}}
                <div class="mkt-surface p-4 sm:p-8 rounded-2xl sm:rounded-[3.5rem] border mkt-border shadow-xl">
                    <h4 class="text-[10px] sm:text-xs font-black text-red-500 italic uppercase tracking-widest mb-4 sm:mb-6 flex items-center gap-3">
                        <span class="w-4 sm:w-6 h-[2px] bg-red-600"></span> IDENTITY & TRANSACTION
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-8">
                        <div>
                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">SAP ID</p>
                            <p class="text-lg sm:text-xl font-black mkt-text italic leading-none">{{ $trackingData->sap_no ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">NO ARTIKEL (ART)</p>
                            <p class="text-lg sm:text-xl font-black text-blue-500 italic leading-none uppercase">{{ $trackingData->art_no ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">TANGGAL ORDER (TGL)</p>
                            <p class="text-lg sm:text-xl font-black mkt-text italic leading-none">{{ $trackingData->tanggal ? \Carbon\Carbon::parse($trackingData->tanggal)->format('d/m/Y') : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">PELANGGAN</p>
                            <p class="text-lg sm:text-xl font-black mkt-text italic leading-none uppercase">{{ $trackingData->pelanggan ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">MARKETING REP (MKT)</p>
                            <p class="text-lg sm:text-xl font-black text-emerald-500 italic leading-none uppercase">{{ $trackingData->mkt ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">KEPERLUAN</p>
                            <p class="text-lg sm:text-xl font-black mkt-text italic leading-none uppercase">{{ $trackingData->keperluan ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- GROUP B: GREIGE TECHNICALS --}}
                <div class="mkt-surface p-4 sm:p-8 rounded-2xl sm:rounded-[3.5rem] border mkt-border shadow-xl">
                    <h4 class="text-[10px] sm:text-xs font-black text-red-500 italic uppercase tracking-widest mb-4 sm:mb-6 flex items-center gap-3">
                        <span class="w-4 sm:w-6 h-[2px] bg-red-600"></span> GREIGE TECHNICALS
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-8">
                        <div>
                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">KONSTRUKSI GREIGE</p>
                            <p class="text-xs sm:text-sm font-black mkt-text italic leading-tight">{{ $trackingData->konstruksi_greige ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">MATERIAL</p>
                            <p class="text-xs sm:text-sm font-black text-emerald-400 italic uppercase leading-tight">{{ $trackingData->material ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">BENANG</p>
                            <p class="text-xs sm:text-sm font-black mkt-text italic leading-tight">{{ $trackingData->benang ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">KELOMPOK KAIN</p>
                            <p class="text-xs sm:text-sm font-black mkt-text italic uppercase leading-tight">{{ $trackingData->kelompok_kain ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- GROUP C: FINISHING TARGETS --}}
                <div class="mkt-surface p-4 sm:p-8 rounded-2xl sm:rounded-[3.5rem] border mkt-border shadow-xl">
                    <h4 class="text-[10px] sm:text-xs font-black text-red-500 italic uppercase tracking-widest mb-4 sm:mb-6 flex items-center gap-3">
                        <span class="w-4 sm:w-6 h-[2px] bg-red-600"></span> FINISHING TARGETS
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 sm:gap-8">
                        <div>
                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">TARGET LEBAR</p>
                            <p class="text-lg sm:text-xl font-black mkt-text italic leading-none">{{ $trackingData->target_lebar ?? '-' }}"</p>
                        </div>
                        <div>
                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">BELAH / BULAT</p>
                            <p class="text-lg sm:text-xl font-black text-emerald-500 italic leading-none uppercase">{{ $trackingData->belah_bulat ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">TARGET GRAMASI</p>
                            <p class="text-lg sm:text-xl font-black mkt-text italic leading-none">{{ $trackingData->target_gramasi ?? '-' }} GSM</p>
                        </div>
                        <div>
                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">WARNA KAIN</p>
                            <p class="text-lg sm:text-xl font-black text-pink-500 italic leading-none uppercase">{{ $trackingData->warna ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">HANDFEEL</p>
                            <p class="text-lg sm:text-xl font-black text-blue-500 italic leading-none uppercase">{{ $trackingData->handfeel ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">TREATMENT KHUSUS</p>
                            <p class="text-lg sm:text-xl font-black text-amber-500 italic leading-none uppercase">{{ $trackingData->treatment_khusus ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- GROUP D: PRODUCTION QUANTITY & NOTES --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-8">
                    {{-- Target Card --}}
                    <div class="col-span-1 mkt-surface p-4 sm:p-8 rounded-2xl sm:rounded-[3rem] border mkt-border shadow-xl flex flex-col justify-between">
                        <div>
                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">TARGET ROLL</p>
                            <p class="text-2xl sm:text-3xl font-black text-emerald-500 italic leading-none">{{ $trackingData->roll_target ?? '0' }} Roll</p>
                        </div>
                        <div class="mt-4">
                            <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">TARGET WEIGHT (KG)</p>
                            <p class="text-2xl sm:text-3xl font-black text-blue-500 italic leading-none">{{ $trackingData->kg_target ?? '0' }} KG</p>
                        </div>
                    </div>

                    {{-- Notes Card --}}
                    <div class="col-span-1 sm:col-span-2 mkt-surface p-4 sm:p-8 rounded-2xl sm:rounded-[3rem] border mkt-border shadow-xl">
                        <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-2 italic">KETERANGAN ARTIKEL</p>
                        <p class="text-xs font-black mkt-text italic uppercase leading-relaxed">{{ $trackingData->keterangan_artikel ?? 'PROSES SESUAI STANDAR PRODUKSI RND.' }}</p>
                    </div>
                </div>

                {{-- ACTUAL PRODUCTION (RAJUT) --}}
                @php $knitLog = $trackingLogs->where('division_name', 'knitting')->first(); @endphp
                <div class="mkt-surface p-4 sm:p-8 rounded-2xl sm:rounded-[3.5rem] border-2 {{ $knitLog ? 'border-emerald-500/20 bg-emerald-500/5' : 'border-slate-800' }} shadow-xl relative overflow-hidden">
                    @if(!$knitLog)
                        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center z-10">
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] italic">WAITING FOR PRODUCTION...</p>
                        </div>
                    @endif
                    <h4 class="text-[9px] font-black {{ $knitLog ? 'text-emerald-500' : 'mkt-text-muted' }} uppercase tracking-widest mb-4 sm:mb-6 flex items-center justify-between">
                        <span>Actual Knitting Result (From Operator)</span>
                        @if($knitLog) <span class="bg-emerald-500 text-white px-2 py-0.5 rounded text-[7px]">DONE</span> @endif
                    </h4>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">Mesin Rajut</p>
                            <p class="text-lg sm:text-xl font-black mkt-text italic leading-none">{{ $knitLog->machine_no ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">ROLL</p>
                            <p class="text-xl sm:text-2xl font-black text-emerald-500 leading-none">{{ $knitLog->roll ?? '0' }}</p>
                        </div>
                        <div>
                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">KG</p>
                            <p class="text-xl sm:text-2xl font-black text-emerald-500 leading-none">{{ $knitLog->kg ?? '0' }}</p>
                        </div>
                    </div>
                    @if($knitLog)
                    <div class="mt-4 sm:mt-6 pt-4 border-t border-white/5 flex justify-between items-center text-[8px] font-bold mkt-text-muted italic uppercase">
                        <span>OP: {{ $knitLog->operator->name ?? 'N/A' }}</span>
                        <span class="text-emerald-500">{{ $knitLog->created_at->format('H:i | d/m') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- COLUMN RIGHT: TIMELINE & MILESTONES --}}
            <div class="col-span-12 lg:col-span-4 flex flex-col space-y-6 sm:space-y-8">
                <div class="mkt-surface p-4 sm:p-8 rounded-2xl sm:rounded-[4rem] border mkt-border shadow-2xl flex-1 flex flex-col bg-[#1a234a]">
                    <h3 class="text-xs font-black mkt-text italic uppercase tracking-[0.2em] mb-6 sm:mb-10 flex items-center gap-4">
                        <span class="w-6 sm:w-8 h-[2px] bg-red-600"></span> TIMELINE TRACKING
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
                                        <div class="h-8 w-8 sm:h-10 sm:w-10 bg-indigo-600 rounded-xl sm:rounded-2xl flex items-center justify-center shadow-[0_0_20px_rgba(79,70,229,0.4)] z-10 transition-all group-hover:scale-110">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    @elseif($isCurrent)
                                        <div class="h-8 w-8 sm:h-10 sm:w-10 bg-indigo-500 rounded-xl sm:rounded-2xl flex items-center justify-center shadow-[0_0_20px_rgba(79,70,229,0.4)] z-10 animate-pulse border border-white/20">
                                            <span class="text-white text-[10px] sm:text-xs font-black italic">{{ $index + 1 }}</span>
                                        </div>
                                    @else
                                        <div class="h-8 w-8 sm:h-10 sm:w-10 bg-slate-800 border border-slate-700 rounded-xl sm:rounded-2xl flex items-center justify-center z-10 opacity-30">
                                            <span class="mkt-text-muted text-[8px] sm:text-[10px] font-black italic">{{ $index + 1 }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- STEP CONTENT --}}
                                <div class="flex items-start justify-between gap-4">
                                    <div class="space-y-1">
                                        <h4 class="text-[10px] sm:text-xs font-black {{ $isDone ? 'mkt-text' : ($isCurrent ? 'text-indigo-600' : 'mkt-text-muted opacity-30') }} italic uppercase tracking-widest leading-none">
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
                                            <div class="bg-emerald-500/10 border border-emerald-500/10 px-2 sm:px-3 py-1 sm:py-2 rounded-xl text-right">
                                                <p class="text-[6px] sm:text-[7px] text-emerald-500 font-black uppercase tracking-widest mb-0.5 opacity-60">COMPLETED</p>
                                                <p class="text-[8px] sm:text-[10px] text-emerald-400 font-black italic leading-none">
                                                    {{ ($step['div'] === 'marketing') ? $trackingData->created_at->format('d/m/Y H:i') : $log->created_at->format('d/m/Y H:i') }}
                                                </p>
                                            </div>
                                            
                                            <button wire:click="toggleDetail('{{ $step['div'] }}')" 
                                                class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl border mkt-border transition-all shadow-lg group/btn shadow-indigo-600/20">
                                                <span class="text-[8px] text-white font-black uppercase tracking-widest">LIHAT</span>
                                                <svg class="w-3 h-3 text-white/70 group-hover/btn:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </button>
                                        @elseif($isCurrent)
                                            <div class="bg-indigo-600/10 border border-indigo-600/30 px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl text-center">
                                                <p class="text-[8px] sm:text-[9px] text-indigo-500 font-black animate-pulse tracking-[0.2em]">WIP</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- FINAL STATUS BADGE --}}
                    <div class="mt-6 sm:mt-10 p-4 sm:p-6 bg-slate-900/50 rounded-2xl sm:rounded-[2.5rem] border mkt-border text-center">
                        <p class="text-[7px] mkt-text-muted font-black uppercase tracking-[0.4em] mb-2 italic">CURRENT PRODUCTION STATE</p>
                        <h5 class="text-sm sm:text-lg font-black text-red-600 italic uppercase tracking-tighter">
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

    @if($expandedLog)
                        <div class="fixed inset-0 z-[120] flex items-center justify-center bg-black/60 backdrop-blur-md px-4 animate-in fade-in duration-300">
                            <div class="mkt-surface border mkt-border w-full max-w-2xl rounded-3xl sm:rounded-[3rem] shadow-[0_0_100px_rgba(0,0,0,0.8)] animate-in zoom-in slide-in-from-bottom-10 duration-500 relative overflow-hidden flex flex-col max-h-[85vh] bg-[#111c44] text-white">
                                
                                {{-- MODAL HEADER --}}
                                <div class="p-5 sm:p-8 border-b mkt-border flex justify-between items-center bg-slate-900/50">
                                    <div class="flex items-center gap-3 sm:gap-4">
                                        <div class="w-10 h-10 sm:w-12 sm:h-12 {{ $expandedLog == 'marketing' ? 'bg-red-600' : 'bg-emerald-600' }} rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg shadow-black/40 shrink-0">
                                            <span class="text-white text-base sm:text-lg font-black italic">{{ $expandedLog == 'marketing' ? 'MO' : 'OP' }}</span>
                                        </div>
                                        <div>
                                            <p class="text-[7px] sm:text-[8px] text-slate-400 font-black uppercase tracking-[0.3em]">
                                                {{ $expandedLog == 'marketing' ? 'MARKETING SPECIFICATIONS' : 'ACTUAL PRODUCTION DATA' }}
                                            </p>
                                            <h3 class="text-base sm:text-xl font-black mkt-text italic uppercase">
                                                {{ strtoupper(str_replace('_', ' ', $expandedLog)) }} RESULT
                                            </h3>
                                        </div>
                                    </div>
                                    <button wire:click="toggleDetail('{{ $expandedLog }}')" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-slate-800 flex items-center justify-center text-white hover:bg-red-600 transition-all shrink-0">
                                        ✕
                                    </button>
                                </div>

                                {{-- MODAL CONTENT --}}
                                <div class="p-4 sm:p-8 overflow-y-auto custom-scrollbar">
                                    @if($expandedLog == 'marketing')
                                        <div class="space-y-8 sm:space-y-10 custom-scrollbar pr-2 sm:pr-4">
                                            {{-- I. IDENTITAS ORDER --}}
                                            <div class="space-y-4">
                                                <p class="text-[9px] font-black text-red-500 uppercase tracking-[0.3em] border-l-4 border-red-500 pl-3">I. IDENTITAS ORDER</p>
                                                <div class="grid grid-cols-2 gap-4 sm:gap-6 bg-white/5 p-4 sm:p-5 rounded-2xl">
                                                    <div>
                                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">LEGACY SAP ID</p>
                                                        <p class="text-[10px] sm:text-[11px] font-black mkt-text opacity-60">{{ $trackingData->sap_no ?? '-' }}</p>
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
                                                        <p class="text-[11px] font-black mkt-text uppercase">{{ $trackingData->roll_target }} ROLL</p>
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
                                                            <p class="text-xl font-black text-red-600 italic tracking-tighter">{{ strtoupper($operatorActual) }}</p>
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
                                                        
                                                        {{-- I. MESIN (COLUMNS 1-6) --}}
                                                        <div class="space-y-4">
                                                            <p class="text-[9px] font-black text-blue-500 uppercase tracking-[0.3em] border-l-4 border-blue-500 pl-3">I. MESIN (SPESIFIKASI ALAT)</p>
                                                            <div class="grid grid-cols-3 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TANGGAL</p>
                                                                    <p class="text-[11px] font-black mkt-text">{{ !empty($techData['tanggal']) ? date('d/m/Y', strtotime($techData['tanggal'])) : $log->created_at->format('d/m/Y') }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">NO MESIN</p>
                                                                    <p class="text-[11px] font-black text-red-400 uppercase italic">{{ $techData['no_mesin'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TYPE MESIN</p>
                                                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['type_mesin'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">GAUGE / INCH</p>
                                                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['gauge_inch'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">JML FEEDER</p>
                                                                    <p class="text-[11px] font-black text-blue-400 uppercase">{{ $techData['jml_feeder'] ?? '0' }} <span class="text-[8px] mkt-text-muted">FDR</span></p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">JML JARUM</p>
                                                                    <p class="text-[11px] font-black text-blue-400 uppercase">{{ $techData['jml_jarum'] ?? '0' }} <span class="text-[8px] mkt-text-muted">JRM</span></p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- II. HASIL GREIGE (COLUMNS 7-10) --}}
                                                        <div class="space-y-4">
                                                            <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">II. HASIL GREIGE (FISIK KAIN)</p>
                                                            <div class="grid grid-cols-4 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">LEBAR</p>
                                                                    <p class="text-[11px] font-black mkt-text">{{ $techData['lebar'] ?? '-' }} <span class="text-[8px] mkt-text-muted">INCH</span></p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">GRAMASI</p>
                                                                    <p class="text-[11px] font-black mkt-text">{{ $techData['gramasi'] ?? '-' }} <span class="text-[8px] mkt-text-muted">GSM</span></p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">KG</p>
                                                                    <p class="text-[11px] font-black text-emerald-400 font-bold">{{ $log->kg }} KG</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">ROLL</p>
                                                                    <p class="text-[11px] font-black text-emerald-400 font-bold">{{ $log->roll }} ROLL</p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- III. PENGGUNAAN BENANG (COLUMNS 11-26) --}}
                                                        <div class="space-y-4">
                                                            <p class="text-[9px] font-black text-red-500 uppercase tracking-[0.3em] border-l-4 border-red-500 pl-3">III. PENGGUNAAN BENANG & YARN LENGTH (YL)</p>
                                                            <div class="grid grid-cols-2 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                {{-- SLOT 1 --}}
                                                                <div class="space-y-2 border-l border-red-500/20 pl-4 bg-white/5 p-3 rounded-xl transition-all">
                                                                    <p class="text-[8px] text-red-400 font-black uppercase mb-1 tracking-widest">SLOT BENANG 1</p>
                                                                    <div class="grid grid-cols-2 gap-2">
                                                                        <div>
                                                                            <p class="text-[8px] mkt-text-muted font-black uppercase">BENANG 1</p>
                                                                            <p class="text-[10px] font-black mkt-text uppercase truncate">{{ $techData['benang_1'] ?? '-' }}</p>
                                                                        </div>
                                                                        <div>
                                                                            <p class="text-[8px] mkt-text-muted font-black uppercase">LOT 1</p>
                                                                            <p class="text-[10px] font-black mkt-text uppercase truncate">{{ $techData['benang_1_lot'] ?? '-' }}</p>
                                                                        </div>
                                                                        <div>
                                                                            <p class="text-[8px] mkt-text-muted font-black uppercase">% 1</p>
                                                                            <p class="text-[10px] font-black text-red-500">{{ $techData['benang_1_percent'] ?? '-' }}</p>
                                                                        </div>
                                                                        <div>
                                                                            <p class="text-[8px] mkt-text-muted font-black uppercase">YL1</p>
                                                                            <p class="text-[10px] font-black text-blue-400">{{ $techData['yl_1'] ?? '-' }}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                {{-- SLOT 2 --}}
                                                                <div class="space-y-2 border-l border-red-500/20 pl-4 bg-white/5 p-3 rounded-xl transition-all">
                                                                    <p class="text-[8px] text-red-400 font-black uppercase mb-1 tracking-widest">SLOT BENANG 2</p>
                                                                    <div class="grid grid-cols-2 gap-2">
                                                                        <div>
                                                                            <p class="text-[8px] mkt-text-muted font-black uppercase">BENANG 2</p>
                                                                            <p class="text-[10px] font-black mkt-text uppercase truncate">{{ $techData['benang_2'] ?? '-' }}</p>
                                                                        </div>
                                                                        <div>
                                                                            <p class="text-[8px] mkt-text-muted font-black uppercase">LOT 2</p>
                                                                            <p class="text-[10px] font-black mkt-text uppercase truncate">{{ $techData['benang_2_lot'] ?? '-' }}</p>
                                                                        </div>
                                                                        <div>
                                                                            <p class="text-[8px] mkt-text-muted font-black uppercase">% 2</p>
                                                                            <p class="text-[10px] font-black text-red-500">{{ $techData['benang_2_percent'] ?? '-' }}</p>
                                                                        </div>
                                                                        <div>
                                                                            <p class="text-[8px] mkt-text-muted font-black uppercase">YL2</p>
                                                                            <p class="text-[10px] font-black text-blue-400">{{ $techData['yl_2'] ?? '-' }}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                {{-- SLOT 3 --}}
                                                                <div class="space-y-2 border-l border-red-500/20 pl-4 bg-white/5 p-3 rounded-xl transition-all">
                                                                    <p class="text-[8px] text-red-400 font-black uppercase mb-1 tracking-widest">SLOT BENANG 3</p>
                                                                    <div class="grid grid-cols-2 gap-2">
                                                                        <div>
                                                                            <p class="text-[8px] mkt-text-muted font-black uppercase">BENANG 3</p>
                                                                            <p class="text-[10px] font-black mkt-text uppercase truncate">{{ $techData['benang_3'] ?? '-' }}</p>
                                                                        </div>
                                                                        <div>
                                                                            <p class="text-[8px] mkt-text-muted font-black uppercase">LOT 3</p>
                                                                            <p class="text-[10px] font-black mkt-text uppercase truncate">{{ $techData['benang_3_lot'] ?? '-' }}</p>
                                                                        </div>
                                                                        <div>
                                                                            <p class="text-[8px] mkt-text-muted font-black uppercase">% 3</p>
                                                                            <p class="text-[10px] font-black text-red-500">{{ $techData['benang_3_percent'] ?? '-' }}</p>
                                                                        </div>
                                                                        <div>
                                                                            <p class="text-[8px] mkt-text-muted font-black uppercase">YL3</p>
                                                                            <p class="text-[10px] font-black text-blue-400">{{ $techData['yl_3'] ?? '-' }}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                {{-- SLOT 4 --}}
                                                                <div class="space-y-2 border-l border-red-500/20 pl-4 bg-white/5 p-3 rounded-xl transition-all">
                                                                    <p class="text-[8px] text-red-400 font-black uppercase mb-1 tracking-widest">SLOT BENANG 4</p>
                                                                    <div class="grid grid-cols-2 gap-2">
                                                                        <div>
                                                                            <p class="text-[8px] mkt-text-muted font-black uppercase">BENANG 4</p>
                                                                            <p class="text-[10px] font-black mkt-text uppercase truncate">{{ $techData['benang_4'] ?? '-' }}</p>
                                                                        </div>
                                                                        <div>
                                                                            <p class="text-[8px] mkt-text-muted font-black uppercase">LOT 4</p>
                                                                            <p class="text-[10px] font-black mkt-text uppercase truncate">{{ $techData['benang_4_lot'] ?? '-' }}</p>
                                                                        </div>
                                                                        <div>
                                                                            <p class="text-[8px] mkt-text-muted font-black uppercase">% 4</p>
                                                                            <p class="text-[10px] font-black text-red-500">{{ $techData['benang_4_percent'] ?? '-' }}</p>
                                                                        </div>
                                                                        <div>
                                                                            <p class="text-[8px] mkt-text-muted font-black uppercase">YL4</p>
                                                                            <p class="text-[10px] font-black text-blue-400">{{ $techData['yl_4'] ?? '-' }}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- IV. NOTE & TARGET PRODUKSI (COLUMNS 27-28) --}}
                                                        <div class="space-y-4">
                                                            <p class="text-[9px] font-black text-slate-500 uppercase tracking-[0.3em] border-l-4 border-slate-500 pl-3">IV. NOTE & TARGET PRODUKSI</p>
                                                            <div class="grid grid-cols-3 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                <div class="col-span-2">
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">NOTE</p>
                                                                    <div class="bg-black/20 p-4 rounded-xl border border-white/5">
                                                                        <p class="text-[10px] font-bold text-slate-400 italic leading-relaxed">"{{ $techData['note'] ?? 'Tidak ada catatan tambahan dari operator.' }}"</p>
                                                                    </div>
                                                                </div>
                                                                <div class="text-right flex flex-col justify-center">
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">PRODUKSI / DAY (KG)</p>
                                                                    <p class="text-2xl font-black text-purple-400 italic tracking-tighter">{{ $techData['produksi_per_day'] ?? '0' }} <span class="text-sm mkt-text-muted">KG</span></p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- V. DATA R&D (SPESIFIKASI RND) --}}
                                                        <div class="space-y-4">
                                                            <p class="text-[9px] font-black text-purple-500 uppercase tracking-[0.3em] border-l-4 border-purple-500 pl-3">V. DATA R&D (INPUT RAJUT)</p>
                                                            <div class="grid grid-cols-3 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">GRAMASI GREIGE</p>
                                                                    <p class="text-[11px] font-black text-purple-400 italic">{{ $techData['rnd_gramasi_greige'] ?? '-' }} GSM</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">MESIN RAJUT</p>
                                                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['rnd_mesin_rajut'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">JENIS MESIN RAJUT</p>
                                                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['rnd_jenis_mesin_rajut'] ?? '-' }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif($expandedLog === 'dyeing')
                                                    <div class="space-y-10 animate-in fade-in duration-700">
                                                        {{-- I. CEK GREIGE --}}
                                                        <div class="space-y-4">
                                                            <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">I. CEK GREIGE</p>
                                                            <div class="grid grid-cols-3 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">CEK GREIGE</p>
                                                                    <p class="text-[11px] font-black text-indigo-400 uppercase italic">{{ $techData['cek_greige'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">LEBAR</p>
                                                                    <p class="text-[11px] font-black text-indigo-400 italic">{{ $techData['lebar'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">GRAMASI</p>
                                                                    <p class="text-[11px] font-black text-indigo-400 italic">{{ $techData['gramasi'] ?? '-' }}</p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- II. PARAMETER LAINNYA --}}
                                                        <div class="space-y-4">
                                                            <p class="text-[9px] font-black text-blue-500 uppercase tracking-[0.3em] border-l-4 border-blue-500 pl-3">II. PARAMETER LAINNYA</p>
                                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OPERATOR</p>
                                                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['operator'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TANGGAL</p>
                                                                    <p class="text-[11px] font-black mkt-text">{{ !empty($techData['tanggal']) ? date('d/m/Y', strtotime($techData['tanggal'])) : '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">JENIS MESIN</p>
                                                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['jenis_mesin'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">NO. MESIN</p>
                                                                    <p class="text-[11px] font-black text-red-400 uppercase italic">{{ $techData['no_mesin'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">WARNA</p>
                                                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['warna'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">KODE WARNA</p>
                                                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['kode_warna'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">DYE SYSTEM</p>
                                                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['dye_system'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TREATMENT (CHEMICAL)</p>
                                                                    <p class="text-[11px] font-black text-emerald-400 uppercase">{{ $techData['treatment'] ?? '-' }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif($expandedLog === 'relax-dryer')
                                                    <div class="space-y-10 animate-in fade-in duration-700">
                                                        {{-- I. IDENTITAS & WAKTU --}}
                                                        <div class="space-y-4">
                                                            <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">I. IDENTITAS & WAKTU</p>
                                                            <div class="grid grid-cols-2 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OPERATOR</p>
                                                                    <p class="text-[11px] font-black text-purple-400 uppercase">{{ $techData['operator'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TANGGAL</p>
                                                                    <p class="text-[11px] font-black text-purple-400">{{ !empty($techData['tanggal']) ? date('d/m/Y', strtotime($techData['tanggal'])) : '-' }}</p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- II. PARAMETER TEKNIS & HASIL FISIK --}}
                                                        <div class="space-y-4">
                                                            <p class="text-[9px] font-black text-blue-500 uppercase tracking-[0.3em] border-l-4 border-blue-500 pl-3">II. PARAMETER TEKNIS & HASIL FISIK</p>
                                                            <div class="grid grid-cols-2 md:grid-cols-3 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                <div>
                                                                    <p class="text-[8px] mkt-text-muted font-black uppercase mb-1">CHEMICAL</p>
                                                                    <p class="text-[11px] font-black text-emerald-400 uppercase italic">{{ $techData['chemical'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[8px] mkt-text-muted font-black uppercase mb-1">HANDFEEL</p>
                                                                    <p class="text-[11px] font-black text-emerald-400 uppercase italic">{{ $techData['handfeel'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[8px] mkt-text-muted font-black uppercase mb-1">MESIN</p>
                                                                    <p class="text-[11px] font-black text-emerald-400 uppercase italic">{{ $techData['no_mesin'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[8px] mkt-text-muted font-black uppercase mb-1">OVERFEED</p>
                                                                    <p class="text-[11px] font-black text-indigo-400">{{ $techData['overfeed'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[8px] mkt-text-muted font-black uppercase mb-1">TEMPERATUR</p>
                                                                    <p class="text-[11px] font-black text-indigo-400">{{ $techData['suhu'] ?? '-' }}°C</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[8px] mkt-text-muted font-black uppercase mb-1">SPEED</p>
                                                                    <p class="text-[11px] font-black text-indigo-400">{{ $techData['speed'] ?? '-' }} m/min</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[8px] mkt-text-muted font-black uppercase mb-1">HASIL LEBAR</p>
                                                                    <p class="text-[11px] font-black text-emerald-400 italic">{{ $techData['lebar'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[8px] mkt-text-muted font-black uppercase mb-1">HASIL GRAMASI</p>
                                                                    <p class="text-[11px] font-black text-emerald-400 italic">{{ $techData['gramasi'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[8px] mkt-text-muted font-black uppercase mb-1">SHRINKAGE (V X H)</p>
                                                                    <p class="text-[11px] font-black text-emerald-400 italic">{{ $techData['shrinkage'] ?? '-' }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif($expandedLog === 'compactor')
                                                    <div class="space-y-10 animate-in fade-in duration-700">
                                                        {{-- I. IDENTITAS & WAKTU --}}
                                                        <div class="space-y-4">
                                                            <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">I. IDENTITAS & WAKTU</p>
                                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 bg-white/5 p-6 rounded-2xl">
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
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">RANGKA</p>
                                                                    <p class="text-[11px] font-black text-slate-100 uppercase italic">{{ $techData['rangka'] ?? '-' }}</p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- II. PARAMETER MESIN --}}
                                                        <div class="space-y-4">
                                                            <p class="text-[9px] font-black text-blue-500 uppercase tracking-[0.3em] border-l-4 border-blue-500 pl-3">II. PARAMETER MESIN & DRIVE SETTING</p>
                                                            <div class="grid grid-cols-2 md:grid-cols-3 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TEMPERATURE</p>
                                                                    <p class="text-[11px] font-black text-indigo-400">{{ $techData['suhu'] ?? '-' }}°C</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">SPEED</p>
                                                                    <p class="text-[11px] font-black text-indigo-400">{{ $techData['speed'] ?? '-' }} m/min</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OVERFEED</p>
                                                                    <p class="text-[11px] font-black text-indigo-400">{{ $techData['overfeed'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">FELT</p>
                                                                    <p class="text-[11px] font-black text-slate-100">{{ $techData['felt'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">DELIVERY SPEED</p>
                                                                    <p class="text-[11px] font-black text-slate-100">{{ $techData['delivery_speed'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">FOLDING SPEED</p>
                                                                    <p class="text-[11px] font-black text-slate-100">{{ $techData['folding_speed'] ?? '-' }}</p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- III. HASIL FISIK & OUTCOME --}}
                                                        <div class="space-y-4">
                                                            <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">III. HASIL FISIK & OUTCOME</p>
                                                            <div class="grid grid-cols-3 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HASIL LEBAR</p>
                                                                    <p class="text-[11px] font-black text-emerald-400 italic">{{ $techData['lebar'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HASIL GRAMASI</p>
                                                                    <p class="text-[11px] font-black text-emerald-400 italic">{{ $techData['gramasi'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">SHRINKAGE (V X H)</p>
                                                                    <p class="text-[11px] font-black text-emerald-400 italic">{{ $techData['shrinkage'] ?? '-' }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif($expandedLog === 'heat-setting')
                                                    <div class="space-y-10 animate-in fade-in duration-700">
                                                        {{-- I. IDENTITAS & WAKTU --}}
                                                        <div class="space-y-4">
                                                            <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">I. IDENTITAS & WAKTU</p>
                                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 bg-white/5 p-6 rounded-2xl">
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
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">RANGKA</p>
                                                                    <p class="text-[11px] font-black text-slate-100 uppercase italic">{{ $techData['rangka'] ?? '-' }}</p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- II. PARAMETER MESIN --}}
                                                        <div class="space-y-4">
                                                            <p class="text-[9px] font-black text-blue-500 uppercase tracking-[0.3em] border-l-4 border-blue-500 pl-3">II. PARAMETER MESIN & DRIVE SETTING</p>
                                                            <div class="grid grid-cols-2 md:grid-cols-3 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TEMPERATUR</p>
                                                                    <p class="text-[11px] font-black text-indigo-400">{{ $techData['suhu'] ?? '-' }}°C</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">SPEED</p>
                                                                    <p class="text-[11px] font-black text-indigo-400">{{ $techData['speed'] ?? '-' }} m/min</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OVERFEED</p>
                                                                    <p class="text-[11px] font-black text-indigo-400">{{ $techData['overfeed'] ?? '-' }}</p>
                                                                </div>
                                                                <div class="col-span-2 md:col-span-3"></div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">DELIVERY SPEED</p>
                                                                    <p class="text-[11px] font-black text-slate-100">{{ $techData['delivery_speed'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">FOLDING SPEED</p>
                                                                    <p class="text-[11px] font-black text-slate-100">{{ $techData['folding_speed'] ?? '-' }}</p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- III. HASIL FISIK & OUTCOME --}}
                                                        <div class="space-y-4">
                                                            <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">III. HASIL FISIK & OUTCOME</p>
                                                            <div class="grid grid-cols-2 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HASIL LEBAR</p>
                                                                    <p class="text-[11px] font-black text-emerald-400 italic">{{ $techData['lebar'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HASIL GRAMASI</p>
                                                                    <p class="text-[11px] font-black text-emerald-400 italic">{{ $techData['gramasi'] ?? '-' }}</p>
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
                                                                    <p class="text-[11px] font-black text-indigo-400 uppercase italic">{{ $techData['no_mesin'] ?? $log->machine_no ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TANGGAL SUBMIT</p>
                                                                    <p class="text-[11px] font-black text-slate-100">{{ !empty($log->created_at) ? date('d/m/Y H:i', strtotime($log->created_at)) : '-' }}</p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- II. TECHNICAL PARAMETERS LIST --}}
                                                        <div class="space-y-6">
                                                            <p class="text-[9px] font-black text-blue-500 uppercase tracking-[0.3em] border-l-4 border-blue-500 pl-3">II. TECHNICAL PARAMETERS LOG</p>
                                                            
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
                                                                    
                                                                    <div class="bg-white/5 border border-white/5 p-6 rounded-[2rem] shadow-sm hover:border-violet-500/50 transition-all duration-300">
                                                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                                                            <!-- Parameter Title -->
                                                                            <div class="col-span-3 flex items-center gap-3">
                                                                                <div class="w-2 h-2 rounded-full bg-violet-500"></div>
                                                                                <span class="text-xs font-black uppercase text-violet-400 tracking-wider font-semibold">
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
                                                            <p class="text-[9px] font-black text-blue-500 uppercase tracking-[0.3em] border-l-4 border-blue-500 pl-3">II. PARAMETER SETTING MESIN</p>
                                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TEMPERATURE</p>
                                                                    <p class="text-[11px] font-black text-indigo-400">{{ $techData['suhu'] ?? '-' }}°C</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">STEAM INJECT</p>
                                                                    <p class="text-[11px] font-black text-indigo-400">{{ $techData['steam_inject'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HOTWIND</p>
                                                                    <p class="text-[11px] font-black text-indigo-400">{{ $techData['hotwind'] ?? '-' }}</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">COLDWIND</p>
                                                                    <p class="text-[11px] font-black text-indigo-400">{{ $techData['coldwind'] ?? '-' }}</p>
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
                                                            <p class="text-[9px] font-black text-blue-500 uppercase tracking-[0.3em] border-l-4 border-blue-500 pl-3">II. DETAIL PARAMETER PROSES (SIDE-BY-SIDE)</p>
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
                                                                            <div class="grid grid-cols-12 gap-6 bg-white/[0.02] border border-white/5 hover:border-violet-500/30 px-6 py-5 rounded-[1.5rem] items-center transition-all duration-300">
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
                                                            <p class="text-[9px] font-black text-blue-500 uppercase tracking-[0.3em] border-l-4 border-blue-500 pl-3">II. HASIL PENGUJIAN FISIK</p>
                                                            <div class="grid grid-cols-2 gap-6 bg-white/5 p-6 rounded-2xl">
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HASIL LEBAR</p>
                                                                    <p class="text-[11px] font-black text-indigo-400 italic">{{ $techData['lebar'] ?? '-' }} cm</p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HASIL GRAMASI</p>
                                                                    <p class="text-[11px] font-black text-indigo-400 italic">{{ $techData['gramasi'] ?? '-' }} gsm</p>
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
                                                            <p class="text-[9px] font-black text-indigo-500 uppercase tracking-[0.3em] border-l-4 border-indigo-500 pl-3">I. IDENTITAS KAIN & OPERATOR (QE)</p>
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
</div>
