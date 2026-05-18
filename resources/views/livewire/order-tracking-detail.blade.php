<?php

use Livewire\Volt\Component;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;

new class extends Component {
    public $orderId;
    public $activeTab = 'marketing';
    public $order;
    public $activities = [];

    public function mount($orderId)
    {
        $this->orderId = $orderId;
        $this->loadData();
    }

    public function loadData()
    {
        $this->order = MarketingOrder::find($this->orderId);
        if ($this->order) {
            $this->activities = ProductionActivity::with('operator')
                ->where('marketing_order_id', $this->order->id)
                ->get()
                ->groupBy('division_name')
                ->toArray();
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }
}; ?>

<div class="bg-[#111c44] text-white w-full max-w-4xl rounded-[2rem] shadow-2xl overflow-hidden border border-slate-800">
    {{-- Header --}}
    <div class="p-6 bg-[#1a234a] flex justify-between items-center border-b border-slate-800">
        <div class="flex items-center gap-3">
            <div class="bg-indigo-600 p-2 rounded-xl">
                <span class="text-xl">📋</span>
            </div>
            <div>
                <h3 class="text-lg font-black uppercase tracking-tighter">Order Tracking Detail</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    Artikel #{{ $order->art_no ?? '-' }} • Full Technical Specification
                </p>
            </div>
        </div>
        <button @click="$dispatch('close-log-detail')" class="bg-slate-800 hover:bg-red-600 p-2.5 rounded-xl transition group">
            <svg class="h-5 w-5 text-slate-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    {{-- Tabs --}}
    <div class="px-6 py-4 bg-[#1a234a] border-b border-slate-800 flex gap-3 overflow-x-auto">
        <button wire:click="setTab('marketing')" class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $activeTab === 'marketing' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700' }}">
            Marketing Req.
        </button>
        @foreach($activities as $division => $logs)
            <button wire:click="setTab('{{ $division }}')" class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $activeTab === $division ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700' }}">
                {{ strtoupper($division) }}
            </button>
        @endforeach
    </div>

    {{-- Content --}}
    <div class="p-6 max-h-[60vh] overflow-y-auto bg-[#111c44] space-y-6">
        @if($activeTab === 'marketing')
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-indigo-600 text-white w-10 h-10 rounded-xl flex items-center justify-center font-black">MO</div>
                    <div>
                        <p class="text-[9px] font-black text-indigo-500 uppercase tracking-widest">Marketing Specifications</p>
                        <h3 class="text-xl font-black uppercase italic tracking-tighter">Marketing Result</h3>
                    </div>
                </div>

                <div class="space-y-4">
                    {{-- I. Identitas Order --}}
                    <div>
                        <h4 class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                            <span class="w-1 h-3 bg-indigo-600 rounded-full"></span>I. Identitas Order
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 bg-[#1a234a] p-4 rounded-xl border border-slate-800">
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">Nomor Artikel</p>
                                <p class="text-xs font-black text-indigo-500 italic">{{ $order->art_no ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">Legacy ID</p>
                                <p class="text-xs font-black text-white italic">{{ $order->sap_no ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">Tanggal Order</p>
                                <p class="text-xs font-black text-white italic">{{ $order->tanggal ? \Carbon\Carbon::parse($order->tanggal)->format('d/m/Y') : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">Pelanggan</p>
                                <p class="text-xs font-black text-white italic">{{ $order->pelanggan ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- II. Klasifikasi & Material --}}
                    <div>
                        <h4 class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                            <span class="w-1 h-3 bg-indigo-600 rounded-full"></span>II. Klasifikasi & Material
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 bg-[#1a234a] p-4 rounded-xl border border-slate-800">
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">MKT (Sales)</p>
                                <p class="text-xs font-black text-white italic">{{ $order->mkt ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">Keperluan</p>
                                <p class="text-xs font-black text-white italic">{{ $order->keperluan ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">Material</p>
                                <p class="text-xs font-black text-white italic">{{ $order->material ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">Benang</p>
                                <p class="text-xs font-black text-white italic">{{ $order->benang ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- III. Spesifikasi Teknis --}}
                    <div>
                        <h4 class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                            <span class="w-1 h-3 bg-indigo-600 rounded-full"></span>III. Spesifikasi Teknis
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 bg-[#1a234a] p-4 rounded-xl border border-slate-800">
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">Target Lebar</p>
                                <p class="text-xs font-black text-white italic">{{ $order->target_lebar ?? '-' }}"</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">Target Gramasi</p>
                                <p class="text-xs font-black text-white italic">{{ $order->target_gramasi ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">Belah / Bulat</p>
                                <p class="text-xs font-black text-white italic">{{ $order->belah_bulat ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">Handfeel</p>
                                <p class="text-xs font-black text-white italic">{{ $order->handfeel ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($activeTab === 'knitting')
            {{-- Custom Knitting Layout --}}
            @php $log = $activities['knitting'][0] ?? null; @endphp
            @if($log)
                {{-- Operator & Machine Card --}}
                <div class="bg-[#1a234a] p-6 rounded-xl border border-slate-800 flex justify-between items-center mb-6">
                    <div class="flex items-center gap-4">
                        <div class="bg-indigo-600/20 text-indigo-500 w-12 h-12 rounded-xl flex items-center justify-center text-xl">👤</div>
                        <div>
                            <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Actual Operator</p>
                            <h3 class="text-xl font-black uppercase text-white">{{ $log['technical_data']['nama_input'] ?? $log['operator']['name'] ?? '-' }}</h3>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Machine Unit</p>
                        <h3 class="text-2xl font-black uppercase text-indigo-500">{{ $log['technical_data']['no_mesin'] ?? '-' }}</h3>
                    </div>
                </div>

                <div class="space-y-4">
                    {{-- I. Identitas & Spesifikasi Mesin --}}
                    <div>
                        <h4 class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                            <span class="w-1 h-3 bg-indigo-600 rounded-full"></span>I. Identitas & Spesifikasi Mesin
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-[#1a234a] p-4 rounded-xl border border-slate-800">
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">No Artikel</p>
                                <p class="text-xs font-black text-indigo-500 italic">{{ $order->art_no ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">Tgl Produksi</p>
                                <p class="text-xs font-black text-white italic">{{ $log['technical_data']['tanggal'] ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">No Mesin / Type</p>
                                <p class="text-xs font-black text-white italic">{{ $log['technical_data']['no_mesin'] ?? '-' }} / {{ $log['technical_data']['typemesin'] ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">Gauge / Inch</p>
                                <p class="text-xs font-black text-white italic">{{ $log['technical_data']['gauge_inch'] ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">Jml Feeder</p>
                                <p class="text-xs font-black text-white italic">{{ $log['technical_data']['jml_feeder'] ?? '-' }} FDR</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">Jml Jarum</p>
                                <p class="text-xs font-black text-white italic">{{ $log['technical_data']['jml_jarum'] ?? '-' }} JRM</p>
                            </div>
                        </div>
                    </div>

                    {{-- II. Hasil Produksi Greige --}}
                    <div>
                        <h4 class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                            <span class="w-1 h-3 bg-indigo-600 rounded-full"></span>II. Hasil Produksi Greige
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 bg-[#1a234a] p-4 rounded-xl border border-slate-800">
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">Lebar / Gramasi</p>
                                <p class="text-xs font-black text-white italic">{{ $log['technical_data']['lebar'] ?? '-' }} X {{ $log['technical_data']['gramasi'] ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">Total Output</p>
                                <p class="text-xs font-black text-indigo-500 italic">1 ROLL</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-bold text-slate-500 uppercase">Actual Weight (KG)</p>
                                <p class="text-xs font-black text-indigo-500 italic">{{ $log['technical_data']['yl1'] ?? '-' }} KG</p>
                            </div>
                        </div>
                    </div>

                    {{-- III. Data Tambahan (Dynamic) --}}
                    <div>
                        <h4 class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                            <span class="w-1 h-3 bg-indigo-600 rounded-full"></span>III. Data Tambahan
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-[#1a234a] p-4 rounded-xl border border-slate-800">
                            @foreach($log['technical_data'] as $key => $value)
                                @if($value && !in_array($key, ['tanggal', 'no_mesin', 'typemesin', 'gauge_inch', 'jml_feeder', 'jml_jarum', 'lebar', 'gramasi', 'nama_input', 'note']))
                                    <div>
                                        <p class="text-[8px] font-bold text-slate-500 uppercase">{{ strtoupper(str_replace('_', ' ', $key)) }}</p>
                                        <p class="text-xs font-black text-white italic">{{ is_array($value) ? json_encode($value) : $value }}</p>
                                    </div>
                                @endif
                            @endforeach
                            @if(isset($log['technical_data']['note']))
                                <div class="col-span-full">
                                    <p class="text-[8px] font-bold text-slate-500 uppercase">Catatan Operator</p>
                                    <p class="text-xs font-bold text-slate-400 italic">"{{ $log['technical_data']['note'] }}"</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-sm font-black text-slate-500 uppercase italic">Belum ada data untuk divisi ini.</p>
                </div>
            @endif
        @else
            {{-- Operator Results --}}
            <div>
                @php $log = $activities[$activeTab][0] ?? null; @endphp
                @if($log)
                    <div class="flex items-center gap-3 mb-4">
                        <div class="bg-indigo-600 text-white w-10 h-10 rounded-xl flex items-center justify-center font-black">{{ strtoupper(substr($activeTab, 0, 2)) }}</div>
                        <div>
                            <p class="text-[9px] font-black text-indigo-500 uppercase tracking-widest">Operator Input</p>
                            <h3 class="text-xl font-black uppercase italic tracking-tighter">{{ strtoupper($activeTab) }} Result</h3>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @if(isset($log['technical_data']) && is_array($log['technical_data']))
                            @foreach($log['technical_data'] as $key => $value)
                                @if(is_array($value))
                                    <div class="col-span-full">
                                        <div class="bg-[#1a234a] p-4 rounded-xl border border-slate-800">
                                            <h4 class="text-[10px] font-black text-indigo-500 uppercase mb-3 border-b border-slate-700 pb-2">{{ strtoupper(str_replace('_', ' ', $key)) }}</h4>
                                            <div class="grid grid-cols-2 gap-3">
                                                @foreach($value as $subKey => $subVal)
                                                    <div class="flex justify-between border-b border-slate-700 pb-1">
                                                        <span class="text-[9px] font-bold text-slate-400 uppercase">{{ strtoupper(str_replace('_', ' ', $subKey)) }}</span>
                                                        <span class="text-xs font-black text-white">{{ $subVal ?? '-' }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="p-3 bg-[#1a234a] rounded-xl border border-slate-800 flex justify-between items-center">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase">{{ strtoupper(str_replace('_', ' ', $key)) }}</span>
                                        <span class="text-xs font-black text-white">{{ $value ?? '-' }}</span>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-sm border border-slate-700">👤</div>
                            <div>
                                <p class="text-[8px] font-black text-slate-500 uppercase leading-none">Operator</p>
                                <p class="text-xs font-black text-white mt-0.5">{{ $log['operator']['name'] ?? 'Unknown Operator' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-[8px] font-black text-slate-500 uppercase leading-none">Timestamp</p>
                            <p class="text-xs font-black text-emerald-500 mt-0.5">{{ \Carbon\Carbon::parse($log['created_at'])->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <p class="text-sm font-black text-slate-500 uppercase italic">Belum ada data untuk divisi ini.</p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="p-4 bg-[#1a234a] border-t border-slate-800 flex justify-end">
        <button @click="$dispatch('close-log-detail')" class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition-all">
            Tutup Detail
        </button>
    </div>
</div>
