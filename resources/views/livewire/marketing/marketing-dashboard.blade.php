<div>
    {{-- Tailwind & Chart.js sudah dikompilasi via Vite, tidak perlu CDN --}}

<div x-data="{ 
    openDetail: @entangle('showDetail'), 
    selected: @entangle('selectedOrder')
}" @close-log-detail.window="$wire.closeDetail()" class="min-h-screen mkt-bg pt-2 pb-8 px-3 sm:px-4 md:px-6 font-inter tracking-tight">
    <div class="max-w-[1600px] mx-auto">
        
        {{-- HEADER UTAMA --}}
        <div class="mb-4 sm:mb-6 flex flex-col gap-3 sm:gap-4 md:flex-row md:justify-between md:items-start w-full">
            {{-- SISI KIRI: JUDUL DASHBOARD --}}
            <div class="min-w-0 flex-1">
                @if($currentMenu === 'dashboard')
                    <div class="animate-in fade-in duration-700">
                        <h1 class="text-2xl sm:text-3xl md:text-4xl font-black uppercase mkt-text leading-tight sm:leading-none">
                            Marketing <span class="text-brand">War Room</span>
                        </h1>
                        <p class="text-[10px] sm:text-xs md:text-sm font-bold mkt-text-muted uppercase tracking-[0.15em] sm:tracking-[0.3em] mt-1.5 sm:mt-2 italic">
                            Duniatex Group Industrial Monitoring Hub
                        </p>
                    </div>
                @endif

                @if($currentMenu === 'calculator')
                    <div class="animate-in fade-in duration-700">
                        <h1 class="text-2xl sm:text-3xl md:text-4xl font-black uppercase mkt-text leading-tight sm:leading-none">
                            Price <span class="text-emerald-600">Simulator</span>
                        </h1>
                        <p class="text-[10px] sm:text-xs md:text-sm font-bold mkt-text-muted uppercase tracking-[0.15em] sm:tracking-[0.3em] mt-1.5 sm:mt-2 italic">
                            Cost & Selling Price Calculation Tool
                        </p>
                    </div>
                @endif
            </div>

                {{-- SISI KANAN: STATUS LIVE & TANGGAL & JAM --}}
            @if($currentMenu === 'dashboard' || $currentMenu === 'calculator')
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-4 w-full md:w-auto md:flex-shrink-0 md:ml-0 animate-in fade-in duration-700">
                    @php
                        $loadColor = $factoryLoad > 80 ? 'text-red-600' : ($factoryLoad > 50 ? 'text-amber-500' : 'text-emerald-500');
                    @endphp
                    <div class="mkt-surface border mkt-border rounded-xl sm:rounded-2xl px-3 py-2 sm:px-4 sm:py-2.5 md:border-r md:border-slate-200 md:pr-6 md:bg-transparent md:border-0 text-left sm:text-right">
                        <p class="text-[8px] sm:text-[10px] font-black mkt-text-muted uppercase leading-none mb-0.5 tracking-widest">Global Factory Load</p>
                        <p class="text-xs sm:text-sm font-black {{ $loadColor }} flex items-center sm:justify-end gap-2 italic">
                            <span class="w-2 h-2 rounded-full animate-pulse bg-current shrink-0"></span>
                            {{ number_format($factoryLoad, 1) }}% BUSY
                        </p>
                    </div>
                    
                    <div class="bg-slate-900 text-white px-3 py-2.5 sm:px-4 sm:py-3 md:px-6 md:py-3 rounded-xl sm:rounded-2xl font-black text-[10px] sm:text-xs uppercase shadow-xl flex items-center justify-center sm:justify-start gap-2 sm:gap-4" wire:ignore>
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <span class="real-time-date">{{ now()->locale('id')->translatedFormat('d M Y') }}</span>
                        </div>
                        <div class="w-px h-3 sm:h-4 bg-white/20"></div>
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <span class="real-time-clock font-mono tabular-nums">{{ now()->format('H:i:s') }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- START DYNAMIC CONTENT --}}
        @if($currentMenu === 'dashboard')
            <div class="animate-in fade-in duration-500">
                {{-- LIVE MACHINE WORKLOAD (Kapasitas Divisi) --}}
                <div class="mb-6 sm:mb-8 md:mb-10">
                    <div class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-end mb-4 sm:mb-6">
                        <h3 class="font-black uppercase mkt-text tracking-tighter flex items-center gap-2 italic text-base sm:text-xl md:text-2xl">
                            <span class="w-6 sm:w-10 h-1 bg-red-600 shrink-0"></span>
                            <span class="leading-tight">Live Operator Activity Stream</span>
                        </h3>
                        <div class="text-left sm:text-right pl-8 sm:pl-0">
                            <p class="text-[8px] sm:text-[9px] font-black mkt-text-muted italic uppercase">Machine Standard Capacity</p>
                            <p class="text-[10px] sm:text-xs font-black mkt-text italic uppercase tracking-widest">{{ number_format($maxCapacity) }} KG / LOT</p>
                        </div>
                    </div>
                    
                    <div wire:poll.10s class="animate-in fade-in duration-500"> 
                        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-2.5 sm:gap-4 md:gap-6">
                            @foreach($stages as $stage)
                                <div class="mkt-surface p-3 sm:p-4 md:p-5 xl:p-4 rounded-2xl sm:rounded-[2rem] md:rounded-[2.5rem] mkt-border border shadow-sm relative overflow-hidden transition-all hover:shadow-xl hover:mkt-border group min-w-0 flex flex-col justify-between h-full">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-start mb-2 sm:mb-4">
                                        <div class="min-w-0">
                                            <p class="text-[7px] sm:text-[9px] font-black mkt-text-muted uppercase tracking-wide sm:tracking-widest leading-none truncate">Dept. {{ $stage['name'] }}</p>
                                            <h4 class="text-xs sm:text-sm md:text-base xl:text-sm 2xl:text-base font-black mkt-text mt-0.5 sm:mt-1 italic uppercase group-hover:text-brand transition-colors leading-tight line-clamp-2 min-h-[32px] sm:min-h-[40px] md:min-h-[48px] xl:min-h-[40px] 2xl:min-h-[48px]">{{ $stage['unit'] }}</h4>
                                        </div>
                                        <div class="flex flex-row sm:flex-col items-center sm:items-end justify-between sm:justify-start gap-1 shrink-0">
                                            <span class="bg-{{ $stage['color'] ?? 'slate' }}-100 text-{{ $stage['color'] ?? 'slate' }}-600 text-[7px] sm:text-[8px] px-2 sm:px-3 py-0.5 sm:py-1 rounded-full font-black whitespace-nowrap">CONNECTED</span>
                                            <p class="text-[8px] sm:text-[10px] font-black mkt-text">{{ $stage['load_count'] }} Artikel</p>
                                        </div>
                                    </div>

                                    <div class="relative pt-1 mb-2 sm:mb-4">
                                        <div class="flex flex-col gap-0.5 sm:flex-row sm:justify-between mb-1.5 sm:mb-2 uppercase italic font-black text-[7px] sm:text-[9px]">
                                            <span class="mkt-text-muted truncate">Load: {{ number_format($stage['load_kg']) }} KG</span>
                                            <span class="{{ $stage['percentage'] > 90 ? 'text-red-600 animate-pulse' : 'mkt-text' }} whitespace-nowrap">
                                                {{ $stage['is_full'] ? 'FULL' : 'AVAILABLE' }}
                                            </span>
                                        </div>
                                        <div class="overflow-hidden h-2 sm:h-2.5 flex rounded-full bg-slate-100 shadow-inner">
                                            <div style="width: {{ $stage['percentage'] ?? 0 }}%" 
                                                class="bg-{{ $stage['color'] ?? 'slate' }}-500 rounded-full shadow-lg transition-all duration-1000">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex items-center gap-1.5 sm:gap-2 min-w-0">
                                            <span class="relative flex h-2 w-2 shrink-0">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-{{ $stage['color'] ?? 'slate' }}-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-{{ $stage['color'] ?? 'slate' }}-500"></span>
                                            </span>
                                            <p class="text-[7px] sm:text-[9px] font-bold mkt-text-muted italic uppercase tracking-tighter truncate">
                                                {{ $stage['desc'] ?? 'Monitoring Active' }}
                                            </p>
                                        </div>
                                        <p class="text-[9px] sm:text-[10px] font-black italic {{ $stage['percentage'] > 80 ? 'text-red-600' : 'text-slate-400' }} sm:text-right">
                                            {{ number_format($stage['percentage'], 1) }}%
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- SUMMARY STATS --}}
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2.5 sm:gap-4 md:gap-6 mb-6 sm:mb-8 md:mb-10">
                    <div class="mkt-surface p-4 sm:p-6 md:p-8 rounded-2xl sm:rounded-[2.5rem] md:rounded-[3rem] shadow-sm mkt-border border relative overflow-hidden group col-span-2 md:col-span-1">
                        <p class="text-[8px] sm:text-[10px] font-black mkt-text-muted uppercase tracking-widest relative z-10">Total Registered Orders</p>
                        <h3 class="text-3xl sm:text-4xl md:text-6xl font-black mkt-text mt-1 sm:mt-2 relative z-10 italic">{{ number_format($totalOrder) }}</h3>
                        <div class="absolute -right-6 -bottom-6 sm:-right-10 sm:-bottom-10 opacity-5 text-5xl sm:text-9xl font-black italic group-hover:scale-110 transition-transform mkt-text hidden sm:block">ART</div>
                    </div>

                    <div class="mkt-surface p-4 sm:p-6 md:p-8 rounded-2xl sm:rounded-[2.5rem] md:rounded-[3rem] shadow-sm mkt-border border relative overflow-hidden min-w-0">
                        <p class="text-[8px] sm:text-[10px] font-black mkt-text-muted uppercase tracking-widest leading-tight">Active Production</p>
                        <h3 class="text-3xl sm:text-4xl md:text-6xl font-black mkt-text mt-1 sm:mt-2 italic">{{ $activeOrder }}</h3>
                        <div class="mt-3 sm:mt-6 flex items-center gap-2">
                            <span class="w-full h-1.5 bg-red-500/20 rounded-full overflow-hidden">
                                <div class="w-full h-full bg-red-600 rounded-full shadow-[0_0_10px_rgba(239,68,68,0.4)]"></div>
                            </span>
                        </div>
                    </div>

                    <div class="mkt-surface p-4 sm:p-6 md:p-8 rounded-2xl sm:rounded-[2.5rem] md:rounded-[3rem] shadow-sm mkt-border border relative overflow-hidden min-w-0">
                        <p class="text-[8px] sm:text-[10px] font-black mkt-text-muted uppercase tracking-widest leading-tight">Finished & QC Pass</p>
                        <h3 class="text-3xl sm:text-4xl md:text-6xl font-black text-emerald-600 mt-1 sm:mt-2 italic">{{ $completedOrder }}</h3>
                        <div class="mt-3 sm:mt-6 flex items-center gap-2">
                            <span class="w-full h-1.5 bg-emerald-500/20 rounded-full overflow-hidden">
                                <div class="w-full h-full bg-emerald-500 rounded-full shadow-[0_0_10px_rgba(16,185,129,0.4)]"></div>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- SALES PERFORMANCE & REPS --}}
                <div class="mb-6 sm:mb-8 md:mb-10">
                    <div class="mkt-surface p-3 sm:p-6 md:p-8 rounded-2xl sm:rounded-[2.5rem] md:rounded-[3rem] mkt-border border shadow-sm">
                        <div class="mb-3 sm:mb-6 md:mb-8">
                            <h3 class="text-base sm:text-xl md:text-2xl font-black uppercase mkt-text tracking-tighter italic leading-tight">Recent Production Flow</h3>
                            <p class="text-[8px] sm:text-[10px] font-bold mkt-text-muted uppercase tracking-wider sm:tracking-widest mt-0.5 sm:mt-1 italic">Industrial Stream Analytics (Live Updates)</p>
                        </div>

                        {{-- Toolbar: HP = search + tombol satu baris; desktop = layout lama --}}
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-2 sm:gap-4 mb-3 sm:mb-6">
                            <div class="flex gap-2 w-full md:max-w-md lg:max-w-lg">
                                <div class="relative flex-1 min-w-0 group">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 mkt-text-muted text-xs group-focus-within:text-brand transition-colors">🔍</span>
                                    <input type="text"
                                        wire:model.live="search"
                                        placeholder="Cari artikel..."
                                        class="w-full pl-9 pr-3 py-2.5 sm:py-3.5 md:py-4 mkt-input border mkt-border rounded-xl sm:rounded-[1.5rem] text-[10px] sm:text-xs font-bold sm:font-black uppercase italic focus:ring-2 sm:focus:ring-4 focus:ring-red-600/10 transition-all outline-none">
                                </div>
                                <a href="{{ route('marketing.dashboard', ['menu' => 'input']) }}"
                                    class="shrink-0 flex items-center justify-center px-3 sm:px-5 py-2.5 sm:py-3.5 bg-red-600 hover:bg-black text-white rounded-xl sm:rounded-[1.5rem] text-[9px] sm:text-xs font-black uppercase tracking-wide transition-all shadow-md shadow-red-600/20"
                                    aria-label="Buat order baru">
                                    <span class="md:hidden text-lg leading-none">+</span>
                                    <span class="hidden md:inline">+ New Order</span>
                                </a>
                            </div>
                        </div>

                        {{-- HP: kartu 2 kolom --}}
                        <div class="md:hidden grid grid-cols-2 gap-2">
                            @forelse($recentOrders as $order)
                                <button type="button"
                                    wire:click="openDetail({{ $order->id }})"
                                    class="mkt-surface-alt mkt-border border rounded-xl p-2.5 text-left hover:border-red-500/50 active:scale-[0.98] transition-all min-w-0 flex flex-col gap-1.5 h-full">
                                    <p class="text-[11px] font-black mkt-text italic leading-none truncate">#{{ $order->art_no }}</p>
                                    <p class="text-[8px] font-bold mkt-text uppercase truncate leading-tight">{{ $order->pelanggan }}</p>
                                    @if($order->warna)
                                        <p class="text-[7px] font-bold mkt-text-muted uppercase italic truncate">{{ $order->warna }}</p>
                                    @endif
                                    <div class="mt-auto pt-0.5">
                                        @if($order->status === 'knitting')
                                            <span class="inline-block max-w-full px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded-full text-[7px] font-black uppercase border border-gray-200 truncate">Knitting</span>
                                        @elseif($order->status === 'dyeing')
                                            <span class="inline-block max-w-full px-1.5 py-0.5 bg-brand-100 text-brand rounded-full text-[7px] font-black uppercase border border-brand-200 truncate">Dyeing</span>
                                        @elseif($order->status === 'finished')
                                            <span class="inline-block max-w-full px-1.5 py-0.5 bg-green-100 text-green-600 rounded-full text-[7px] font-black uppercase border border-green-200 truncate">✅ Ready</span>
                                        @else
                                            <span class="inline-block max-w-full px-1.5 py-0.5 bg-amber-100 text-amber-600 rounded-full text-[7px] font-black uppercase border border-amber-200 truncate">{{ $order->status }}</span>
                                        @endif
                                        @if($order->deviation)
                                            <span class="inline-block mt-1 px-1.5 py-0.5 bg-red-100 text-red-600 rounded-full text-[7px] font-black uppercase">Deviasi</span>
                                        @endif
                                        @if($order->overdue)
                                            <span class="inline-block mt-1 px-1.5 py-0.5 bg-yellow-100 text-yellow-600 rounded-full text-[7px] font-black uppercase">Overdue</span>
                                        @endif
                                    </div>
                                    <span class="text-[7px] font-black mkt-text-muted uppercase italic pt-0.5">
                                        <span wire:loading.remove wire:target="openDetail({{ $order->id }})">Detail →</span>
                                        <span wire:loading wire:target="openDetail({{ $order->id }})">...</span>
                                    </span>
                                </button>
                            @empty
                                <div class="col-span-2 py-10 text-center">
                                    <div class="bg-slate-100 dark:bg-slate-800 p-3 rounded-full mb-3 inline-flex">
                                        <span class="text-2xl">🔎</span>
                                    </div>
                                    <p class="text-xs font-black mkt-text uppercase italic">Data Tidak Ditemukan</p>
                                    @if($search)
                                        <p class="text-[9px] font-bold mkt-text-muted uppercase mt-1 px-4">
                                            Tidak ada hasil untuk "<span class="mkt-text font-black">{{ $search }}</span>"
                                        </p>
                                        <button wire:click="$set('search', '')" class="mt-3 text-[9px] font-black mkt-text-muted uppercase underline hover:text-brand">
                                            Bersihkan
                                        </button>
                                    @endif
                                </div>
                            @endforelse
                        </div>

                        {{-- Desktop: tabel penuh --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full text-left border-separate border-spacing-y-3" id="productionTable">
                                <thead>
                                    <tr class="text-[10px] font-black mkt-text-muted uppercase tracking-widest">
                                        <th class="px-4 pb-2">NO ARTIKEL</th>
                                        <th class="px-4 pb-2">Customer / Article</th>
                                        <th class="px-4 pb-2">Warna</th>
                                        <th class="px-4 pb-2">Status Pipeline</th>
                                        <th class="px-4 pb-2 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentOrders as $order)
                                        <tr class="mkt-surface-alt hover:opacity-80 transition-all group table-row-item">
                                            <td class="px-4 py-4 rounded-l-[1.5rem] font-black text-xs mkt-text italic sap-cell">
                                                #{{ $order->art_no }}
                                            </td>
                                            <td class="px-4 py-4 customer-cell">
                                                <p class="text-[10px] font-black mkt-text uppercase">{{ $order->pelanggan }}</p>
                                                <p class="text-[9px] font-bold mkt-text-muted uppercase italic">{{ $order->sap_no }}</p>
                                            </td>
                                            <td class="px-4 py-4 text-[10px] font-black uppercase italic mkt-text">{{ $order->warna }}</td>
                                            <td class="px-4 py-4">
                                                @if($order->status === 'knitting')
                                                    <span class="inline-block max-w-full px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded-full text-[9px] font-black uppercase italic border border-gray-200 truncate">Knitting</span>
                                                @elseif($order->status === 'dyeing')
                                                    <span class="inline-block max-w-full px-1.5 py-0.5 bg-brand-100 text-brand rounded-full text-[9px] font-black uppercase italic border border-brand-200 truncate">🧶 Proses Dyeing</span>
                                                @elseif($order->status === 'finished')
                                                    <span class="inline-block max-w-full px-1.5 py-0.5 bg-green-100 text-green-600 rounded-full text-[9px] font-black uppercase italic border border-green-200 truncate">✅ Ready</span>
                                                @else
                                                    <span class="inline-block max-w-full px-1.5 py-0.5 bg-amber-100 text-amber-600 rounded-full text-[9px] font-black uppercase italic border border-amber-200 truncate">{{ $order->status }}</span>
                                                @endif
                                                @if($order->deviation)
                                                    <span class="ml-1 inline-block px-1.5 py-0.5 bg-red-100 text-red-600 rounded-full text-[8px] font-black uppercase">Deviasi</span>
                                                @endif
                                                @if($order->overdue)
                                                    <span class="ml-1 inline-block px-1.5 py-0.5 bg-yellow-100 text-yellow-600 rounded-full text-[8px] font-black uppercase">Overdue</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 rounded-r-[1.5rem] text-right">
                                                <button wire:click="openDetail({{ $order->id }})" class="text-[10px] font-black mkt-text-muted uppercase hover:text-brand hover:underline italic">
                                                    <span wire:loading.remove wire:target="openDetail({{ $order->id }})">Detail</span>
                                                    <span wire:loading wire:target="openDetail({{ $order->id }})">...</span>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-12 text-center">
                                                <div class="flex flex-col items-center justify-center">
                                                    <div class="bg-slate-100 p-4 rounded-full mb-4">
                                                        <span class="text-3xl">🔎</span>
                                                    </div>
                                                    <p class="text-sm font-black mkt-text uppercase italic tracking-tighter">Data Tidak Ditemukan</p>
                                                    <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-widest mt-1">
                                                        Tidak ada pesanan dengan kata kunci "<span class="mkt-text font-black">{{ $search }}</span>"
                                                    </p>
                                                    <button wire:click="$set('search', '')" class="mt-4 text-[9px] font-black mkt-text-muted uppercase underline decoration-2 underline-offset-4 hover:text-brand">
                                                        Bersihkan Pencarian
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
         </div>

                {{-- CRITICAL ALERTS & HUB --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 md:gap-8">
                    <div class="mkt-surface p-4 sm:p-6 md:p-8 rounded-2xl sm:rounded-[2.5rem] md:rounded-[3rem] mkt-border border shadow-sm border-l-4 sm:border-l-8 border-l-red-600">
                        <h3 class="font-black uppercase mkt-text tracking-tighter mb-4 sm:mb-6 flex flex-wrap items-center gap-2 sm:gap-3 italic text-sm sm:text-base">
                            Critical Orders Alert <span class="bg-red-100 text-red-600 text-[8px] sm:text-[10px] px-2 sm:px-3 py-0.5 sm:py-1 rounded-full animate-pulse">{{ $knittingOrder }} URGENT</span>
                        </h3>
                        <div class="space-y-3 sm:space-y-4">
                            @foreach($recentOrders->where('status', 'knitting')->take(2) as $urgent)
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 bg-red-50 dark:bg-red-900/20 p-3 sm:p-4 rounded-xl sm:rounded-[1.5rem] border border-red-100 dark:border-red-900/30">
                                <div class="space-y-1 min-w-0">
                                    <p class="text-[9px] sm:text-[10px] font-black text-red-600 dark:text-red-400 uppercase italic leading-none">ARTIKEL #{{ $urgent->art_no }}</p>
                                    <p class="text-[11px] sm:text-xs font-bold mkt-text uppercase truncate">{{ $urgent->pelanggan }}</p>
                                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-[8px] sm:text-[9px] font-bold mt-1">
                                        <span class="text-slate-600 dark:text-slate-300">{{ $urgent->created_at->format('d/m/Y') }}</span>
                                        <span class="text-slate-600 dark:text-slate-300">{{ $urgent->created_at->format('H:i') }}</span>
                                        <span class="bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300 px-2 py-0.5 rounded-full uppercase text-[7px] sm:text-[8px]">Nyangkut: {{ strtoupper($urgent->status) }}</span>
                                    </div>
                                </div>
                                <button wire:click="pushOperator({{ $urgent->id }})" 
                                        wire:loading.attr="disabled"
                                        class="w-full sm:w-auto shrink-0 bg-slate-900 dark:bg-slate-800 text-white text-[8px] sm:text-[9px] font-black px-4 py-2 rounded-xl uppercase hover:bg-red-600 transition-all italic flex items-center justify-center gap-2">
                                    <span wire:loading.remove>Push Operator</span>
                                    <span wire:loading>🚀 Sending...</span>
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mkt-surface-alt p-4 sm:p-6 md:p-8 rounded-2xl sm:rounded-[2.5rem] md:rounded-[3rem] mkt-border border">
                        <h3 class="font-black uppercase mkt-text tracking-tighter mb-4 sm:mb-6 italic text-sm sm:text-base">Marketing Command Hub</h3>
                        <div class="w-full">
                            <button wire:click="$set('currentMenu', 'orders')" class="w-full mkt-surface p-4 sm:p-6 rounded-xl sm:rounded-[2.5rem] shadow-sm mkt-border border hover:border-red-600 transition-all text-left group flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[9px] sm:text-[10px] font-black mkt-text-muted group-hover:text-brand uppercase">Strategic Analytics</p>
                                    <p class="text-sm sm:text-lg md:text-xl font-black mkt-text mt-1 italic uppercase tracking-tighter leading-tight">Lead-Time & Delivery Forecast</p>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($currentMenu === 'input')
            <div class="animate-in slide-in-from-bottom-4 duration-500">
                @livewire('marketing.order-form')
            </div>
        @elseif($currentMenu === 'orders')
            <div class="animate-in slide-in-from-bottom-4 duration-500">
                @livewire('marketing.order-list')
            </div>
        @elseif($currentMenu === 'calculator')
            <div class="animate-in slide-in-from-bottom-4 duration-500">
                @livewire('marketing.price-calculator')
            </div>
        @endif
        {{-- END DYNAMIC CONTENT --}}

        {{-- SIDE-OVER DETAIL MODAL --}}
    <div x-show="openDetail"
         class="fixed inset-0 z-[100] overflow-hidden print-isolate"
         x-cloak
         style="display: none;">

        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity no-print"
             x-show="openDetail"
             @click="$wire.closeDetail()"></div>

        <div class="fixed inset-0 sm:inset-y-0 sm:right-0 sm:left-auto pl-0 sm:pl-6 md:pl-10 max-w-full flex w-full sm:w-auto print-isolate">
            <div x-show="openDetail"
                 class="w-full sm:max-w-2xl md:max-w-5xl lg:max-w-7xl text-left h-full print-isolate">

                <div id="print-area" class="h-full flex flex-col mkt-surface shadow-2xl rounded-none sm:rounded-l-[2rem] md:rounded-l-[3rem] overflow-hidden border-0 sm:border-l mkt-border print-isolate">
                    <div class="flex-1 overflow-y-auto custom-scrollbar min-h-0 print-isolate">
                        @if($showDetail && $selectedOrder)
                            <livewire:order-tracking-detail :order-id="$selectedOrder['id']" :key="$selectedOrder['id']" />
                        @endif
                    </div>

                    {{-- Footer Modal --}}
                    <div class="p-3 sm:p-4 md:p-8 pb-[max(0.75rem,env(safe-area-inset-bottom))] mkt-surface-alt border-t mkt-border flex flex-col gap-2 sm:gap-3 shrink-0 no-print-bg">
                        <a :href="'/marketing/orders/' + selected?.id + '/print'" target="_blank" class="w-full bg-brand text-white px-4 py-3 sm:py-3.5 rounded-xl font-black text-[10px] sm:text-xs uppercase tracking-wider hover:bg-black transition-all flex items-center justify-center gap-2 shadow-lg shadow-brand/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            <span>PRINT SURAT JALAN</span>
                        </a>

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
                            }).then((result) => { if (result.isConfirmed) { $wire.deleteOrder(selected.id) } })" class="bg-red-50/10 text-red-600 border border-red-500/20 px-3 py-3 rounded-xl font-black text-[10px] sm:text-xs uppercase tracking-wider hover:bg-red-600 hover:text-white transition-all flex items-center justify-center">
                                <span>HAPUS</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .font-inter { font-family: 'Inter', sans-serif; }
    </style>

</div>
