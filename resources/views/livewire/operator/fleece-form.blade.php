<?php

use Livewire\Volt\Component;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $orderId;
    public $sub_proses = 'raising'; // raising, brushing, shearing
    
    // Properti Umum
    public $operator, $tanggal;
    
    // Properti Teknis (Akan disimpan dalam technical_data)
    public $standar_bulu, $speed, $cloth_out, $bend_pin, $stright_pin;
    public $rpm_drum, $lebar_gsm, $drum_brush; // raising khusus
    public $cloth_speed, $left_brush, $right_brush, $tension; // brushing khusus
    public $exknitting, $shear; // shearing khusus

    public function mount($orderId)
    {
        $this->orderId = $orderId;
        $this->tanggal = date('Y-m-d');
        $this->operator = Auth::user()->name;
    }

    public function submit()
    {
        $this->validate([
            'sub_proses' => 'required',
            'speed' => 'required|numeric',
        ]);

        DB::transaction(function () {
            ProductionActivity::create([
                'marketing_order_id' => $this->orderId,
                'user_id' => Auth::id(),
                'type' => 'fleece',
                'division_name' => 'fleece',
                'shift' => $this->determineShift(),
                'technical_data' => [
                    'sub_proses' => $this->sub_proses,
                    'operator' => $this->operator,
                    'tanggal' => $this->tanggal,
                    'standar_bulu' => $this->standar_bulu,
                    'speed' => $this->speed,
                    'cloth_out' => $this->cloth_out,
                    'bend_pin' => $this->bend_pin,
                    'stright_pin' => $this->stright_pin,
                    'rpm_drum' => $this->rpm_drum,
                    'lebar_gsm' => $this->lebar_gsm,
                    'drum_brush' => $this->drum_brush,
                    'cloth_speed' => $this->cloth_speed,
                    'left_brush' => $this->left_brush,
                    'right_brush' => $this->right_brush,
                    'tension' => $this->tension,
                    'exknitting' => $this->exknitting,
                    'shear' => $this->shear,
                ]
            ]);

            // Jika sampai di tahap shearing, bisa dianggap selesai atau lanjut ke divisi berikutnya
            if($this->sub_proses === 'shearing') {
                MarketingOrder::where('id', $this->orderId)->update(['status' => 'completed']);
            }
        });

        session()->flash('message', 'Data Fleece ' . strtoupper($this->sub_proses) . ' berhasil disimpan!');
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
                    @foreach(['raising', 'brushing', 'shearing'] as $tab)
                    <button wire:click="$set('sub_proses', '{{ $tab }}')" 
                        class="flex-1 py-3 rounded-xl font-black uppercase text-[10px] transition-all {{ $sub_proses === $tab ? 'bg-amber-500 text-white shadow-lg' : 'mkt-text-muted' }}">
                        {{ $tab }}
                    </button>
                    @endforeach
                </div>

                <div class="flex items-center gap-4 mb-8">
                    <div class="bg-amber-500 p-3 rounded-2xl text-white shadow-lg shadow-amber-200">
                        🧶
                    </div>
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter mkt-text">Fleece Process</h2>
                        <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-widest">Mode: <span class="text-amber-600">{{ strtoupper($sub_proses) }}</span></p>
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

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6 pt-4 border-t mkt-border">
                        
                        {{-- Field yang ada di hampir semua sub-proses --}}
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Speed (Int)</label>
                            <input type="number" wire:model="speed" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold text-amber-600">
                        </div>

                        @if($sub_proses !== 'shearing')
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Standar Bulu (Int)</label>
                            <input type="number" wire:model="standar_bulu" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        @endif

                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Cloth Out (Float)</label>
                            <input type="number" step="0.01" wire:model="cloth_out" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>

                        {{-- KHUSUS RAISING --}}
                        @if($sub_proses === 'raising')
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Bend Pin (Int)</label>
                            <input type="number" wire:model="bend_pin" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Stright Pin (Int)</label>
                            <input type="number" wire:model="stright_pin" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">RPM Drum (Int)</label>
                            <input type="number" wire:model="rpm_drum" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Drum Brush (Varchar)</label>
                            <input type="text" wire:model="drum_brush" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold" placeholder="Input detail drum brush...">
                        </div>
                        @endif

                        {{-- KHUSUS BRUSHING --}}
                        @if($sub_proses === 'brushing')
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Cloth Speed (Float)</label>
                            <input type="number" step="0.01" wire:model="cloth_speed" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Left Brush (Int)</label>
                            <input type="number" wire:model="left_brush" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Right Brush (Int)</label>
                            <input type="number" wire:model="right_brush" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">RPM Drum (Int)</label>
                            <input type="number" wire:model="rpm_drum" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Tension 1/2/3 (Int)</label>
                            <input type="number" wire:model="tension" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        @endif

                        {{-- KHUSUS SHEARING --}}
                        @if($sub_proses === 'shearing')
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Exknitting (Int)</label>
                            <input type="number" wire:model="exknitting" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Shear (Int)</label>
                            <input type="number" wire:model="shear" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 font-bold">
                        </div>
                        @endif
                    </div>

                    <div class="bg-slate-900 p-6 rounded-[2rem] shadow-xl mt-8">
                        <div>
                            <label class="block text-[9px] font-black mkt-text-muted uppercase mb-2 ml-1">Hasil Lebar / Gramasi (Int)</label>
                            <input type="number" wire:model="lebar_gsm" class="w-full bg-slate-800 border-none rounded-xl px-4 py-3 font-black text-white focus:ring-2 focus:ring-amber-500 text-xl" placeholder="0">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-amber-500 text-white py-5 rounded-2xl font-black uppercase text-xs shadow-2xl transition-all hover:bg-black group">
                        Simpan Data Fleece {{ strtoupper($sub_proses) }} <span class="group-hover:ml-2 transition-all">🚀</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>