    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="min-h-screen bg-[#f8fafc] pt-2 pb-8 px-6 font-inter tracking-tight">
    <div class="max-w-[1600px] mx-auto">
        
        {{-- HEADER UTAMA --}}
        <div class="mb-2 flex flex-row justify-between items-start w-full">
            {{-- SISI KIRI: JUDUL DASHBOARD --}}
            <div class="flex-grow">
                @if($currentMenu === 'dashboard')
                    <div class="animate-in fade-in duration-700">
                        <h1 class="text-4xl font-black uppercase text-slate-900 leading-none">
                            Marketing <span class="text-red-600">War Room</span>
                        </h1>
                        <p class="text-sm font-bold text-slate-400 uppercase tracking-[0.3em] mt-2 italic">
                            Duniatex Group Industrial Monitoring Hub
                        </p>
                    </div>
                @endif
            </div>

            {{-- SISI KANAN: STATUS LIVE & TANGGAL (Hanya muncul di Dashboard) --}}
            @if($currentMenu === 'dashboard')
                <div class="flex flex-row items-center gap-4 flex-shrink-0 ml-10 animate-in fade-in duration-700">
                    <div class="text-right border-r border-slate-200 pr-6 hidden md:block">
                        <p class="text-[10px] font-black text-slate-400 uppercase leading-none mb-1 tracking-widest">System Status</p>
                        <p class="text-sm font-bold text-green-500 flex items-center justify-end gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full animate-ping"></span> LIVE DATA
                        </p>
                    </div>
                    
                    <div class="bg-slate-900 text-white px-6 py-3 rounded-2xl font-black text-xs uppercase shadow-xl flex items-center gap-3 whitespace-nowrap">
                        <span class="opacity-50 text-base">📅</span> 
                        <span>{{ now()->format('d M Y') }}</span>
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
                        <h3 class="font-black uppercase text-slate-800 tracking-tighter flex items-center gap-2 italic text-2xl">
                            <span class="w-10 h-1 bg-red-600"></span> Live Operator Activity Stream
                        </h3>
                        <span class="text-[10px] font-black text-slate-400 italic">TOTAL: {{ count($stages) }} PRODUCTION UNITS</span>
                    </div>
                    
                    <div wire:poll.5s class="animate-in fade-in duration-500"> 
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @foreach($stages as $stage)
                                <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden min-h-[180px]">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Dept. {{ $stage['name'] }}</p>
                                            <h4 class="text-lg font-black text-slate-800 mt-1 italic uppercase">{{ $stage['unit'] }}</h4>
                                        </div>
                                        <span class="bg-{{ $stage['color'] ?? 'slate' }}-100 text-{{ $stage['color'] ?? 'slate' }}-600 text-[8px] px-3 py-1 rounded-full font-black">CONNECTED</span>
                                    </div>

                                    <div class="relative pt-1 mb-4">
                                        <div class="flex justify-between mb-2 uppercase italic font-black text-[10px]">
                                            <span class="text-slate-400">Workload</span>
                                            <span class="text-slate-900">{{ $stage['load'] ?? 0 }} SAP</span>
                                        </div>
                                        <div class="overflow-hidden h-2.5 flex rounded-full bg-slate-100 shadow-inner">
                                            <div style="width: {{ $stage['percentage'] ?? 0 }}%" 
                                                class="bg-{{ $stage['color'] ?? 'slate' }}-500 rounded-full shadow-lg transition-all duration-1000">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-{{ $stage['color'] ?? 'slate' }}-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-{{ $stage['color'] ?? 'slate' }}-500"></span>
                                        </span>
                                        <p class="text-[9px] font-bold text-slate-400 italic uppercase tracking-tighter">
                                            {{ $stage['desc'] ?? 'Monitoring Active' }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- SUMMARY STATS --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
                    <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100 group">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total SAP Registered</p>
                        <h3 class="text-4xl font-black text-slate-800 mt-1">{{ number_format($totalOrder) }}</h3>
                        <p class="text-[10px] font-bold text-blue-500 mt-2 italic underline uppercase">Database Archives</p>
                    </div>

                    <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100">
                        {{-- Ubah teks dari 'Pending' menjadi 'Knitting Queue' --}}
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Knitting Queue</p>
                        <h3 class="text-4xl font-black text-red-600 mt-1">{{ $knittingOrder }}</h3>
                        <div class="w-full bg-slate-100 h-2 rounded-full mt-4 overflow-hidden">
                            <div class="bg-red-600 h-full w-[100%] rounded-full animate-pulse"></div>
                        </div>
                        <p class="text-[9px] font-bold text-slate-400 mt-2 uppercase tracking-tighter text-right italic">Waiting for Weaving</p>
                    </div>

                    <div class="bg-slate-900 p-6 rounded-[2.5rem] shadow-xl text-white group">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Production Realization</p>
                        <h3 class="text-4xl font-black text-white mt-1">{{ $activeOrder + $completedOrder }} <span class="text-sm font-black text-red-500 italic">SAP</span></h3>
                        <p class="text-[10px] font-bold text-slate-400 mt-2 italic">Total SAP On/Post Production</p>
                    </div>

                    <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Finished & QC Pass</p>
                        <h3 class="text-4xl font-black text-green-600 mt-1">{{ $completedOrder }}</h3>
                        <button class="mt-3 text-[10px] font-black text-slate-400 underline uppercase tracking-widest hover:text-red-600">Export Completed List</button>
                    </div>
                </div>

                {{-- SALES PERFORMANCE & REPS --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
                    <div class="lg:col-span-2 bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm">
                        <div class="flex justify-between items-center mb-8">
                            <div>
                                <h3 class="font-black uppercase text-slate-800 tracking-tighter italic">Recent Production Flow</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Status 10 SAP Terakhir</p>
                            </div>
                            <div class="relative w-full md:w-72 group">
                                <span class="absolute left-4 top-3.5 text-slate-400 group-focus-within:text-red-600 transition-colors">🔍</span>
                                <input type="text" 
                                    wire:model.live="search" 
                                    placeholder="Cari No SAP / Pelanggan / Artikel..." 
                                    class="w-full pl-12 pr-4 py-3 bg-slate-50 border-none rounded-2xl text-[10px] font-black uppercase italic focus:ring-2 focus:ring-red-500/20 transition-all outline-none">
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-separate border-spacing-y-3" id="productionTable">
                                <thead>
                                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                        <th class="px-4 pb-2">SAP NO</th>
                                        <th class="px-4 pb-2">Customer / Article</th>
                                        <th class="px-4 pb-2">Warna</th>
                                        <th class="px-4 pb-2">Status Pipeline</th>
                                        <th class="px-4 pb-2 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentOrders as $order)
                                        <tr class="bg-slate-50/50 hover:bg-slate-100 transition-all group table-row-item">
                                            <td class="px-4 py-4 rounded-l-[1.5rem] font-black text-xs text-blue-600 italic sap-cell">
                                                #{{ $order->sap_no }}
                                            </td>
                                            <td class="px-4 py-4 customer-cell">
                                                <p class="text-[10px] font-black text-slate-800 uppercase">{{ $order->pelanggan }}</p>
                                                <p class="text-[9px] font-bold text-slate-400 uppercase italic">{{ $order->art_no }}</p>
                                            </td>
                                            <td class="px-4 py-4 text-[10px] font-black uppercase italic">{{ $order->warna }}</td>
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
                                                <button class="text-[10px] font-black text-red-600 uppercase hover:underline italic">Detail</button>
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
                                                    <p class="text-sm font-black text-slate-800 uppercase italic tracking-tighter">
                                                        Data Tidak Ditemukan
                                                    </p>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
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

                    <div class="bg-slate-900 p-8 rounded-[3rem] shadow-xl text-white">
                        <h3 class="font-black uppercase text-red-500 tracking-tighter mb-6 italic text-center underline decoration-red-500/30 decoration-4">Order Pipeline Stats</h3>
                        <div class="space-y-8">
                            <div class="flex justify-between items-center border-b border-white/5 pb-4">
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Waiting List</p>
                                    <h4 class="text-2xl font-black italic">Knitting</h4>
                                </div>
                                <p class="text-3xl font-black text-red-600 leading-none">{{ $knittingOrder }}</p>
                            </div>
                            <div class="flex justify-between items-center border-b border-white/5 pb-4">
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">On Progress</p>
                                    <h4 class="text-2xl font-black italic">Production</h4>
                                </div>
                                <p class="text-3xl font-black text-blue-500 leading-none">{{ $activeOrder }}</p>
                            </div>
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Released</p>
                                    <h4 class="text-2xl font-black italic">Finished</h4>
                                </div>
                                <p class="text-3xl font-black text-green-500 leading-none">{{ $completedOrder }}</p>
                            </div>
                        </div>
                        <a href="{{ route('marketing.dashboard', ['menu' => 'input']) }}" class="block text-center w-full mt-10 py-4 bg-red-600 hover:bg-black text-white rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-red-600/20">Create New Order SAP</a>
                    </div>
                </div>

                {{-- CRITICAL ALERTS & HUB --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm border-l-8 border-l-red-600">
                        <h3 class="font-black uppercase text-slate-800 tracking-tighter mb-6 flex items-center gap-3 italic">
                            Critical Orders Alert <span class="bg-red-100 text-red-600 text-[10px] px-3 py-1 rounded-full animate-pulse">{{ $knittingOrder }} URGENT</span>
                        </h3>
                        <div class="space-y-4">
                            @foreach($recentOrders->where('status', 'knitting')->take(2) as $urgent)
                            <div class="flex justify-between items-center bg-red-50/50 p-4 rounded-[1.5rem] border border-red-100">
                                <div>
                                    <p class="text-[10px] font-black text-red-600 uppercase italic leading-none">SAP #{{ $urgent->sap_no }}</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase">{{ $urgent->pelanggan }}</p>
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

                    <div class="bg-slate-50 p-8 rounded-[3rem] border border-slate-100">
                        <h3 class="font-black uppercase text-slate-800 tracking-tighter mb-6 italic">Marketing Command Hub</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <button class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 hover:border-red-600 transition-all text-left group">
                                <p class="text-[10px] font-black text-slate-400 group-hover:text-red-600 uppercase">Export Report</p>
                                <p class="text-sm font-black text-slate-800 mt-1 italic">Monthly Production</p>
                            </button>
                            <button class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 hover:border-red-600 transition-all text-left group">
                                <p class="text-[10px] font-black text-slate-400 group-hover:text-red-600 uppercase">Lead-Time Analysis</p>
                                <p class="text-sm font-black text-slate-800 mt-1 italic">Delivery Forecast</p>
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

</div>