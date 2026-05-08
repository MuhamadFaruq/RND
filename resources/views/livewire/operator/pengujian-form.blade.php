<?php

use Livewire\Volt\Component;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $orderId;
    
    // Properti Form Pengujian
    public $operator, $tanggal;
    public $lebar, $gramasi, $shrinkage, $spirality, $skewness;

    public function mount($orderId)
    {
        $this->orderId = $orderId;
        $this->tanggal = date('Y-m-d');
        $this->operator = Auth::user()->name;
    }

    public function submit()
    {
        $this->validate([
            'lebar' => 'required|numeric',
            'gramasi' => 'required|numeric',
            'shrinkage' => 'required|numeric',
        ]);

        try {
            $productionService = app(ProductionService::class);
            $productionService->processPengujian(
                $this->orderId,
                Auth::id(),
                $this->determineShift(),
                [
                    'operator' => $this->operator,
                    'tanggal' => $this->tanggal,
                    'lebar' => $this->lebar,
                    'gramasi' => $this->gramasi,
                    'shrinkage' => $this->shrinkage,
                    'spirality' => $this->spirality,
                    'skewness' => $this->skewness,
                ]
            );

            session()->flash('message', 'Data Pengujian QC & LAB berhasil disimpan! 🏆');
            return redirect()->route('operator.logbook');
        } catch (\Exception $e) {
            $this->dispatch('show-error-toast', message: 'Gagal menyimpan data Pengujian: ' . $e->getMessage());
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
                    <div class="bg-cyan-500 p-3 rounded-2xl text-white shadow-lg shadow-cyan-200">
                        🔬
                    </div>
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter mkt-text">Pengujian (QC & LAB)</h2>
                        <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-widest">Final Quality Control & Lab Test</p>
                    </div>
                </div>

                <form wire:submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Operator Penguji</label>
                            <input type="text" wire:model="operator" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Tanggal Uji</label>
                            <input type="date" wire:model="tanggal" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 pt-4 border-t mkt-border">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Hasil Lebar (Int)</label>
                            <input type="number" wire:model="lebar" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold text-cyan-600 focus:ring-cyan-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Hasil Gramasi (Int)</label>
                            <input type="number" wire:model="gramasi" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold text-cyan-600 focus:ring-cyan-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-900 p-6 rounded-[2rem] shadow-xl">
                        <div>
                            <label class="block text-[9px] font-black mkt-text-muted uppercase mb-2 ml-1">Shrinkage (Int)</label>
                            <input type="number" wire:model="shrinkage" class="w-full bg-slate-800 border-none rounded-xl px-4 py-3 font-black text-white focus:ring-2 focus:ring-cyan-500" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-[9px] font-black mkt-text-muted uppercase mb-2 ml-1">Spirality (Int)</label>
                            <input type="number" wire:model="spirality" class="w-full bg-slate-800 border-none rounded-xl px-4 py-3 font-black text-white focus:ring-2 focus:ring-cyan-500" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-[9px] font-black mkt-text-muted uppercase mb-2 ml-1">Skewness (Int)</label>
                            <input type="number" wire:model="skewness" class="w-full bg-slate-800 border-none rounded-xl px-4 py-3 font-black text-rose-500 focus:ring-2 focus:ring-rose-500" placeholder="0">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-cyan-600 text-white py-5 rounded-2xl font-black uppercase text-xs shadow-2xl transition-all hover:bg-black group">
                        Simpan Hasil Pengujian <span class="group-hover:ml-2 transition-all">✅</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>