<div class="py-4 md:py-8 mkt-bg min-h-screen italic mkt-text">
    <div class="w-full max-w-[1600px] mx-auto px-3 sm:px-4 md:px-6">
        <div class="mkt-surface rounded-2xl md:rounded-3xl p-4 md:p-8 shadow-sm border mkt-border">

            {{-- Header --}}
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-3xl md:text-4xl font-[1000] italic leading-none mkt-text tracking-tighter">
                        COLD STORAGE <br>
                        <span class="text-red-500">ARCHIVE BIN</span>
                    </h1>
                    <div class="flex items-center gap-2 mt-4">
                        <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                        <p class="text-[10px] font-black mkt-text-muted uppercase tracking-[0.2em]">Data Pesanan yang Dihapus / Voided</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.dashboard') }}" class="px-6 py-3 rounded-xl mkt-surface border mkt-border mkt-text font-black text-xs uppercase hover:bg-slate-800 transition-all">
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>

            {{-- Toolbar --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div class="w-full sm:w-96 relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari SAP / Artikel / Pelanggan..."
                        class="w-full pl-11 pr-4 py-3 mkt-surface border mkt-border rounded-xl font-bold text-xs mkt-text placeholder-slate-500 focus:ring-1 focus:ring-red-500 focus:border-red-500 transition-all outline-none italic uppercase">
                </div>
            </div>

            {{-- Table --}}
            <div class="mkt-surface rounded-2xl shadow-sm border mkt-border overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="mkt-surface-alt border-b mkt-border">
                            <th class="px-5 py-4 text-[10px] font-black mkt-text-muted uppercase italic tracking-wider">Artikel / SAP</th>
                            <th class="px-5 py-4 text-[10px] font-black mkt-text-muted uppercase italic tracking-wider">Pelanggan</th>
                            <th class="px-5 py-4 text-[10px] font-black mkt-text-muted uppercase italic tracking-wider">Dihapus Oleh</th>
                            <th class="px-5 py-4 text-[10px] font-black mkt-text-muted uppercase italic tracking-wider">Waktu Dihapus</th>
                            <th class="px-5 py-4 text-[10px] font-black mkt-text-muted uppercase italic text-center tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y mkt-border">
                        @forelse($archivedOrders as $archive)
                            <tr class="hover:bg-red-500/5 transition-colors duration-300">
                                <td class="px-5 py-4">
                                    <h4 class="text-sm font-black mkt-text uppercase leading-none italic">{{ $archive->art_no ?? '-' }}</h4>
                                    <p class="text-[10px] font-bold text-slate-500 mt-1 uppercase italic">SAP: {{ $archive->sap_no ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="text-xs font-bold mkt-text uppercase">{{ $archive->pelanggan ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-slate-800 flex items-center justify-center text-[10px] font-black text-white">
                                            {{ substr($archive->deleter->name ?? '?', 0, 1) }}
                                        </div>
                                        <span class="text-[11px] font-black mkt-text uppercase">{{ $archive->deleter->name ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="text-[10px] font-black mkt-text-muted uppercase">{{ $archive->created_at->format('d M Y H:i') }}</span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <button onclick="confirmDestroy({{ $archive->id }}, '{{ $archive->art_no }}')" class="px-3 py-1.5 bg-red-600/10 text-red-500 hover:bg-red-600 hover:text-white rounded-lg text-[9px] font-black uppercase tracking-wider transition-all border border-red-600/20">
                                        Destroy
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-16 text-center">
                                    <div class="inline-flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-slate-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <p class="text-xs font-black mkt-text-muted uppercase tracking-widest">Tempat Sampah Kosong</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $archivedOrders->links(data: ['scrollTo' => false]) }}
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDestroy(id, artNo) {
        Swal.fire({
            title: 'HANCURKAN PERMANEN?',
            html: `<div class="text-sm font-bold text-slate-300 mt-2">Data <span class="text-red-400">#${artNo}</span> akan dihapus dari Cold Storage selamanya dan tidak dapat dikembalikan.</div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#334155',
            confirmButtonText: 'YA, HANCURKAN!',
            cancelButtonText: 'BATAL',
            background: '#1e293b',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('destroyPermanently', id);
            }
        });
    }

    document.addEventListener('livewire:init', () => {
        Livewire.on('show-toast', (event) => {
            const data = Array.isArray(event) ? event[0] : event;
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: data.type || 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#1e293b',
                color: '#fff'
            });
        });

        Livewire.on('show-error-toast', (event) => {
            const data = Array.isArray(event) ? event[0] : event;
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: data.message,
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                background: '#1e293b',
                color: '#fff'
            });
        });
    });
</script>
