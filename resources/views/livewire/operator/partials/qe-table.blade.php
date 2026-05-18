{{-- resources/views/livewire/operator/partials/qe-table.blade.php --}}
<div class="mkt-surface p-6 rounded-[2.5rem] shadow-sm border mkt-border flex justify-between items-center group italic hover:border-violet-500 transition-all duration-300">
    <div class="flex items-center gap-6">
        <div class="bg-violet-600/20 text-violet-400 w-14 h-14 rounded-2xl flex items-center justify-center font-black text-xl shadow-lg border border-violet-500/30">
            🛡️
        </div>
        <div class="text-left">
            <span class="text-[10px] font-black text-violet-400 uppercase tracking-widest drop-shadow-[0_0_5px_rgba(139,92,246,0.3)]">#{{ $job->art_no }}</span>
            @if($job->is_urgent)
                <span class="ml-2 bg-red-600 text-white text-[8px] font-black px-2 py-0.5 rounded-full uppercase animate-pulse">URGENT</span>
            @endif
            <h4 class="text-xl font-black mkt-text leading-none uppercase">{{ $job->art_no }}</h4>
            <p class="text-[10px] font-bold mkt-text-muted uppercase mt-1">{{ $job->pelanggan }}</p>
            @if($job->processing_by)
                <div class="mt-2 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse shadow-[0_0_5px_rgba(245,158,11,0.5)]"></span>
                    <span class="text-[8px] font-black text-amber-500 uppercase italic">
                        ⚙️ IN PROGRESS: {{ $job->processingBy->name ?? 'Unknown' }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-8">
        <div class="text-right border-r pr-8 mkt-border">
            <p class="text-[9px] font-black mkt-text-muted uppercase leading-none mb-1">Status</p>
            <p class="text-xs font-black text-violet-400 uppercase italic">Menunggu Final QE</p>
        </div>
        
        <div class="flex flex-col gap-2 w-full max-w-[150px]">
            @if($job->processing_by && $job->processing_by !== auth()->id())
                <button wire:click="takeOverProcessAndRedirect({{ $job->id }})" 
                        class="bg-amber-500 text-white px-4 py-2 rounded-2xl text-[9px] font-black uppercase hover:bg-amber-600 transition-all shadow-lg shadow-amber-500/20">
                    AMBIL ALIH
                </button>
            @elseif($job->processing_by === auth()->id())
                <button wire:click="startProcessAndRedirect({{ $job->id }})" 
                        class="bg-indigo-600 text-white px-6 py-2 rounded-2xl text-[10px] font-black uppercase hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-600/30 animate-pulse">
                    LANJUTKAN PROSES
                </button>
            @else
                <button wire:click="showOrderDetail({{ $job->id }})" 
                    class="mkt-surface-alt border mkt-border mkt-text px-6 py-3 rounded-2xl text-[10px] font-black uppercase hover:bg-indigo-600 hover:border-transparent hover:text-white transition-all duration-300 shadow-md">
                    DETAIL & TERIMA
                </button>
            @endif
        </div>
    </div>
</div>