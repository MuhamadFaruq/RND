{{-- resources/views/livewire/operator/partials/knitting-table.blade.php --}}

{{-- Desktop: Table Layout --}}
<div class="hidden md:block mkt-surface rounded-2xl shadow-sm border mkt-border overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="mkt-surface-alt border-b mkt-border">
                <th class="px-4 lg:px-5 py-3 text-[9px] font-black mkt-text-muted uppercase italic tracking-wider">ARTIKEL NO</th>
                <th class="px-4 lg:px-5 py-3 text-[9px] font-black mkt-text-muted uppercase italic tracking-wider">Produk (Knitting)</th>
                <th class="px-4 lg:px-5 py-3 text-[9px] font-black mkt-text-muted uppercase italic tracking-wider">Material</th>
                <th class="px-4 lg:px-5 py-3 text-[9px] font-black mkt-text-muted uppercase italic tracking-wider">Target</th>
                <th class="px-4 lg:px-5 py-3 text-[9px] font-black mkt-text-muted uppercase italic text-center tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y mkt-border">
            @forelse($workQueue as $job)
                <tr class="hover:bg-indigo-600/5 transition-colors duration-300">
                    <td class="px-4 lg:px-5 py-3">
                        <span class="text-[10px] font-black text-indigo-400 uppercase italic">#{{ $job->art_no }}</span>
                        @if($job->is_urgent)
                            <span class="ml-1 bg-red-600 text-white text-[7px] font-black px-1.5 py-0.5 rounded-full uppercase animate-pulse">URGENT</span>
                        @endif
                    </td>
                    <td class="px-4 lg:px-5 py-3">
                        <h4 class="text-xs font-black mkt-text uppercase leading-none italic">{{ $job->art_no }}</h4>
                        <p class="text-[9px] font-bold mkt-text-muted mt-0.5 uppercase italic">{{ $job->warna }}</p>
                    </td>
                    <td class="px-4 lg:px-5 py-3">
                        <p class="text-[10px] font-black mkt-text uppercase italic">{{ $job->material }}</p>
                        <p class="text-[9px] font-bold text-emerald-400 mt-0.5 uppercase italic truncate max-w-[140px]">{{ $job->benang }}</p>
                    </td>
                    <td class="px-4 lg:px-5 py-3">
                        <p class="text-sm font-black mkt-text leading-none italic">{{ (float)$job->kg_target }} KG</p>
                        @if($job->processing_by)
                            <div class="mt-1 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                <span class="text-[7px] font-black text-amber-500 uppercase italic truncate max-w-[100px]">
                                    {{ $job->processingBy->name ?? 'Unknown' }}
                                </span>
                            </div>
                        @endif
                    </td>
                    <td class="px-4 lg:px-5 py-3 text-center">
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
                                class="mkt-surface-alt border mkt-border mkt-text px-4 py-1.5 rounded-xl font-black text-[9px] uppercase hover:bg-indigo-600 hover:border-transparent hover:text-white transition-all shadow-sm">
                                DETAIL & TERIMA
                            </button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-12 text-center mkt-text-muted font-black uppercase text-[10px] italic">Belum ada order masuk dari Marketing</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Mobile: Card Layout --}}
<div class="md:hidden space-y-3">
    @forelse($workQueue as $job)
        <div class="mkt-surface p-4 rounded-2xl shadow-sm border mkt-border italic">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-600/15 text-indigo-400 w-10 h-10 rounded-xl flex items-center justify-center font-black text-[10px] border border-indigo-500/20 shrink-0">KN</div>
                    <div>
                        <span class="text-[9px] font-black text-indigo-400 uppercase">#{{ $job->art_no }}</span>
                        @if($job->is_urgent)
                            <span class="ml-1 bg-red-600 text-white text-[7px] font-black px-1.5 py-0.5 rounded-full uppercase animate-pulse">URGENT</span>
                        @endif
                        <h4 class="text-sm font-black mkt-text uppercase leading-none mt-0.5">{{ $job->art_no }}</h4>
                        <p class="text-[9px] font-bold mkt-text-muted uppercase mt-0.5">{{ $job->warna }}</p>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-[8px] font-black mkt-text-muted uppercase">Target</p>
                    <p class="text-sm font-black mkt-text italic">{{ (float)$job->kg_target }} KG</p>
                </div>
            </div>
            <div class="flex items-center justify-between pt-2 border-t mkt-border">
                <p class="text-[9px] font-bold mkt-text-muted uppercase truncate max-w-[50%]">{{ $job->material }} • {{ $job->benang }}</p>
                @if($job->processing_by && $job->processing_by !== auth()->id())
                    <button wire:click="takeOverProcessAndRedirect({{ $job->id }})" 
                            class="bg-amber-500 text-white px-3 py-1.5 rounded-xl text-[8px] font-black uppercase">AMBIL ALIH</button>
                @elseif($job->processing_by === auth()->id())
                    <button wire:click="startProcessAndRedirect({{ $job->id }})" 
                            class="bg-indigo-600 text-white px-3 py-1.5 rounded-xl text-[8px] font-black uppercase animate-pulse">LANJUTKAN</button>
                @else
                    <button wire:click="showOrderDetail({{ $job->id }})" 
                        class="mkt-surface-alt border mkt-border mkt-text px-3 py-1.5 rounded-xl font-black text-[8px] uppercase hover:bg-indigo-600 hover:text-white transition-all">DETAIL & TERIMA</button>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-12 mkt-surface-alt rounded-2xl border-2 border-dashed border-white/10">
            <p class="text-slate-400 font-black uppercase text-[10px]">Belum ada order masuk</p>
        </div>
    @endforelse
</div>