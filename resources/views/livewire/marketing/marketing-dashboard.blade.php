<div>
    {{-- Tailwind & Chart.js sudah dikompilasi via Vite, tidak perlu CDN --}}

<div x-data="{ 
    openDetail: @entangle('showDetail'), 
    selected: @entangle('selectedOrder'),
    showLogDetail: false,
    selectedLog: null
}" class="min-h-screen mkt-bg pt-2 pb-8 px-3 sm:px-4 md:px-6 font-inter tracking-tight">
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
                            <span class="opacity-50 text-xs">📅</span>
                            <span class="real-time-date">{{ now()->locale('id')->translatedFormat('d M Y') }}</span>
                        </div>
                        <div class="w-px h-3 sm:h-4 bg-white/20"></div>
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <span class="opacity-50 text-xs">⏰</span>
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
                        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-3 gap-2.5 sm:gap-4 md:gap-6">
                            @foreach($stages as $stage)
                                <div class="mkt-surface p-3 sm:p-4 md:p-6 rounded-2xl sm:rounded-[2rem] md:rounded-[2.5rem] mkt-border border shadow-sm relative overflow-hidden transition-all hover:shadow-xl hover:mkt-border group min-w-0">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-start mb-2 sm:mb-4">
                                        <div class="min-w-0">
                                            <p class="text-[7px] sm:text-[9px] font-black mkt-text-muted uppercase tracking-wide sm:tracking-widest leading-none truncate">Dept. {{ $stage['name'] }}</p>
                                            <h4 class="text-xs sm:text-base md:text-lg font-black mkt-text mt-0.5 sm:mt-1 italic uppercase group-hover:text-brand transition-colors leading-tight line-clamp-2">{{ $stage['unit'] }}</h4>
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
                        <div class="w-full bg-slate-100 h-2 sm:h-3 rounded-full mt-3 sm:mt-6 overflow-hidden border mkt-border">
                            <div class="bg-red-600 h-full w-[100%] rounded-full animate-pulse shadow-[0_0_15px_rgba(220,38,38,0.3)]"></div>
                        </div>
                    </div>

                    <div class="mkt-surface p-4 sm:p-6 md:p-8 rounded-2xl sm:rounded-[2.5rem] md:rounded-[3rem] shadow-sm mkt-border border relative overflow-hidden min-w-0">
                        <p class="text-[8px] sm:text-[10px] font-black mkt-text-muted uppercase tracking-widest leading-tight">Finished & QC Pass</p>
                        <h3 class="text-3xl sm:text-4xl md:text-6xl font-black text-green-600 mt-1 sm:mt-2 italic">{{ $completedOrder }}</h3>
                        <div class="mt-3 sm:mt-8 flex items-center gap-2">
                            <span class="w-full h-1 bg-green-500/20 rounded-full overflow-hidden">
                                <div class="w-full h-full bg-green-500 rounded-full shadow-[0_0_10px_rgba(34,197,94,0.4)]"></div>
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
                                                    <span class="px-3 py-1 bg-gray-100 text-gray-500 rounded-full text-[9px] font-black uppercase italic border border-gray-200">Menunggu Knitting</span>
                                                @elseif($order->status === 'dyeing')
                                                    <span class="px-3 py-1 bg-brand-100 text-brand rounded-full text-[9px] font-black uppercase italic border border-brand-200">🧶 Proses Dyeing</span>
                                                @elseif($order->status === 'finished')
                                                    <span class="px-3 py-1 bg-green-100 text-green-600 rounded-full text-[9px] font-black uppercase italic border border-green-200">✅ Ready</span>
                                                @else
                                                    <span class="px-3 py-1 bg-amber-100 text-amber-600 rounded-full text-[9px] font-black uppercase italic border border-amber-200">{{ $order->status }}</span>
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
                                        <span class="text-slate-600 dark:text-slate-300">📅 {{ $urgent->created_at->format('d/m/Y') }}</span>
                                        <span class="text-slate-600 dark:text-slate-300">⏰ {{ $urgent->created_at->format('H:i') }}</span>
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
                                <span class="text-xl sm:text-2xl group-hover:translate-x-2 transition-transform shrink-0">📊</span>
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
         class="fixed inset-0 z-[100] overflow-hidden" 
         x-cloak 
         style="display: none;">
        
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
             x-show="openDetail"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="$wire.closeDetail()"></div>

        <div class="fixed inset-y-0 right-0 pl-0 sm:pl-6 md:pl-10 max-w-full flex w-full sm:w-auto">
            <div x-show="openDetail" 
                 x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="w-full sm:max-w-lg md:max-w-3xl text-left">
                
                <div id="print-area" class="h-full flex flex-col mkt-surface shadow-2xl rounded-none sm:rounded-l-[2rem] md:rounded-l-[3rem] overflow-hidden">
                    <div class="p-4 sm:p-6 md:p-8 bg-slate-900 text-white flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center border-b border-white/10 no-print-bg shrink-0">
                        <div class="min-w-0 flex-1">
                            <h2 class="text-lg sm:text-2xl font-black italic uppercase tracking-tighter leading-tight sm:leading-none text-white">
                                Industrial Order <span class="text-brand">Detail</span>
                            </h2>
                            <p class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic truncate">
                                ID: <span x-text="selected ? selected.art_no : ''" class="text-white font-black"></span>
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button @click="showLogDetail = true" class="flex-1 sm:flex-none bg-brand-600 hover:bg-brand-700 text-white px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl text-[10px] sm:text-xs font-black uppercase transition-all flex items-center justify-center gap-1.5 shadow-lg no-print">
                                <span>Hasil</span>
                                <span>👁️</span>
                            </button>
                            <button wire:click="closeDetail" class="bg-white/10 hover:bg-red-600 p-2.5 sm:p-3 rounded-xl sm:rounded-2xl transition group no-print">
                                <svg class="h-5 w-5 sm:h-6 sm:w-6 text-slate-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8 space-y-4 sm:space-y-6 md:space-y-8 pb-24 sm:pb-32 bg-slate-50/30 dark:bg-transparent">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 md:gap-8">
                            <div class="mkt-surface p-6 rounded-[2rem] border mkt-border shadow-sm">
                                <h3 class="mkt-text-muted font-black mb-4 border-b mkt-border pb-2 uppercase italic tracking-tighter text-sm flex items-center">
                                    <span class="w-2 h-4 bg-brand mr-2 rounded-full"></span>I. Identity & Sales
                                </h3>
                                <div class="space-y-4">
                                    <div class="flex justify-between border-b mkt-border pb-1 font-bold">
                                        <p class="text-[10px] mkt-text-muted uppercase tracking-widest">Pelanggan</p>
                                        <p class="mkt-text uppercase" x-text="selected?.pelanggan"></p>
                                    </div>
                                    <div class="flex justify-between border-b mkt-border pb-1 font-bold">
                                        <p class="text-[10px] mkt-text-muted uppercase tracking-widest">Artikel No</p>
                                        <p class="mkt-text uppercase" x-text="selected?.art_no"></p>
                                    </div>
                                    <div class="flex justify-between border-b mkt-border pb-1 font-bold">
                                        <p class="text-[10px] mkt-text-muted uppercase tracking-widest">MKT Representative</p>
                                        <p class="mkt-text italic" x-text="selected?.mkt"></p>
                                    </div>
                                    <div class="flex justify-between font-bold">
                                        <p class="text-[10px] mkt-text-muted uppercase tracking-widest">Keperluan</p>
                                        <p class="mkt-text" x-text="selected?.keperluan"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="mkt-surface p-6 rounded-[2rem] border mkt-border shadow-sm">
                                <h3 class="mkt-text font-black mb-4 border-b mkt-border pb-2 uppercase italic tracking-tighter text-sm flex items-center">
                                    <span class="w-2 h-4 bg-brand mr-2 rounded-full"></span>II. Technical Specs
                                </h3>
                                <div class="space-y-4 font-bold">
                                    <div class="flex justify-between border-b mkt-border pb-1">
                                        <p class="text-[10px] mkt-text-muted uppercase tracking-widest">Material</p>
                                        <p class="mkt-text uppercase" x-text="selected?.material"></p>
                                    </div>
                                    <div class="flex justify-between border-b mkt-border pb-1">
                                        <p class="text-[10px] mkt-text-muted uppercase tracking-widest">Benang</p>
                                        <p class="mkt-text uppercase" x-text="selected?.benang"></p>
                                    </div>
                                    <div class="flex justify-between border-b mkt-border pb-1">
                                        <p class="text-[10px] mkt-text-muted uppercase tracking-widest">Konstruksi Greige</p>
                                        <p class="mkt-text" x-text="selected?.konstruksi_greige"></p>
                                    </div>
                                    <div class="flex justify-between">
                                        <p class="text-[10px] mkt-text-muted uppercase tracking-widest">Finishing Warna</p>
                                        <p class="mkt-text uppercase italic" x-text="selected?.warna"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mkt-surface-alt p-8 rounded-[3rem] mkt-text border mkt-border shadow-sm">
                            <h3 class="mkt-text-muted font-black mb-6 uppercase italic tracking-tighter text-center underline underline-offset-8">Production Specification Matrix</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center font-bold">
                                <div class="border-r mkt-border">
                                    <p class="text-[9px] mkt-text-muted uppercase mb-1">Kelompok Kain</p>
                                    <p class="text-lg mkt-text" x-text="selected?.kelompok_kain"></p>
                                </div>
                                <div class="border-r mkt-border">
                                    <p class="text-[9px] mkt-text-muted uppercase mb-1">Lebar / Gramasi</p>
                                    <p class="text-lg mkt-text"><span x-text="selected?.target_lebar"></span>" / <span x-text="selected?.target_gramasi"></span></p>
                                </div>
                                <div class="border-r mkt-border">
                                    <p class="text-[9px] mkt-text-muted uppercase mb-1">Belah / Bulat</p>
                                    <p class="text-lg uppercase mkt-text" x-text="selected?.belah_bulat"></p>
                                </div>
                                <div>
                                    <p class="text-[9px] mkt-text-muted uppercase mb-1">Handfeel</p>
                                    <p class="text-lg uppercase mkt-text" x-text="selected?.handfeel"></p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 font-black italic">
                            <div class="bg-brand text-white p-8 rounded-[2.5rem] shadow-lg shadow-brand-100 flex justify-between items-center">
                                <p class="text-xs uppercase tracking-[0.2em]">Total Roll Target</p>
                                <h4 class="text-4xl underline decoration-4 underline-offset-8" x-text="selected?.roll_target"></h4>
                            </div>
                            <div class="bg-emerald-600 text-white p-8 rounded-[2.5rem] shadow-lg shadow-emerald-100 flex justify-between items-center">
                                <p class="text-xs uppercase tracking-[0.2em]">Total Net Weight (KG)</p>
                                <h4 class="text-4xl underline decoration-4 underline-offset-8" x-text="selected?.kg_target"></h4>
                            </div>
                        </div>

                        <div class="mkt-surface p-8 rounded-[3rem] border-l-[12px] border-red-600 shadow-sm border mkt-border">
                            <p class="text-[10px] font-black mkt-text-muted uppercase mb-2 italic tracking-widest">Special Treatment & Instructions:</p>
                            <p class="text-lg font-black mkt-text uppercase mb-4 underline decoration-red-600/30 underline-offset-4" x-text="selected?.treatment_khusus || '-'"></p>
                            <hr class="mkt-border my-4">
                            <p class="text-[10px] font-black mkt-text-muted uppercase mb-2 italic tracking-widest">Internal Marketing Notes:</p>
                            <p class="text-xs font-bold mkt-text-muted leading-relaxed italic mkt-surface-alt p-4 rounded-2xl" x-text="selected?.keterangan_artikel || 'No additional internal notes provided.'"></p>
                        </div>

                        <div class="mkt-surface p-8 rounded-[3rem] border mkt-border shadow-sm overflow-hidden">
                            <h3 class="mkt-text font-black mb-8 uppercase italic tracking-tighter text-sm flex items-center">
                                <span class="w-2 h-4 bg-brand mr-2 rounded-full"></span>III. Production Milestone
                            </h3>
                            
                            <div class="relative">
                                <div class="absolute left-4 top-0 h-full w-0.5 mkt-surface-alt"></div>
                                <div class="absolute left-4 top-0 w-0.5 bg-red-600 transition-all duration-700"
                                    :style="selected?.status === 'knitting' ? 'height: 10%' : 
                                            (selected?.status === 'dyeing' ? 'height: 20%' : 
                                            (selected?.status === 'relax-dryer' ? 'height: 30%' : 
                                            (selected?.status === 'finishing' ? 'height: 40%' : 
                                            (selected?.status === 'stenter' ? 'height: 50%' : 
                                            (selected?.status === 'tumbler' ? 'height: 60%' : 
                                            (selected?.status === 'fleece' ? 'height: 70%' : 
                                            (selected?.status === 'pengujian' ? 'height: 80%' : 
                                            (selected?.status === 'qe' ? 'height: 90%' : 
                                            (selected?.status === 'finished' ? 'height: 100%' : 'height: 5%')))))))))"
                                ></div>

                                <div class="space-y-6 relative">
                                    <div class="flex items-start gap-6">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center z-10 bg-red-600 text-white shadow-lg shadow-red-200">
                                            <span class="text-[10px] font-black">✓</span>
                                        </div>
                                        <div>
                                            <p class="text-xs font-black uppercase italic text-slate-800">Marketing & Sales</p>
                                            <p class="text-[9px] font-bold text-slate-400">ORDER CREATED & REGISTERED</p>
                                        </div>
                                    </div>

                                    @php
                                        $milestones = [];
                                        $milestones[] = ['status' => 'knitting', 'label' => 'Knitting Process', 'desc' => 'RAJUT UNIT DDT 2'];
                                        $milestones[] = ['status' => 'dyeing', 'label' => 'SCR / Dyeing', 'desc' => 'PROSES WARNA & PENCUCIAN'];
                                        
                                        if (!empty($selectedOrder['req_tumbler'])) {
                                            $milestones[] = ['status' => 'relax-dryer', 'label' => 'Relax Dryer', 'desc' => 'PENGERINGAN TANPA TEGANGAN'];
                                        }
                                        if (!empty($selectedOrder['req_compactor'])) {
                                            $milestones[] = ['status' => 'compactor', 'label' => 'Compactor', 'desc' => 'FINISHING BULAT (COTTON)'];
                                        }
                                        if (!empty($selectedOrder['req_heat_setting'])) {
                                            $milestones[] = ['status' => 'heat-setting', 'label' => 'Heat Setting', 'desc' => 'FINISHING BULAT (PE/POLYESTER)'];
                                        }
                                        if (!empty($selectedOrder['req_stenter'])) {
                                            $milestones[] = ['status' => 'stenter', 'label' => 'Stenter Process', 'desc' => 'SETTING LEBAR & GRAMASI'];
                                        }
                                        if (!empty($selectedOrder['req_tumbler'])) {
                                            $milestones[] = ['status' => 'tumbler', 'label' => 'Tumbler Dry', 'desc' => 'PROSES BULKING KAIN'];
                                        }
                                        if (!empty($selectedOrder['req_fleece'])) {
                                            $milestones[] = ['status' => 'fleece', 'label' => 'Fleece / Brushing', 'desc' => 'GARUK BULU (UNIT FLEECE)'];
                                        }
                                        if (!empty($selectedOrder['req_pengujian'])) {
                                            $milestones[] = ['status' => 'pengujian', 'label' => 'QC & Lab Testing', 'desc' => 'PENGUJIAN FISIK KAIN'];
                                        }
                                        if (!empty($selectedOrder['req_qe'])) {
                                            $milestones[] = ['status' => 'qe', 'label' => 'QE Approval', 'desc' => 'FINAL INSPECTION & RELEASE'];
                                        }
                                        
                                        // Generate IDs and after array dynamically
                                        $allStatuses = array_map(fn($m) => $m['status'], $milestones);
                                        $allStatuses[] = 'finished';
                                        
                                        $id = 2;
                                        foreach($milestones as $key => &$m) {
                                            $m['id'] = str_pad($id++, 2, '0', STR_PAD_LEFT);
                                            $m['after'] = array_slice($allStatuses, $key + 1);
                                        }
                                    @endphp

                                    @foreach($milestones as $m)
                                        @php $log = $activitiesLogs[$m['status']][0] ?? null; @endphp
                                        <div class="flex items-start gap-6">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center z-10 transition-all duration-500"
                                                :class="[ @foreach($m['after'] as $a)'{{$a}}',@endforeach ].includes(selected?.status) ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-200' : 'bg-slate-100 text-slate-600'">
                                                <span class="text-[10px] font-black" x-text="[ @foreach($m['after'] as $a)'{{$a}}',@endforeach ].includes(selected?.status) ? '✓' : '{{ $m['id'] }}'"></span>
                                            </div>
                                            <div class="flex-1 flex justify-between items-center">
                                                <div>
                                                    <p class="text-xs font-black uppercase italic" :class="selected?.status === '{{ $m['status'] }}' ? 'text-brand animate-pulse' : 'text-slate-800 dark:text-slate-200'">{{ $m['label'] }}</p>
                                                    <p class="text-[9px] font-bold text-slate-400 uppercase">{{ $m['desc'] }}</p>
                                                    @if($log)
                                                        <p class="text-[8px] font-black text-emerald-500 mt-1">DONE: {{ \Carbon\Carbon::parse($log['created_at'])->format('d/m H:i') }}</p>
                                                    @endif
                                                </div>
                                                
                                                @if($log)
                                                    <p class="text-[8px] font-black text-emerald-500 mt-1">DONE: {{ \Carbon\Carbon::parse($log['created_at'])->format('d/m H:i') }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-data="{ show: false, message: '', type: 'success' }"
            x-init="
                window.addEventListener('show-toast', (event) => {
                    console.log('Sinyal Masuk:', event.detail);
                    let data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
                    message = data.message;
                    type = data.type;
                    show = true;
                    setTimeout(() => show = false, 4000);
                });
            "
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-4"
            class="fixed bottom-10 right-10 z-[9999]"
            style="display: none;">
            
            <div :class="type === 'success' ? 'bg-slate-900' : 'bg-red-600'" 
                class="text-white px-8 py-5 rounded-[2rem] shadow-2xl border border-white/10 flex items-center gap-5 min-w-[320px]">
                <div class="bg-white/20 p-3 rounded-2xl text-2xl">🚀</div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] opacity-50 leading-none mb-1">Production Alert</p>
                    <p class="text-xs font-black italic tracking-tight" x-text="message"></p>
                </div>
            </div>
        </div>



    {{-- SECONDARY MODAL: TECHNICAL DATA DETAILS (full screen HP) --}}
    <div x-show="showLogDetail" @close-log-detail.window="showLogDetail = false" class="fixed inset-0 z-[110] flex items-end sm:items-center justify-center p-0 sm:p-4" x-cloak style="display: none;">
        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-md transition-opacity" @click="showLogDetail = false"></div>
        <div class="relative z-10 w-full h-full sm:h-auto sm:max-h-[95dvh] max-w-full sm:max-w-4xl flex flex-col">
            @if($selectedOrder)
                <livewire:order-tracking-detail :order-id="$selectedOrder['id']" :key="$selectedOrder['id']" />
            @endif
        </div>
    </div>
    </div>

    <style>
        .font-inter { font-family: 'Inter', sans-serif; }
    </style>

</div></div>
