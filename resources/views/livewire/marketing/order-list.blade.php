<div x-data="{ 
    openDetail: @entangle('showDetail'), 
    selected: @entangle('selectedOrder'),
    showLogDetail: false,
    selectedLog: null
}" @close-log-detail.window="$wire.closeDetail()" class="animate-in fade-in duration-500 italic tracking-tighter mkt-bg min-h-screen">

    <div class="max-w-[1600px] mx-auto">
        
        {{-- SECTION 1: COMPACT HEADER --}}
        <div class="mb-5 md:mb-8 flex flex-col gap-4">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mkt-surface mkt-border p-5 md:p-6 rounded-[1.5rem] md:rounded-[3rem] border shadow-xl">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-8 md:h-10 bg-red-600 rounded-full"></div>
                    <div>
                        <h3 class="font-black uppercase mkt-text italic text-lg md:text-2xl tracking-tighter">Order <span class="text-red-600">Pipeline</span></h3>
                        <p class="text-[8px] md:text-[10px] font-bold mkt-text-muted uppercase tracking-widest italic">Marketing Control Center</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 md:gap-4 items-center w-full md:w-auto">
                    {{-- Date Filter --}}
                    <div class="flex items-center mkt-surface-alt border mkt-border rounded-2xl px-4 py-2 gap-2 flex-1 md:flex-none">
                        <input type="date" wire:model.live="startDate" class="text-[9px] md:text-[11px] font-black mkt-text-muted uppercase border-none focus:ring-0 bg-transparent w-full md:w-auto p-0">
                        <span class="mkt-text-muted font-black text-[9px] md:text-[11px]">TO</span>
                        <input type="date" wire:model.live="endDate" class="text-[9px] md:text-[11px] font-black mkt-text-muted uppercase border-none focus:ring-0 bg-transparent w-full md:w-auto p-0">
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-2 w-full sm:w-auto">
                        <button wire:click="exportExcel" wire:loading.attr="disabled" class="flex-1 sm:flex-none bg-slate-900 text-white px-5 md:px-8 py-3 rounded-xl font-black text-[9px] md:text-[11px] uppercase hover:bg-red-600 transition-all shadow-lg flex items-center justify-center gap-2">
                            <span wire:loading.remove>Export XLS</span>
                            <span wire:loading>...</span>
                        </button>
                        
                        <a href="{{ route('marketing.dashboard', ['menu' => 'input']) }}" 
                        class="flex-1 sm:flex-none bg-red-600 hover:bg-black text-white px-5 md:px-8 py-3 rounded-xl text-[9px] md:text-[11px] font-black uppercase tracking-widest transition-all shadow-lg shadow-red-600/20 text-center">
                        + New Order
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 md:gap-6">
                <div class="mkt-surface p-4 md:p-7 rounded-[1.5rem] md:rounded-[2.5rem] shadow-lg border mkt-border flex flex-col justify-center relative overflow-hidden">
                    <p class="text-[8px] md:text-[10px] font-black mkt-text-muted uppercase tracking-widest italic z-10 leading-none">Total Pesanan</p>
                    <h4 wire:key="counter-{{ $totalOrder }}" class="text-2xl md:text-5xl font-black mkt-text italic mt-2 z-10 leading-none">
                        {{ $totalOrder }}
                    </h4>
                    <div class="absolute -right-4 -bottom-4 text-6xl opacity-5 font-black italic select-none">ALL</div>
                </div>
                <div class="bg-red-600 p-4 md:p-7 rounded-[1.5rem] md:rounded-[2.5rem] shadow-xl shadow-red-600/20 border-b-4 border-black/20 relative overflow-hidden group flex flex-col justify-center">
                    <div class="absolute -right-2 -top-2 text-white/10 group-hover:scale-110 transition-transform hidden md:block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1 6h2v8h-2v-8zm1 12.25c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25z"/></svg>
                    </div>
                    <p class="text-[8px] md:text-[10px] font-black text-red-100 uppercase tracking-widest italic z-10 leading-none">Stuck Orders</p>
                    <h4 class="text-2xl md:text-4xl font-black text-white mt-2 z-10 leading-none">
                        {{ \App\Models\MarketingOrder::where('status', 'knitting')->where('created_at', '<=', now()->subDays(2))->count() }}
                    </h4>
                </div>
            </div>
        </div>

        {{-- SECTION 3: FILTER HUB --}}
        <div class="mkt-surface p-3 md:p-4 rounded-xl md:rounded-[2rem] shadow-sm mb-4 md:mb-6 flex flex-col md:flex-row gap-2 md:gap-4 items-stretch md:items-center border mkt-border">
            <div class="flex-1 relative w-full">
                <span class="absolute left-3 md:left-4 top-1/2 -translate-y-1/2 mkt-text-muted font-bold">
                    <svg class="w-3 h-3 md:w-3.5 md:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                <input wire:model.live="search" type="text" placeholder="CARI ARTIKEL / PELANGGAN..." 
                    class="w-full pl-8 md:pl-10 pr-3 md:pr-4 py-2 mkt-surface-alt border-none rounded-lg md:rounded-xl text-xs md:text-sm font-bold focus:ring-2 focus:ring-red-500/20 transition-all outline-none mkt-text">
            </div>

            <div class="flex items-center gap-2">
                <span class="text-[8px] md:text-[10px] font-black mkt-text-muted hidden md:inline">RENTANG:</span>
                <select wire:model.live="dateRange" 
                        wire:change="$refresh" 
                        class="mkt-surface border mkt-border mkt-text rounded-lg md:rounded-xl text-[10px] md:text-[12px] font-black py-1.5 md:py-2 flex-1 md:flex-none">
                    <option value="semua">SEMUA</option>
                    <option value="harian">HARI INI</option>
                    <option value="mingguan">MINGGUAN</option>
                    <option value="bulanan">BULANAN</option>
                    <option value="tahunan">TAHUNAN</option>
                </select>
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <span class="text-[8px] md:text-[10px] font-black mkt-text-muted uppercase tracking-widest hidden md:block">Status:</span>
                <select wire:model.live="statusFilter" class="w-full md:w-40 py-1.5 md:py-2 px-3 md:px-4 mkt-input mkt-border border rounded-lg md:rounded-xl text-[10px] md:text-xs font-black uppercase tracking-tighter outline-none cursor-pointer hover:opacity-80 transition-colors italic">
                    <option value="">Semua Status</option>
                    <option value="knitting">Knitting</option>
                    <option value="dyeing">Dyeing</option>
                    <option value="relax-dryer">Relax Dryer</option>
                    <option value="compactor">Compactor</option>
                    <option value="heat-setting">Heat Setting</option>
                    <option value="stenter">Stenter</option>
                    <option value="tumbler">Tumbler</option>
                    <option value="fleece">Fleece</option>
                    <option value="pengujian">Pengujian</option>
                    <option value="qe">QE Approval</option>
                    <option value="finished">Selesai (Finished)</option>
                </select>
            </div>
        </div>

        {{-- SECTION 4: MOBILE ORDER CARDS --}}
        <div class="block md:hidden space-y-3 mb-4">
            @forelse($orders as $order)
                @php
                    $statusMap = [
                        'knitting' => ['bg' => 'bg-brand/10', 'text' => 'text-brand', 'border' => 'border-brand'],
                        'dyeing' => ['bg' => 'bg-amber-500/10', 'text' => 'text-amber-600', 'border' => 'border-amber-500'],
                        'relax-dryer' => ['bg' => 'bg-cyan-500/10', 'text' => 'text-cyan-600', 'border' => 'border-cyan-500'],
                        'finished' => ['bg' => 'bg-green-500/10', 'text' => 'text-green-600', 'border' => 'border-green-500'],
                    ];
                    $cur = $statusMap[$order->status] ?? ['bg' => 'bg-slate-500/10', 'text' => 'text-slate-500', 'border' => 'border-slate-400'];
                @endphp
                <div class="mkt-surface p-3.5 rounded-xl border mkt-border shadow-sm relative overflow-hidden {{ $order->status == 'knitting' && $order->created_at->diffInDays(now()) >= 2 ? 'border-l-4 border-l-red-500' : 'border-l-4 ' . $cur['border'] }}">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <span class="text-xs font-black mkt-text italic tracking-tight">#{{ $order->art_no }}</span>
                            @if($order->status == 'knitting' && $order->created_at->diffInDays(now()) >= 2)
                                <span class="text-[7px] font-black text-red-600 uppercase ml-1 animate-pulse">⚠ {{ $order->created_at->diffInDays(now()) }}D</span>
                            @endif
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[7px] font-black tracking-wider {{ $cur['bg'] }} {{ $cur['text'] }} uppercase italic">
                            {{ strtoupper($order->status) }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-1.5 text-[9px]">
                        <div class="flex justify-between">
                            <span class="font-black text-slate-500 uppercase">Pelanggan</span>
                            <span class="font-bold mkt-text uppercase text-right truncate max-w-[55%]">{{ $order->pelanggan }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-black text-slate-500 uppercase">Tanggal</span>
                            <span class="font-bold mkt-text">{{ \Carbon\Carbon::parse($order->tanggal)->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-black text-slate-500 uppercase">MKT</span>
                            <span class="font-bold mkt-text-muted">{{ $order->mkt }}</span>
                        </div>
                        @if($order->processing_by)
                            <div class="flex items-center gap-1 text-[7px] font-black text-red-600 uppercase animate-pulse mt-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                ON MACHINE: {{ $order->processingBy->name ?? 'OP' }}
                            </div>
                        @endif
                    </div>
                    <div class="mt-2 pt-2 border-t mkt-border flex justify-end">
                        <button wire:click="openDetail({{ $order->id }})" class="px-4 py-1.5 bg-slate-900 text-white rounded-lg text-[8px] font-black uppercase hover:bg-red-600 transition shadow-sm">
                            <span wire:loading.remove wire:target="openDetail({{ $order->id }})">DETAIL</span>
                            <span wire:loading wire:target="openDetail({{ $order->id }})">...</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="mkt-surface p-10 rounded-xl border mkt-border text-center">
                    <p class="text-xs font-black mkt-text-muted uppercase italic">Tidak ada data</p>
                </div>
            @endforelse
            <div class="p-3 mkt-surface rounded-xl border mkt-border shadow-sm">
                {{ $orders->links() }}
            </div>
        </div>

        {{-- SECTION 4: DESKTOP TABLE --}}
        <div class="hidden md:block mkt-surface rounded-[2.5rem] shadow-sm overflow-hidden mkt-border border">
            <div class="p-5 md:p-8 border-b mkt-border mkt-surface">
                <h3 class="font-black uppercase mkt-text tracking-tighter italic text-sm md:text-lg">Master Data <span class="text-red-600">Marketing Order</span></h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="mkt-surface-alt">
                        <tr>
                            <th class="p-6 text-[10px] font-black uppercase mkt-text-muted tracking-widest">NO ARTIKEL</th>
                            <th class="p-6 text-[10px] font-black uppercase mkt-text-muted tracking-widest">Tanggal Order</th>
                            <th class="p-6 text-[10px] font-black uppercase mkt-text-muted tracking-widest">Pelanggan</th>
                            <th class="p-6 text-[10px] font-black uppercase mkt-text-muted tracking-widest">LEGACY ID (SAP)</th>
                            <th class="p-6 text-[10px] font-black uppercase mkt-text-muted tracking-widest">Sales (MKT)</th>
                            <th class="p-6 text-[10px] font-black uppercase mkt-text-muted tracking-widest">Keperluan</th>
                            <th class="p-6 text-[10px] font-black uppercase mkt-text-muted tracking-widest text-center">Status</th>
                            <th class="p-6 text-[10px] font-black uppercase mkt-text-muted tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y mkt-border uppercase text-sm font-bold mkt-text">
                        @forelse($orders as $order)
                            <tr class="hover:bg-red-600/5 dark:hover:bg-red-600/10 transition group relative {{ $order->status == 'knitting' && $order->created_at->diffInDays(now()) >= 2 ? 'bg-red-50/10 dark:bg-red-900/10' : '' }}">
                                
                                <td class="p-6 relative">
                                    <div class="flex flex-col">
                                        <span class="italic mkt-text tracking-tighter underline underline-offset-4 font-black">
                                            {{ $order->art_no }}
                                        </span>
                                        
                                        @if($order->status == 'knitting' && $order->created_at->diffInDays(now()) >= 2)
                                            <span class="absolute -left-1 top-1/2 -translate-y-1/2 w-1.5 h-12 bg-red-600 rounded-full animate-pulse"></span>
                                            <span class="text-[8px] font-black text-red-600 uppercase mt-1 flex items-center gap-1 animate-bounce">
                                                Butuh Follow Up ({{ $order->created_at->diffInDays(now()) }} Hari)
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                
                                <td class="p-6">
                                    <div class="flex flex-col">
                                        <span class="mkt-text">{{ \Carbon\Carbon::parse($order->tanggal)->format('d/m/Y') }}</span>
                                        <span class="text-[9px] {{ $order->created_at->diffInDays(now()) >= 2 && $order->status == 'knitting' ? 'text-red-500 font-black' : 'text-slate-400' }} italic font-medium lowercase leading-none mt-1">
                                            Input: {{ $order->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </td>

                                <td class="p-6 tracking-tight mkt-text">{{ $order->pelanggan }}</td>
                                <td class="p-6 mkt-text-muted uppercase italic text-[11px]">{{ $order->sap_no }}</td>
                                <td class="p-6 mkt-text-muted">{{ $order->mkt }}</td>
                                <td class="p-6 italic text-[10px] mkt-text-muted">{{ $order->keperluan }}</td>

                                <td class="p-6 text-center whitespace-nowrap">
                                    @php
                                        $statusMap = [
                                            'knitting'    => ['bg' => 'bg-brand-100', 'text' => 'text-brand', 'icon' => ''],
                                            'dyeing'      => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600', 'icon' => ''],
                                            'relax-dryer' => ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-600', 'icon' => ''],
                                            'finishing'   => ['bg' => 'bg-brand-100', 'text' => 'text-brand-600', 'icon' => ''],
                                            'stenter'     => ['bg' => 'bg-brand-100', 'text' => 'text-brand-600', 'icon' => ''],
                                            'tumbler'     => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600', 'icon' => ''],
                                            'fleece'      => ['bg' => 'bg-rose-100', 'text' => 'text-rose-600', 'icon' => ''],
                                            'pengujian'   => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'icon' => ''],
                                            'qe'          => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'icon' => ''],
                                            'finished'    => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'icon' => ''],
                                        ];
                                        
                                        $current = $statusMap[$order->status] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-500', 'icon' => '●'];
                                    @endphp

                                    <div class="flex flex-col items-center gap-1">
                                        <span class="inline-flex items-center px-4 py-2 rounded-full text-[9px] font-black tracking-widest {{ $current['bg'] }} {{ $current['text'] }} border border-current/20 uppercase italic">
                                            {{ $current['icon'] }} {{ strtoupper($order->status) }}
                                        </span>

                                        @if($order->processing_by)
                                            <span class="text-[8px] font-black text-red-600 uppercase flex items-center gap-1 animate-pulse">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                                ON MACHINE: {{ $order->processingBy->name ?? 'OP' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="p-6 text-right">
                                    <button wire:click="openDetail({{ $order->id }})" 
                                        class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-[10px] font-black hover:bg-red-600 transition shadow-md group-hover:scale-105 transform">
                                        <span wire:loading.remove wire:target="openDetail({{ $order->id }})">DETAIL</span>
                                        <span wire:loading wire:target="openDetail({{ $order->id }})">...</span>
                                    </button>
                                </td>
                            </tr>
                            @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-6 mkt-surface border-t mkt-border">
                {{ $orders->links() }}
            </div>
        </div>
    </div>

    {{-- SIDE-OVER DETAIL MODAL --}}
    <div x-show="openDetail" 
         class="fixed inset-0 z-[100] overflow-hidden" 
         x-cloak 
         style="display: none;">
        
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
             x-show="openDetail"
             @click="$wire.closeDetail()"></div>

        <div class="fixed inset-0 sm:inset-y-0 sm:right-0 sm:left-auto pl-0 sm:pl-6 md:pl-10 max-w-full flex w-full sm:w-auto">
            <div x-show="openDetail" 
                 class="w-full sm:max-w-2xl md:max-w-5xl lg:max-w-7xl text-left h-full">
                
                <div id="print-area" class="h-full flex flex-col mkt-surface shadow-2xl rounded-none sm:rounded-l-[2rem] md:rounded-l-[3rem] overflow-hidden border-0 sm:border-l mkt-border">
                    <div class="flex-1 overflow-y-auto custom-scrollbar min-h-0">
                        @if($showDetail && $selectedOrder)
                            <livewire:order-tracking-detail :order-id="$selectedOrder['id']" :key="$selectedOrder['id']" />
                        @endif
                    </div>

                    {{-- Footer Modal --}}
                    <div class="p-3 sm:p-4 md:p-8 pb-[max(0.75rem,env(safe-area-inset-bottom))] mkt-surface-alt border-t mkt-border flex flex-col gap-2 sm:gap-3 shrink-0 no-print-bg">
                        <button onclick="window.print()" class="w-full bg-brand text-white px-4 py-3 sm:py-3.5 rounded-xl font-black text-[10px] sm:text-xs uppercase tracking-wider hover:bg-black transition-all flex items-center justify-center gap-2 shadow-lg shadow-brand/20">
                            <span>PRINT SURAT JALAN</span>
                        </button>

                        <div class="grid grid-cols-2 gap-2 sm:gap-4">
                            <a :href="'/marketing/orders/' + selected?.id + '/edit'" class="mkt-surface mkt-text border mkt-border px-3 py-3 rounded-xl font-black text-[10px] sm:text-xs uppercase tracking-wider hover:bg-brand-600 hover:text-white transition-all flex items-center justify-center">
                                <span>EDIT</span>
                            </a>
                            <button type="button" @click="Swal.fire({
                                title: 'HAPUS ARTIKEL INI?',
                                text: 'Yakin menghapus data Artikel ' + selected.art_no + '? Data tidak dapat dikembalikan.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#dc2626',
                                confirmButtonText: 'YA, HAPUS',
                                cancelButtonText: 'BATAL',
                                background: '#0f172a',
                                color: '#fff',
                                customClass: { popup: 'rounded-[2rem] border border-white/10 backdrop-blur-xl', title: 'font-black italic uppercase tracking-tighter', confirmButton: 'rounded-xl font-bold uppercase text-xs px-6 py-3', cancelButton: 'rounded-xl font-bold uppercase text-xs px-6 py-3' }
                            }).then((result) => { if (result.isConfirmed) { $wire.deleteOrder(selected.id) } })" class="bg-red-500/10 text-red-600 border border-red-500/20 px-3 py-3 rounded-xl font-black text-[10px] sm:text-xs uppercase tracking-wider hover:bg-red-600 hover:text-white transition-all flex items-center justify-center">
                                <span>HAPUS</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <style>
        [x-cloak] { display: none !important; }
        @media print {
            body * { visibility: hidden; }
            #print-area, #print-area * { visibility: visible; }
            #print-area { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print, .no-print-bg { display: none !important; }
        }
    </style>
</div>