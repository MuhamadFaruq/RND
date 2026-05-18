{{-- resources/views/livewire/operator/partials/compactor-table.blade.php --}}
<div class="mkt-surface rounded-[2.5rem] shadow-sm border mkt-border overflow-hidden animate-in fade-in duration-500">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="mkt-surface-alt border-b mkt-border font-black italic">
                <th class="px-6 py-4 text-[10px] mkt-text-muted uppercase">ARTIKEL NO</th>
                <th class="px-6 py-4 text-[10px] mkt-text-muted uppercase">Produk (COMPACTOR)</th>
                <th class="px-6 py-4 text-[10px] mkt-text-muted uppercase">Status Alur</th>
                <th class="px-6 py-4 text-[10px] mkt-text-muted uppercase text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y mkt-border italic">
            @forelse($workQueue as $job)
                <tr class="hover:bg-indigo-600/5 transition-colors">
                    <td class="px-6 py-5">
                        <span class="text-[10px] font-black text-indigo-500 uppercase">#{{ $job->art_no }}</span>
                        @if($job->is_urgent)
                            <span class="ml-2 bg-red-600 text-white text-[8px] font-black px-2 py-0.5 rounded-full uppercase animate-pulse">URGENT</span>
                        @endif
                    </td>
                    <td class="px-6 py-5">
                        <h4 class="text-sm font-black mkt-text uppercase leading-none">{{ $job->art_no }}</h4>
                        <p class="text-[10px] font-bold mkt-text-muted mt-1 uppercase">{{ $job->warna }}</p>
                    </td>

                    <td class="px-6 py-5 text-xs">
                        <div class="flex flex-col gap-2">
                            <span class="px-3 py-1 bg-indigo-600/10 text-indigo-500 rounded-full text-[9px] font-black uppercase w-fit">
                                Siap Compactor ({{ $job->status }})
                            </span>
                        </div>
                    </td>

                    <td class="px-6 py-5 text-center">
                        {{-- Memicu Modal Detail sesuai SOP --}}
                        <button wire:click="showOrderDetail({{ $job->id }})" 
                                class="mkt-surface-alt border mkt-border mkt-text px-6 py-3 rounded-2xl text-[10px] font-black uppercase hover:bg-indigo-600 hover:text-white transition-all shadow-lg shadow-slate-900/20">
                            DETAIL & PROSES
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-20 text-center mkt-text-muted font-black uppercase text-xs italic">
                        Tidak ada antrean untuk divisi Compactor...
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>