{{-- resources/views/livewire/operator/partials/pengujian-table.blade.php --}}
<div class="mkt-surface p-4 md:p-5 rounded-2xl shadow-sm border mkt-border flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 group italic hover:border-cyan-500 transition-all duration-300">
    <div class="flex items-center gap-3">
        <div class="bg-cyan-600/20 text-cyan-400 w-10 h-10 rounded-xl flex items-center justify-center font-black text-[10px] shadow-sm border border-cyan-500/30 shrink-0">
            PJ
        </div>
        <div class="text-left min-w-0">
            <span class="text-[9px] font-black text-cyan-400 uppercase tracking-wider">#{{ $job->art_no }}</span>
            @if($job->is_urgent)
                <span class="ml-1 bg-red-600 text-white text-[7px] font-black px-1.5 py-0.5 rounded-full uppercase animate-pulse">URGENT</span>
            @endif
            <h4 class="text-sm font-black mkt-text leading-none uppercase">{{ $job->art_no }}</h4>
            <p class="text-[9px] font-bold mkt-text-muted uppercase mt-0.5 truncate">{{ $job->pelanggan }}</p>
            @if($job->processing_by)
                <div class="mt-1 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    <span class="text-[7px] font-black text-amber-500 uppercase italic truncate">
                        IN PROGRESS: {{ $job->processingBy->name ?? 'Unknown' }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    <div class="flex items-center justify-between sm:justify-end gap-4 sm:gap-6 pt-2 sm:pt-0 border-t sm:border-t-0 mkt-border">
        <div class="text-left sm:text-right sm:border-r sm:pr-6 mkt-border">
            <p class="text-[8px] font-black mkt-text-muted uppercase leading-none mb-0.5">Target</p>
            <p class="text-sm font-black mkt-text italic">{{ (float)$job->kg_target }} KG</p>
        </div>
        
        <div class="shrink-0">
            @if($job->processing_by && $job->processing_by !== auth()->id())
                <button wire:click="takeOverProcessAndRedirect({{ $job->id }})" 
                        class="bg-amber-500 text-white px-3 py-1.5 rounded-xl text-[8px] font-black uppercase hover:bg-amber-600 transition-all shadow-sm">
                    AMBIL ALIH
                </button>
            @elseif($job->processing_by === auth()->id())
                <button wire:click="startProcessAndRedirect({{ $job->id }})" 
                        class="bg-indigo-600 text-white px-4 py-1.5 rounded-xl text-[9px] font-black uppercase hover:bg-indigo-700 transition-all shadow-sm animate-pulse">
                    LANJUTKAN
                </button>
            @else
                <button wire:click="showOrderDetail({{ $job->id }})" 
                    class="mkt-surface-alt border mkt-border mkt-text px-4 py-2 rounded-xl text-[9px] font-black uppercase hover:bg-indigo-600 hover:text-white hover:border-transparent transition-all shadow-sm">
                    DETAIL & TERIMA
                </button>
            @endif
        </div>
    </div>
</div>