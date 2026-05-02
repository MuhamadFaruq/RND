<div class="mkt-surface rounded-[2.5rem] shadow-sm border mkt-border overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="mkt-surface border-b mkt-border">
                <th class="px-6 py-4 text-[10px] font-black mkt-text-muted uppercase italic">SAP NO</th>
                <th class="px-6 py-4 text-[10px] font-black mkt-text-muted uppercase italic">Produk (Dyeing)</th>
                <th class="px-6 py-4 text-[10px] font-black mkt-text-muted uppercase italic">Status Alur</th>
                <th class="px-6 py-4 text-[10px] font-black mkt-text-muted uppercase italic text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($workQueue as $job)
                <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="px-6 py-5">
                        <span class="text-xs font-black text-blue-600 uppercase italic">#{{ $job->sap_no }}</span>
                    </td>
                    <td class="px-6 py-5">
                        <h4 class="text-sm font-black mkt-text uppercase leading-none italic">{{ $job->art_no }}</h4>
                        <p class="text-[10px] font-bold mkt-text-muted mt-1 uppercase italic">{{ $job->warna }}</p>
                    </td>

                    {{-- TARUH DI SINI (MENGGANTIKAN STATUS LAMA) --}}
                    <td class="px-6 py-5">
                        <div class="flex flex-col gap-2">
                            <span class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-[9px] font-black uppercase italic w-fit">
                                Siap Celup (Status: {{ $job->status }})
                            </span>
                            <div class="flex items-center gap-2 ml-1">
                                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                <span class="text-[8px] font-black text-green-600 uppercase italic">
                                    Diterima dari Knitting
                                </span>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-5 text-center">
                        <button wire:click="showOrderDetail({{ $job->id }})" 
                                class="bg-slate-900 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase hover:bg-blue-600 transition-all shadow-lg shadow-slate-200">
                            DETAIL & PROSES
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-20 text-center mkt-text-muted font-black uppercase text-xs italic">Menunggu hasil produksi dari Knitting...</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>