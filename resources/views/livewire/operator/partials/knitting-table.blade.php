{{-- resources/views/livewire/operator/partials/knitting-table.blade.php --}}
<div class="mkt-surface rounded-[2.5rem] shadow-sm border mkt-border overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="mkt-surface-alt border-b mkt-border">
                <th class="px-6 py-4 text-[10px] font-black mkt-text-muted uppercase italic tracking-wider">ARTIKEL NO</th>
                <th class="px-6 py-4 text-[10px] font-black mkt-text-muted uppercase italic tracking-wider">Produk (Knitting)</th>
                <th class="px-6 py-4 text-[10px] font-black mkt-text-muted uppercase italic tracking-wider">Material</th>
                <th class="px-6 py-4 text-[10px] font-black mkt-text-muted uppercase italic tracking-wider">Target</th>
                <th class="px-6 py-4 text-[10px] font-black mkt-text-muted uppercase italic text-center tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y mkt-border">
            @forelse($workQueue as $job)
                <tr class="hover:bg-indigo-600/5 transition-colors duration-300">
                    <td class="px-6 py-5">
                        <span class="text-[10px] font-black text-indigo-400 uppercase italic drop-shadow-[0_0_5px_rgba(99,102,241,0.3)]">#{{ $job->art_no }}</span>
                        @if($job->is_urgent)
                            <span class="ml-2 bg-red-600 text-white text-[8px] font-black px-2 py-0.5 rounded-full uppercase animate-pulse">URGENT</span>
                        @endif
                    </td>
                    <td class="px-6 py-5">
                        <h4 class="text-sm font-black mkt-text uppercase leading-none italic">{{ $job->art_no }}</h4>
                        <p class="text-[10px] font-bold mkt-text-muted mt-1 uppercase italic">{{ $job->warna }}</p>
                    </td>
                    <td class="px-6 py-5">
                        <p class="text-xs font-black mkt-text uppercase italic">{{ $job->material }}</p>
                        <p class="text-[10px] font-bold text-emerald-400 mt-1 uppercase italic">{{ $job->benang }}</p>
                    </td>
                    <td class="px-6 py-5">
                        <p class="text-lg font-black mkt-text leading-none italic drop-shadow-sm">{{ number_format($job->kg_target, 1) }} KG</p>
                        @if($job->processing_by)
                            <div class="mt-2 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse shadow-[0_0_5px_rgba(245,158,11,0.5)]"></span>
                                <span class="text-[8px] font-black text-amber-500 uppercase italic">
                                    ⚙️ IN PROGRESS: {{ $job->processingBy->name ?? 'Unknown' }}
                                </span>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-5 text-center flex flex-col gap-2 items-center justify-center">
                        @if($job->processing_by && $job->processing_by !== auth()->id())
                            <button wire:click="takeOverProcessAndRedirect({{ $job->id }})" 
                                    class="bg-amber-500 text-white px-4 py-2 rounded-2xl text-[9px] font-black uppercase hover:bg-amber-600 transition-all shadow-lg shadow-amber-500/20 w-full max-w-[150px]">
                                AMBIL ALIH
                            </button>
                        @elseif($job->processing_by === auth()->id())
                            <button wire:click="startProcessAndRedirect({{ $job->id }})" 
                                    class="bg-indigo-600 text-white px-6 py-2 rounded-2xl text-[10px] font-black uppercase hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-600/30 animate-pulse w-full max-w-[150px]">
                                LANJUTKAN PROSES
                            </button>
                        @else
                            <button wire:click="showOrderDetail({{ $job->id }})" 
                                class="mkt-surface-alt border mkt-border mkt-text px-6 py-2 rounded-xl font-black text-[10px] uppercase hover:bg-indigo-600 hover:border-transparent hover:text-white transition-all duration-300 shadow-md w-full max-w-[150px]">
                                DETAIL & TERIMA
                            </button>
                        @endif
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