<?php

use Livewire\Volt\Component;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $orderId;
    
    // Properti Form Tumbler
    public $operator, $tanggal, $temperatur;
    public $steam_inject, $hotwind, $coldwind;
    public $lebar, $gramasi, $shrinkage;

    public function mount($orderId)
    {
        $this->orderId = $orderId;
        $this->tanggal = date('Y-m-d');
        $this->operator = Auth::user()->name;
    }

    public function submit()
    {
        $this->validate([
            'temperatur' => 'required|numeric',
            'steam_inject' => 'required|numeric',
            'lebar' => 'required|numeric',
            'gramasi' => 'required|numeric',
        ]);

        try {
            $productionService = app(ProductionService::class);
            $productionService->processTumbler(
                $this->orderId,
                Auth::id(),
                $this->determineShift(),
                [
                    'operator' => $this->operator,
                    'tanggal' => $this->tanggal,
                    'temperatur' => $this->temperatur,
                    'steam_inject' => $this->steam_inject,
                    'hotwind' => $this->hotwind,
                    'coldwind' => $this->coldwind,
                    'lebar' => $this->lebar,
                    'gramasi' => $this->gramasi,
                    'shrinkage' => $this->shrinkage,
                ]
            );

            session()->flash('message', 'Data Tumbler Dry berhasil disimpan! ✅');
            return redirect()->route('operator.logbook');
        } catch (\Exception $e) {
            $this->dispatch('show-error-toast', message: 'Gagal menyimpan data Tumbler: ' . $e->getMessage());
        }
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
                    <div class="bg-orange-500 p-3 rounded-2xl text-white shadow-lg shadow-orange-200">
                        🌀
                    </div>
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter mkt-text">Tumbler Dry</h2>
                        <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-widest">Shaker & Dimensional Improvement</p>
                    </div>
                </div>

                <form wire:submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Operator</label>
                            <input type="text" wire:model="operator" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Tanggal</label>
                            <input type="date" wire:model="tanggal" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-4 border-t mkt-border">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Temp (°C)</label>
                            <input type="number" wire:model="temperatur" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold text-orange-600">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Steam Inject</label>
                            <input type="number" wire:model="steam_inject" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Hotwind</label>
                            <input type="number" wire:model="hotwind" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Coldwind</label>
                            <input type="number" wire:model="coldwind" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-900 p-6 rounded-[2rem] shadow-xl">
                        <div>
                            <label class="block text-[9px] font-black mkt-text-muted uppercase mb-2 ml-1">Lebar (Int)</label>
                            <input type="number" wire:model="lebar" class="w-full bg-slate-800 border-none rounded-xl px-4 py-3 font-black text-white focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-[9px] font-black mkt-text-muted uppercase mb-2 ml-1">Gramasi (Int)</label>
                            <input type="number" wire:model="gramasi" class="w-full bg-slate-800 border-none rounded-xl px-4 py-3 font-black text-white focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-[9px] font-black mkt-text-muted uppercase mb-2 ml-1">Shrinkage V x H</label>
                            <input type="number" wire:model="shrinkage" class="w-full bg-slate-800 border-none rounded-xl px-4 py-3 font-black text-orange-400 focus:ring-2 focus:ring-orange-500">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-orange-600 text-white py-5 rounded-2xl font-black uppercase text-xs shadow-2xl transition-all hover:bg-black group">
                        Simpan Data Tumbler <span class="group-hover:ml-2 transition-all">🚀</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>