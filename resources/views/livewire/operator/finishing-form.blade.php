<?php

use Livewire\Volt\Component;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $orderId;
    public $jenis_proses = 'compactor'; // Default tab
    
    // Properti Form Umum (Berbagi antara Compactor & Heat Setting)
    public $operator, $tanggal, $no_mesin, $rangka;
    public $temperatur, $speed, $overfeed, $delivery_speed, $folding_speed;
    public $hasil_lebar, $hasil_gramasi, $shrinkage;
    
    // Properti Khusus Compactor
    public $felt;

    public function mount($orderId)
    {
        $this->orderId = $orderId;
        $this->tanggal = date('Y-m-d');
        $this->operator = Auth::user()->name;
    }

    public function submit()
    {
        $this->validate([
            'no_mesin' => 'required',
            'speed' => 'required|numeric',
            'hasil_lebar' => 'required|numeric',
            'hasil_gramasi' => 'required|numeric',
        ]);

        ProductionActivity::create([
            'marketing_order_id' => $this->orderId,
            'user_id' => Auth::id(),
            'type' => 'finishing',
            'no_mesin' => $this->no_mesin,
            'shift' => $this->determineShift(),
            'technical_data' => [
                'sub_proses' => $this->jenis_proses,
                'operator' => $this->operator,
                'tanggal' => $this->tanggal,
                'rangka' => $this->rangka,
                'temperatur' => $this->temperatur,
                'speed' => $this->speed,
                'overfeed' => $this->overfeed,
                'delivery_speed' => $this->delivery_speed,
                'folding_speed' => $this->folding_speed,
                'felt' => $this->jenis_proses === 'compactor' ? $this->felt : null,
                'hasil_lebar' => $this->hasil_lebar,
                'hasil_gramasi' => $this->hasil_gramasi,
                'shrinkage' => $this->shrinkage,
            ]
        ]);

        // Update status Marketing Order menjadi SELESAI atau ke QC
        MarketingOrder::where('id', $this->orderId)->update(['status' => 'completed']);

        session()->flash('message', 'Data Finishing (' . strtoupper($this->jenis_proses) . ') berhasil disimpan! 🚀');
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
        <div class="max-w-4xl mx-auto px-4">
            <div class="mkt-surface rounded-[2.5rem] p-8 shadow-xl border mkt-border">
                
                <div class="flex mkt-input p-2 rounded-2xl mb-8">
                    <button wire:click="$set('jenis_proses', 'compactor')" 
                        class="flex-1 py-3 rounded-xl font-black uppercase text-[10px] transition-all {{ $jenis_proses === 'compactor' ? 'mkt-surface shadow-sm text-emerald-600' : 'mkt-text-muted' }}">
                        Compactor (Cotton)
                    </button>
                    <button wire:click="$set('jenis_proses', 'heatsetting')" 
                        class="flex-1 py-3 rounded-xl font-black uppercase text-[10px] transition-all {{ $jenis_proses === 'heatsetting' ? 'mkt-surface shadow-sm text-rose-600' : 'mkt-text-muted' }}">
                        Heat Setting (Polyester/PE)
                    </button>
                </div>

                <div class="flex items-center gap-4 mb-8">
                    <div class="{{ $jenis_proses === 'compactor' ? 'bg-emerald-500 shadow-emerald-200' : 'bg-rose-500 shadow-rose-200' }} p-3 rounded-2xl text-white shadow-lg">
                        {{ $jenis_proses === 'compactor' ? '🌀' : '🌡️' }}
                    </div>
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter mkt-text">
                            {{ $jenis_proses === 'compactor' ? 'Compactor Finishing' : 'Heat Setting Finishing' }}
                        </h2>
                        <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-widest">
                            {{ $jenis_proses === 'compactor' ? 'Finishing Bulat Cotton' : 'Finishing Bulat Poliester/PE' }}
                        </p>
                    </div>
                </div>

                <form wire:submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Operator (Text)</label>
                            <input type="text" wire:model="operator" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold focus:border-emerald-400 focus:ring-0 transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Tanggal (Date)</label>
                            <input type="date" wire:model="tanggal" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold focus:border-emerald-400 focus:ring-0 transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">No Mesin (DD)</label>
                            <select wire:model="no_mesin" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                                <option value="">Pilih Mesin</option>
                                <option value="FIN-01">FIN-01</option>
                                <option value="FIN-02">FIN-02</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Rangka (DD)</label>
                            <select wire:model="rangka" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                                <option value="">Pilih Rangka</option>
                                <option value="R1">Rangka 1</option>
                                <option value="R2">Rangka 2</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Temperatur</label>
                            <input type="text" wire:model="temperatur" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Speed (Int)</label>
                            <input type="number" wire:model="speed" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold text-emerald-600">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Overfeed (Int)</label>
                            <input type="number" wire:model="overfeed" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                    </div>

                    @if($jenis_proses === 'compactor')
                    <div class="animate-in fade-in slide-in-from-top-2 duration-300">
                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Felt (DD)</label>
                        <select wire:model="felt" class="w-full bg-emerald-50 border-2 border-emerald-100 rounded-xl px-4 py-3 font-bold text-emerald-700">
                            <option value="">Pilih Felt</option>
                            <option value="Tipe A">Tipe A</option>
                            <option value="Tipe B">Tipe B</option>
                        </select>
                    </div>
                    @endif

                    <div class="grid grid-cols-2 gap-6 pt-4 border-t mkt-border">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Delivery Speed (Int)</label>
                            <input type="number" wire:model="delivery_speed" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Folding Speed (Int)</label>
                            <input type="number" wire:model="folding_speed" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-900 p-6 rounded-[2rem] shadow-xl">
                        <div>
                            <label class="block text-[9px] font-black mkt-text-muted uppercase mb-2 ml-1">Hasil Lebar (Int)</label>
                            <input type="number" wire:model="hasil_lebar" class="w-full bg-slate-800 border-none rounded-xl px-4 py-3 font-black text-white focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[9px] font-black mkt-text-muted uppercase mb-2 ml-1">Hasil Gramasi (Int)</label>
                            <input type="number" wire:model="hasil_gramasi" class="w-full bg-slate-800 border-none rounded-xl px-4 py-3 font-black text-white focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[9px] font-black mkt-text-muted uppercase mb-2 ml-1">Shrinkage V x H (Int)</label>
                            <input type="number" wire:model="shrinkage" class="w-full bg-slate-800 border-none rounded-xl px-4 py-3 font-black text-rose-500 focus:ring-2 focus:ring-rose-500">
                        </div>
                    </div>

                    <button type="submit" class="w-full {{ $jenis_proses === 'compactor' ? 'bg-emerald-600 shadow-emerald-100' : 'bg-rose-600 shadow-rose-100' }} text-white py-5 rounded-2xl font-black uppercase text-xs shadow-2xl transition-all hover:bg-black group">
                        Simpan Data {{ $jenis_proses === 'compactor' ? 'Compactor' : 'Heat Setting' }} <span class="group-hover:ml-2 transition-all">🚀</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>