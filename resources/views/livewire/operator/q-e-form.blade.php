<?php

use Livewire\Volt\Component;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $orderId;
    
    // Properti Form QE
    public $operator, $fabric_name, $lebar, $gramasi, $shrinkage, $note;

    public function mount($orderId)
    {
        $this->orderId = $orderId;
        $order = MarketingOrder::find($orderId);
        
        $this->operator = Auth::user()->name;
        // Default fabric name diambil dari artikel MO
        $this->fabric_name = $order ? $order->art_no : '';
    }

    public function submit()
    {
        $this->validate([
            'fabric_name' => 'required',
            'lebar' => 'required|numeric',
            'gramasi' => 'required|numeric',
        ]);

        DB::transaction(function () {
            ProductionActivity::create([
                'marketing_order_id' => $this->orderId,
                'user_id' => Auth::id(),
                'type' => 'qe',
                'division_name' => 'qe',
                'shift' => $this->determineShift(),
                'technical_data' => [
                    'operator' => $this->operator,
                    'fabric_name' => $this->fabric_name,
                    'lebar' => $this->lebar,
                    'gramasi' => $this->gramasi,
                    'shrinkage' => $this->shrinkage,
                    'note' => $this->note,
                ]
            ]);

            // QE adalah tahap Final Description
            MarketingOrder::where('id', $this->orderId)->update(['status' => 'completed']);
        });

        session()->flash('message', 'Data QE Final Description berhasil disimpan! ✨');
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
                
                <div class="flex items-center gap-4 mb-8">
                    <div class="bg-violet-600 p-3 rounded-2xl text-white shadow-lg shadow-violet-200">
                        🛡️
                    </div>
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter mkt-text">QE (Final Description)</h2>
                        <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-widest">Quality Engineering & Final Validation</p>
                    </div>
                </div>

                <form wire:submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Operator QE</label>
                            <input type="text" wire:model="operator" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Fabric Name</label>
                            <input type="text" wire:model="fabric_name" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t mkt-border">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Lebar (Int)</label>
                            <input type="number" wire:model="lebar" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold text-violet-600">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Gramasi (Int)</label>
                            <input type="number" wire:model="gramasi" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold text-violet-600">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Shrinkage (Int)</label>
                            <input type="number" wire:model="shrinkage" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Note (Catatan Final)</label>
                        <textarea wire:model="note" rows="4" class="w-full mkt-surface border-2 mkt-border rounded-2xl px-4 py-3 font-bold italic" placeholder="Tuliskan keterangan akhir kain di sini..."></textarea>
                    </div>

                    <button type="submit" class="w-full bg-violet-600 text-white py-5 rounded-2xl font-black uppercase text-xs shadow-2xl transition-all hover:bg-black group">
                        Simpan Deskripsi Final QE <span class="group-hover:ml-2 transition-all">🚀</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>