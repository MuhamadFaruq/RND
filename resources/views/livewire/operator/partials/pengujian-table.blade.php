{{-- resources/views/livewire/operator/partials/pengujian-table.blade.php --}}
<div class="bg-gradient-to-br from-slate-900/60 to-slate-800/40 backdrop-blur-xl p-6 rounded-[2.5rem] shadow-2xl border border-white/10 flex justify-between items-center group italic hover:border-cyan-500 transition-all duration-300">
    <div class="flex items-center gap-6">
        <div class="bg-cyan-600/20 text-cyan-400 w-14 h-14 rounded-2xl flex items-center justify-center font-black text-xl shadow-lg border border-cyan-500/30">
            🔬
        </div>
        <div class="text-left">
            <span class="text-[10px] font-black text-cyan-400 uppercase tracking-widest drop-shadow-[0_0_5px_rgba(6,182,212,0.3)]">#{{ $job->art_no }}</span>
            @if($job->is_urgent)
                <span class="ml-2 bg-red-600 text-white text-[8px] font-black px-2 py-0.5 rounded-full uppercase animate-pulse">URGENT</span>
            @endif
            <h4 class="text-xl font-black text-white leading-none uppercase">{{ $job->art_no }}</h4>
            <p class="text-[10px] font-bold text-slate-400 uppercase mt-1">{{ $job->pelanggan }}</p>
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
        <div class="text-right border-r pr-8 border-white/10">
            <p class="text-[9px] font-black text-slate-400 uppercase leading-none mb-1">Target Berat</p>
            <p class="text-base font-black text-white italic">{{ number_format($job->kg_target, 1) }} KG</p>
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
                    class="bg-white/5 dark:bg-slate-900/50 border border-white/10 dark:border-white/5 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase hover:bg-indigo-600 hover:text-white hover:border-transparent transition-all duration-300 shadow-md">
                    DETAIL & TERIMA
                </button>
            @endif
        </div>
    </div>
</div>