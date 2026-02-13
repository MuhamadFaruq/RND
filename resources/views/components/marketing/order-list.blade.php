<div x-data="{ openDetail: false, selected: {} }" class="min-h-screen bg-slate-50 py-8 px-4 italic tracking-tighter">
    <script src="https://cdn.tailwindcss.com"></script>

    <div class="max-w-7xl mx-auto">
        
        <div class="mb-8 flex justify-between items-end">
            <div class="flex items-center">
                <a href="/dashboard" class="mr-6 group flex items-center justify-center w-12 h-12 bg-white rounded-2xl border border-slate-200 shadow-sm hover:bg-slate-50 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 group-hover:text-red-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h2 class="text-3xl font-black uppercase tracking-tighter text-slate-800 leading-none">
                        Marketing <span class="text-red-600">Command Center</span>
                    </h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Industrial Production Monitoring System</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3 items-center">
                <div class="flex items-center bg-white border border-slate-200 rounded-2xl px-4 py-2 gap-2 shadow-sm">
                    <input type="date" wire:model.live="startDate" class="text-[10px] font-black uppercase border-none focus:ring-0 bg-transparent">
                    <span class="text-slate-300">TO</span>
                    <input type="date" wire:model.live="endDate" class="text-[10px] font-black uppercase border-none focus:ring-0 bg-transparent">
                </div>

                <button wire:click="exportExcel" wire:loading.attr="disabled" class="bg-white border border-slate-200 text-slate-600 px-6 py-4 rounded-2xl font-black text-xs uppercase hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2">
                    <span wire:loading.remove>📊 Export Full Excel</span>
                    <span wire:loading>⌛ Generating...</span>
                </button>
                
                <a href="{{ route('marketing.orders.create') }}" class="bg-red-600 text-white px-8 py-4 rounded-2xl font-black text-xs uppercase shadow-lg shadow-red-200 hover:bg-black transition-all hover:-translate-y-1">
                    + Buat Order Baru
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-[2rem] border-b-4 border-blue-500 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase mb-1 tracking-widest">Total Pesanan</p>
                <h4 class="text-3xl font-black text-slate-800 leading-none">{{ $totalOrder }}</h4>
            </div>
            <div class="bg-white p-6 rounded-[2rem] border-b-4 border-amber-500 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase mb-1 tracking-widest">Menunggu (Pending)</p>
                <h4 class="text-3xl font-black text-slate-800 leading-none">{{ $pendingOrder }}</h4>
            </div>
            <div class="bg-white p-6 rounded-[2rem] border-b-4 border-indigo-500 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase mb-1 tracking-widest">Produksi</p>
                <h4 class="text-3xl font-black text-slate-800 leading-none">{{ $activeOrder }}</h4>
            </div>
            <div class="bg-white p-6 rounded-[2rem] border-b-4 border-green-500 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase mb-1 tracking-widest">Selesai</p>
                <h4 class="text-3xl font-black text-slate-800 leading-none">{{ $completedOrder }}</h4>
            </div>
            <div class="bg-red-600 p-6 rounded-[2rem] shadow-lg shadow-red-200 border-b-4 border-black relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 text-white/10 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1 6h2v8h-2v-8zm1 12.25c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25z"/></svg>
                </div>
                <p class="text-[10px] font-black text-red-100 uppercase mb-1 tracking-widest">Stuck Orders (>2 Days)</p>
                <h4 class="text-3xl font-black text-white leading-none">
                    {{ \App\Models\MarketingOrder::where('status', 'pending')->where('created_at', '<=', now()->subDays(2))->count() }}
                </h4>
            </div>
        </div>

        <div class="bg-white p-4 rounded-[2rem] shadow-sm mb-6 flex flex-col md:flex-row gap-4 items-center border border-slate-100">
    <div class="flex-1 relative w-full">
        <span class="absolute left-4 top-3 text-slate-400 font-bold">🔍</span>
        <input wire:model.live="search" type="text" placeholder="Cari nomor SAP, Pelanggan, atau Nama Artikel..." 
            class="w-full pl-10 pr-4 py-2 bg-slate-50 border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-red-500/20 transition-all outline-none">
    </div>

    <div class="flex items-center gap-2 w-full md:w-auto">
        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest hidden md:block">Rentang:</span>
        <select wire:model.live="timeFilter" class="w-full md:w-40 py-2 px-4 bg-slate-900 text-white border-none rounded-xl text-xs font-black uppercase tracking-tighter outline-none cursor-pointer hover:bg-red-600 transition-colors italic">
            <option value="">Semua Waktu</option>
            <option value="today">Harian (Hari Ini)</option>
            <option value="weekly">Mingguan</option>
            <option value="monthly">Bulanan</option>
            <option value="yearly">Tahunan</option>
        </select>
    </div>

    <div class="flex items-center gap-2 w-full md:w-auto">
        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest hidden md:block">Status:</span>
        <select wire:model.live="statusFilter" class="w-full md:w-40 py-2 px-4 bg-slate-50 border-none rounded-xl text-xs font-black uppercase tracking-tighter text-slate-600 outline-none cursor-pointer hover:bg-slate-100 transition-colors italic">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="in-progress">In Progress</option>
            <option value="completed">Completed</option>
        </select>
    </div>
</div>

        <div class="bg-white rounded-[2.5rem] shadow-sm overflow-hidden border border-slate-100">
            <div class="p-8 border-b border-slate-50 bg-white">
                <h3 class="font-black uppercase text-slate-800 tracking-tighter italic text-lg">Master Data <span class="text-red-600">Marketing Order</span></h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="p-6 text-[10px] font-black uppercase text-slate-400 tracking-widest">SAP NO</th>
                            <th class="p-6 text-[10px] font-black uppercase text-slate-400 tracking-widest">Tanggal Order</th>
                            <th class="p-6 text-[10px] font-black uppercase text-slate-400 tracking-widest">Pelanggan</th>
                            <th class="p-6 text-[10px] font-black uppercase text-slate-400 tracking-widest">Artikel No</th>
                            <th class="p-6 text-[10px] font-black uppercase text-slate-400 tracking-widest">Sales (MKT)</th>
                            <th class="p-6 text-[10px] font-black uppercase text-slate-400 tracking-widest">Keperluan</th>
                            <th class="p-6 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center">Status</th>
                            <th class="p-6 text-[10px] font-black uppercase text-slate-400 tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 uppercase text-sm font-bold text-slate-700">
                        @forelse($orders as $order)
                            <tr class="hover:bg-slate-50 transition group relative {{ $order->status == 'pending' && $order->created_at->diffInDays(now()) >= 2 ? 'bg-red-50/50' : '' }}">
                                
                                <td class="p-6 relative">
                                    <div class="flex flex-col">
                                        <span class="italic text-blue-600 tracking-tighter underline underline-offset-4 font-black">
                                            {{ $order->sap_no }}
                                        </span>
                                        
                                        @if($order->status == 'pending' && $order->created_at->diffInDays(now()) >= 2)
                                            <span class="absolute -left-1 top-1/2 -translate-y-1/2 w-1.5 h-12 bg-red-600 rounded-full animate-pulse"></span>
                                            <span class="text-[8px] font-black text-red-600 uppercase mt-1 flex items-center gap-1 animate-bounce">
                                                ⚠️ Butuh Follow Up ({{ $order->created_at->diffInDays(now()) }} Hari)
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                
                                <td class="p-6">
                                    <div class="flex flex-col">
                                        <span class="text-slate-800">{{ \Carbon\Carbon::parse($order->tanggal)->format('d/m/Y') }}</span>
                                        <span class="text-[9px] {{ $order->created_at->diffInDays(now()) >= 2 && $order->status == 'pending' ? 'text-red-500 font-black' : 'text-slate-400' }} italic font-medium lowercase leading-none mt-1">
                                            Input: {{ $order->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </td>

                                <td class="p-6 tracking-tight">{{ $order->pelanggan }}</td>
                                <td class="p-6 text-slate-500 uppercase">{{ $order->art_no }}</td>
                                <td class="p-6 text-slate-500">{{ $order->mkt }}</td>
                                <td class="p-6 italic text-[10px] text-slate-400">{{ $order->keperluan }}</td>

                                <td class="p-6 text-center">
                                    @php
                                        $statusClasses = [
                                            'pending' => 'bg-amber-100 text-amber-600 border border-amber-200',
                                            'in-progress' => 'bg-blue-100 text-blue-600 border border-blue-200',
                                            'completed' => 'bg-green-100 text-green-600 border border-green-200',
                                        ];
                                    @endphp
                                    <span class="px-4 py-1.5 rounded-full text-[9px] font-black tracking-widest uppercase {{ $statusClasses[$order->status] ?? 'bg-slate-100' }}">
                                        ● {{ $order->status }}
                                    </span>
                                </td>

                                <td class="p-6 text-right">
                                    <button @click="selected = {{ json_encode($order) }}; openDetail = true" 
                                        class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-[10px] font-black hover:bg-red-600 transition shadow-md group-hover:scale-105 transform">
                                        DETAIL
                                    </button>
                                </td>
                            </tr>
                            @empty
                        @endforelse
                    </tbody>
                </table>
                <div class="p-6 bg-slate-50/50 border-t border-slate-50">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>

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
             @click="openDetail = false"></div>

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
                                Internal Tracking ID: <span x-text="selected.sap_no" class="text-white font-black"></span>
                            </p>
                            
                            <div class="mt-4 flex flex-col border-l-2 border-red-500 pl-3">
                                <span class="text-red-500 text-sm font-black italic uppercase">
                                    Order Date: <span x-text="selected.tanggal ? new Date(selected.tanggal).toLocaleDateString('id-ID') : '-'"></span>
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3 mr-8 no-print">
                            <button @click="window.print()" 
                                class="no-print px-6 py-2.5 bg-amber-500 text-white rounded-xl text-[10px] font-black hover:bg-amber-600 transition shadow-sm uppercase italic flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print Label
                            </button>
      
                            <a :href="'/marketing/orders/' + selected.id + '/edit'" 
                                class="no-print px-6 py-2.5 bg-white text-slate-900 rounded-xl text-[10px] font-black hover:bg-red-600 hover:text-white transition shadow-sm uppercase italic">
                                📝 Edit Data
                            </a>
                            <button @click="if(confirm('Yakin hapus data SAP ' + selected.sap_no + '?')) { $wire.deleteOrder(selected.id); openDetail = false; }" 
                                class="no-print px-6 py-2.5 bg-red-600/10 text-red-500 border border-red-500/50 rounded-xl text-[10px] font-black hover:bg-red-600 hover:text-white transition uppercase italic">
                                🗑️ Delete
                            </button>
                        </div>

                        <button @click="openDetail = false" class="bg-white/10 hover:bg-red-600 p-3 rounded-2xl transition group">
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
                                        <p class="text-slate-800 uppercase" x-text="selected.pelanggan"></p>
                                    </div>
                                    <div class="flex justify-between border-b border-slate-50 pb-1 font-bold">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">Artikel No</p>
                                        <p class="text-slate-800 uppercase" x-text="selected.art_no"></p>
                                    </div>
                                    <div class="flex justify-between border-b border-slate-50 pb-1 font-bold">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">MKT Representative</p>
                                        <p class="text-slate-800 italic" x-text="selected.mkt"></p>
                                    </div>
                                    <div class="flex justify-between font-bold">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">Keperluan</p>
                                        <p class="text-slate-800" x-text="selected.keperluan"></p>
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
                                        <p class="text-slate-800 uppercase" x-text="selected.material"></p>
                                    </div>
                                    <div class="flex justify-between border-b border-slate-50 pb-1">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">Benang</p>
                                        <p class="text-slate-800 uppercase" x-text="selected.benang"></p>
                                    </div>
                                    <div class="flex justify-between border-b border-slate-50 pb-1">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">Konstruksi Greige</p>
                                        <p class="text-slate-800" x-text="selected.konstruksi_greig"></p>
                                    </div>
                                    <div class="flex justify-between">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest text-red-600">Finishing Warna</p>
                                        <p class="text-red-600 uppercase italic" x-text="selected.warna"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-slate-900 p-8 rounded-[3rem] text-white shadow-xl shadow-slate-200">
                            <h3 class="text-red-500 font-black mb-6 uppercase italic tracking-tighter text-center underline underline-offset-8">Production Specification Matrix</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center font-bold">
                                <div class="border-r border-white/10">
                                    <p class="text-[9px] text-slate-400 uppercase mb-1">Kelompok Kain</p>
                                    <p class="text-lg" x-text="selected.kelompok_kain"></p>
                                </div>
                                <div class="border-r border-white/10">
                                    <p class="text-[9px] text-slate-400 uppercase mb-1">Lebar / Gramasi</p>
                                    <p class="text-lg"><span x-text="selected.target_lebar"></span>" / <span x-text="selected.target_gramasi"></span></p>
                                </div>
                                <div class="border-r border-white/10">
                                    <p class="text-[9px] text-slate-400 uppercase mb-1">Belah / Bulat</p>
                                    <p class="text-lg uppercase" x-text="selected.belah_bulat"></p>
                                </div>
                                <div>
                                    <p class="text-[9px] text-slate-400 uppercase mb-1">Handfeel</p>
                                    <p class="text-lg uppercase" x-text="selected.handfeel"></p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 font-black italic">
                            <div class="bg-blue-600 text-white p-8 rounded-[2.5rem] shadow-lg shadow-blue-100 flex justify-between items-center">
                                <p class="text-xs uppercase tracking-[0.2em]">Total Roll Target</p>
                                <h4 class="text-4xl underline decoration-4 underline-offset-8" x-text="selected.roll_target"></h4>
                            </div>
                            <div class="bg-emerald-600 text-white p-8 rounded-[2.5rem] shadow-lg shadow-emerald-100 flex justify-between items-center">
                                <p class="text-xs uppercase tracking-[0.2em]">Total Net Weight (KG)</p>
                                <h4 class="text-4xl underline decoration-4 underline-offset-8" x-text="selected.kg_target"></h4>
                            </div>
                        </div>

                        <div class="bg-white p-8 rounded-[3rem] border-l-[12px] border-red-600 shadow-sm">
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-2 italic tracking-widest">Special Treatment & Instructions:</p>
                            <p class="text-lg font-black text-slate-800 uppercase mb-4 underline decoration-red-600/30 underline-offset-4" x-text="selected.treatment_khusus || '-'"></p>
                            <hr class="border-slate-100 my-4">
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-2 italic tracking-widest">Internal Marketing Notes:</p>
                            <p class="text-xs font-bold text-slate-600 leading-relaxed italic bg-slate-50 p-4 rounded-2xl" x-text="selected.keterangan_artikel || 'No additional internal notes provided.'"></p>
                        </div>
                        <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm overflow-hidden">
                            <h3 class="text-slate-900 font-black mb-8 uppercase italic tracking-tighter text-sm flex items-center">
                                <span class="w-2 h-4 bg-red-600 mr-2 rounded-full"></span>III. Real-Time Production Milestone
                            </h3>
                            
                            <div class="relative">
                                <div class="absolute left-4 top-0 h-full w-0.5 bg-slate-100"></div>

                                <div class="absolute left-4 top-0 w-0.5 bg-red-600 transition-all duration-1000"
                                    :style="selected.status === 'pending' ? 'height: 20%' : (selected.status === 'in-progress' ? 'height: 60%' : 'height: 100%')">
                                </div>

                                <div class="space-y-8 relative">
                                    <div class="flex items-start gap-6">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center z-10 transition-colors"
                                            :class="selected.status ? 'bg-red-600 text-white shadow-lg shadow-red-200' : 'bg-slate-200 text-slate-400'">
                                            <span class="text-[10px] font-black">01</span>
                                        </div>
                                        <div>
                                            <p class="text-xs font-black uppercase italic" :class="selected.status ? 'text-slate-800' : 'text-slate-400'">Order Entry & Verification</p>
                                            <p class="text-[9px] font-bold text-slate-400">ADMIN MARKETING / SAP SYSTEM</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-6">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center z-10 transition-colors"
                                            :class="selected.status === 'in-progress' || selected.status === 'completed' ? 'bg-red-600 text-white shadow-lg shadow-red-200' : 'bg-slate-200 text-slate-400'">
                                            <span class="text-[10px] font-black">02</span>
                                        </div>
                                        <div>
                                            <p class="text-xs font-black uppercase italic" :class="selected.status === 'in-progress' || selected.status === 'completed' ? 'text-slate-800' : 'text-slate-400'">PPC Planning & Knitting</p>
                                            <p class="text-[9px] font-bold text-slate-400">PLANNING DEPARTMENT / WEAVING UNIT</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-6">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center z-10 transition-colors"
                                            :class="selected.status === 'in-progress' || selected.status === 'completed' ? 'bg-red-600 text-white shadow-lg shadow-red-200' : 'bg-slate-200 text-slate-400'">
                                            <span class="text-[10px] font-black">03</span>
                                        </div>
                                        <div>
                                            <p class="text-xs font-black uppercase italic" :class="selected.status === 'in-progress' || selected.status === 'completed' ? 'text-slate-800' : 'text-slate-400'">Dyeing & Chemical Finishing</p>
                                            <p class="text-[9px] font-bold text-slate-400">PRODUCTION UNIT / DYEING UNIT</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-6">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center z-10 transition-colors"
                                            :class="selected.status === 'completed' ? 'bg-red-600 text-white shadow-lg shadow-red-200' : 'bg-slate-200 text-slate-400'">
                                            <span class="text-[10px] font-black">04</span>
                                        </div>
                                        <div>
                                            <p class="text-xs font-black uppercase italic" :class="selected.status === 'completed' ? 'text-slate-800' : 'text-slate-400'">Final Inspection & Packing</p>
                                            <p class="text-[9px] font-bold text-slate-400">QC LAB / WAREHOUSE FINISHED GOODS</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #ef4444; }
        .font-inter { font-family: 'Inter', sans-serif; }

        @media print {
            /* 1. Reset Total & Scaling agar muat 1 lembar */
            @page {
                size: A4;
                margin: 10mm; /* Margin kertas */
            }

            html, body {
                height: auto !important;
                overflow: visible !important;
                background: #fff !important;
                font-size: 10pt; /* Memperkecil font standar saat print */
            }

            body * {
                visibility: hidden !important;
            }

            /* 2. Menghilangkan Milestone & Navigasi */
            .no-print, 
            button, 
            .no-print-bg,
            [x-show="openDetail"] .bg-white.p-8.rounded-\[3rem\].border.border-slate-100.shadow-sm.overflow-hidden:last-child { 
                display: none !important; 
            }

            /* 3. Tampilkan & Reset Container Print */
            #print-area, #print-area * {
                visibility: visible !important;
            }

            #print-area {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                display: block !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                background: white !important;
                transform: none !important;
            }

            /* 4. Optimalisasi Spasi agar Responsif & Cukup 1 Lembar */
            .p-8 { padding: 1rem !important; } /* Memperkecil padding konten */
            .space-y-8 > :not([hidden]) ~ :not([hidden]) { margin-top: 1rem !important; }
            .mb-8 { margin-bottom: 0.5rem !important; }
            .pb-32 { padding-bottom: 0 !important; }

            /* 5. Grid Responsif (Tetap 2 Kolom di Kertas) */
            .grid {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 10px !important;
            }

            /* Khusus kartu yang harus full width */
            .bg-slate-900, .grid-cols-1 {
                grid-column: span 2 / span 2 !important;
            }

            /* 6. Fix Warna & Background */
            .bg-slate-900 { background-color: #111827 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .bg-red-600 { background-color: #dc2626 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .bg-blue-600 { background-color: #2563eb !important; -webkit-print-color-adjust: exact; }
            .bg-emerald-600 { background-color: #059669 !important; -webkit-print-color-adjust: exact; }
            .text-red-500 { color: #dc2626 !important; -webkit-print-color-adjust: exact; }
            .bg-slate-50\/30 { background-color: transparent !important; }

            /* 7. Matikan semua dekorasi lengkung berlebih */
            .rounded-\[2rem\], .rounded-\[3rem\], .rounded-l-\[3rem\] {
                border-radius: 8px !important;
                border: 1px solid #e2e8f0 !important;
            }

            /* 8. Sembunyikan Scrollbar */
            .overflow-y-auto {
                overflow: visible !important;
                height: auto !important;
            }
        }
    </style>
</div>