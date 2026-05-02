<?php

use Livewire\Volt\Component;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $orderId;
    public $sub_proses = 'preset'; // Default: preset, drying, finishing
    
    // Properti Form
    public $operator, $tanggal, $no_mesin;
    public $temperatur, $speed, $padder, $rangka;
    public $overfeed_a, $overfeed_b, $fan_blower;
    public $delivery_speed, $folding_speed;
    public $chemical_1, $chemical_2;
    public $hasil_lebar, $hasil_gramasi, $shrinkage;

    public function mount($orderId)
    {
        $this->orderId = $orderId;
        $this->tanggal = date('Y-m-d');
        $this->operator = Auth::user()->name;
    }

    public function setSubProses($name)
    {
        $this->sub_proses = $name;
        // Opsional: Kosongkan field jika ingin input baru tiap tab
        // $this->reset(['temperatur', 'speed', 'hasil_lebar', 'hasil_gramasi', 'shrinkage']);
    }

    public function submit()
    {
        $this->validate([
            'no_mesin' => 'required',
            'temperatur' => 'required|numeric',
            'speed' => 'required|numeric',
        ]);

        DB::transaction(function () {
            ProductionActivity::create([
                'marketing_order_id' => $this->orderId,
                'user_id' => Auth::id(),
                'type' => 'stenter',
                'no_mesin' => $this->no_mesin,
                'shift' => $this->determineShift(),
                'technical_data' => [
                    'sub_proses' => $this->sub_proses,
                    'operator' => $this->operator,
                    'tanggal' => $this->tanggal,
                    'temperatur' => $this->temperatur,
                    'speed' => $this->speed,
                    'padder' => $this->padder,
                    'rangka' => $this->rangka,
                    'overfeed_a' => $this->overfeed_a,
                    'overfeed_b' => $this->overfeed_b,
                    'fan_blower' => $this->fan_blower,
                    'delivery_speed' => $this->delivery_speed,
                    'folding_speed' => $this->folding_speed,
                    'chemical_1' => $this->chemical_1,
                    'chemical_2' => $this->chemical_2,
                    'hasil_lebar' => $this->hasil_lebar,
                    'hasil_gramasi' => $this->hasil_gramasi,
                    'shrinkage' => $this->shrinkage,
                ]
            ]);

            // Jika tahap terakhir (finishing) selesai, ubah status order
            if ($this->sub_proses === 'finishing') {
                MarketingOrder::where('id', $this->orderId)->update(['status' => 'completed']);
            }
        });

        session()->flash('message', 'Data Stenter ' . strtoupper($this->sub_proses) . ' berhasil disimpan!');
        return redirect()->route('operator.logbook');
    }

    private function determineShift() {
        $hour = date('H');
        if ($hour >= 7 && $hour < 15) return 1;
        if ($hour >= 15 && $hour < 23) return 2;
        return 3;
    }
};
?>

<div>
    <div class="py-12 bg-transparent min-h-screen italic">
        <div class="max-w-5xl mx-auto px-4">
            <div class="mkt-surface rounded-[2.5rem] p-8 shadow-xl border mkt-border">
                
                <div class="flex mkt-input p-2 rounded-2xl mb-8">
                    @foreach(['preset', 'drying', 'finishing'] as $tab)
                    <button wire:click="$set('sub_proses', '{{ $tab }}')" 
                        class="flex-1 py-3 rounded-xl font-black uppercase text-[10px] transition-all {{ $sub_proses === $tab ? 'bg-indigo-600 text-white shadow-lg' : 'mkt-text-muted' }}">
                        {{ $tab }}
                    </button>
                    @endforeach
                </div>

                <div class="flex items-center gap-4 mb-8">
                    <div class="bg-indigo-600 p-3 rounded-2xl text-white shadow-lg shadow-indigo-200">
                        🔥
                    </div>
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter mkt-text">Stenter (Finishing Belah)</h2>
                        <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-widest">Mode: <span class="text-indigo-600">{{ strtoupper($sub_proses) }}</span></p>
                    </div>
                </div>

                <form wire:submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Operator</label>
                            <input type="text" wire:model="operator" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Tanggal</label>
                            <input type="date" wire:model="tanggal" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">No Mesin</label>
                            <input type="text" wire:model="no_mesin" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold" placeholder="Contoh: ST-01">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-4 border-t mkt-border">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Temperature (Int)</label>
                            <input type="number" wire:model="temperatur" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold text-indigo-600">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Speed (Int)</label>
                            <input type="number" wire:model="speed" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold text-indigo-600">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Padder (Int)</label>
                            <input type="number" wire:model="padder" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Rangka (Int)</label>
                            <input type="number" wire:model="rangka" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Overfeed A (Int)</label>
                            <input type="number" wire:model="overfeed_a" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Overfeed B (Int)</label>
                            <input type="number" wire:model="overfeed_b" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Fan / Blower (Int)</label>
                            <input type="number" wire:model="fan_blower" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold text-orange-600">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 bg-indigo-50/50 p-6 rounded-[2rem] border border-dashed border-indigo-200">
                        <div>
                            <label class="block text-[10px] font-black text-indigo-400 uppercase mb-2 ml-1">Chemical 1 (Int)</label>
                            <input type="number" wire:model="chemical_1" class="w-full mkt-surface border-2 border-indigo-100 rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-indigo-400 uppercase mb-2 ml-1">Chemical 2 (Int)</label>
                            <input type="number" wire:model="chemical_2" class="w-full mkt-surface border-2 border-indigo-100 rounded-xl px-4 py-3 font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Delivery Speed (Int)</label>
                            <input type="number" wire:model="delivery_speed" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Folding Speed (Int)</label>
                            <input type="number" wire:model="folding_speed" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-900 p-6 rounded-[2.5rem] shadow-xl">
                        <div>
                            <label class="block text-[9px] font-black mkt-text-muted uppercase mb-2 ml-1">Hasil Lebar (Int)</label>
                            <input type="number" wire:model="hasil_lebar" class="w-full bg-slate-800 border-none rounded-xl px-4 py-3 font-black text-white focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[9px] font-black mkt-text-muted uppercase mb-2 ml-1">Hasil Gramasi (Int)</label>
                            <input type="number" wire:model="hasil_gramasi" class="w-full bg-slate-800 border-none rounded-xl px-4 py-3 font-black text-white focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[9px] font-black mkt-text-muted uppercase mb-2 ml-1">Shrinkage (Int)</label>
                            <input type="number" wire:model="shrinkage" class="w-full bg-slate-800 border-none rounded-xl px-4 py-3 font-black text-rose-500 focus:ring-2 focus:ring-rose-500">
                        </div>
                    </div>

                    <button type="submit"  wire:click="setSubProses('{{ $tab }}')" class="w-full bg-indigo-600 text-white py-5 rounded-2xl font-black uppercase text-xs shadow-2xl transition-all hover:bg-black group">
                        Simpan Data Stenter {{ strtoupper($sub_proses) }} <span class="group-hover:ml-2 transition-all">🚀</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>