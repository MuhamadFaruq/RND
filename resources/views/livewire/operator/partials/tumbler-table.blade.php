{{-- resources/views/livewire/operator/partials/tumbler-table.blade.php --}}
<div class="mkt-surface p-4 md:p-5 rounded-2xl shadow-sm border mkt-border flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 group italic hover:border-orange-500 transition-all duration-300">
    <div class="flex items-center gap-3">
        <div class="bg-orange-600/20 text-orange-400 w-10 h-10 rounded-xl flex items-center justify-center font-black text-[10px] shadow-sm border border-orange-500/30 shrink-0">
            TB
        </div>
        <div class="text-left min-w-0">
            <span class="text-[9px] font-black text-orange-400 uppercase tracking-wider">#{{ $job->art_no }}</span>
            @if($job->is_urgent)
                <span class="ml-1 bg-red-600 text-white text-[7px] font-black px-1.5 py-0.5 rounded-full uppercase animate-pulse">URGENT</span>
            @endif
            <h4 class="text-sm font-black mkt-text leading-none uppercase">{{ $job->art_no }}</h4>
            <p class="text-[9px] font-bold mkt-text-muted uppercase mt-0.5 truncate">{{ $job->pelanggan }}</p>
        </div>
    </div>

    <div class="flex items-center justify-between sm:justify-end gap-4 sm:gap-6 pt-2 sm:pt-0 border-t sm:border-t-0 mkt-border">
        <div class="text-left sm:text-right sm:border-r sm:pr-6 mkt-border">
            <p class="text-[8px] font-black mkt-text-muted uppercase leading-none mb-0.5">Target</p>
            <p class="text-sm font-black mkt-text italic">{{ (float)$job->kg_target }} KG</p>
        </div>
        
        <button wire:click="showOrderDetail({{ $job->id }})" 
            class="mkt-surface-alt border mkt-border mkt-text px-4 py-2 rounded-xl text-[9px] font-black uppercase hover:bg-indigo-600 hover:text-white hover:border-transparent transition-all shadow-sm shrink-0">
            DETAIL & PROSES
        </button>
    </div>
</div>