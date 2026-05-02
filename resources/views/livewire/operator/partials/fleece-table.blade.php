<div class="mkt-surface p-6 rounded-[2.5rem] shadow-sm border mkt-border flex justify-between items-center group italic hover:border-amber-300 transition-all">
    <div class="flex items-center gap-6">
        <div class="bg-amber-50 text-amber-600 w-14 h-14 rounded-2xl flex items-center justify-center font-black text-xl shadow-sm">
            🧶
        </div>
        <div class="text-left">
            <span class="text-[10px] font-black text-amber-600 uppercase tracking-widest">#{{ $job->sap_no }}</span>
            <h4 class="text-xl font-black mkt-text leading-none uppercase">{{ $job->art_no }}</h4>
            <p class="text-[10px] font-bold mkt-text-muted uppercase mt-1">{{ $job->pelanggan }}</p>
        </div>
    </div>

    <div class="flex items-center gap-8">
        <div class="text-right border-r pr-8 mkt-border">
            <p class="text-[9px] font-black mkt-text-muted uppercase leading-none mb-1">Target Berat</p>
            <p class="text-base font-black mkt-text italic">{{ number_format($job->kg_target, 1) }} KG</p>
        </div>
        
        <button wire:click="showOrderDetail({{ $job->id }})" 
            class="bg-slate-900 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase hover:bg-blue-600 transition-all shadow-lg shadow-slate-200">
            DETAIL & PROSES
        </button>
    </div>
</div>