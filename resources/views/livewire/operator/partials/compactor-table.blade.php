{{-- resources/views/livewire/operator/partials/compactor-table.blade.php --}}

{{-- Desktop: Table Layout --}}
<div class="hidden md:block mkt-surface rounded-2xl shadow-sm border mkt-border overflow-hidden animate-in fade-in duration-500">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="mkt-surface-alt border-b mkt-border font-black italic">
                <th class="px-4 lg:px-5 py-3 text-[9px] mkt-text-muted uppercase">ARTIKEL NO</th>
                <th class="px-4 lg:px-5 py-3 text-[9px] mkt-text-muted uppercase">Produk (COMPACTOR)</th>
                <th class="px-4 lg:px-5 py-3 text-[9px] mkt-text-muted uppercase">Status Alur</th>
                <th class="px-4 lg:px-5 py-3 text-[9px] mkt-text-muted uppercase text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y mkt-border italic">
            @forelse($workQueue as $job)
                <tr class="hover:bg-indigo-600/5 transition-colors">
                    <td class="px-4 lg:px-5 py-3">
                        <span class="text-[10px] font-black text-indigo-500 uppercase">#{{ $job->art_no }}</span>
                        @if($job->is_urgent)
                            <span class="ml-1 bg-red-600 text-white text-[7px] font-black px-1.5 py-0.5 rounded-full uppercase animate-pulse">URGENT</span>
                        @endif
                    </td>
                    <td class="px-4 lg:px-5 py-3">
                        <h4 class="text-xs font-black mkt-text uppercase leading-none">{{ $job->art_no }}</h4>
                        <p class="text-[9px] font-bold mkt-text-muted mt-0.5 uppercase">{{ $job->warna }}</p>
                    </td>
                    <td class="px-4 lg:px-5 py-3 text-xs">
                        <span class="px-2 py-0.5 bg-indigo-600/10 text-indigo-500 rounded-full text-[8px] font-black uppercase">
                            Siap Compactor
                        </span>
                    </td>
                    <td class="px-4 lg:px-5 py-3 text-center">
                        <button wire:click="showOrderDetail({{ $job->id }})" 
                                class="mkt-surface-alt border mkt-border mkt-text px-4 py-1.5 rounded-xl text-[9px] font-black uppercase hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                            DETAIL & PROSES
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-12 text-center mkt-text-muted font-black uppercase text-[10px] italic">
                        Tidak ada antrean untuk divisi Compactor...
                    </td>
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
                    <div class="bg-emerald-600/15 text-emerald-400 w-10 h-10 rounded-xl flex items-center justify-center font-black text-[10px] border border-emerald-500/20 shrink-0">CP</div>
                    <div>
                        <span class="text-[9px] font-black text-indigo-500 uppercase">#{{ $job->art_no }}</span>
                        @if($job->is_urgent)
                            <span class="ml-1 bg-red-600 text-white text-[7px] font-black px-1.5 py-0.5 rounded-full uppercase animate-pulse">URGENT</span>
                        @endif
                        <h4 class="text-sm font-black mkt-text uppercase leading-none mt-0.5">{{ $job->art_no }}</h4>
                        <p class="text-[9px] font-bold mkt-text-muted uppercase mt-0.5">{{ $job->warna }}</p>
                    </div>
                </div>
                <span class="px-2 py-0.5 bg-indigo-600/10 text-indigo-500 rounded-full text-[7px] font-black uppercase shrink-0">Siap Compactor</span>
            </div>
            <div class="flex justify-end pt-2 border-t mkt-border">
                <button wire:click="showOrderDetail({{ $job->id }})" 
                    class="mkt-surface-alt border mkt-border mkt-text px-3 py-1.5 rounded-xl font-black text-[8px] uppercase hover:bg-indigo-600 hover:text-white transition-all">DETAIL & PROSES</button>
            </div>
        </div>
    @empty
        <div class="text-center py-12 mkt-surface-alt rounded-2xl border-2 border-dashed border-white/10">
            <p class="text-slate-400 font-black uppercase text-[10px]">Tidak ada antrean Compactor</p>
        </div>
    @endforelse
</div>