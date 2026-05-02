{{-- resources/views/livewire/operator/partials/compactor-table.blade.php --}}
<div class="mkt-surface rounded-[2.5rem] shadow-sm border mkt-border overflow-hidden animate-in fade-in duration-500">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="mkt-surface border-b mkt-border font-black italic">
                <th class="px-6 py-4 text-[10px] mkt-text-muted uppercase">SAP NO</th>
                <th class="px-6 py-4 text-[10px] mkt-text-muted uppercase">Produk (COMPACTOR)</th>
                <th class="px-6 py-4 text-[10px] mkt-text-muted uppercase">Status Alur</th>
                <th class="px-6 py-4 text-[10px] mkt-text-muted uppercase text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50 italic">
            @forelse($workQueue as $job)
                <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="px-6 py-5">
                        <span class="text-xs font-black text-blue-600 uppercase">#{{ $job->sap_no }}</span>
                    </td>
                    <td class="px-6 py-5">
                        <h4 class="text-sm font-black mkt-text uppercase leading-none">{{ $job->art_no }}</h4>
                        <p class="text-[10px] font-bold mkt-text-muted mt-1 uppercase">{{ $job->warna }}</p>
                    </td>

                    <td class="px-6 py-5 text-xs">
                        <div class="flex flex-col gap-2">
                            <span class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-[9px] font-black uppercase w-fit">
                                Siap Compactor ({{ $job->status }})
                            </span>
                        </div>
                    </td>

                    <td class="px-6 py-5 text-center">
                        {{-- Memicu Modal Detail sesuai SOP --}}
                        <button wire:click="showOrderDetail({{ $job->id }})" 
                                class="bg-slate-900 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase hover:bg-blue-600 transition-all shadow-lg shadow-slate-200">
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