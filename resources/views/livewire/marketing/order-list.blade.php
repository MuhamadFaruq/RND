<div x-data="{ 
    openDetail: @entangle('showDetail'), 
    selected: @entangle('selectedOrder'),
    showLogDetail: false,
    selectedLog: null
}" class="animate-in fade-in duration-500 italic tracking-tighter mkt-bg min-h-screen">

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
                <div class="flex items-center mkt-surface-alt border mkt-border rounded-2xl px-4 py-2 gap-2">
                    <input type="date" wire:model.live="startDate" class="text-[10px] font-black mkt-text-muted uppercase border-none focus:ring-0 bg-transparent">
                    <span class="mkt-text-muted font-black text-[10px]">TO</span>
                    <input type="date" wire:model.live="endDate" class="text-[10px] font-black mkt-text-muted uppercase border-none focus:ring-0 bg-transparent">
                </div>

                {{-- Action Buttons --}}
                <button wire:click="exportExcel" wire:loading.attr="disabled" class="bg-slate-900 text-white px-6 py-3 rounded-xl font-black text-[10px] uppercase hover:bg-red-600 transition-all shadow-md flex items-center gap-2">
                    <span wire:loading.remove>📊 Export Excel</span>
                    <span wire:loading>⌛ Processing...</span>
                </button>
                
                {{-- Tombol New Order - Perbaikan: Fokus ke No Artikel --}}
                <a href="{{ route('marketing.dashboard', ['menu' => 'input']) }}" 
                class="bg-red-600 hover:bg-black text-white px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg shadow-red-600/20">
                + Create New No Artikel
                </a>
            </div>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="mkt-surface p-6 rounded-2xl shadow-sm border mkt-border">
                <p class="text-[10px] font-black mkt-text-muted uppercase">Total Pesanan</p>
                <h4 wire:key="counter-{{ $totalOrder }}" class="text-4xl font-black text-blue-600 italic">
                    {{ $totalOrder }}
                </h4>
            </div>
            <div class="bg-red-600 p-6 rounded-[2rem] shadow-xl shadow-red-600/20 border-b-4 border-black relative overflow-hidden group">
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
        <div class="mkt-surface p-4 rounded-[2rem] shadow-sm mb-6 flex flex-col md:flex-row gap-4 items-center border mkt-border">
            <div class="flex-1 relative w-full">
                <span class="absolute left-4 top-3 mkt-text-muted font-bold">🔍</span>
                <input wire:model.live="search" type="text" placeholder="CARI NOMOR ARTIKEL ATAU PELANGGAN..." 
                    class="w-full pl-10 pr-4 py-2 mkt-surface-alt border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-red-500/20 transition-all outline-none mkt-text">
            </div>

            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black mkt-text-muted">RENTANG:</span>
                <select wire:model.live="dateRange" 
                        wire:change="$refresh" 
                        class="mkt-surface border mkt-border mkt-text rounded-xl text-[12px] font-black">
                    <option value="semua">SEMUA WAKTU</option>
                    <option value="harian">HARIAN (HARI INI)</option>
                    <option value="mingguan">MINGGUAN</option>
                    <option value="bulanan">BULANAN</option>
                    <option value="tahunan">TAHUNAN</option>
                </select>
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <span class="text-[10px] font-black mkt-text-muted uppercase tracking-widest hidden md:block">Status:</span>
                <select wire:model.live="statusFilter" class="w-full md:w-40 py-2 px-4 mkt-input mkt-border border rounded-xl text-xs font-black uppercase tracking-tighter outline-none cursor-pointer hover:opacity-80 transition-colors italic">
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

        {{-- SECTION 4: TABLE --}}
        <div class="mkt-surface rounded-[2.5rem] shadow-sm overflow-hidden mkt-border border">
            <div class="p-8 border-b mkt-border mkt-surface">
                <h3 class="font-black uppercase mkt-text tracking-tighter italic text-lg">Master Data <span class="text-red-600">Marketing Order</span></h3>
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
                                        <span class="italic text-blue-600 tracking-tighter underline underline-offset-4 font-black">
                                            {{ $order->art_no }}
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
                                <td class="p-6 mkt-text-muted uppercase italic text-[11px]">{{ $order->sap_no }}</td>
                                <td class="p-6 mkt-text-muted">{{ $order->mkt }}</td>
                                <td class="p-6 italic text-[10px] mkt-text-muted">{{ $order->keperluan }}</td>

                                <td class="p-6 text-center whitespace-nowrap">
                                    @php
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

        <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
            <div x-show="openDetail" 
                 class="w-screen max-w-3xl text-left">
                
                <div id="print-area" class="h-full flex flex-col mkt-surface shadow-2xl rounded-l-[3rem] overflow-hidden">
                    <div class="p-8 bg-slate-900 text-white flex justify-between items-center rounded-tl-[3rem] border-b border-white/10 no-print-bg">
                        <div class="flex-1">
                            <h2 class="text-2xl font-black italic uppercase tracking-tighter leading-none text-white">
                                Industrial Order <span class="text-red-500 text-3xl italic">Detail</span>
                            </h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic">
                                Internal Tracking ID: <span x-text="selected ? selected.art_no : ''" class="text-white font-black"></span>
                            </p>
                        </div>
                        
                        <button @click="showLogDetail = true" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl text-xs font-black uppercase transition-all flex items-center gap-2 shadow-lg shadow-indigo-900/50 mr-4 no-print">
                            <span>Lihat Hasil</span>
                            <span>👁️</span>
                        </button>
                        
                        <button wire:click="closeDetail" class="bg-white/10 hover:bg-red-600 p-3 rounded-2xl transition group">
                            <svg class="no-print h-6 w-6 text-slate-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-8 space-y-8 pb-32 bg-slate-50/30">
                        {{-- Identitas --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="mkt-surface p-6 rounded-[2rem] border mkt-border shadow-sm">
                                <h3 class="text-red-600 font-black mb-4 border-b pb-2 uppercase italic tracking-tighter text-sm flex items-center">
                                    <span class="w-2 h-4 bg-red-600 mr-2 rounded-full"></span>I. Identity & Sales
                                </h3>
                                <div class="space-y-4">
                                    <div class="flex justify-between border-b border-slate-50 pb-1 font-bold">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">Pelanggan</p>
                                        <p class="text-slate-800 uppercase" x-text="selected?.pelanggan"></p>
                                    </div>
                                    <div class="flex justify-between border-b border-slate-50 pb-1 font-bold">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">No Artikel</p>
                                        <p class="text-slate-800 uppercase" x-text="selected?.art_no"></p>
                                    </div>
                                    <div class="flex justify-between border-b border-slate-50 pb-1 font-bold">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">MKT Representative</p>
                                        <p class="text-slate-800 italic" x-text="selected?.mkt"></p>
                                    </div>
                                    <div class="flex justify-between font-bold">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">Legacy ID (SAP)</p>
                                        <p class="text-slate-800" x-text="selected?.sap_no"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="mkt-surface p-6 rounded-[2rem] border mkt-border shadow-sm">
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

                        {{-- Data Teknis Matrix --}}
                        <div class="mkt-surface-alt p-8 rounded-[3rem] mkt-text border mkt-border shadow-sm">
                            <h3 class="text-red-600 font-black mb-6 uppercase italic tracking-tighter text-center underline underline-offset-8">Production Specification Matrix</h3>
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

                        {{-- Quantity --}}
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

                        {{-- Milestone --}}
                        <div class="mkt-surface p-8 rounded-[3rem] border mkt-border shadow-sm overflow-hidden">
                            <h3 class="text-slate-900 font-black mb-8 uppercase italic tracking-tighter text-sm flex items-center">
                                <span class="w-2 h-4 bg-red-600 mr-2 rounded-full"></span>III. Production Milestone
                            </h3>
                            <div class="relative">
                                <div class="absolute left-4 top-0 h-full w-0.5 bg-slate-100"></div>
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
                                        $milestones[] = ['status' => 'knitting', 'label' => 'Knitting Process'];
                                        $milestones[] = ['status' => 'dyeing', 'label' => 'SCR / Dyeing'];
                                        
                                        if (!empty($selectedOrder['req_tumbler'])) {
                                            $milestones[] = ['status' => 'relax-dryer', 'label' => 'Relax Dryer'];
                                        }
                                        if (!empty($selectedOrder['req_compactor'])) {
                                            $milestones[] = ['status' => 'compactor', 'label' => 'Compactor'];
                                        }
                                        if (!empty($selectedOrder['req_heat_setting'])) {
                                            $milestones[] = ['status' => 'heat-setting', 'label' => 'Heat Setting'];
                                        }
                                        if (!empty($selectedOrder['req_stenter'])) {
                                            $milestones[] = ['status' => 'stenter', 'label' => 'Stenter Process'];
                                        }
                                        if (!empty($selectedOrder['req_tumbler'])) {
                                            $milestones[] = ['status' => 'tumbler', 'label' => 'Tumbler Dry'];
                                        }
                                        if (!empty($selectedOrder['req_fleece'])) {
                                            $milestones[] = ['status' => 'fleece', 'label' => 'Fleece / Brushing'];
                                        }
                                        if (!empty($selectedOrder['req_pengujian'])) {
                                            $milestones[] = ['status' => 'pengujian', 'label' => 'QC & Lab Testing'];
                                        }
                                        if (!empty($selectedOrder['req_qe'])) {
                                            $milestones[] = ['status' => 'qe', 'label' => 'QE Approval'];
                                        }
                                        
                                        // Generate IDs dynamically
                                        $id = 2;
                                        foreach($milestones as &$m) {
                                            $m['id'] = str_pad($id++, 2, '0', STR_PAD_LEFT);
                                        }
                                    @endphp
                                    @endphp

                                    @foreach($milestones as $m)
                                        @php $log = $activitiesLogs[$m['status']][0] ?? null; @endphp
                                        <div class="flex items-start gap-6">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center z-10 {{ $log ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                                <span class="text-[10px] font-black">{{ $log ? '✓' : $m['id'] }}</span>
                                            </div>
                                            <div class="flex-1 flex justify-between items-center">
                                                <div>
                                                    <p class="text-xs font-black uppercase italic text-slate-800">{{ $m['label'] }}</p>
                                                    @if($log)
                                                        <p class="text-[8px] font-black text-emerald-500 mt-1">DONE: {{ \Carbon\Carbon::parse($log['created_at'])->format('d/m H:i') }}</p>
                                                    @endif
                                                </div>

                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer Modal: Aksi Hapus & Print --}}
                    <div class="p-8 bg-slate-50 border-t mkt-border flex justify-between items-center no-print-bg">
                        <button onclick="window.print()" class="bg-blue-600 text-white px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-black transition-all flex items-center gap-3 shadow-lg shadow-blue-100">
                            <span>🖨️ PRINT SURAT JALAN RND</span>
                        </button>

                        <div class="flex gap-4">
                            <a :href="'/marketing/orders/' + selected?.id + '/edit'" class="bg-slate-900 text-white px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-red-600 transition-all flex items-center gap-3">
                                <span>✏️ EDIT ORDER</span>
                            </a>
                            <button @click="if(confirm('Yakin hapus data Artikel ' + selected.art_no + '?')) { $wire.deleteOrder(selected.id) }" class="bg-red-50 text-red-600 border border-red-200 px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-red-600 hover:text-white transition-all">
                                <span>🗑️ PURGE DATA</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL LOG DETAIL --}}
    <div x-show="showLogDetail" @close-log-detail.window="showLogDetail = false" class="fixed inset-0 z-[110] flex items-center justify-center p-4" x-cloak style="display: none;">
        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-md" @click="showLogDetail = false"></div>
        <div class="relative z-10 w-full max-w-4xl">
            @if($selectedOrder)
                <livewire:order-tracking-detail :order-id="$selectedOrder['id']" :key="$selectedOrder['id']" />
            @endif
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