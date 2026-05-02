<div x-data="{ openDetail: @entangle('showDetail'), selected: @entangle('selectedOrder') }" class="animate-in fade-in duration-500 italic tracking-tighter mkt-bg min-h-screen">

    <div class="max-w-[1600px] mx-auto">
        
        {{-- SECTION 1: COMPACT HEADER --}}
        {{-- Menghilangkan judul besar "Command Center" karena sudah ada di Navbar/Dashboard --}}
        <div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4 mkt-surface mkt-border p-5 rounded-[2.5rem] border shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-2 h-8 bg-red-600 rounded-full"></div>
                <h3 class="font-black uppercase mkt-text italic">Order Pipeline <span class="text-red-600">Control</span></h3>
            </div>

            <div class="flex flex-wrap gap-3 items-center">
                {{-- Date Filter --}}
                <div class="flex items-center bg-slate-50 border border-slate-700 rounded-2xl px-4 py-2 gap-2">
                    <input type="date" wire:model.live="startDate" class="text-[10px] font-black text-slate-600 uppercase border-none focus:ring-0 bg-transparent">
                    <span class="text-slate-600 font-black">TO</span>
                    <input type="date" wire:model.live="endDate" class="text-[10px] font-black text-slate-600 uppercase border-none focus:ring-0 bg-transparent">
                </div>

                {{-- Action Buttons --}}
                <button wire:click="exportExcel" wire:loading.attr="disabled" class="bg-slate-900 text-white px-6 py-3 rounded-xl font-black text-[10px] uppercase hover:bg-red-600 transition-all shadow-md flex items-center gap-2">
                    <span wire:loading.remove>📊 Export Excel</span>
                    <span wire:loading>⌛ Processing...</span>
                </button>
                
                {{-- Tombol New SAP - Perbaikan: hapus mt-10 dan w-full agar sejajar --}}
                <a href="{{ route('marketing.dashboard', ['menu' => 'input']) }}" 
                class="bg-red-600 hover:bg-black text-white px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg shadow-red-600/20">
                + Create New Order SAP
                </a>
            </div>
        </div>
        
        {{-- SECTION 2: ANALYTICS CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <p class="text-[10px] font-black text-slate-400 uppercase">Total Pesanan</p>
                
                {{-- PERBAIKAN: Tambahkan wire:key dinamis di sini --}}
                <h4 wire:key="counter-{{ $totalOrder }}" class="text-4xl font-black text-blue-600 italic">
                    {{ $totalOrder }}
                </h4>
            </div>
            <div class="bg-red-600 p-6 rounded-[2rem] shadow-lg shadow-red-200 border-b-4 border-black relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 text-white/10 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1 6h2v8h-2v-8zm1 12.25c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25z"/></svg>
                </div>
                <p class="text-[10px] font-black text-red-100 uppercase mb-1 tracking-widest">Stuck Orders (>2 Days)</p>
                <h4 class="text-3xl font-black text-white leading-none">
                    {{ \App\Models\MarketingOrder::where('status', 'knitting')->where('created_at', '<=', now()->subDays(2))->count() }}
                </h4>
            </div>
        </div>

        {{-- SECTION 3: FILTER HUB --}}
        <div class="bg-white p-4 rounded-[2rem] shadow-sm mb-6 flex flex-col md:flex-row gap-4 items-center border border-slate-100">
            <div class="flex-1 relative w-full">
                <span class="absolute left-4 top-3 text-slate-400 font-bold">🔍</span>
                <input wire:model.live="search" type="text" placeholder="Cari nomor SAP, Pelanggan, atau Nama Artikel..." 
                    class="w-full pl-10 pr-4 py-2 bg-slate-50 border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-red-500/20 transition-all outline-none">
            </div>

            {{-- Di bagian filter rentang --}}
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black text-slate-400">RENTANG:</span>
                <select wire:model.live="dateRange" 
                        wire:change="$refresh" 
                        class="bg-slate-900 text-white rounded-xl text-[15px] font-black text-slate-400">
                    <option value="semua">SEMUA WAKTU</option>
                    <option value="harian">HARIAN (HARI INI)</option>
                    <option value="mingguan">MINGGUAN</option>
                    <option value="bulanan">BULANAN</option>
                    <option value="tahunan">TAHUNAN</option>
                </select>
            </div>

            {{-- Ganti bagian select status dengan ini --}}
            <div class="flex items-center gap-2 w-full md:w-auto">
                <span class="text-[10px] font-black mkt-text-muted uppercase tracking-widest hidden md:block">Status:</span>
                <select wire:model.live="statusFilter" class="w-full md:w-40 py-2 px-4 mkt-input border-none rounded-xl text-xs font-black uppercase tracking-tighter text-slate-600 outline-none cursor-pointer hover:opacity-80 transition-colors italic">
                    <option value="">Semua Status</option>
                    <option value="knitting">Knitting</option>
                    <option value="dyeing">Dyeing</option>
                    <option value="relax-dryer">Relax Dryer</option>
                    <option value="finishing">Finishing</option>
                    <option value="stenter">Stenter</option>
                    <option value="tumbler">Tumbler</option>
                    <option value="fleece">Fleece</option>
                    <option value="pengujian">Pengujian</option>
                    <option value="qe">QE Approval</option>
                    <option value="finished">Selesai (Finished)</option>
                </select>
            </div>
        </div>

        {{-- SECTION 4: TABLE --}}
        <div class="mkt-surface rounded-[2.5rem] shadow-sm overflow-hidden mkt-border border">
            <div class="p-8 border-b mkt-border mkt-surface">
                <h3 class="font-black uppercase mkt-text tracking-tighter italic text-lg">Master Data <span class="text-red-600">Marketing Order</span></h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="mkt-surface-alt">
                        <tr>
                            <th class="p-6 text-[10px] font-black uppercase mkt-text-muted tracking-widest">SAP NO</th>
                            <th class="p-6 text-[10px] font-black uppercase mkt-text-muted tracking-widest">Tanggal Order</th>
                            <th class="p-6 text-[10px] font-black uppercase mkt-text-muted tracking-widest">Pelanggan</th>
                            <th class="p-6 text-[10px] font-black uppercase mkt-text-muted tracking-widest">Artikel No</th>
                            <th class="p-6 text-[10px] font-black uppercase mkt-text-muted tracking-widest">Sales (MKT)</th>
                            <th class="p-6 text-[10px] font-black uppercase mkt-text-muted tracking-widest">Keperluan</th>
                            <th class="p-6 text-[10px] font-black uppercase mkt-text-muted tracking-widest text-center">Status</th>
                            <th class="p-6 text-[10px] font-black uppercase mkt-text-muted tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y mkt-border uppercase text-sm font-bold mkt-text">
                        @forelse($orders as $order)
                            <tr class="hover:bg-slate-50 transition group relative {{ $order->status == 'knitting' && $order->created_at->diffInDays(now()) >= 2 ? 'bg-red-50/50' : '' }}">
                                
                                <td class="p-6 relative">
                                    <div class="flex flex-col">
                                        <span class="italic text-blue-600 tracking-tighter underline underline-offset-4 font-black">
                                            {{ $order->sap_no }}
                                        </span>
                                        
                                        @if($order->status == 'knitting' && $order->created_at->diffInDays(now()) >= 2)
                                            <span class="absolute -left-1 top-1/2 -translate-y-1/2 w-1.5 h-12 bg-red-600 rounded-full animate-pulse"></span>
                                            <span class="text-[8px] font-black text-red-600 uppercase mt-1 flex items-center gap-1 animate-bounce">
                                                ⚠️ Butuh Follow Up ({{ $order->created_at->diffInDays(now()) }} Hari)
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
                                <td class="p-6 mkt-text-muted uppercase">{{ $order->art_no }}</td>
                                <td class="p-6 mkt-text-muted">{{ $order->mkt }}</td>
                                <td class="p-6 italic text-[10px] mkt-text-muted">{{ $order->keperluan }}</td>

                                {{-- Logika baru: Tidak ada lagi label khusus 'knitting' --}}
                                <td class="p-6 text-center whitespace-nowrap">
                                    @php
                                        // Mapping warna untuk setiap divisi
                                        $statusMap = [
                                            'knitting'    => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'icon' => '🧶'],
                                            'dyeing'      => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600', 'icon' => '🧪'],
                                            'relax-dryer' => ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-600', 'icon' => '💨'],
                                            'finishing'   => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-600', 'icon' => '✨'],
                                            'stenter'     => ['bg' => 'bg-violet-100', 'text' => 'text-violet-600', 'icon' => '📏'],
                                            'tumbler'     => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600', 'icon' => '🌀'],
                                            'fleece'      => ['bg' => 'bg-rose-100', 'text' => 'text-rose-600', 'icon' => '🧥'],
                                            'pengujian'   => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'icon' => '🔬'],
                                            'qe'          => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'icon' => '✅'],
                                            'finished'    => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'icon' => '🏁'],
                                        ];
                                        
                                        $current = $statusMap[$order->status] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-500', 'icon' => '●'];
                                    @endphp

                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-[9px] font-black tracking-widest {{ $current['bg'] }} {{ $current['text'] }} border border-current/20 uppercase italic">
                                        {{ $current['icon'] }} {{ strtoupper($order->status) }}
                                    </span>
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
                <div class="p-6 mkt-surface-alt border-t mkt-border">
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
                                Internal Tracking ID: <span x-text="selected?.sap_no" class="text-white font-black"></span>
                            </p>
                            
                            <div class="mt-4 flex flex-col border-l-2 border-red-500 pl-3">
                                <span class="text-red-500 text-sm font-black italic uppercase">
                                    Order Date: <span x-text="selected?.tanggal ? new Date(selected.tanggal).toLocaleDateString('id-ID') : '-'"></span>
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
      
                            <a :href="'/marketing/orders/' + (selected ? selected.id : '') + '/edit'" 
                                class="no-print px-6 py-2.5 bg-white text-slate-900 rounded-xl text-[10px] font-black hover:bg-red-600 hover:text-white transition shadow-sm uppercase italic">
                                📝 Edit Data
                            </a>
                            <button @click="if(confirm('Yakin hapus data SAP ' + (selected?.sap_no ?? '') + '?')) { $wire.deleteOrder(selected.id); }" 
                                class="no-print px-6 py-2.5 bg-red-600/10 text-red-500 border border-red-500/50 rounded-xl text-[10px] font-black hover:bg-red-600 hover:text-white transition uppercase italic">
                                🗑️ Delete
                            </button>
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
                                <span class="w-2 h-4 bg-red-600 mr-2 rounded-full"></span>III. Real-Time Production Milestone
                            </h3>
                            
                            <div class="relative">
                                {{-- Garis Background Abu-abu --}}
                                <div class="absolute left-4 top-0 h-full w-0.5 bg-slate-100"></div>

                                {{-- Garis Progress Merah Dinamis --}}
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
                                    {{-- 01: MARKETING --}}
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
                                            ['id' => '02', 'status' => 'knitting', 'label' => 'Knitting Process', 'desc' => 'RAJUT UNIT DDT 2', 'after' => ['dyeing', 'relax-dryer', 'finishing', 'stenter', 'tumbler', 'fleece', 'pengujian', 'qe', 'finished']],
                                            ['id' => '03', 'status' => 'dyeing', 'label' => 'SCR / Dyeing', 'desc' => 'PROSES WARNA & PENCUCIAN', 'after' => ['relax-dryer', 'finishing', 'stenter', 'tumbler', 'fleece', 'pengujian', 'qe', 'finished']],
                                            ['id' => '04', 'status' => 'relax-dryer', 'label' => 'Relax Dryer', 'desc' => 'PENGERINGAN TANPA TEGANGAN', 'after' => ['finishing', 'stenter', 'tumbler', 'fleece', 'pengujian', 'qe', 'finished']],
                                            ['id' => '05', 'status' => 'finishing', 'label' => 'Chemical Finishing', 'desc' => 'PELEMBUT & OBAT FINISH', 'after' => ['stenter', 'tumbler', 'fleece', 'pengujian', 'qe', 'finished']],
                                            ['id' => '06', 'status' => 'stenter', 'label' => 'Stenter Process', 'desc' => 'SETTING LEBAR & GRAMASI', 'after' => ['tumbler', 'fleece', 'pengujian', 'qe', 'finished']],
                                            ['id' => '07', 'status' => 'tumbler', 'label' => 'Tumbler Dry', 'desc' => 'PROSES BULKING KAIN', 'after' => ['fleece', 'pengujian', 'qe', 'finished']],
                                            ['id' => '08', 'status' => 'fleece', 'label' => 'Fleece / Brushing', 'desc' => 'GARUK BULU (UNIT FLEECE)', 'after' => ['pengujian', 'qe', 'finished']],
                                            ['id' => '09', 'status' => 'pengujian', 'label' => 'QC & Lab Testing', 'desc' => 'PENGUJIAN FISIK KAIN', 'after' => ['qe', 'finished']],
                                            ['id' => '10', 'status' => 'qe', 'label' => 'QE Approval', 'desc' => 'FINAL INSPECTION & RELEASE', 'after' => ['finished']],
                                        ];
                                    @endphp

                                    @foreach($milestones as $m)
                                        @php 
                                            $log = $activitiesLogs[$m['status']][0] ?? null; 
                                            $isDone = in_array($order->status, $m['after']); // Fallback logic
                                        @endphp
                                        <div class="flex items-start gap-6">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center z-10 transition-all duration-500"
                                                :class="[ @foreach($m['after'] as $a)'{{$a}}',@endforeach ].includes(selected?.status) ? 'bg-red-600 text-white' : 'bg-slate-100 text-slate-400'">
                                                <span class="text-[10px] font-black" x-text="[ @foreach($m['after'] as $a)'{{$a}}',@endforeach ].includes(selected?.status) ? '✓' : '{{ $m['id'] }}'"></span>
                                            </div>

                                            <div class="flex-1">
                                                <div class="flex justify-between items-start">
                                                    <p class="text-xs font-black uppercase italic" :class="selected?.status === '{{ $m['status'] }}' ? 'text-red-600 animate-pulse' : ''">
                                                        {{ $m['label'] }}
                                                    </p>
                                                    @if($log)
                                                        <span class="text-[8px] font-black text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-full">
                                                            DONE: {{ \Carbon\Carbon::parse($log['created_at'])->format('d/m H:i') }}
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                <p class="text-[9px] font-bold text-slate-400 uppercase">{{ $m['desc'] }}</p>

                                                @if($log)
                                                    <div class="mt-1 flex items-center gap-2">
                                                        <span class="text-[8px] text-slate-500 italic">Operator:</span>
                                                        <span class="text-[8px] font-black text-slate-700 uppercase">{{ $log['operator']['name'] ?? 'System' }}</span>
                                                        @if(isset($log['kg']) && $log['kg'] > 0)
                                                            <span class="text-[8px] text-slate-300">|</span>
                                                            <span class="text-[8px] font-bold text-blue-600">{{ $log['kg'] }} KG</span>
                                                        @endif
                                                    </div>
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