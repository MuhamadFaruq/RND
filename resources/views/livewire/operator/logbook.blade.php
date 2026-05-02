<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\ProductionActivity;
use App\Models\MarketingOrder;
use App\Models\ProductionNote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    use WithPagination;

    public $currentMenu = 'dashboard'; 
    public $search = '';
    public $messageText = "";

    // Properti kontrol tampilan
    public $isProcessing = false; 
    public $selectedJobId = null;

    public $selectedLog = null;
    public $showEditModal = false;

    public function mount()
    {
        $menuFromUrl = request()->query('menu');
        $this->currentMenu = $menuFromUrl ?? 'dashboard';
    }

    // --- FUNGSI START PROCESS SEKARANG SUDAH DI LUAR MOUNT ---
    public function startProcess($id) {
        $order = MarketingOrder::find($id);
        if ($order) {
            $this->selectedJobId = $id;
            $this->isProcessing = true;
        }
    }

    public function deleteEntry($id)
    {
        $log = ProductionActivity::find($id);

        if ($log) {
            $order = MarketingOrder::find($log->marketing_order_id);
            $role = auth()->user()->role;

            // 1. Simpan ke Riwayat Penghapusan (Audit Trail)
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'DELETE_PRODUCTION_LOG',
                'division' => $role,
                'sap_no' => $order->sap_no,
                'details' => "Menghapus data: {$log->kg} KG / {$log->roll} Roll",
            ]);

            // 2. Kembalikan Status Order ke Antrean Divisi Saat Ini
            if ($order) {
                $order->update(['status' => $role]);
            }

            // 3. Hapus Data
            $log->delete();

            session()->flash('message', "Log SAP #{$order->sap_no} berhasil dihapus & dicatat di sistem.");
            
            $this->dispatch('show-toast', [
                'message' => 'Penghapusan berhasil dicatat oleh sistem.',
                'type' => 'warning'
            ]);
        }
    }

    public function cancelProcess() {
        $this->isProcessing = false;
        $this->selectedJobId = null;
    }

    protected $listeners = ['change-menu' => 'setMenu'];

    public function setMenu($menu) {
        $this->currentMenu = $menu;
        $this->isProcessing = false; // Reset state saat ganti menu
        $this->resetPage();
    }

    public $selectedOrder = null;
    public $showModal = false;

    public function showOrderDetail($id) {
        $this->selectedOrder = MarketingOrder::find($id);
        if ($this->selectedOrder) {
            $this->showModal = true;
        }
    }

    public function closeModal() {
        $this->showModal = false;
        $this->selectedOrder = null;
        $this->showInputForm = false;
    }

    public $showInputForm = false;
    public $qty_kg, $qty_roll, $shift;

    public $operator_name;

    public function openInputForm() {
        $this->showInputForm = true;
        $this->operator_name = "";
        $this->shift = $this->determineShift();
    }

    public function determineShift() {
        $hour = date('H');
        if ($hour >= 7 && $hour < 15) return 1;
        if ($hour >= 15 && $hour < 23) return 2;
        return 3;
    }

    public function submitProduction() {
        // 1. SMART VALIDATION
        $this->validate([
            'operator_name' => 'required|min:3',
            // Validasi KG: Minimal 0.1 dan Maksimal 5000 (menghindari typo nol berlebih)
            'qty_kg' => 'required|numeric|min:0.1|max:5000', 
            // Validasi Roll: Wajib angka bulat minimal 1
            'qty_roll' => 'required|integer|min:1',
        ]);

        // 2. ANOMALY DETECTION (Logging otomatis jika input di luar kewajaran)
        if ($this->qty_kg > 1000) {
            \Illuminate\Support\Facades\Log::warning(
                "⚠️ DETEKSI INPUT TINGGI: Operator " . auth()->user()->name . 
                " menginput " . $this->qty_kg . " KG untuk SAP #" . $this->selectedOrder->sap_no
            );
        }

        DB::transaction(function () {
            $role = auth()->user()->role;

            if ($this->selectedLog) {
                // Logika Update (Audit Trail mencatat data lama & baru)
                \App\Models\ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'EDIT_PRODUCTION_DATA',
                    'division' => $role,
                    'sap_no' => $this->selectedOrder->sap_no,
                    'details' => "EDIT: Dari {$this->selectedLog->kg} KG menjadi {$this->qty_kg} KG",
                ]);

                $this->selectedLog->update([
                    'kg' => $this->qty_kg,
                    'roll' => $this->qty_roll,
                    'technical_data' => [
                        'kg' => $this->qty_kg,
                        'roll' => $this->qty_roll,
                        'operator_manual_name' => $this->operator_name,
                    ]
                ]);
                
                $message = 'Data produksi berhasil diperbarui! ✏️';
            } else {
                // Logika Create Baru
                ProductionActivity::create([
                    'operator_id' => Auth::id(),
                    'marketing_order_id' => $this->selectedOrder->id,
                    'division_name' => $role,
                    'shift' => $this->shift,
                    'kg' => $this->qty_kg,
                    'roll' => $this->qty_roll,
                    'technical_data' => [
                        'kg' => $this->qty_kg,
                        'roll' => $this->qty_roll,
                        'operator_manual_name' => $this->operator_name,
                    ]
                ]);

                $this->updateOrderStatus($role);
                $message = 'Berhasil! Data dikirim ke divisi berikutnya. 🚀';
            }

            $this->selectedOrder->save();
            session()->flash('message', $message);
        });

        $this->reset(['qty_kg', 'qty_roll', 'operator_name', 'showInputForm', 'showModal', 'selectedLog', 'isProcessing']);
    }

    // Fungsi pembantu agar kode submitProduction lebih bersih
    private function updateOrderStatus($role) {
        if ($role === 'knitting') $this->selectedOrder->status = 'dyeing';
        elseif ($role === 'dyeing') $this->selectedOrder->status = 'relax-dryer';
        elseif ($role === 'relax-dryer') $this->selectedOrder->status = 'finishing';
        elseif ($role === 'finishing') $this->selectedOrder->status = 'stenter';
        elseif ($role === 'stenter') $this->selectedOrder->status = 'tumbler';
        elseif ($role === 'tumbler') $this->selectedOrder->status = 'fleece';
        elseif ($role === 'fleece') $this->selectedOrder->status = 'pengujian';
        elseif ($role === 'pengujian') $this->selectedOrder->status = 'qe';
        elseif ($role === 'qe') $this->selectedOrder->status = 'finished';
    }

    public function viewLogDetail($id) {
        $this->selectedLog = ProductionActivity::with('marketingOrder')->find($id);
        
        // Memanfaatkan modal detail yang sudah ada
        $this->selectedOrder = $this->selectedLog->marketingOrder;
        $this->showModal = true;

        $this->dispatch('show-toast', [
            'message' => "Membuka Detail SAP #" . $this->selectedOrder->sap_no,
            'type' => 'success'
        ]);
    }

    public function editLog($id) {
        $log = ProductionActivity::find($id);
        
        if ($log) {
            // PENTING: Kirim marketing_order_id (nilai '1' di database kamu)
            $this->selectedJobId = $log->marketing_order_id; 
            
            $this->isProcessing = true;
            $this->currentMenu = 'orders';
            
            session()->flash('message', 'Mode Edit Aktif untuk SAP: ' . $log->marketingOrder->sap_no);
        }
    } 
    // public function editLog($id) {
    //     $log = ProductionActivity::find($id);
        
    //     // Set ID Marketing Order agar form knitting bisa memuat data tech-nya
    //     $this->selectedJobId = $log->marketing_order_id;
        
    //     $this->isProcessing = true;
    //     $this->currentMenu = 'orders';
        
    //     session()->flash('message', 'Mode Edit Aktif: Mengambil data SAP #' . $log->marketingOrder->sap_no);
    // }

    public function saveNote() {
        $this->validate(['messageText' => 'required|min:3']);

        ProductionNote::create([
            'user_id' => Auth::id(),
            'message' => $this->messageText,
            'is_read' => false
        ]);

        $this->messageText = "";
        session()->flash('message', 'Pesan handover berhasil disimpan.');
    }

    public function markAsRead($id) {
        ProductionNote::where('id', $id)->update(['is_read' => true]);
    }

    public function with()
    {
        $user = Auth::user();
        $targetShift = \App\Models\Setting::where('key', 'target_minimal')->first()->value ?? 400;

        // 1. Hitung Pencapaian
        $totalKgToday = ProductionActivity::where('operator_id', $user->id)
            ->whereDate('created_at', today())
            ->sum('kg');
            
        $progress = ($targetShift > 0) ? min(($totalKgToday / $targetShift) * 100, 100) : 0;

        // 2. LOGIKA FILTER ANTREAN (Estafet)
        $orderQuery = MarketingOrder::query();

        // Gunakan variabel agar pengecekan lebih stabil
        $role = $user->role;

        if ($role === 'knitting') {
            // Knitting melihat yang baru dari Marketing
            $orderQuery->where('status', 'knitting');
        } 
        elseif ($role === 'dyeing') {
            // Dyeing melihat yang SUDAH SELESAI di Knitting
            $orderQuery->where('status', 'dyeing');
        }
        elseif ($role === 'relax-dryer') {
            // Menangkap lemparan dari Dyeing
            $orderQuery->where('status', 'relax-dryer');
        }
        elseif ($role === 'finishing') {
            // Menangkap lemparan dari Relax Dryer
            $orderQuery->where('status', 'finishing');
        }
        elseif ($role === 'stenter') {
            // Menangkap lemparan dari Finishing
            $orderQuery->where('status', 'stenter');
        }
        elseif ($role === 'tumbler') {
            // Menangkap lemparan dari Stenter
            $orderQuery->where('status', 'tumbler');
        }
        elseif ($role === 'fleece') {
            // Menangkap lemparan dari Tumbler
            $orderQuery->where('status', 'fleece');
        }
        elseif ($role === 'pengujian') {
            // Menangkap lemparan dari Fleece
            $orderQuery->where('status', 'pengujian');
        }
        elseif ($role === 'qe') {
            // Menangkap lemparan dari Pengujian
            $orderQuery->where('status', 'qe');
        }
        else {
            // Role lain tidak melihat apa-apa
            $orderQuery->whereRaw('1 = 0'); 
        }

        

        // 3. Eksekusi Query (Pisahkan count dan pagination)
        $totalknittingCount = (clone $orderQuery)->count();
        $workQueue = $orderQuery->latest()->paginate(10, ['*'], 'knittingPage');

        return [
            'currentMenu' => $this->currentMenu, 
            'targetShift' => $targetShift,
            'progress' => $progress,
            'totalKgToday' => $totalKgToday,
            'totalKnitting' => $totalknittingCount, // Gunakan hasil hitung di atas
            'totalDone' => ProductionActivity::where('operator_id', $user->id)->count(),
            'unreadNotes' => ProductionNote::with('user')->where('is_read', false)->latest()->get(),
            'allNotes' => ProductionNote::with('user')->latest()->paginate(10, ['*'], 'notesPage'),
            'workQueue' => $workQueue,
            'activities' => ProductionActivity::with('marketingOrder')
                ->where('operator_id', $user->id)
                ->when($this->search, fn($q) => $q->whereHas('marketingOrder', fn($sq) => $sq->where('sap_no', 'like', "%{$this->search}%")))
                ->latest()
                ->paginate(10),
        ];
    }
};
?>

<div>
    <div class="py-8 bg-transparent min-h-screen font-sans tracking-tighter italic text-left">
    <div class="max-w-6xl mx-auto px-4">
        <div class="min-h-[400px]">

            {{-- 1. TAMPILAN DASHBOARD --}}
            @if($currentMenu === 'dashboard')
                <div class="animate-in fade-in duration-500">
                    {{-- HEADER --}}
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
                        <div class="text-left">
                            <h2 class="text-3xl font-black uppercase tracking-tighter mkt-text leading-none italic">
                                @if(auth()->user()->role === 'knitting') 
                                    Knitting
                                @elseif(auth()->user()->role === 'dyeing') 
                                    Dyeing 
                                @elseif(auth()->user()->role === 'relax-dryer') 
                                    Relax Dryer 
                                @elseif(auth()->user()->role === 'finishing') 
                                    Finishing 
                                @elseif(auth()->user()->role === 'stenter') 
                                    Stenter
                                @elseif(auth()->user()->role === 'tumbler') 
                                    Tumbler
                                @elseif(auth()->user()->role === 'fleece') 
                                    Fleece
                                @elseif(auth()->user()->role === 'pengujian') 
                                    Pengujian
                                @elseif(auth()->user()->role === 'qe') 
                                    QE
                                @else 
                                    Operator 
                                @endif
                                <span class="text-red-600">Production</span>
                            </h2>
                            <div class="mt-2 flex items-center gap-2">
                                <div class="bg-slate-900 text-white px-3 py-1 rounded-lg shadow-lg">
                                    <p class="real-time-clock text-xs font-black tracking-widest leading-none">00:00:00</p>
                                </div>
                                <p class="real-time-date text-[10px] font-bold mkt-text-muted uppercase tracking-widest italic"></p>
                            </div>
                        </div>
                        
                        <div class="mkt-surface px-6 py-3 rounded-2xl shadow-sm border mkt-border flex items-center gap-4">
                            <div class="text-right italic">
                                <p class="text-[9px] font-black mkt-text-muted uppercase leading-none">Status Mesin</p>
                                <p class="text-xs font-black text-green-500 uppercase mt-1">Optimal Performance</p>
                            </div>
                            <div class="relative flex">
                                <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                            </div>
                        </div>
                    </div>

                    {{-- WIDGETS --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="md:col-span-2 bg-slate-900 p-8 rounded-[2.5rem] shadow-xl text-white relative overflow-hidden">
                            <div class="relative z-10 text-left">
                                <p class="text-[10px] font-black mkt-text-muted uppercase mb-1 italic">Pencapaian Target Shift</p>
                                <div class="flex justify-between items-end mb-4">
                                    <h4 class="text-6xl font-black leading-none italic">{{ number_format($progress, 1) }}%</h4>
                                    <div class="text-right leading-none">
                                        <p class="text-[10px] mkt-text-muted uppercase mb-1">Target: {{ $targetShift }} KG</p>
                                        <p class="text-xl font-black text-red-500 italic">{{ number_format($totalKgToday, 1) }} / {{ $targetShift }}</p>
                                    </div>
                                </div>
                                <div class="w-full bg-slate-800 h-4 rounded-full overflow-hidden border border-white/5">
                                    @php
                                        $barColor = match(true) {
                                            $progress < 50 => 'bg-red-600',
                                            $progress < 100 => 'bg-amber-500',
                                            default => 'bg-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.5)]'
                                        };
                                    @endphp
                                    <div class="{{ $barColor }} h-full rounded-full transition-all duration-700" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                            <div class="absolute -right-10 -bottom-10 opacity-5 text-[12rem] font-black italic">UNIT</div>
                        </div>

                        <div class="space-y-4">
                            <div class="mkt-surface p-6 rounded-[2rem] border mkt-border shadow-sm text-left">
                                <p class="text-[9px] font-black mkt-text-muted uppercase italic">Antrean Masuk</p>
                                <h4 class="text-4xl font-black text-blue-600 italic">{{ $totalKnitting }}</h4>
                            </div>
                            <div class="mkt-surface p-6 rounded-[2rem] border mkt-border shadow-sm text-left">
                                <p class="text-[9px] font-black mkt-text-muted uppercase italic">Total Selesai</p>
                                <h4 class="text-4xl font-black text-green-600 italic">{{ $totalDone }}</h4>
                            </div>
                        </div>
                    </div>

                    @if (session()->has('message'))
                        <div class="mb-6 p-4 bg-green-600 text-white rounded-2xl font-black uppercase text-xs italic shadow-lg animate-bounce">
                            ✅ {{ session('message') }}
                        </div>
                    @endif

                    {{-- HANDOVER --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-red-600 p-8 rounded-[2.5rem] shadow-lg flex flex-col justify-center items-center hover:bg-slate-900 transition-all cursor-pointer shadow-red-200">
                            <div class="text-4xl mb-2 animate-bounce">🚨</div>
                            <h4 class="text-white font-black uppercase text-sm italic">Emergency Call</h4>
                        </div>

                        <div class="md:col-span-2 mkt-surface p-6 rounded-[2.5rem] shadow-sm border mkt-border italic text-left">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-black uppercase mkt-text italic">Buat <span class="text-blue-600">Pesan Handover</span></h3>
                                <button wire:click="saveNote" class="bg-blue-600 text-white px-6 py-2 rounded-xl text-[10px] font-black uppercase hover:bg-black">Simpan</button>
                            </div>
                            <textarea wire:model="messageText" placeholder="Tulis kendala mesin..." class="w-full mkt-surface border-none rounded-2xl p-4 text-xs font-bold italic h-24 resize-none"></textarea>
                        </div>
                    </div>

                    {{-- PESAN TERBARU --}}
                    <div class="mkt-surface p-8 rounded-[3rem] shadow-sm border mkt-border italic text-left">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-black uppercase italic mkt-text leading-none">Pesan <span class="text-red-600">Terbaru</span></h3>
                            <span class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-[10px] font-black uppercase">{{ $unreadNotes->count() }} Baru</span>
                        </div>
                        <div class="space-y-3">
                            @forelse($unreadNotes as $note)
                                <div class="flex justify-between items-center p-4 mkt-surface rounded-2xl border-l-4 border-red-500 hover:mkt-input transition-all group">
                                    <div class="flex-1">
                                        <p class="text-[9px] font-black mkt-text-muted uppercase mb-1">
                                            {{ $note->user->name }} • {{ $note->created_at->format('H:i') }}
                                        </p>
                                        <p class="text-xs font-bold mkt-text italic">"{{ $note->message }}"</p>
                                    </div>
                                    <button wire:click="markAsRead({{ $note->id }})" class="opacity-0 group-hover:opacity-100 mkt-surface p-2 rounded-lg shadow-sm text-[9px] font-black text-green-600 uppercase transition-all">
                                        Selesai/Baca
                                    </button>
                                </div>
                            @empty
                                <p class="text-center py-10 mkt-text-muted font-black uppercase text-xs italic">Tidak ada pesan baru</p>
                            @endforelse
                        </div>
                    </div>
                </div>

            {{-- 2. TAMPILAN PERMINTAAN --}}
            @elseif($currentMenu === 'orders')
                <div class="animate-in slide-in-from-bottom-4 duration-500 text-left italic">
                    
                    {{-- SAKLAR UTAMA: JIKA TIDAK SEDANG PROSES (TAMPILKAN LIST) --}}
                    @if(!$isProcessing)
                        <div class="mb-8 flex justify-between items-end">
                            <div>
                                <h2 class="text-3xl font-black uppercase tracking-tighter mkt-text leading-none">
                                    DUNIATEX <span class="text-blue-600">Execution</span>
                                </h2>
                                <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-widest mt-2">
                                    Divisi: {{ auth()->user()->role }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            @if(auth()->user()->role === 'relax-dryer')
                                @forelse($workQueue as $job)
                                    @include('livewire.operator.partials.relax-dryer-table', ['job' => $job])
                                @empty
                                    <div class="text-center py-20 mkt-surface rounded-[3rem] border-2 border-dashed mkt-border italic">
                                        <p class="mkt-text-muted font-black uppercase text-xs">Tidak ada antrean relax dryer</p>
                                    </div>
                                @endforelse
                            @elseif(auth()->user()->role === 'knitting')
                            @forelse($workQueue as $job)
                                @include('livewire.operator.partials.knitting-table', ['job' => $job])
                            @empty
                                <div class="text-center py-20 mkt-surface rounded-[3rem] border-2 border-dashed mkt-border italic">
                                    <p class="mkt-text-muted font-black uppercase text-xs">Tidak ada antrean knitting</p>
                                </div>
                            @endforelse

                            @elseif(auth()->user()->role === 'dyeing')
                                @include('livewire.operator.partials.dyeing-table')

                            @elseif(auth()->user()->role === 'finishing')
                                @forelse($workQueue as $job)
                                    @include('livewire.operator.partials.finishing-table', ['job' => $job])
                                @empty
                                    <div class="text-center py-20 mkt-surface rounded-[3rem] border-2 border-dashed mkt-border italic">
                                        <p class="mkt-text-muted font-black uppercase text-xs">Tidak ada antrean finishing</p>
                                    </div>
                                @endforelse

                            @elseif(auth()->user()->role === 'stenter')
                                @forelse($workQueue as $job)
                                    @include('livewire.operator.partials.stenter-table', ['job' => $job])
                                @empty
                                    <div class="text-center py-20 mkt-surface rounded-[3rem] border-2 border-dashed mkt-border italic">
                                        <p class="mkt-text-muted font-black uppercase text-xs">Tidak ada antrean stenter</p>
                                    </div>
                                @endforelse
                            @elseif(auth()->user()->role === 'tumbler')
                                @forelse($workQueue as $job)
                                    @include('livewire.operator.partials.tumbler-table', ['job' => $job])
                                @empty
                                    <div class="text-center py-20 mkt-surface rounded-[3rem] border-2 border-dashed mkt-border italic">
                                        <p class="mkt-text-muted font-black uppercase text-xs">Tidak ada antrean tumbler</p>
                                    </div>
                                @endforelse
                            @elseif(auth()->user()->role === 'fleece')
                                @forelse($workQueue as $job)
                                    @include('livewire.operator.partials.fleece-table', ['job' => $job])
                                @empty
                                    <div class="text-center py-20 mkt-surface rounded-[3rem] border-2 border-dashed mkt-border italic">
                                        <p class="mkt-text-muted font-black uppercase text-xs">Tidak ada antrean fleece</p>
                                    </div>
                                @endforelse
                            @elseif(auth()->user()->role === 'pengujian')
                                @forelse($workQueue as $job)
                                    @include('livewire.operator.partials.pengujian-table', ['job' => $job])
                                @empty
                                    <div class="text-center py-20 mkt-surface rounded-[3rem] border-2 border-dashed mkt-border italic">
                                        <p class="mkt-text-muted font-black uppercase text-xs">Tidak ada antrean pengujian</p>
                                    </div>
                                @endforelse
                            @elseif(auth()->user()->role === 'qe')
                                @forelse($workQueue as $job)
                                    @include('livewire.operator.partials.qe-table', ['job' => $job])
                                @empty
                                    <div class="text-center py-20 mkt-surface rounded-[3rem] border-2 border-dashed mkt-border italic">
                                        <p class="mkt-text-muted font-black uppercase text-xs">Tidak ada antrean qe</p>
                                    </div>
                                @endforelse
                            @endif
                        </div>
                        
                        {{-- PAGINATION --}}
                        @if($workQueue->hasPages())
                            <div class="mt-4">
                                {{ $workQueue->links() }}
                            </div>
                        @endif

                    {{-- SAKLAR UTAMA: JIKA SEDANG PROSES (TAMPILKAN FORM DETAIL) --}}
                    @else
                        <div class="animate-in fade-in duration-500">
                            @if(auth()->user()->role === 'relax-dryer')
                                @livewire('operator.relax-dryer-form', ['orderId' => $selectedJobId])
                            @elseif(auth()->user()->role === 'finishing')
                                @livewire('operator.finishing-form', ['orderId' => $selectedJobId])
                            @elseif(auth()->user()->role === 'stenter')
                                @livewire('operator.stenter-form', ['orderId' => $selectedJobId])
                            @elseif(auth()->user()->role === 'tumbler')
                                @livewire('operator.tumbler-form', ['orderId' => $selectedJobId])
                            @elseif(auth()->user()->role === 'fleece')
                                @livewire('operator.fleece-form', ['orderId' => $selectedJobId])
                            @elseif(auth()->user()->role === 'pengujian')
                                @livewire('operator.pengujian-form', ['orderId' => $selectedJobId])
                            @elseif(auth()->user()->role === 'qe')
                                @livewire('operator.qe-form', ['orderId' => $selectedJobId])
                            @else
                                @livewire('operator.knitting-form', [
                                    'orderId' => $selectedJobId
                                ], key('knit-form-' . $selectedJobId))
                            @endif
                            
                            {{-- Tombol Batal --}}
                            <div class="mt-8 text-center">
                                <button wire:click="cancelProcess" class="text-[10px] font-black uppercase mkt-text-muted hover:text-red-600 transition-all flex items-center justify-center gap-2 mx-auto">
                                    ✕ Batalkan dan Kembali ke Antrean
                                </button>
                            </div>
                        </div>
                    @endif

                </div> {{-- Penutup div animate-in --}}

            {{-- 3. TAMPILAN RIWAYAT --}}
            @elseif($currentMenu === 'history')
                <div class="animate-in slide-in-from-bottom-4 duration-500 text-left italic font-black uppercase tracking-tighter">
                    <div class="mb-8 flex justify-between items-end">
                        <h2 class="text-3xl mkt-text leading-none italic">Production <span class="text-red-600">Logs</span></h2>
                        <input wire:model.live="search" type="text" placeholder="CARI SAP..." class="px-6 py-3 mkt-surface border mkt-border rounded-2xl text-xs outline-none focus:ring-2 focus:ring-red-600/20 italic">
                    </div>
                    
                    <div class="space-y-4">
                        @foreach($activities as $item)
                            <div class="mkt-surface p-6 rounded-[2.5rem] shadow-sm border mkt-border flex justify-between items-center group italic">
                                <div class="text-left flex items-center gap-4">
                                    <div class="text-2xl">
                                        @if($item->division_name === 'knitting') 🧶
                                        @elseif($item->division_name === 'dyeing') 🧪
                                        @else ⚙️ 
                                        @endif
                                    </div>
                                    
                                    <div>
                                        <span class="text-xs text-blue-600 italic">#{{ $item->marketingOrder->sap_no ?? 'N/A' }}</span>
                                        <h4 class="text-lg mkt-text leading-none italic">{{ $item->marketingOrder->art_no ?? 'UNKNOWN' }}</h4>
                                        {{-- Info Operator --}}
                                        <p class="text-[8px] mkt-text-muted mt-1 uppercase">
                                            PIC: {{ $item->technical_data['nama_input'] ?? 'OPERATOR' }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex gap-4 border-l mkt-border pl-10 items-center">
                                    <div class="text-right mr-4">
                                        <p class="text-[9px] mkt-text-muted mb-1 italic">Output</p>
                                        <p class="text-xl text-red-600 leading-none italic">
                                            {{ number_format($item->kg ?? 0, 1) }} KG
                                        </p>
                                    </div>

                                    {{-- ACTION BUTTONS --}}
                                    <div class="flex gap-2">
                                        {{-- Tombol Detail --}}
                                        <button wire:click="showOrderDetail({{ $item->marketing_order_id }})" 
                                                class="w-10 h-10 mkt-input text-slate-600 rounded-xl hover:bg-slate-900 hover:text-white transition-all flex items-center justify-center">
                                            🔍
                                        </button>

                                        {{-- Tombol Edit --}}
                                        <button wire:click="editLog({{ $item->id }})" 
                                                class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center">
                                            ✏️
                                        </button>
                                        
                                        {{-- Tombol Hapus --}}
                                        <button wire:click="deleteEntry({{ $item->id }})" 
                                                onclick="confirm('Hapus log ini? Menghapus log dapat mengubah alur status pesanan.') || event.stopImmediatePropagation()"
                                                class="w-10 h-10 bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition-all flex items-center justify-center">
                                            🗑️
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        <div class="mt-8">
                            {{ $activities->links() }}
                        </div>
                    </div>
                </div>
            

            {{-- 4. TAMPILAN ARSIP PESAN (PENTING: Gunakan @elseif) --}}
            @elseif($currentMenu === 'notes')
                <div class="animate-in slide-in-from-bottom-4 duration-500 text-left italic">
                    <div class="mb-8 flex justify-between items-end">
                        <div>
                            <h2 class="text-3xl font-black uppercase tracking-tighter mkt-text leading-none">
                                Message <span class="text-blue-600">Archive</span>
                            </h2>
                            <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-widest mt-2">Seluruh riwayat pesan dan handover</p>
                        </div>
                        <button wire:click="setMenu('dashboard')" class="bg-slate-900 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase italic shadow-lg">
                            ⬅ Kembali ke Dashboard
                        </button>
                    </div>

                    <div class="space-y-4">
                        @forelse($allNotes as $note)
                            <div class="mkt-surface p-6 rounded-[2.5rem] shadow-sm border mkt-border flex justify-between items-center group {{ $note->is_read ? 'opacity-60' : 'border-l-4 border-l-red-500' }}">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="text-[9px] font-black px-3 py-1 {{ $note->is_read ? 'mkt-input mkt-text-muted' : 'bg-red-600 text-white' }} rounded-full uppercase">
                                            {{ $note->is_read ? 'SUDAH DIBACA' : 'BARU' }}
                                        </span>
                                        <p class="text-[10px] font-black mkt-text-muted uppercase">
                                            {{ $note->user->name }} • {{ $note->created_at->format('d M Y | H:i') }} WIB
                                        </p>
                                    </div>
                                    <p class="text-sm font-bold mkt-text">"{{ $note->message }}"</p>
                                </div>

                                @if(!$note->is_read)
                                <button wire:click="markAsRead({{ $note->id }})" class="bg-green-50 text-green-600 px-4 py-2 rounded-xl text-[9px] font-black uppercase hover:bg-green-600 hover:text-white transition-all">
                                    Tandai Dibaca
                                </button>
                                @endif
                            </div>
                        @empty
                            <div class="py-20 text-center mkt-surface rounded-[3rem] border-2 border-dashed mkt-border">
                                <p class="mkt-text-muted font-black uppercase text-xs">Arsip Kosong</p>
                            </div>
                        @endforelse

                        <div class="mt-8">
                            {{ $allNotes->links() }}
                        </div>
                    </div>
                </div>
            @endif {{-- AKHIR DARI RANGKAIAN IF --}}
        </div>
    </div>
</div>

    {{-- MODAL DETAIL & INPUT ORDER --}}
@if($showModal && $selectedOrder)
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4 overflow-y-auto">
    <div class="mkt-surface rounded-[3rem] w-full max-w-4xl my-auto overflow-hidden shadow-2xl border-4 border-slate-900 animate-in zoom-in duration-300">
        
        {{-- Header Modal dengan Info SAP --}}
        <div class="bg-slate-900 p-8 flex justify-between items-center italic">
            <div>
                <h3 class="text-white text-2xl font-black uppercase tracking-tighter leading-none">
                    @if($showInputForm) INPUT HASIL PRODUKSI @else DETAIL ORDER MARKETING @endif 
                    <span class="text-red-600">#{{ $selectedOrder->sap_no }}</span>
                </h3>
                <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-widest mt-2">Internal Tracking ID: {{ $selectedOrder->id }}</p>
            </div>
            <button wire:click="closeModal" class="mkt-surface/10 hover:bg-red-600 p-3 rounded-2xl text-white transition-all">&times;</button>
        </div>

        <div class="p-8 mkt-surface/30">
            @if(!$showInputForm)
                {{-- TAMPILAN 1: DETAIL LENGKAP UNTUK EKSEKUSI OPERATOR --}}
                <div class="space-y-8">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="mkt-surface p-6 rounded-[2rem] border mkt-border shadow-sm italic">
                            <h3 class="text-red-600 font-black mb-4 border-b pb-2 uppercase text-xs flex items-center">
                                <span class="w-2 h-4 bg-red-600 mr-2 rounded-full"></span>I. Identity & Sales
                            </h3>
                            <div class="space-y-3 font-bold text-xs">
                                <div class="flex justify-between border-b mkt-border pb-1">
                                    <span class="mkt-text-muted uppercase">Pelanggan</span>
                                    <span class="mkt-text uppercase">{{ $selectedOrder->pelanggan }}</span>
                                </div>
                                <div class="flex justify-between border-b mkt-border pb-1">
                                    <span class="mkt-text-muted uppercase">Artikel No</span>
                                    <span class="mkt-text uppercase">{{ $selectedOrder->art_no }}</span>
                                </div>
                                <div class="flex justify-between border-b mkt-border pb-1">
                                    <span class="mkt-text-muted uppercase">Tanggal Order</span>
                                    <span class="mkt-text">{{ $selectedOrder->tanggal ? \Carbon\Carbon::parse($selectedOrder->tanggal)->format('d/m/Y') : '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="mkt-text-muted uppercase">Sales (Mkt)</span>
                                    <span class="mkt-text italic uppercase">{{ $selectedOrder->mkt ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mkt-surface p-6 rounded-[2rem] border mkt-border shadow-sm italic">
                            <h3 class="text-slate-900 font-black mb-4 border-b pb-2 uppercase text-xs flex items-center">
                                <span class="w-2 h-4 bg-slate-900 mr-2 rounded-full"></span>II. Technical Specs
                            </h3>
                            <div class="space-y-3 font-bold text-xs">
                                <div class="flex justify-between border-b mkt-border pb-1">
                                    <span class="mkt-text-muted uppercase">Material</span>
                                    <span class="mkt-text uppercase">{{ $selectedOrder->material }}</span>
                                </div>
                                <div class="flex justify-between border-b mkt-border pb-1">
                                    <span class="mkt-text-muted uppercase">Benang</span>
                                    <span class="mkt-text uppercase">{{ $selectedOrder->benang }}</span>
                                </div>
                                <div class="flex justify-between border-b mkt-border pb-1">
                                    <span class="mkt-text-muted uppercase">Konstruksi Greige</span>
                                    <span class="mkt-text italic uppercase">{{ $selectedOrder->konstruksi_greig }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-red-600 uppercase">Warna Finishing</span>
                                    <span class="text-red-600 uppercase font-black italic">{{ $selectedOrder->warna }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-900 p-8 rounded-[3rem] text-white shadow-xl shadow-slate-200">
                        <h3 class="text-red-500 font-black mb-6 uppercase italic tracking-tighter text-center underline underline-offset-8">Production Specification Matrix</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center font-bold">
                            <div class="border-r border-white/10 italic">
                                <p class="text-[9px] mkt-text-muted uppercase mb-1">Kelompok Kain</p>
                                <p class="text-lg uppercase">{{ $selectedOrder->kelompok_kain }}</p>
                            </div>
                            <div class="border-r border-white/10 italic">
                                <p class="text-[9px] mkt-text-muted uppercase mb-1">Lebar / Gramasi</p>
                                <p class="text-lg">{{ $selectedOrder->target_lebar ?? '0' }}" / {{ $selectedOrder->target_gramasi ?? '0' }}</p>
                            </div>
                            <div class="border-r border-white/10 italic">
                                <p class="text-[9px] mkt-text-muted uppercase mb-1">Belah / Bulat</p>
                                <p class="text-lg uppercase">{{ $selectedOrder->belah_bulat }}</p>
                            </div>
                            <div class="italic">
                                <p class="text-[9px] mkt-text-muted uppercase mb-1">Handfeel</p>
                                <p class="text-lg uppercase">{{ $selectedOrder->handfeel }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 italic font-black">
                        <div class="bg-blue-600 text-white p-8 rounded-[2.5rem] shadow-lg flex justify-between items-center">
                            <p class="text-xs uppercase tracking-[0.2em]">Total Roll Target</p>
                            <h4 class="text-4xl underline decoration-4 underline-offset-8">{{ $selectedOrder->roll_target ?? '0' }}</h4>
                        </div>
                        <div class="bg-emerald-600 text-white p-8 rounded-[2.5rem] shadow-lg flex justify-between items-center">
                            <p class="text-xs uppercase tracking-[0.2em]">Total Net Weight (KG)</p>
                            <h4 class="text-4xl underline decoration-4 underline-offset-8">{{ number_format($selectedOrder->kg_target, 1) }}</h4>
                        </div>
                    </div>

                    <div class="mkt-surface p-8 rounded-[3rem] border-l-[12px] border-red-600 shadow-sm space-y-4 text-left italic">
                        <div>
                            <p class="text-[10px] font-black mkt-text-muted uppercase mb-2 tracking-widest">Special Treatment & Instructions:</p>
                            <p class="text-lg font-black mkt-text uppercase underline decoration-red-600/30 underline-offset-4">{{ $selectedOrder->treatment_khusus ?? '-' }}</p>
                        </div>
                        <hr class="mkt-border">
                        <div>
                            <p class="text-[10px] font-black mkt-text-muted uppercase mb-2 tracking-widest">Internal Marketing Notes:</p>
                            <p class="text-xs font-bold text-slate-600 leading-relaxed mkt-surface p-4 rounded-2xl italic">"{{ $selectedOrder->keterangan_artikel ?? 'No additional internal notes provided.' }}"</p>
                        </div>
                    </div>

                   <div class="pt-4">
                        @php 
                            // 1. Tentukan route berdasarkan role user yang sedang login
                            $role = auth()->user()->role;
                            $targetRoute = 'operator.' . $role; 

                            // 2. Fallback sederhana jika role tidak terdaftar di route
                            if (!Route::has($targetRoute)) {
                                $targetRoute = 'operator.knitting'; 
                            }
                        @endphp

                        {{-- Gunakan variabel $targetRoute di dalam route() --}}
                        <a href="{{ route($targetRoute, ['sap' => $selectedOrder->sap_no]) }}" 
                            class="w-full bg-slate-900 text-white py-6 rounded-[2rem] font-black uppercase text-sm flex items-center justify-center gap-3 hover:bg-blue-600 transition-all shadow-xl tracking-[0.2em]">
                            
                            {{-- Nama tombol otomatis berubah sesuai divisi --}}
                            ⚙️ MULAI EKSEKUSI {{ strtoupper($role) }}
                        </a>
                    </div>
                </div>
        
            @else
                {{-- TAMPILAN 2: FORM INPUT HASIL --}}
                <form wire:submit.prevent="submitProduction" class="italic text-left font-black">
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div class="col-span-2 bg-blue-50 p-4 rounded-2xl flex justify-between items-center">
                            <p class="text-xs text-blue-800 uppercase italic">Shift Kerja Aktif:</p>
                            <span class="bg-blue-600 text-white px-4 py-1 rounded-full text-xs italic tracking-widest uppercase font-black">Shift {{ $shift }}</span>
                        </div>

                        <div>
                            <label class="text-[10px] mkt-text-muted uppercase ml-2">Total Berat (KG)</label>
                            <input type="number" step="0.1" wire:model="qty_kg" 
                                class="w-full mkt-input border-none rounded-2xl p-4 text-xl font-black text-red-600 {{ $errors->has('qty_kg') ? 'ring-2 ring-red-500' : '' }}">
                            @error('qty_kg') <p class="text-[9px] text-red-500 mt-1 ml-2 font-bold uppercase italic">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-[10px] mkt-text-muted uppercase ml-2">Jumlah Roll</label>
                            <input type="number" wire:model="qty_roll" required
                                class="w-full mkt-input border-none rounded-2xl p-4 text-xl font-black mkt-text focus:ring-4 focus:ring-slate-100 italic" placeholder="0">
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button type="button" wire:click="$set('showInputForm', false)" 
                            class="flex-1 bg-slate-200 text-slate-600 py-4 rounded-2xl font-black uppercase text-xs">KEMBALI</button>
                        <button type="submit" 
                            class="flex-[2] bg-green-600 text-white py-4 rounded-2xl font-black uppercase text-xs shadow-lg shadow-green-200 hover:bg-black transition-all">
                            SIMPAN HASIL PRODUKSI ✅
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endif
</div>

<script>
    function updateClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        const options = { day: '2-digit', month: 'short', year: 'numeric' };
        
        const clockEl = document.getElementById('real-time-clock');
        const dateEl = document.getElementById('real-time-date');
        
        if(clockEl) clockEl.textContent = `${h}:${m}:${s}`;
        if(dateEl) dateEl.textContent = now.toLocaleDateString('id-ID', options).toUpperCase();
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>