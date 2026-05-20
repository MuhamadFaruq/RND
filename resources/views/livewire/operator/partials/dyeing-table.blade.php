{{-- resources/views/livewire/operator/partials/dyeing-table.blade.php --}}

{{-- Desktop: Table Layout --}}
<div class="hidden md:block mkt-surface rounded-2xl shadow-sm border mkt-border overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="mkt-surface-alt border-b mkt-border">
                <th class="px-4 lg:px-5 py-3 text-[9px] font-black mkt-text-muted uppercase italic tracking-wider">ARTIKEL NO</th>
                <th class="px-4 lg:px-5 py-3 text-[9px] font-black mkt-text-muted uppercase italic tracking-wider">Produk (Dyeing)</th>
                <th class="px-4 lg:px-5 py-3 text-[9px] font-black mkt-text-muted uppercase italic tracking-wider">Status Alur</th>
                <th class="px-4 lg:px-5 py-3 text-[9px] font-black mkt-text-muted uppercase italic text-center tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y mkt-border">
            @forelse($workQueue as $job)
                <tr class="hover:bg-indigo-600/5 transition-colors duration-300">
                    <td class="px-4 lg:px-5 py-3">
                        <span class="text-[10px] font-black text-indigo-400 uppercase">#{{ $job->art_no }}</span>
                        @if($job->is_urgent)
                            <span class="ml-1 bg-red-600 text-white text-[7px] font-black px-1.5 py-0.5 rounded-full uppercase animate-pulse">URGENT</span>
                        @endif
                    </td>
                    <td class="px-4 lg:px-5 py-3">
                        <h4 class="text-xs font-black mkt-text uppercase leading-none italic">{{ $job->art_no }}</h4>
                        <p class="text-[9px] font-bold mkt-text-muted mt-0.5 uppercase italic">{{ $job->warna }}</p>
                    </td>

                    <td class="px-4 lg:px-5 py-3">
                        @if($job->processing_by)
                            <div class="flex flex-col gap-1">
                                <span class="px-2 py-0.5 bg-amber-600/20 text-amber-500 rounded-full text-[8px] font-black uppercase w-fit border border-amber-500/30">
                                    IN PROGRESS
                                </span>
                                <div class="flex items-center gap-1 ml-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    <span class="text-[7px] font-black text-amber-500 uppercase italic truncate max-w-[100px]">
                                        {{ $job->processingBy->name ?? 'Unknown' }}
                                    </span>
                                </div>
                            </div>
                        @else
                            <div class="flex flex-col gap-1">
                                <span class="px-2 py-0.5 bg-indigo-600/20 text-indigo-400 rounded-full text-[8px] font-black uppercase italic w-fit border border-indigo-500/30">
                                    Tersedia
                                </span>
                                <div class="flex items-center gap-1 ml-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                    <span class="text-[7px] font-black text-green-500 uppercase italic">Menunggu</span>
                                </div>
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
                                    class="bg-indigo-600 text-white px-4 py-1.5 rounded-xl text-[9px] font-black uppercase hover:bg-indigo-700 transition-all shadow-sm">
                                LANJUTKAN
                            </button>
                        @else
                            <button wire:click="showOrderDetail({{ $job->id }})" 
                                    class="mkt-surface-alt border mkt-border mkt-text px-4 py-1.5 rounded-xl text-[9px] font-black uppercase hover:bg-indigo-600 hover:border-transparent hover:text-white transition-all shadow-sm">
                                DETAIL & TERIMA
                            </button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-12 text-center mkt-text-muted font-black uppercase text-[10px] italic">Menunggu hasil produksi dari Knitting...</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Mobile: Card Layout --}}
<div class="md:hidden space-y-3">
    @forelse($workQueue as $job)
        <div class="mkt-surface p-4 rounded-2xl shadow-sm border mkt-border italic">
            <div class="flex items-start justify-between mb-2">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-600/15 text-indigo-400 w-10 h-10 rounded-xl flex items-center justify-center font-black text-[10px] border border-indigo-500/20 shrink-0">DY</div>
                    <div>
                        <span class="text-[9px] font-black text-indigo-400 uppercase">#{{ $job->art_no }}</span>
                        @if($job->is_urgent)
                            <span class="ml-1 bg-red-600 text-white text-[7px] font-black px-1.5 py-0.5 rounded-full uppercase animate-pulse">URGENT</span>
                        @endif
                        <h4 class="text-sm font-black mkt-text uppercase leading-none mt-0.5">{{ $job->art_no }}</h4>
                        <p class="text-[9px] font-bold mkt-text-muted uppercase mt-0.5">{{ $job->warna }}</p>
                    </div>
                </div>
                @if($job->processing_by)
                    <span class="px-2 py-0.5 bg-amber-600/20 text-amber-500 rounded-full text-[7px] font-black uppercase border border-amber-500/30 shrink-0">IN PROGRESS</span>
                @else
                    <span class="px-2 py-0.5 bg-green-600/20 text-green-400 rounded-full text-[7px] font-black uppercase border border-green-500/30 shrink-0">TERSEDIA</span>
                @endif
            </div>
            <div class="flex items-center justify-end pt-2 border-t mkt-border">
                @if($job->processing_by && $job->processing_by !== auth()->id())
                    <button wire:click="takeOverProcessAndRedirect({{ $job->id }})" class="bg-amber-500 text-white px-3 py-1.5 rounded-xl text-[8px] font-black uppercase">AMBIL ALIH</button>
                @elseif($job->processing_by === auth()->id())
                    <button wire:click="startProcessAndRedirect({{ $job->id }})" class="bg-indigo-600 text-white px-3 py-1.5 rounded-xl text-[8px] font-black uppercase">LANJUTKAN</button>
                @else
                    <button wire:click="showOrderDetail({{ $job->id }})" class="mkt-surface-alt border mkt-border mkt-text px-3 py-1.5 rounded-xl font-black text-[8px] uppercase hover:bg-indigo-600 hover:text-white transition-all">DETAIL & TERIMA</button>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-12 mkt-surface-alt rounded-2xl border-2 border-dashed border-white/10">
            <p class="text-slate-400 font-black uppercase text-[10px]">Menunggu hasil dari Knitting...</p>
        </div>
    @endforelse
</div>