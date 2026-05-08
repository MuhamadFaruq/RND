<div>
    {{-- Tailwind & Chart.js sudah dikompilasi via Vite, tidak perlu CDN --}}

<div x-data="{ openDetail: @entangle('showDetail'), selected: @entangle('selectedOrder') }" class="min-h-screen mkt-bg pt-2 pb-8 px-6 font-inter tracking-tight">
    <div class="max-w-[1600px] mx-auto">
        
        {{-- HEADER UTAMA --}}
        <div class="mb-2 flex flex-row justify-between items-start w-full">
            {{-- SISI KIRI: JUDUL DASHBOARD --}}
            <div class="flex-grow">
                @if($currentMenu === 'dashboard')
                    <div class="animate-in fade-in duration-700">
                        <h1 class="text-4xl font-black uppercase mkt-text leading-none">
                            Marketing <span class="text-red-600">War Room</span>
                        </h1>
                        <p class="text-sm font-bold mkt-text-muted uppercase tracking-[0.3em] mt-2 italic">
                            Duniatex Group Industrial Monitoring Hub
                        </p>
                    </div>
                @endif
            </div>

                {{-- SISI KANAN: STATUS LIVE & TANGGAL & JAM --}}
            @if($currentMenu === 'dashboard')
                <div class="flex flex-row items-center gap-4 flex-shrink-0 ml-10 animate-in fade-in duration-700">
                    <div class="text-right border-r border-slate-200 pr-6 hidden md:block">
                        <p class="text-[10px] font-black text-slate-400 uppercase leading-none mb-1 tracking-widest">Global Factory Load</p>
                        @php
                            $loadColor = $factoryLoad > 80 ? 'text-red-600' : ($factoryLoad > 50 ? 'text-amber-500' : 'text-emerald-500');
                        @endphp
                        <p class="text-sm font-black {{ $loadColor }} flex items-center justify-end gap-2 italic">
                            <span class="w-2 h-2 rounded-full animate-pulse bg-current"></span> 
                            {{ number_format($factoryLoad, 1) }}% BUSY
                        </p>
                    </div>
                    
                    <div class="bg-slate-900 text-white px-6 py-3 rounded-2xl font-black text-xs uppercase shadow-xl flex items-center gap-4 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <span class="opacity-50">📅</span> 
                            <span class="real-time-date">{{ now()->format('d M Y') }}</span>
                        </div>
                        <div class="w-px h-4 bg-white/20"></div>
                        <div class="flex items-center gap-2">
                            <span class="opacity-50">⏰</span> 
                            <span class="real-time-clock font-mono">00:00:00</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- START DYNAMIC CONTENT --}}
        @if($currentMenu === 'dashboard')
            <div class="animate-in fade-in duration-500">
                {{-- LIVE MACHINE WORKLOAD (Kapasitas Divisi) --}}
                <div class="mb-10">
                    <div class="flex justify-between items-end mb-6">
                        <h3 class="font-black uppercase mkt-text tracking-tighter flex items-center gap-2 italic text-2xl">
                            <span class="w-10 h-1 bg-red-600"></span> Live Operator Activity Stream
                        </h3>
                        <div class="text-right">
                            <p class="text-[9px] font-black mkt-text-muted italic uppercase">Machine Standard Capacity</p>
                            <p class="text-xs font-black mkt-text italic uppercase tracking-widest">{{ number_format($maxCapacity) }} KG / LOT</p>
                        </div>
                    </div>
                    
                    <div wire:poll.10s class="animate-in fade-in duration-500"> 
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @foreach($stages as $stage)
                                <div class="mkt-surface p-6 rounded-[2.5rem] mkt-border border shadow-sm relative overflow-hidden transition-all hover:shadow-xl hover:mkt-border group">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <p class="text-[9px] font-black mkt-text-muted uppercase tracking-widest leading-none">Dept. {{ $stage['name'] }}</p>
                                            <h4 class="text-lg font-black mkt-text mt-1 italic uppercase group-hover:text-red-600 transition-colors">{{ $stage['unit'] }}</h4>
                                        </div>
                                        <div class="flex flex-col items-end">
                                            <span class="bg-{{ $stage['color'] ?? 'slate' }}-100 text-{{ $stage['color'] ?? 'slate' }}-600 text-[8px] px-3 py-1 rounded-full font-black">CONNECTED</span>
                                            <p class="text-[10px] font-black mkt-text mt-1">{{ $stage['load_count'] }} SAP</p>
                                        </div>
                                    </div>

                                    <div class="relative pt-1 mb-4">
                                        <div class="flex justify-between mb-2 uppercase italic font-black text-[9px]">
                                            <span class="mkt-text-muted">Load: {{ number_format($stage['load_kg']) }} KG</span>
                                            <span class="{{ $stage['percentage'] > 90 ? 'text-red-600 animate-pulse' : 'mkt-text' }}">
                                                {{ $stage['is_full'] ? 'FULL LOAD' : 'SLOT AVAILABLE' }}
                                            </span>
                                        </div>
                                        <div class="overflow-hidden h-2.5 flex rounded-full bg-slate-100 shadow-inner">
                                            <div style="width: {{ $stage['percentage'] ?? 0 }}%" 
                                                class="bg-{{ $stage['color'] ?? 'slate' }}-500 rounded-full shadow-lg transition-all duration-1000">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="relative flex h-2 w-2">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-{{ $stage['color'] ?? 'slate' }}-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-{{ $stage['color'] ?? 'slate' }}-500"></span>
                                            </span>
                                            <p class="text-[9px] font-bold mkt-text-muted italic uppercase tracking-tighter">
                                                {{ $stage['desc'] ?? 'Monitoring Active' }}
                                            </p>
                                        </div>
                                        <p class="text-[10px] font-black italic {{ $stage['percentage'] > 80 ? 'text-red-600' : 'text-slate-400' }}">
                                            {{ number_format($stage['percentage'], 1) }}%
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- SUMMARY STATS --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div class="bg-slate-900 p-8 rounded-[3rem] shadow-xl text-white relative overflow-hidden group">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest relative z-10">Total Registered Orders</p>
                        <h3 class="text-6xl font-black text-white mt-2 relative z-10 italic">{{ number_format($totalOrder) }}</h3>
                        <div class="absolute -right-10 -bottom-10 opacity-5 text-9xl font-black italic group-hover:scale-110 transition-transform">SAP</div>
                    </div>

                    <div class="mkt-surface p-8 rounded-[3rem] shadow-sm mkt-border border relative overflow-hidden">
                        <p class="text-[10px] font-black mkt-text-muted uppercase tracking-widest">Active Production</p>
                        <h3 class="text-6xl font-black text-red-600 mt-2 italic">{{ $activeOrder }}</h3>
                        <div class="w-full bg-slate-100 h-3 rounded-full mt-6 overflow-hidden border mkt-border">
                            <div class="bg-red-600 h-full w-[100%] rounded-full animate-pulse shadow-[0_0_15px_rgba(220,38,38,0.3)]"></div>
                        </div>
                    </div>

                    <div class="mkt-surface p-8 rounded-[3rem] shadow-sm mkt-border border relative overflow-hidden">
                        <p class="text-[10px] font-black mkt-text-muted uppercase tracking-widest">Finished & QC Pass</p>
                        <h3 class="text-6xl font-black text-green-600 mt-2 italic">{{ $completedOrder }}</h3>
                        <div class="mt-8 flex items-center gap-2">
                            <span class="w-full h-1 bg-green-500/20 rounded-full overflow-hidden">
                                <div class="w-full h-full bg-green-500 rounded-full shadow-[0_0_10px_rgba(34,197,94,0.4)]"></div>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- SALES PERFORMANCE & REPS --}}
                <div class="mb-10">
                    <div class="mkt-surface p-8 rounded-[3rem] mkt-border border shadow-sm">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                            <div>
                                <h3 class="text-2xl font-black uppercase mkt-text tracking-tighter italic">Recent Production Flow</h3>
                                <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-widest mt-1 italic">Industrial Stream Analytics (Live Updates)</p>
                            </div>
                            <div class="flex flex-row items-center gap-4 w-full md:w-auto">
                                <div class="relative w-full md:w-80 group">
                                    <span class="absolute left-5 top-4 mkt-text-muted group-focus-within:text-red-600 transition-colors">🔍</span>
                                    <input type="text" 
                                        wire:model.live="search" 
                                        placeholder="Search SAP / Customer..." 
                                        class="w-full pl-14 pr-6 py-4 mkt-input border-none rounded-[1.5rem] text-xs font-black uppercase italic focus:ring-4 focus:ring-red-600/10 transition-all outline-none">
                                </div>
                                <a href="{{ route('marketing.dashboard', ['menu' => 'input']) }}" class="hidden md:flex items-center gap-2 px-6 py-4 bg-red-600 hover:bg-black text-white rounded-[1.5rem] text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-red-600/20 whitespace-nowrap">
                                    <span>+ New Order</span>
                                </a>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-separate border-spacing-y-3" id="productionTable">
                                <thead>
                                    <tr class="text-[10px] font-black mkt-text-muted uppercase tracking-widest">
                                        <th class="px-4 pb-2">SAP NO</th>
                                        <th class="px-4 pb-2">Customer / Article</th>
                                        <th class="px-4 pb-2">Warna</th>
                                        <th class="px-4 pb-2">Status Pipeline</th>
                                        <th class="px-4 pb-2 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentOrders as $order)
                                        <tr class="mkt-surface-alt hover:opacity-80 transition-all group table-row-item">
                                            <td class="px-4 py-4 rounded-l-[1.5rem] font-black text-xs text-blue-600 italic sap-cell">
                                                #{{ $order->sap_no }}
                                            </td>
                                            <td class="px-4 py-4 customer-cell">
                                                <p class="text-[10px] font-black mkt-text uppercase">{{ $order->pelanggan }}</p>
                                                <p class="text-[9px] font-bold mkt-text-muted uppercase italic">{{ $order->art_no }}</p>
                                            </td>
                                            <td class="px-4 py-4 text-[10px] font-black uppercase italic mkt-text">{{ $order->warna }}</td>
                                            <td class="px-4 py-4">
                                                {{-- Badge Status yang sudah kita rapikan sebelumnya --}}
                                                @if($order->status === 'knitting')
                                                    <span class="px-3 py-1 bg-gray-100 text-gray-500 rounded-full text-[9px] font-black uppercase italic border border-gray-200">Menunggu Knitting</span>
                                                @elseif($order->status === 'dyeing')
                                                    <span class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-[9px] font-black uppercase italic border border-blue-200">🧶 Proses Dyeing</span>
                                                @elseif($order->status === 'finished')
                                                    <span class="px-3 py-1 bg-green-100 text-green-600 rounded-full text-[9px] font-black uppercase italic border border-green-200">✅ Ready</span>
                                                @else
                                                    <span class="px-3 py-1 bg-amber-100 text-amber-600 rounded-full text-[9px] font-black uppercase italic border border-amber-200">{{ $order->status }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 rounded-r-[1.5rem] text-right">
                                                <button wire:click="openDetail({{ $order->id }})" class="text-[10px] font-black text-red-600 uppercase hover:underline italic">
                                                    <span wire:loading.remove wire:target="openDetail({{ $order->id }})">Detail</span>
                                                    <span wire:loading wire:target="openDetail({{ $order->id }})">...</span>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        {{-- Tampilan saat data pencarian TIDAK DITEMUKAN --}}
                                        <tr>
                                            <td colspan="5" class="px-4 py-12 text-center">
                                                <div class="flex flex-col items-center justify-center">
                                                    <div class="bg-slate-100 p-4 rounded-full mb-4">
                                                        <span class="text-3xl">🔎</span>
                                                    </div>
                                                    <p class="text-sm font-black mkt-text uppercase italic tracking-tighter">
                                                        Data Tidak Ditemukan
                                                    </p>
                                                    <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-widest mt-1">
                                                        Tidak ada pesanan dengan kata kunci "<span class="text-red-600">{{ $search }}</span>"
                                                    </p>
                                                    <button wire:click="$set('search', '')" class="mt-4 text-[9px] font-black text-blue-600 uppercase underline decoration-2 underline-offset-4">
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
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="mkt-surface p-8 rounded-[3rem] mkt-border border shadow-sm border-l-8 border-l-red-600">
                        <h3 class="font-black uppercase mkt-text tracking-tighter mb-6 flex items-center gap-3 italic">
                            Critical Orders Alert <span class="bg-red-100 text-red-600 text-[10px] px-3 py-1 rounded-full animate-pulse">{{ $knittingOrder }} URGENT</span>
                        </h3>
                        <div class="space-y-4">
                            @foreach($recentOrders->where('status', 'knitting')->take(2) as $urgent)
                            <div class="flex justify-between items-center bg-red-50/50 p-4 rounded-[1.5rem] border border-red-100">
                                <div>
                                    <p class="text-[10px] font-black text-red-600 uppercase italic leading-none">SAP #{{ $urgent->sap_no }}</p>
                                    <p class="text-xs font-bold mkt-text mt-1 uppercase">{{ $urgent->pelanggan }}</p>
                                </div>
                                <button wire:click="pushOperator({{ $urgent->id }})" 
                                        wire:loading.attr="disabled"
                                        class="bg-slate-900 text-white text-[9px] font-black px-4 py-2 rounded-xl uppercase hover:bg-red-600 transition-all italic flex items-center gap-2">
                                    <span wire:loading.remove>Push Operator</span>
                                    <span wire:loading>🚀 Sending...</span>
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mkt-surface-alt p-8 rounded-[3rem] mkt-border border">
                        <h3 class="font-black uppercase mkt-text tracking-tighter mb-6 italic">Marketing Command Hub</h3>
                        <div class="w-full">
                            <button wire:click="$set('currentMenu', 'orders')" class="w-full mkt-surface p-6 rounded-[2.5rem] shadow-sm mkt-border border hover:border-red-600 transition-all text-left group flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-black mkt-text-muted group-hover:text-red-600 uppercase">Strategic Analytics</p>
                                    <p class="text-xl font-black mkt-text mt-1 italic uppercase tracking-tighter">Lead-Time & Delivery Forecast</p>
                                </div>
                                <span class="text-2xl group-hover:translate-x-2 transition-transform">📊</span>
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

        <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
            <div x-show="openDetail" 
                 x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="w-screen max-w-3xl text-left">
                
                <div id="print-area" class="h-full flex flex-col bg-white shadow-2xl rounded-l-[3rem] overflow-hidden">
                    <div class="p-8 bg-slate-900 text-white flex justify-between items-center rounded-tl-[3rem] border-b border-white/10 no-print-bg">
                        <div class="flex-1">
                            <h2 class="text-2xl font-black italic uppercase tracking-tighter leading-none text-white">
                                Industrial Order <span class="text-red-500 text-3xl italic">Detail</span>
                            </h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic">
                                Internal Tracking ID: <span x-text="selected ? selected.sap_no : ''" class="text-white font-black"></span>
                            </p>
                        </div>
                        
                        <button wire:click="closeDetail" class="bg-white/10 hover:bg-red-600 p-3 rounded-2xl transition group">
                            <svg class="no-print h-6 w-6 text-slate-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-8 space-y-8 pb-32 bg-slate-50/30">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm">
                                <h3 class="text-red-600 font-black mb-4 border-b pb-2 uppercase italic tracking-tighter text-sm flex items-center">
                                    <span class="w-2 h-4 bg-red-600 mr-2 rounded-full"></span>I. Identity & Sales
                                </h3>
                                <div class="space-y-4">
                                    <div class="flex justify-between border-b border-slate-50 pb-1 font-bold">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">Pelanggan</p>
                                        <p class="text-slate-800 uppercase" x-text="selected?.pelanggan"></p>
                                    </div>
                                    <div class="flex justify-between border-b border-slate-50 pb-1 font-bold">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">Artikel No</p>
                                        <p class="text-slate-800 uppercase" x-text="selected?.art_no"></p>
                                    </div>
                                    <div class="flex justify-between border-b border-slate-50 pb-1 font-bold">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">MKT Representative</p>
                                        <p class="text-slate-800 italic" x-text="selected?.mkt"></p>
                                    </div>
                                    <div class="flex justify-between font-bold">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">Keperluan</p>
                                        <p class="text-slate-800" x-text="selected?.keperluan"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm">
                                <h3 class="text-slate-900 font-black mb-4 border-b pb-2 uppercase italic tracking-tighter text-sm flex items-center">
                                    <span class="w-2 h-4 bg-slate-900 mr-2 rounded-full"></span>II. Technical Specs
                                </h3>
                                <div class="space-y-4 font-bold">
                                    <div class="flex justify-between border-b border-slate-50 pb-1">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">Material</p>
                                        <p class="text-slate-800 uppercase" x-text="selected?.material"></p>
                                    </div>
                                    <div class="flex justify-between border-b border-slate-50 pb-1">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">Benang</p>
                                        <p class="text-slate-800 uppercase" x-text="selected?.benang"></p>
                                    </div>
                                    <div class="flex justify-between border-b border-slate-50 pb-1">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">Konstruksi Greige</p>
                                        <p class="text-slate-800" x-text="selected?.konstruksi_greige"></p>
                                    </div>
                                    <div class="flex justify-between">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest text-red-600">Finishing Warna</p>
                                        <p class="text-red-600 uppercase italic" x-text="selected?.warna"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-900 p-8 rounded-[3rem] text-white shadow-xl shadow-slate-200">
                            <h3 class="text-red-500 font-black mb-6 uppercase italic tracking-tighter text-center underline underline-offset-8">Production Specification Matrix</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center font-bold">
                                <div class="border-r border-white/10">
                                    <p class="text-[9px] text-slate-400 uppercase mb-1">Kelompok Kain</p>
                                    <p class="text-lg" x-text="selected?.kelompok_kain"></p>
                                </div>
                                <div class="border-r border-white/10">
                                    <p class="text-[9px] text-slate-400 uppercase mb-1">Lebar / Gramasi</p>
                                    <p class="text-lg"><span x-text="selected?.target_lebar"></span>" / <span x-text="selected?.target_gramasi"></span></p>
                                </div>
                                <div class="border-r border-white/10">
                                    <p class="text-[9px] text-slate-400 uppercase mb-1">Belah / Bulat</p>
                                    <p class="text-lg uppercase" x-text="selected?.belah_bulat"></p>
                                </div>
                                <div>
                                    <p class="text-[9px] text-slate-400 uppercase mb-1">Handfeel</p>
                                    <p class="text-lg uppercase" x-text="selected?.handfeel"></p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 font-black italic">
                            <div class="bg-blue-600 text-white p-8 rounded-[2.5rem] shadow-lg shadow-blue-100 flex justify-between items-center">
                                <p class="text-xs uppercase tracking-[0.2em]">Total Roll Target</p>
                                <h4 class="text-4xl underline decoration-4 underline-offset-8" x-text="selected?.roll_target"></h4>
                            </div>
                            <div class="bg-emerald-600 text-white p-8 rounded-[2.5rem] shadow-lg shadow-emerald-100 flex justify-between items-center">
                                <p class="text-xs uppercase tracking-[0.2em]">Total Net Weight (KG)</p>
                                <h4 class="text-4xl underline decoration-4 underline-offset-8" x-text="selected?.kg_target"></h4>
                            </div>
                        </div>

                        <div class="bg-white p-8 rounded-[3rem] border-l-[12px] border-red-600 shadow-sm">
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-2 italic tracking-widest">Special Treatment & Instructions:</p>
                            <p class="text-lg font-black text-slate-800 uppercase mb-4 underline decoration-red-600/30 underline-offset-4" x-text="selected?.treatment_khusus || '-'"></p>
                            <hr class="border-slate-100 my-4">
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-2 italic tracking-widest">Internal Marketing Notes:</p>
                            <p class="text-xs font-bold text-slate-600 leading-relaxed italic bg-slate-50 p-4 rounded-2xl" x-text="selected?.keterangan_artikel || 'No additional internal notes provided.'"></p>
                        </div>

                        <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm overflow-hidden">
                            <h3 class="text-slate-900 font-black mb-8 uppercase italic tracking-tighter text-sm flex items-center">
                                <span class="w-2 h-4 bg-red-600 mr-2 rounded-full"></span>III. Production Milestone
                            </h3>
                            
                            <div class="relative">
                                <div class="absolute left-4 top-0 h-full w-0.5 bg-slate-100"></div>
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
                                        $milestones = [
                                            ['id' => '02', 'status' => 'knitting', 'label' => 'Knitting Process', 'desc' => 'RAJUT UNIT DDT 2', 'after' => ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'stenter', 'tumbler', 'fleece', 'pengujian', 'qe', 'finished']],
                                            ['id' => '03', 'status' => 'dyeing', 'label' => 'SCR / Dyeing', 'desc' => 'PROSES WARNA & PENCUCIAN', 'after' => ['relax-dryer', 'compactor', 'heat-setting', 'stenter', 'tumbler', 'fleece', 'pengujian', 'qe', 'finished']],
                                            ['id' => '04', 'status' => 'relax-dryer', 'label' => 'Relax Dryer', 'desc' => 'PENGERINGAN TANPA TEGANGAN', 'after' => ['compactor', 'heat-setting', 'stenter', 'tumbler', 'fleece', 'pengujian', 'qe', 'finished']],
                                            ['id' => '05', 'status' => 'compactor', 'label' => 'Compactor', 'desc' => 'FINISHING BULAT (COTTON)', 'after' => ['heat-setting', 'stenter', 'tumbler', 'fleece', 'pengujian', 'qe', 'finished']],
                                            ['id' => '06', 'status' => 'heat-setting', 'label' => 'Heat Setting', 'desc' => 'FINISHING BULAT (PE/POLYESTER)', 'after' => ['stenter', 'tumbler', 'fleece', 'pengujian', 'qe', 'finished']],
                                            ['id' => '07', 'status' => 'stenter', 'label' => 'Stenter Process', 'desc' => 'SETTING LEBAR & GRAMASI', 'after' => ['tumbler', 'fleece', 'pengujian', 'qe', 'finished']],
                                            ['id' => '08', 'status' => 'tumbler', 'label' => 'Tumbler Dry', 'desc' => 'PROSES BULKING KAIN', 'after' => ['fleece', 'pengujian', 'qe', 'finished']],
                                            ['id' => '09', 'status' => 'fleece', 'label' => 'Fleece / Brushing', 'desc' => 'GARUK BULU (UNIT FLEECE)', 'after' => ['pengujian', 'qe', 'finished']],
                                            ['id' => '10', 'status' => 'pengujian', 'label' => 'QC & Lab Testing', 'desc' => 'PENGUJIAN FISIK KAIN', 'after' => ['qe', 'finished']],
                                            ['id' => '11', 'status' => 'qe', 'label' => 'QE Approval', 'desc' => 'FINAL INSPECTION & RELEASE', 'after' => ['finished']],
                                        ];
                                    @endphp

                                    @foreach($milestones as $m)
                                        @php $log = $activitiesLogs[$m['status']][0] ?? null; @endphp
                                        <div class="flex items-start gap-6">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center z-10 transition-all duration-500"
                                                :class="[ @foreach($m['after'] as $a)'{{$a}}',@endforeach ].includes(selected?.status) ? 'bg-red-600 text-white' : 'bg-slate-100 text-slate-400'">
                                                <span class="text-[10px] font-black" x-text="[ @foreach($m['after'] as $a)'{{$a}}',@endforeach ].includes(selected?.status) ? '✓' : '{{ $m['id'] }}'"></span>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-xs font-black uppercase italic" :class="selected?.status === '{{ $m['status'] }}' ? 'text-red-600 animate-pulse' : ''">{{ $m['label'] }}</p>
                                                <p class="text-[9px] font-bold text-slate-400 uppercase">{{ $m['desc'] }}</p>
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

    <div class="mb-6 flex justify-between items-start"> </div>
    </div>

    <style>
        .font-inter { font-family: 'Inter', sans-serif; }
    </style>

</div></div>
