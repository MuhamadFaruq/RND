{{-- resources/views/livewire/operator/partials/knitting-table.blade.php --}}
<div class="mkt-surface rounded-[2.5rem] shadow-sm border mkt-border overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="mkt-surface border-b mkt-border">
                <th class="px-6 py-4 text-[10px] font-black mkt-text-muted uppercase italic">SAP NO</th>
                <th class="px-6 py-4 text-[10px] font-black mkt-text-muted uppercase italic">Produk (Knitting)</th>
                <th class="px-6 py-4 text-[10px] font-black mkt-text-muted uppercase italic">Material</th>
                <th class="px-6 py-4 text-[10px] font-black mkt-text-muted uppercase italic">Target</th>
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
                    <td class="px-6 py-5">
                        <p class="text-xs font-black mkt-text uppercase italic">{{ $job->material }} {{ $job->benang }}</p>
                    </td>
                    <td class="px-6 py-5">
                        <p class="text-lg font-black mkt-text leading-none italic">{{ number_format($job->kg_target, 1) }} KG</p>
                    </td>
                    <td class="px-6 py-5 text-center">
                        <button wire:click="showOrderDetail({{ $job->id }})" 
                            class="bg-slate-900 text-white px-6 py-2 rounded-xl font-black text-[10px] uppercase hover:bg-blue-600 transition-all">
                            DETAIL & PROSES
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-20 text-center mkt-text-muted font-black uppercase text-xs italic">Belum ada order masuk dari Marketing</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>