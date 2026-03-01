<?php

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\ProductionActivity;
use App\Models\MarketingOrder;

new class extends Component {
    public $sap_no;
    public $order;

    // Definisikan semua variabel model agar sinkron dengan input form
    public $gramasi, $tanggal_proses, $jenis_mesin, $no_mesin;
    public $warna, $kode_warna, $dye_system, $treatment;

    public function mount($sap) 
    {
        $this->sap_no = $sap;
        $this->order = MarketingOrder::where('sap_no', $sap)->first();
        
        if (!$this->order) {
            abort(404);
        }

        // Auto-fill data awal dari tabel marketing_orders jika ada
        $this->warna = $this->order->warna;
        $this->tanggal_proses = now()->format('Y-m-d');
    }

    // --- INI FUNGSI YANG HILANG TADI ---
    public function saveDyeing()
    {
        $this->validate([
            'gramasi' => 'required',
            'tanggal_proses' => 'required|date',
            'jenis_mesin' => 'required',
            'no_mesin' => 'required',
            'warna' => 'required',
            'dye_system' => 'required',
        ]);

        DB::transaction(function () {
            // 1. Simpan aktivitas produksi
            ProductionActivity::create([
                'operator_id' => auth()->id(),
                'marketing_order_id' => $this->order->id,
                'division_name' => 'dyeing',
                'technical_data' => [
                    'gramasi' => $this->gramasi,
                    'jenis_mesin' => $this->jenis_mesin,
                    'no_mesin' => $this->no_mesin,
                    'kode_warna' => $this->kode_warna,
                    'dye_system' => $this->dye_system,
                    'treatment' => $this->treatment,
                ]
            ]);

            // 2. Update status ke proses selanjutnya (misal: stenter)
            $this->order->update(['status' => 'stenter']);
        });

        session()->flash('message', 'Data SCR/DYEING berhasil disimpan! 🚀');
        
        // Kembali ke logbook
        return redirect()->route('operator.logbook', ['menu' => 'orders']);
    }
}
?>
<div class="py-8 bg-slate-50 min-h-screen font-sans tracking-tighter italic text-left">
    <div class="max-w-4xl mx-auto px-4">
        
        {{-- HEADER FORM --}}
        <div class="mb-8 flex justify-between items-end border-b-4 border-slate-900 pb-4">
            <div>
                <h2 class="text-4xl font-black uppercase text-slate-800 leading-none italic">
                    SCR / <span class="text-blue-600">DYEING</span>
                </h2>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">
                    Production Control & Chemical Treatment
                </p>
            </div>
            <div class="text-right">
                <span class="bg-slate-900 text-white px-4 py-1 rounded-full text-xs font-black uppercase italic">
                    SAP: #{{ $sap_no }}
                </span>
            </div>
        </div>

        <form wire:submit.prevent="saveDyeing" class="space-y-6">
            {{-- SEKSI 1: CEK GREIGE --}}
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 italic">
                <h3 class="text-lg font-black uppercase text-slate-800 mb-6 flex items-center gap-2 italic">
                    <span class="w-2 h-6 bg-blue-600 rounded-full"></span>
                    CEK GREIGE
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- LEBAR/GRAMASI --}}
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-2 italic">Lebar / Gramasi (Inch/Gsm)</label>
                        <input type="text" wire:model="gramasi" 
                            class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold italic focus:ring-4 focus:ring-blue-100" 
                            placeholder="Contoh: 103/22">
                        @error('gramasi') <span class="text-red-500 text-[9px] font-bold italic ml-2">{{ $message }}</span> @enderror
                    </div>

                    {{-- TANGGAL --}}
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-2 italic">Tanggal Proses</label>
                        <input type="date" wire:model="tanggal_proses" 
                            class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold italic focus:ring-4 focus:ring-blue-100">
                        @error('tanggal_proses') <span class="text-red-500 text-[9px] font-bold italic ml-2">{{ $message }}</span> @enderror
                    </div>

                    {{-- JENIS MESIN --}}
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-2 italic">Jenis Mesin</label>
                        <select wire:model="jenis_mesin" 
                            class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold italic focus:ring-4 focus:ring-blue-100">
                            <option value="">-- Pilih Mesin --</option>
                            <option value="Jet Dyeing">Jet Dyeing</option>
                            <option value="Long Dyeing">Long Dyeing</option>
                            <option value="Winch Dyeing">Winch Dyeing</option>
                        </select>
                        @error('jenis_mesin') <span class="text-red-500 text-[9px] font-bold italic ml-2">{{ $message }}</span> @enderror
                    </div>

                    {{-- NO MESIN --}}
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-2 italic">No. Mesin</label>
                        <input type="number" wire:model="no_mesin" 
                            class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold italic focus:ring-4 focus:ring-blue-100" 
                            placeholder="00">
                        @error('no_mesin') <span class="text-red-500 text-[9px] font-bold italic ml-2">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- SEKSI 2: SPESIFIKASI WARNA --}}
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 italic">
                <h3 class="text-lg font-black uppercase text-slate-800 mb-6 flex items-center gap-2 italic">
                    <span class="w-2 h-6 bg-red-600 rounded-full"></span>
                    WARNA & SYSTEM
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- WARNA --}}
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-2 italic">Warna</label>
                        <input type="text" wire:model="warna" 
                            class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold italic focus:ring-4 focus:ring-red-100" 
                            placeholder="Nama Warna">
                        @error('warna') <span class="text-red-500 text-[9px] font-bold italic ml-2">{{ $message }}</span> @enderror
                    </div>

                    {{-- KODE WARNA --}}
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-2 italic">Kode Warna</label>
                        <input type="text" wire:model="kode_warna" 
                            class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold italic focus:ring-4 focus:ring-red-100" 
                            placeholder="Contoh: D-BK-001">
                        @error('kode_warna') <span class="text-red-500 text-[9px] font-bold italic ml-2">{{ $message }}</span> @enderror
                    </div>

                    {{-- DYE SYSTEM --}}
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-2 italic">Dye System</label>
                        <input type="text" wire:model="dye_system" 
                            class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold italic focus:ring-4 focus:ring-blue-100" 
                            placeholder="Contoh: Disperse / Reactive">
                        @error('dye_system') <span class="text-red-500 text-[9px] font-bold italic ml-2">{{ $message }}</span> @enderror
                    </div>

                    {{-- TREATMENT (CHEMICAL) --}}
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-2 italic">Treatment (Chemical)</label>
                        <textarea wire:model="treatment" 
                            class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold italic h-32 resize-none focus:ring-4 focus:ring-blue-100" 
                            placeholder="Tuliskan detail penggunaan chemical..."></textarea>
                        @error('treatment') <span class="text-red-500 text-[9px] font-bold italic ml-2">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- TOMBOL SUBMIT --}}
            <div class="flex gap-4">
                <a href="{{ route('operator.logbook', ['menu' => 'orders']) }}" 
                    class="flex-1 bg-slate-200 text-slate-600 py-6 rounded-[2rem] font-black uppercase text-xs text-center shadow-lg hover:bg-slate-300 transition-all">
                    Batal
                </a>
                <button type="submit" 
                    class="flex-[2] bg-blue-600 text-white py-6 rounded-[2rem] font-black uppercase text-xs shadow-xl shadow-blue-200 hover:bg-black transition-all">
                    Simpan & Teruskan Ke Stenter ✅
                </button>
            </div>
        </form>
    </div>
</div>