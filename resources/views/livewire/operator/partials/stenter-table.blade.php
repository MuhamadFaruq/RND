{{-- resources/views/livewire/operator/partials/stenter-table.blade.php --}}
<div class="mkt-surface p-6 rounded-[2.5rem] shadow-sm border mkt-border flex justify-between items-center group italic hover:border-indigo-500 transition-all duration-300">
    <div class="flex items-center gap-6">
        <div class="bg-indigo-600/20 text-indigo-400 w-14 h-14 rounded-2xl flex items-center justify-center font-black text-xl shadow-lg border border-indigo-500/30">
            🔥
        </div>
        <div class="text-left">
            <span class="text-[10px] font-black text-amber-400 uppercase tracking-widest drop-shadow-[0_0_5px_rgba(245,158,11,0.3)]">#{{ $job->art_no }}</span>
            @if($job->is_urgent)
                <span class="ml-2 bg-red-600 text-white text-[8px] font-black px-2 py-0.5 rounded-full uppercase animate-pulse">URGENT</span>
            @endif
            <h4 class="text-xl font-black mkt-text leading-none uppercase">{{ $job->art_no }}</h4>
            <div class="flex items-center gap-2 mt-1">
                <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-tighter">{{ $job->pelanggan }}</p>
                <span class="w-1 h-1 bg-slate-300 dark:bg-slate-700 rounded-full"></span>
                <p class="text-[10px] font-black text-indigo-400 uppercase tracking-tighter">{{ $job->warna }}</p>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-8">
        <div class="text-right border-r pr-8 mkt-border">
            <p class="text-[9px] font-black mkt-text-muted uppercase leading-none mb-1">Target Berat</p>
            <p class="text-base font-black mkt-text italic">{{ number_format($job->kg_target, 1) }} <span class="text-[10px]">KG</span></p>
        </div>
        
        <button wire:click="showOrderDetail({{ $job->id }})" 
            class="mkt-surface-alt border mkt-border mkt-text px-6 py-3 rounded-2xl text-[10px] font-black uppercase hover:bg-indigo-600 hover:text-white hover:border-transparent transition-all duration-300 shadow-md">
            DETAIL & PROSES
        </button>
    </div>
</div>