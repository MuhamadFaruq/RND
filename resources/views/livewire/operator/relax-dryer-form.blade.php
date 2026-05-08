<?php

use Livewire\Volt\Component;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $sap_no;
    public $order;
    
    // Properti Form
    public $no_mesin, $shift, $suhu_set, $speed, $overfeed, $jumlah_roll, $berat_kg;
    public $art_no, $pelanggan;

    public function mount($sap_no)
    {
        // Tangkap SAP dari URL
        $this->sap_no = $sap_no;
        $this->order = MarketingOrder::where('sap_no', $sap_no)->first();

        if ($this->order) {
            $this->art_no = $this->order->art_no;
            $this->pelanggan = $this->order->pelanggan;
            $this->berat_kg = $this->order->qty_kg;
        } else {
            abort(404, 'Order tidak ditemukan');
        }
    }

    public function submit()
    {
        $this->validate([
            'no_mesin' => 'required',
            'shift' => 'required',
            'suhu_set' => 'required|numeric',
            'speed' => 'required|numeric',
            'jumlah_roll' => 'required|integer',
            'berat_kg' => 'required|numeric',
        ]);

        try {
            $productionService = app(ProductionService::class);
            $productionService->processRelaxDryer(
                $this->order->id,
                auth()->id(),
                $this->berat_kg,
                $this->jumlah_roll,
                $this->shift,
                [
                    'no_mesin' => $this->no_mesin,
                    'suhu_set' => $this->suhu_set,
                    'speed' => $this->speed,
                    'overfeed' => $this->overfeed,
                ]
            );

            session()->flash('message', 'Data Relax Dryer berhasil disimpan!');
            
            // Setelah simpan, balikkan ke Logbook (Menu Orders)
            return redirect()->route('operator.logbook', ['menu' => 'orders']);
        } catch (\Exception $e) {
            $this->dispatch('show-error-toast', message: 'Gagal menyimpan data Relax Dryer: ' . $e->getMessage());
        }
    }
};
?>
<div>
    <div class="py-12 bg-transparent min-h-screen italic"> {{-- Tambahkan italic agar konsisten --}}
        <div class="max-w-4xl mx-auto px-4">
            <div class="mkt-surface rounded-[2.5rem] p-8 shadow-xl border mkt-border">
                
                {{-- Indikator Step --}}
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center gap-4">
                        <div class="bg-violet-500 p-3 rounded-2xl shadow-lg shadow-violet-200 text-white">☁️</div>
                        <div>
                            <h2 class="text-xl font-black italic uppercase tracking-tighter mkt-text">Relax Dryer</h2>
                            <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-widest">Tensionless Drying Process</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-black mkt-text-muted block uppercase">Status Saat Ini</span>
                        <span class="text-xs font-black text-violet-600 uppercase italic">Proses Pengeringan</span>
                    </div>
                </div>

                <form wire:submit.prevent="submit" class="space-y-6">
                    {{-- Section SAP - Sekarang Dibuat Lock/Read-Only --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-violet-50/30 rounded-[2rem] border border-dashed border-violet-200">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-2">SAP NO (Locked)</label>
                            <input type="text" wire:model="sap_no" readonly 
                                class="w-full rounded-xl border-none mkt-surface shadow-sm focus:ring-0 font-black text-slate-600 italic">
                        </div>
                        <div class="flex flex-col justify-center">
                            <span class="text-[9px] font-black mkt-text-muted uppercase ml-1">Keterangan Order</span>
                            <span class="text-sm font-black text-violet-700 uppercase italic">{{ $art_no ?? '-' }}</span>
                            <span class="text-[10px] font-bold text-slate-500 uppercase italic">{{ $pelanggan ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 italic">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-2">No. Mesin</label>
                            <input type="text" wire:model="no_mesin" class="w-full rounded-xl mkt-border font-bold italic">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-2">Shift</label>
                            <select wire:model="shift" class="w-full rounded-xl mkt-border font-bold italic">
                                <option value="">Pilih</option>
                                <option value="1">1</option><option value="2">2</option><option value="3">3</option>
                            </select>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-2">Suhu Pengering (°C)</label>
                            <input type="number" wire:model="suhu_set" class="w-full rounded-xl mkt-border font-black text-violet-600 italic">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Speed (m/min)</label>
                            <input type="number" wire:model="speed" class="w-full rounded-xl mkt-border">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Overfeed (%)</label>
                            <input type="number" wire:model="overfeed" class="w-full rounded-xl mkt-border">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-4 border-t mkt-border">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Total Roll</label>
                            <input type="number" wire:model="jumlah_roll" class="w-full rounded-xl mkt-border">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Berat (KG)</label>
                            <input type="number" step="0.01" wire:model="berat_kg" class="w-full rounded-xl mkt-border font-black">
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" class="w-full bg-violet-600 text-white py-5 rounded-[2rem] font-black italic uppercase tracking-tighter hover:bg-black transition shadow-xl shadow-violet-100">
                            Simpan & Teruskan Ke Compactor 🚀
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>