{{-- resources/views/livewire/operator/partials/finishing-table.blade.php --}}
<div class="mkt-surface p-4 md:p-5 rounded-2xl shadow-sm border mkt-border flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 group italic hover:border-emerald-500 transition-all duration-300">
    <div class="flex items-center gap-3">
        <div class="bg-emerald-600/20 text-emerald-400 w-10 h-10 rounded-xl flex items-center justify-center font-black text-[10px] shadow-sm border border-emerald-500/30 shrink-0">
            FN
        </div>
        <div class="text-left min-w-0">
            <span class="text-[9px] font-black text-emerald-400 uppercase tracking-wider">#{{ $job->art_no }}</span>
            @if($job->is_urgent)
                <span class="ml-1 bg-red-600 text-white text-[7px] font-black px-1.5 py-0.5 rounded-full uppercase animate-pulse">URGENT</span>
            @endif
            <h4 class="text-sm font-black mkt-text leading-none uppercase">{{ $job->art_no }}</h4>
            <div class="flex items-center gap-1.5 mt-0.5">
                <p class="text-[9px] font-bold mkt-text-muted uppercase tracking-tighter truncate">{{ $job->pelanggan }}</p>
                <span class="w-1 h-1 bg-slate-500 rounded-full shrink-0"></span>
                <p class="text-[9px] font-black text-rose-400 uppercase tracking-tighter truncate">{{ $job->warna }}</p>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between sm:justify-end gap-4 sm:gap-6 pt-2 sm:pt-0 border-t sm:border-t-0 mkt-border">
        <div class="text-left sm:text-right sm:border-r sm:pr-6 mkt-border">
            <p class="text-[8px] font-black mkt-text-muted uppercase leading-none mb-0.5">Target</p>
            <p class="text-sm font-black mkt-text italic">{{ (float)$job->kg_target }} <span class="text-[9px]">KG</span></p>
        </div>
        
        <button wire:click="showOrderDetail({{ $job->id }})" 
            class="mkt-surface-alt border mkt-border mkt-text px-4 py-2 rounded-xl text-[9px] font-black uppercase hover:bg-brand-600 hover:text-white hover:border-transparent transition-all shadow-sm shrink-0">
            DETAIL & PROSES
        </button>
    </div>
</div>