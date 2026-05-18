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
    public $machineStatus = "running"; // running, downtime, maintenance

    // Properti kontrol tampilan
    public $isProcessing = false; 
    public $selectedJobId = null;

    public $selectedLog = null;
    public $showEditModal = false;
    public $productionHistory = [];

    public function mount()
    {
        $menuFromUrl = request()->query('menu');
        $this->currentMenu = $menuFromUrl ?? 'dashboard';

        // Ambil status mesin terakhir dari database
        $division = auth()->user()->role;
        $this->machineStatus = \App\Models\Setting::where('key', 'machine_status_' . $division)->value('value') ?? 'running';
    }

    // --- FUNGSI START PROCESS ---
    public function startProcess($id) {
        $service = app(\App\Services\ProductionService::class);
        $service->startJob($id, auth()->id());
        
        $this->selectedJobId = $id;
        $this->isProcessing = true;
    }

    public function startProcessAndRedirect($id) {
        $service = app(\App\Services\ProductionService::class);
        $repo = app(\App\Repositories\OrderRepository::class);
        
        $order = MarketingOrder::find($id);
        if ($order) {
            $service->startJob($id, auth()->id());

            // Tentukan route berdasarkan role user
            $middlePath = ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'stenter', 'tumbler', 'fleece', 'finishing'];
            $role = auth()->user()->role;
            $targetRoute = in_array($role, $middlePath) ? 'operator.single' : 'operator.' . $role;

            if (!\Illuminate\Support\Facades\Route::has($targetRoute)) $targetRoute = 'operator.logbook';

            return redirect()->route($targetRoute, ['artikel' => $order->art_no]);
        }
    }

    public function takeOverProcessAndRedirect($id) {
        $service = app(\App\Services\ProductionService::class);
        $order = MarketingOrder::find($id);
        
        if ($order) {
            $service->takeOverJob($id, auth()->id(), auth()->user()->role);

            $middlePath = ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'stenter', 'tumbler', 'fleece', 'finishing'];
            $role = auth()->user()->role;
            $targetRoute = in_array($role, $middlePath) ? 'operator.single' : 'operator.' . $role;

            if (!\Illuminate\Support\Facades\Route::has($targetRoute)) $targetRoute = 'operator.logbook';

            return redirect()->route($targetRoute, ['artikel' => $order->art_no]);
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
                'art_no' => $order->art_no,
                'details' => "Menghapus data: {$log->kg} KG / {$log->roll} Roll",
            ]);

            // 2. Kembalikan Status Order ke Antrean Divisi Saat Ini
            if ($order) {
                $order->update(['status' => $role]);
            }

            // 3. Hapus Data
            $log->delete();

            session()->flash('message', "Log Artikel #{$order->art_no} berhasil dihapus & dicatat di sistem.");
            
            $this->dispatch('show-toast', [
                'message' => 'Penghapusan berhasil dicatat oleh sistem.',
                'type' => 'warning'
            ]);
        }
    }

    public function cancelProcess() {
        if ($this->selectedJobId) {
            $service = app(\App\Services\ProductionService::class);
            $service->cancelJob($this->selectedJobId, auth()->id());
        }

        $this->isProcessing = false;
        $this->selectedJobId = null;
    }

    public function sendMessage() {
        $this->validate(['messageText' => 'required|min:5']);

        ProductionNote::create([
            'user_id' => auth()->id(),
            'message' => $this->messageText,
            'is_read' => false
        ]);

        $this->messageText = "";
        session()->flash('message', 'Pesan berhasil dikirim ke seluruh tim! 📩');
    }

    public function markAsRead($id) {
        $note = ProductionNote::find($id);
        if ($note) {
            $note->update(['is_read' => true]);
        }
    }

    public function setMachineStatus($status) {
        $this->machineStatus = $status;
        $division = auth()->user()->role;

        // Simpan ke Setting agar bisa dibaca Admin secara LIVE
        \App\Models\Setting::updateOrCreate(
            ['key' => 'machine_status_' . $division],
            [
                'value' => $status,
                'group' => 'production'
            ]
        );
        
        // Log ke ActivityLog untuk audit trail
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'MACHINE_STATUS_CHANGE',
            'model' => 'MACHINE',
            'description' => "Operator " . auth()->user()->name . " (Divisi: " . strtoupper($division) . ") mengubah status mesin menjadi: " . strtoupper($status),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        $this->dispatch('show-toast', [
            'message' => 'Status mesin ' . strtoupper($division) . ' diperbarui: ' . strtoupper($status),
            'type' => 'info'
        ]);
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
        $this->selectedLog = null; 
        if ($this->selectedOrder) {
            $this->productionHistory = ProductionActivity::with('operator')
                ->where('marketing_order_id', $id)
                ->orderBy('created_at', 'asc')
                ->get();
            $this->showModal = true;
        }
    }

    public function closeModal() {
        $this->showModal = false;
        $this->selectedOrder = null;
        $this->selectedLog = null;
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
                " menginput " . $this->qty_kg . " KG untuk Artikel #" . $this->selectedOrder->art_no
            );
        }

        try {
            $role = auth()->user()->role;
            $service = app(\App\Services\ProductionService::class);

            $service->submitOperatorActivity(
                $this->selectedOrder->id,
                auth()->id(),
                $role,
                $this->qty_kg,
                $this->qty_roll,
                $this->shift,
                $this->operator_name,
                $this->selectedLog ? $this->selectedLog->id : null
            );

            $message = $this->selectedLog 
                ? 'Data produksi berhasil diperbarui! ✏️' 
                : 'Berhasil! Data dikirim ke divisi berikutnya. 🚀';

            session()->flash('message', $message);
        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'message' => 'Gagal menyimpan data produksi: ' . $e->getMessage(),
                'type' => 'error'
            ]);
            return;
        }

        $this->reset(['qty_kg', 'qty_roll', 'operator_name', 'showInputForm', 'showModal', 'selectedLog', 'isProcessing', 'productionHistory']);
    }

    public function viewLogDetail($id) {
        $this->selectedLog = ProductionActivity::with(['marketingOrder', 'operator'])->find($id);
        
        $this->selectedOrder = $this->selectedLog->marketingOrder;
        
        // Ambil riwayat produksi untuk pesanan ini
        $this->productionHistory = ProductionActivity::with('operator')
            ->where('marketing_order_id', $this->selectedOrder->id)
            ->orderBy('created_at', 'asc')
            ->get();

        $this->showModal = true;

        $this->dispatch('show-toast', [
            'message' => "Membuka Detail Artikel: " . $this->selectedOrder->art_no,
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
            
            session()->flash('message', 'Mode Edit Aktif untuk Artikel: ' . $log->marketingOrder->art_no);
        }
    } 
    // public function editLog($id) {
    //     $log = ProductionActivity::find($id);
        
    //     // Set ID Marketing Order agar form knitting bisa memuat data tech-nya
    //     $this->selectedJobId = $log->marketing_order_id;
        
    //     $this->isProcessing = true;
    //     $this->currentMenu = 'orders';
        
    //     session()->flash('message', 'Mode Edit Aktif: Mengambil data Artikel #' . $log->marketingOrder->art_no);
    // }


    public function with()
    {
        $user = Auth::user();
        $orderRepo = app(\App\Repositories\OrderRepository::class);
        $activityRepo = app(\App\Repositories\ActivityRepository::class);

        $targetShift = \App\Models\Setting::where('key', 'target_minimal')->first()->value ?? 400;

        // 1. Hitung Pencapaian
        $totalKgToday = ProductionActivity::where('operator_id', $user->id)
            ->whereDate('created_at', today())
            ->sum('kg');
            
        $progress = ($targetShift > 0) ? min(($totalKgToday / $targetShift) * 100, 100) : 0;

        // 2. Gunakan Repository untuk Queue & History
        $workQueue = $orderRepo->getQueue($user->role, $this->search);
        $totalKnitting = $workQueue->total();

        return [
            'currentMenu' => $this->currentMenu, 
            'targetShift' => $targetShift,
            'progress' => $progress,
            'totalKgToday' => $totalKgToday,
            'totalKnitting' => $totalKnitting,
            'totalDone' => ProductionActivity::where('operator_id', $user->id)->count(),
            'workQueue' => $workQueue,
            'activities' => $activityRepo->getOperatorHistory($user->id, $this->search),
            'recentNotes' => ProductionNote::with('user')->latest()->take(3)->get(),
            'allNotes' => ProductionNote::with('user')->latest()->paginate(10),
            'suggestions' => empty($this->search) ? [] : MarketingOrder::where('art_no', 'like', '%'.$this->search.'%')->take(5)->get(),
        ];
    }
};
?>
<div>
    <div class="py-8 bg-transparent min-h-screen font-sans tracking-tighter italic text-left">
    <div class="max-w-[1920px] w-full mx-auto px-4 md:px-8 lg:px-12">
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
                                    Compactor / Heat Setting 
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
                                <div class="mkt-surface-alt backdrop-blur-sm px-3 py-1.5 rounded-lg shadow-lg border mkt-border">
                                    <p class="real-time-clock text-xs font-black tracking-widest leading-none text-indigo-400 drop-shadow-[0_0_5px_rgba(99,102,241,0.5)]">00:00:00</p>
                                </div>
                                <p class="real-time-date text-[10px] font-bold text-slate-400 uppercase tracking-widest italic"></p>
                            </div>
                        </div>
                        
                        <div class="mkt-surface-alt backdrop-blur-md px-6 py-3 rounded-2xl shadow-sm border mkt-border flex items-center gap-4">
                            <div class="text-right italic">
                                <p class="text-[9px] font-black text-slate-400 uppercase leading-none">Status Mesin</p>
                                <p class="text-xs font-black {{ $machineStatus === 'running' ? 'text-green-500' : ($machineStatus === 'downtime' ? 'text-amber-500' : 'text-indigo-600') }} uppercase mt-1">
                                    {{ $machineStatus === 'running' ? 'Optimal Performance' : ($machineStatus === 'downtime' ? 'Machine Downtime' : 'Under Maintenance') }}
                                </p>
                            </div>
                            <div class="relative flex">
                                <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full {{ $machineStatus === 'running' ? 'bg-green-400' : ($machineStatus === 'downtime' ? 'bg-amber-400' : 'bg-indigo-400') }} opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 {{ $machineStatus === 'running' ? 'bg-green-500' : ($machineStatus === 'downtime' ? 'bg-amber-500' : 'bg-indigo-600') }}"></span>
                            </div>
                        </div>
                    </div>


                    {{-- WIDGETS --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="md:col-span-2 mkt-surface p-8 rounded-[2.5rem] shadow-2xl relative overflow-hidden border mkt-border">
                            <div class="relative z-10 text-left">
                                <p class="text-[10px] font-black mkt-text-muted uppercase mb-1 italic">Pencapaian Target Shift</p>
                                <div class="flex justify-between items-end mb-4">
                                     <h4 class="text-6xl font-black leading-none italic mkt-text">{{ number_format($progress, 1) }}%</h4>
                                    <div class="text-right leading-none">
                                        <p class="text-[10px] mkt-text-muted uppercase mb-1">Target: {{ $targetShift }} KG</p>
                                        <p class="text-xl font-black text-red-500 italic">{{ number_format($totalKgToday, 1) }} / {{ $targetShift }}</p>
                                    </div>
                                </div>
                                <div class="relative flex items-center justify-center mt-4 group">
                                    {{-- SVG Gauge with Scale Marks --}}
                                    <svg class="w-56 h-28" viewBox="0 0 100 50">
                                        {{-- Background Path --}}
                                        <path d="M 10 50 A 40 40 0 0 1 90 50" fill="none" stroke="var(--mkt-border)" stroke-width="8" stroke-linecap="round" />
                                        
                                        {{-- Progress Path with Glow --}}
                                        <path d="M 10 50 A 40 40 0 0 1 90 50" fill="none" 
                                            stroke="{{ $progress < 100 ? '#4f46e5' : '#10b981' }}" 
                                            stroke-width="8" stroke-linecap="round" 
                                            stroke-dasharray="{{ $progress * 1.25 }}, 125" 
                                            class="transition-all duration-1000" />

                                        {{-- Scale Marks (Ticks) --}}
                                        @foreach(range(0, 10) as $i)
                                            @php 
                                                $angle = 180 + ($i * 18); 
                                                $r1 = 32; $r2 = 38;
                                                $x1 = 50 + $r1 * cos(deg2rad($angle));
                                                $y1 = 50 + $r1 * sin(deg2rad($angle));
                                                $x2 = 50 + $r2 * cos(deg2rad($angle));
                                                $y2 = 50 + $r2 * sin(deg2rad($angle));
                                            @endphp
                                            <line x1="{{ $x1 }}" y1="{{ $y1 }}" x2="{{ $x2 }}" y2="{{ $y2 }}" stroke="var(--mkt-text)" stroke-opacity="0.2" stroke-width="0.5" />
                                        @endforeach
                                    </svg>
                                    
                                    <div class="absolute bottom-2 text-center">
                                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none">Actual Output</p>
                                        <p class="text-3xl font-black mkt-text italic leading-none mt-1 drop-shadow-lg">
                                            {{ number_format($totalKgToday, 1) }} 
                                            <span class="text-[10px] text-red-600 block mt-1">KILOGRAM</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute -right-10 -bottom-10 opacity-5 text-[12rem] font-black italic select-none">UNIT</div>
                        </div>

                        <div class="space-y-4">
                            <div class="mkt-surface-alt backdrop-blur-md p-6 rounded-[2rem] border mkt-border shadow-lg text-left hover:scale-[1.02] transition-transform duration-300">
                                <p class="text-[9px] font-black text-slate-400 uppercase italic">Antrean Masuk</p>
                                <h4 class="text-4xl font-black text-indigo-500 italic drop-shadow-[0_0_10px_rgba(99,102,241,0.3)]">{{ $totalKnitting }}</h4>
                            </div>
                            <div class="mkt-surface-alt backdrop-blur-md p-6 rounded-[2rem] border mkt-border shadow-lg text-left hover:scale-[1.02] transition-transform duration-300">
                                <p class="text-[9px] font-black text-slate-400 uppercase italic">Total Selesai</p>
                                <h4 class="text-4xl font-black text-emerald-500 italic drop-shadow-[0_0_10px_rgba(16,185,129,0.3)]">{{ $totalDone }}</h4>
                            </div>
                        </div>
                    </div>

                    {{-- MACHINE STATUS CONTROL (MOVED TO MIDDLE) --}}
                    <div class="mkt-surface p-8 rounded-[3rem] border mkt-border shadow-2xl mb-8 flex flex-col md:flex-row items-center justify-between gap-8 relative overflow-hidden">
                        <div class="relative z-10">
                            <h3 class="text-sm font-black uppercase italic mkt-text mb-1">Machine Control Center</h3>
                            <p class="text-[10px] font-bold mkt-text-muted uppercase italic">Kelola status operasional mesin produksi Anda secara real-time</p>
                        </div>
                        
                        <div class="flex gap-4 relative z-10">
                            <button wire:click="setMachineStatus('running')" 
                                class="flex flex-col items-center gap-2 p-4 rounded-3xl transition-all duration-300 min-w-[120px] {{ $machineStatus === 'running' ? 'bg-gradient-to-br from-green-600/30 to-emerald-600/5 border-green-500 shadow-[0_0_20px_rgba(34,197,94,0.4)] scale-105' : 'mkt-surface-alt mkt-border hover:opacity-80' }} border">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl {{ $machineStatus === 'running' ? 'bg-green-600 text-white' : 'mkt-surface-alt text-slate-500' }}">⚡</div>
                                <span class="text-[9px] font-black uppercase italic mkt-text">Running</span>
                            </button>
                            
                            <button wire:click="setMachineStatus('downtime')" 
                                class="flex flex-col items-center gap-2 p-4 rounded-3xl transition-all duration-300 min-w-[120px] {{ $machineStatus === 'downtime' ? 'bg-gradient-to-br from-amber-600/30 to-yellow-600/5 border-amber-500 shadow-[0_0_20px_rgba(245,158,11,0.4)] scale-105' : 'mkt-surface-alt mkt-border hover:opacity-80' }} border">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl {{ $machineStatus === 'downtime' ? 'bg-amber-500 text-white' : 'mkt-surface-alt text-slate-500' }}">⚠️</div>
                                <span class="text-[9px] font-black uppercase italic mkt-text">Downtime</span>
                            </button>
                            
                            <button wire:click="setMachineStatus('maintenance')" 
                                class="flex flex-col items-center gap-2 p-4 rounded-3xl transition-all duration-300 min-w-[120px] {{ $machineStatus === 'maintenance' ? 'bg-gradient-to-br from-indigo-600/30 to-violet-600/5 border-indigo-500 shadow-[0_0_20px_rgba(79,70,229,0.4)] scale-105' : 'mkt-surface-alt mkt-border hover:opacity-80' }} border">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl {{ $machineStatus === 'maintenance' ? 'bg-indigo-600 text-white' : 'mkt-surface text-slate-500' }}">🛠️</div>
                                <span class="text-[9px] font-black uppercase italic mkt-text">Repair</span>
                            </button>
                        </div>
                        <div class="absolute -right-20 -top-20 text-[15rem] font-black italic opacity-5 select-none tracking-tighter mkt-text">STATUS</div>
                    </div>

                    {{-- SHIFT COMMUNICATION (RESTORED) --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                        <div class="lg:col-span-2 mkt-surface p-8 rounded-[3rem] border mkt-border shadow-2xl">
                            <h3 class="text-sm font-black uppercase italic mkt-text mb-6 flex items-center gap-2">
                                <span class="w-2 h-4 bg-indigo-600 rounded-full shadow-[0_0_10px_rgba(99,102,241,0.5)]"></span>
                                Pesan Antar Shift / Kendala
                            </h3>
                            <div class="space-y-4 mb-6">
                                @forelse($recentNotes as $note)
                                    <div class="mkt-surface-alt p-4 rounded-2xl border mkt-border flex justify-between items-start hover:mkt-surface transition-colors duration-300">
                                        <div class="flex-1">
                                            <p class="text-[8px] font-black mkt-text-muted uppercase mb-1">
                                                {{ $note->user->name }} • {{ $note->created_at->diffForHumans() }}
                                            </p>
                                            <p class="text-xs font-bold mkt-text">"{{ $note->message }}"</p>
                                        </div>
                                        @if(!$note->is_read)
                                            <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse shadow-[0_0_10px_rgba(239,68,68,0.5)]"></span>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-[10px] text-slate-500 font-bold uppercase italic py-4">Belum ada pesan terbaru.</p>
                                @endforelse
                            </div>
                            
                            <form wire:submit.prevent="sendMessage" class="relative group">
                                <input wire:model="messageText" type="text" placeholder="Tulis pesan atau laporkan kendala mesin di sini..." 
                                    class="w-full mkt-surface-alt border mkt-border rounded-2xl px-6 py-4 text-xs font-bold pr-32 focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition-all italic mkt-text placeholder-slate-400">
                                <button type="submit" class="absolute right-2 top-2 bg-indigo-600 text-white px-5 py-2 rounded-xl text-[9px] font-black uppercase italic shadow-lg hover:bg-indigo-700 hover:shadow-indigo-500/30 transition-all">
                                    KIRIM PESAN 📩
                                </button>
                            </form>
                            @error('messageText') <p class="text-[9px] text-red-500 font-bold mt-2 uppercase italic">{{ $message }}</p> @enderror
                        </div>

                        <div class="mkt-surface p-8 rounded-[3rem] border mkt-border shadow-2xl flex flex-col justify-center items-center text-center group">
                            <div class="text-4xl mb-4 group-hover:scale-110 transition-transform duration-300">📂</div>
                            <h4 class="text-sm font-black uppercase italic mkt-text mb-2">Arsip Pesan</h4>
                            <p class="text-[9px] font-bold mkt-text-muted uppercase mb-6">Lihat seluruh riwayat komunikasi antar shift</p>
                            <button wire:click="setMenu('notes')" class="w-full mkt-surface-alt border mkt-border py-3 rounded-2xl text-[10px] font-black uppercase italic hover:bg-indigo-600 hover:text-white hover:border-transparent transition-all duration-300 shadow-md">
                                BUKA ARSIP
                            </button>
                        </div>
                    </div>

                    @if (session()->has('message'))
                        <div class="mb-6 p-4 bg-green-600 text-white rounded-2xl font-black uppercase text-xs italic shadow-lg animate-bounce">
                            ✅ {{ session('message') }}
                        </div>
                    @endif

                </div>

            {{-- 2. TAMPILAN PERMINTAAN --}}
            @elseif($currentMenu === 'orders')
                <div class="animate-in slide-in-from-bottom-4 duration-500 text-left italic">
                    
                    {{-- SAKLAR UTAMA: JIKA TIDAK SEDANG PROSES (TAMPILKAN LIST) --}}
                    @if(!$isProcessing)
                        <div class="mb-8 flex justify-between items-end">
                            <div>
                                <h2 class="text-3xl font-black uppercase tracking-tighter mkt-text leading-none">
                                    DUNIATEX <span class="text-indigo-600">Execution</span>
                                </h2>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">
                                    Divisi: {{ auth()->user()->role }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            @if(auth()->user()->role === 'relax-dryer')
                                @forelse($workQueue as $job)
                                    @include('livewire.operator.partials.relax-dryer-table', ['job' => $job])
                                @empty
                                    <div class="text-center py-20 mkt-surface-alt backdrop-blur-md rounded-[3rem] border-2 border-dashed border-white/10 italic">
                                        <p class="text-slate-400 font-black uppercase text-xs">Tidak ada antrean relax dryer</p>
                                    </div>
                                @endforelse
                            @elseif(auth()->user()->role === 'knitting')
                            @forelse($workQueue as $job)
                                @include('livewire.operator.partials.knitting-table', ['job' => $job])
                            @empty
                                <div class="text-center py-20 mkt-surface-alt backdrop-blur-md rounded-[3rem] border-2 border-dashed border-white/10 italic">
                                    <p class="text-slate-400 font-black uppercase text-xs">Tidak ada antrean knitting</p>
                                </div>
                            @endforelse

                            @elseif(auth()->user()->role === 'dyeing')
                                @include('livewire.operator.partials.dyeing-table')

                            @elseif(auth()->user()->role === 'finishing')
                                @forelse($workQueue as $job)
                                    @include('livewire.operator.partials.finishing-table', ['job' => $job])
                                @empty
                                    <div class="text-center py-20 mkt-surface-alt backdrop-blur-md rounded-[3rem] border-2 border-dashed border-white/10 italic">
                                        <p class="text-slate-400 font-black uppercase text-xs">Tidak ada antrean finishing</p>
                                    </div>
                                @endforelse

                            @elseif(auth()->user()->role === 'stenter')
                                @forelse($workQueue as $job)
                                    @include('livewire.operator.partials.stenter-table', ['job' => $job])
                                @empty
                                    <div class="text-center py-20 mkt-surface-alt backdrop-blur-md rounded-[3rem] border-2 border-dashed border-white/10 italic">
                                        <p class="text-slate-400 font-black uppercase text-xs">Tidak ada antrean stenter</p>
                                    </div>
                                @endforelse
                            @elseif(auth()->user()->role === 'tumbler')
                                @forelse($workQueue as $job)
                                    @include('livewire.operator.partials.tumbler-table', ['job' => $job])
                                @empty
                                    <div class="text-center py-20 mkt-surface-alt backdrop-blur-md rounded-[3rem] border-2 border-dashed border-white/10 italic">
                                        <p class="text-slate-400 font-black uppercase text-xs">Tidak ada antrean tumbler</p>
                                    </div>
                                @endforelse
                            @elseif(auth()->user()->role === 'fleece')
                                @forelse($workQueue as $job)
                                    @include('livewire.operator.partials.fleece-table', ['job' => $job])
                                @empty
                                    <div class="text-center py-20 mkt-surface-alt backdrop-blur-md rounded-[3rem] border-2 border-dashed border-white/10 italic">
                                        <p class="text-slate-400 font-black uppercase text-xs">Tidak ada antrean fleece</p>
                                    </div>
                                @endforelse
                            @elseif(auth()->user()->role === 'pengujian')
                                @forelse($workQueue as $job)
                                    @include('livewire.operator.partials.pengujian-table', ['job' => $job])
                                @empty
                                    <div class="text-center py-20 mkt-surface-alt backdrop-blur-md rounded-[3rem] border-2 border-dashed border-white/10 italic">
                                        <p class="text-slate-400 font-black uppercase text-xs">Tidak ada antrean pengujian</p>
                                    </div>
                                @endforelse
                            @elseif(auth()->user()->role === 'qe')
                                @forelse($workQueue as $job)
                                    @include('livewire.operator.partials.qe-table', ['job' => $job])
                                @empty
                                    <div class="text-center py-20 mkt-surface-alt backdrop-blur-md rounded-[3rem] border-2 border-dashed border-white/10 italic">
                                        <p class="text-slate-400 font-black uppercase text-xs">Tidak ada antrean qe</p>
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
                                <button wire:click="cancelProcess" class="text-[10px] font-black uppercase text-slate-400 hover:text-red-600 transition-all flex items-center justify-center gap-2 mx-auto">
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
                        <div class="relative" x-data="{ open: false }">
                            <input wire:model.live="search" 
                                   @focus="open = true" 
                                   @click.away="open = false"
                                   type="text" 
                                   placeholder="CARI NO ARTIKEL..." 
                                   class="px-6 py-3 mkt-surface-alt border mkt-border rounded-2xl text-xs mkt-text outline-none focus:ring-2 focus:ring-red-600/20 italic">
                            
                            <div x-show="open && $wire.search.length > 0" 
                                 class="absolute z-10 w-full mt-2 mkt-surface border mkt-border rounded-xl shadow-lg overflow-hidden"
                                 style="display: none;">
                                @forelse($suggestions as $suggestion)
                                    <div class="px-4 py-2 hover:mkt-surface-alt cursor-pointer" 
                                         @click="$wire.set('search', '{{ $suggestion->art_no }}'); open = false;">
                                        <p class="text-xs font-bold mkt-text">{{ $suggestion->art_no }}</p>
                                        <p class="text-[10px] text-slate-400 uppercase">{{ $suggestion->pelanggan }}</p>
                                    </div>
                                @empty
                                    <div class="px-4 py-2 text-slate-400 text-xs uppercase">Tidak ada hasil.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        @forelse($activities as $item)
                            <div class="mkt-surface backdrop-blur-md p-6 rounded-[2.5rem] shadow-sm border mkt-border flex justify-between items-center group italic">
                                <div class="text-left flex items-center gap-4">
                                    <div class="text-2xl">
                                        @if($item->division_name === 'knitting') 🧶
                                        @elseif($item->division_name === 'dyeing') 🧪
                                        @else ⚙️ 
                                        @endif
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-lg mkt-text font-black leading-none italic">{{ $item->marketingOrder->art_no ?? 'UNKNOWN' }}</h4>
                                        <p class="text-[9px] text-indigo-600 font-bold mt-1 uppercase italic tracking-widest">ARTIKEL NO: {{ $item->marketingOrder->art_no ?? 'N/A' }}</p>
                                        {{-- Info Operator --}}
                                        <p class="text-[8px] text-slate-400 mt-1 uppercase">
                                            PIC: {{ $item->technical_data['nama_input'] ?? 'OPERATOR' }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex gap-4 border-l mkt-border pl-10 items-center">
                                    <div class="text-right mr-4">
                                        <p class="text-[9px] text-slate-400 mb-1 italic">Output</p>
                                        <p class="text-xl text-red-600 leading-none italic">
                                            {{ number_format($item->kg ?? 0, 1) }} KG
                                        </p>
                                    </div>

                                    {{-- ACTION BUTTONS --}}
                                    <div class="flex gap-2">
                                        {{-- Tombol Detail --}}
                                        <button wire:click="viewLogDetail({{ $item->id }})" 
                                                class="w-10 h-10 mkt-surface-alt text-slate-400 rounded-xl hover:mkt-surface hover:text-white transition-all flex items-center justify-center">
                                            🔍
                                        </button>

                                        {{-- Tombol Edit --}}
                                        <button wire:click="editLog({{ $item->id }})" 
                                                class="w-10 h-10 bg-blue-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition-all flex items-center justify-center">
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
                        @empty
                            <div class="text-center py-24 mkt-surface-alt backdrop-blur-md rounded-[3rem] border-2 border-dashed border-white/10 opacity-60">
                                <div class="text-5xl mb-4">📭</div>
                                <p class="text-slate-400 font-black uppercase text-xs tracking-[0.2em]">Belum ada riwayat produksi atau hasil pencarian tidak ditemukan</p>
                            </div>
                        @endforelse
                        
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
                                Message <span class="text-indigo-600">Archive</span>
                            </h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Seluruh riwayat pesan dan handover</p>
                        </div>
                        <button wire:click="setMenu('dashboard')" class="mkt-surface-alt mkt-text px-6 py-3 rounded-2xl text-[10px] font-black uppercase italic shadow-lg border mkt-border">
                            ⬅ Kembali ke Dashboard
                        </button>
                    </div>

                    <div class="space-y-4">
                        @forelse($allNotes as $note)
                            <div class="mkt-surface-alt backdrop-blur-md p-6 rounded-[2.5rem] shadow-sm border mkt-border flex justify-between items-center group {{ $note->is_read ? 'opacity-60' : 'border-l-4 border-l-red-500' }}">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="text-[9px] font-black px-3 py-1 {{ $note->is_read ? 'mkt-surface mkt-text-muted' : 'bg-red-600 text-white' }} rounded-full uppercase border mkt-border">
                                            {{ $note->is_read ? 'SUDAH DIBACA' : 'BARU' }}
                                        </span>
                                        <p class="text-[10px] font-black text-slate-400 uppercase">
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
                            <div class="py-20 text-center mkt-surface-alt backdrop-blur-md rounded-[3rem] border-2 border-dashed border-white/10">
                                <p class="text-slate-400 font-black uppercase text-xs">Arsip Kosong</p>
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
    <div class="mkt-surface rounded-[3rem] w-full max-w-4xl my-auto overflow-hidden shadow-2xl border-4 mkt-border animate-in zoom-in duration-300">
        
        {{-- Header Modal dengan Info Artikel --}}
        <div class="mkt-surface-alt p-8 flex justify-between items-center italic border-b mkt-border">
            <div>
                <h3 class="mkt-text text-2xl font-black uppercase tracking-tighter leading-none">
                    @if($showInputForm) INPUT HASIL PRODUKSI @else DETAIL ORDER MARKETING @endif 
                    <span class="text-indigo-500">#{{ $selectedOrder->art_no }}</span>
                </h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Internal Tracking ID: {{ $selectedOrder->id }}</p>
            </div>
            <button wire:click="closeModal" class="mkt-surface-alt hover:bg-red-600 p-3 rounded-2xl mkt-text hover:text-white transition-all border mkt-border">&times;</button>
        </div>

        <div class="p-8 mkt-surface">
            @if(!$showInputForm)
                {{-- TAMPILAN 1: DETAIL LENGKAP UNTUK EKSEKUSI OPERATOR --}}
                <div class="space-y-8">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="mkt-surface-alt backdrop-blur-md p-6 rounded-[2rem] border mkt-border shadow-sm italic">
                            <h3 class="text-indigo-500 font-black mb-4 border-b mkt-border pb-2 uppercase text-xs flex items-center">
                                <span class="w-2 h-4 bg-indigo-600 mr-2 rounded-full"></span>I. Identity & Sales
                            </h3>
                            <div class="space-y-3 font-bold text-xs">
                                <div class="flex justify-between border-b mkt-border pb-1">
                                    <span class="text-slate-400 uppercase">Pelanggan</span>
                                    <span class="mkt-text uppercase">{{ $selectedOrder->pelanggan }}</span>
                                </div>
                                <div class="flex justify-between border-b mkt-border pb-1">
                                    <span class="text-slate-400 uppercase">Artikel No</span>
                                    <span class="mkt-text uppercase">{{ $selectedOrder->art_no }}</span>
                                </div>
                                <div class="flex justify-between border-b mkt-border pb-1">
                                    <span class="text-slate-400 uppercase">Tanggal Order</span>
                                    <span class="mkt-text">{{ $selectedOrder->tanggal ? \Carbon\Carbon::parse($selectedOrder->tanggal)->format('d/m/Y') : '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400 uppercase">Sales (Mkt)</span>
                                    <span class="mkt-text italic uppercase">{{ $selectedOrder->mkt ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mkt-surface-alt backdrop-blur-md p-6 rounded-[2rem] border mkt-border shadow-sm italic">
                            <h3 class="text-indigo-500 font-black mb-4 border-b mkt-border pb-2 uppercase text-xs flex items-center">
                                <span class="w-2 h-4 bg-indigo-600 mr-2 rounded-full"></span>II. Technical Specs
                            </h3>
                            <div class="space-y-3 font-bold text-xs">
                                <div class="flex justify-between border-b mkt-border pb-1">
                                    <span class="text-slate-400 uppercase">Material</span>
                                    <span class="mkt-text uppercase">{{ $selectedOrder->material }}</span>
                                </div>
                                <div class="flex justify-between border-b mkt-border pb-1">
                                    <span class="text-slate-400 uppercase">Benang</span>
                                    <span class="mkt-text uppercase">{{ $selectedOrder->benang }}</span>
                                </div>
                                <div class="flex justify-between border-b mkt-border pb-1">
                                    <span class="text-slate-400 uppercase">Konstruksi Greige</span>
                                    <span class="mkt-text italic uppercase">{{ $selectedOrder->konstruksi_greige }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-indigo-500 uppercase">Warna Finishing</span>
                                    <span class="text-indigo-500 uppercase font-black italic">{{ $selectedOrder->warna }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mkt-surface-alt p-8 rounded-[3rem] shadow-xl border mkt-border">
                        <h3 class="text-indigo-500 font-black mb-6 uppercase italic tracking-tighter text-center underline underline-offset-8">Production Specification Matrix</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center font-bold">
                            <div class="border-r mkt-border italic">
                                <p class="text-[9px] text-slate-400 uppercase mb-1">Kelompok Kain</p>
                                <p class="text-lg uppercase mkt-text">{{ $selectedOrder->kelompok_kain }}</p>
                            </div>
                            <div class="border-r mkt-border italic">
                                <p class="text-[9px] text-slate-400 uppercase mb-1">Lebar / Gramasi</p>
                                <p class="text-lg mkt-text">{{ $selectedOrder->target_lebar ?? '0' }}" / {{ $selectedOrder->target_gramasi ?? '0' }}</p>
                            </div>
                            <div class="border-r mkt-border italic">
                                <p class="text-[9px] text-slate-400 uppercase mb-1">Belah / Bulat</p>
                                <p class="text-lg uppercase mkt-text">{{ $selectedOrder->belah_bulat }}</p>
                            </div>
                            <div class="italic">
                                <p class="text-[9px] text-slate-400 uppercase mb-1">Handfeel</p>
                                <p class="text-lg uppercase mkt-text">{{ $selectedOrder->handfeel }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 italic font-black">
                        <div class="bg-indigo-600 text-white p-8 rounded-[2.5rem] shadow-lg flex justify-between items-center">
                            <p class="text-xs uppercase tracking-[0.2em]">Total Roll Target</p>
                            <h4 class="text-4xl underline decoration-4 underline-offset-8">{{ $selectedOrder->roll_target ?? '0' }}</h4>
                        </div>
                        <div class="bg-emerald-600 text-white p-8 rounded-[2.5rem] shadow-lg flex justify-between items-center">
                            <p class="text-xs uppercase tracking-[0.2em]">Total Net Weight (KG)</p>
                            <h4 class="text-4xl underline decoration-4 underline-offset-8">{{ number_format($selectedOrder->kg_target, 1) }}</h4>
                        </div>
                    </div>

                    <div class="mkt-surface-alt backdrop-blur-md p-8 rounded-[3rem] border-l-[12px] border-red-600 shadow-sm space-y-4 text-left italic border-y border-r mkt-border">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-2 tracking-widest">Special Treatment & Instructions:</p>
                            <p class="text-lg font-black mkt-text uppercase underline decoration-red-600/30 underline-offset-4">{{ $selectedOrder->treatment_khusus ?? '-' }}</p>
                        </div>
                        <hr class="mkt-border">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-2 tracking-widest">Internal Marketing Notes:</p>
                            <p class="text-xs font-bold text-slate-400 leading-relaxed mkt-surface p-4 rounded-2xl italic">"{{ $selectedOrder->keterangan_artikel ?? 'No additional internal notes provided.' }}"</p>
                        </div>
                    </div>

                    {{-- SECTION BARU: JEJAK PRODUKSI (TRACEABILITY) --}}
                    @if(count($productionHistory) > 0)
                    <div class="space-y-4">
                        <h3 class="mkt-text font-black uppercase italic tracking-tighter text-lg flex items-center gap-2">
                            <span class="w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xs">III</span>
                            JEJAK PRODUKSI <span class="text-red-600">(HISTORY)</span>
                        </h3>
                        
                        <div class="space-y-4 relative before:absolute before:left-[19px] before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                            @foreach($productionHistory as $history)
                                <div class="relative pl-12">
                                    {{-- DOT INDICATOR --}}
                                    <div class="absolute left-0 top-1 w-10 h-10 rounded-full {{ $history->division_name === 'knitting' ? 'bg-indigo-600' : 'bg-emerald-600' }} flex items-center justify-center z-10 shadow-sm font-black text-[10px] text-white">
                                        {{ substr(strtoupper($history->division_name), 0, 1) }}
                                    </div>
                                    
                                    <div class="mkt-surface backdrop-blur-md p-6 rounded-[2rem] border mkt-border shadow-sm group hover:border-red-600 transition-all">
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <span class="text-[9px] font-black {{ $history->division_name === 'knitting' ? 'text-indigo-600 bg-blue-50' : 'text-emerald-600 bg-emerald-50' }} px-3 py-1 rounded-full uppercase tracking-widest">
                                                    DIVISI: {{ strtoupper($history->division_name) }}
                                                </span>
                                                <h4 class="text-sm font-black mkt-text mt-2 uppercase italic tracking-tight">
                                                    {{ $history->operator->name ?? $history->technical_data['operator_manual_name'] ?? 'Unknown Operator' }}
                                                </h4>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-[9px] text-slate-400 font-bold uppercase">{{ $history->created_at->format('d M Y') }}</p>
                                                <p class="text-[10px] font-black mkt-text uppercase italic tabular-nums">{{ $history->created_at->format('H:i') }} WIB</p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-3 pt-3 border-t mkt-border">
                                            <div class="italic">
                                                <p class="text-[8px] text-slate-400 uppercase mb-1">Hasil (KG)</p>
                                                <p class="text-sm font-black text-red-600">{{ number_format($history->kg, 1) }} KG</p>
                                            </div>
                                            <div class="italic">
                                                <p class="text-[8px] text-slate-400 uppercase mb-1">Roll</p>
                                                <p class="text-sm font-black mkt-text">{{ $history->roll }} ROLL</p>
                                            </div>
                                            
                                            {{-- Technical Data Preview (Loop 2-3 data pertama saja agar tidak penuh) --}}
                                            @php $count = 0; @endphp
                                            @foreach($history->technical_data ?? [] as $key => $val)
                                                @if(!in_array($key, ['kg', 'roll', 'operator_manual_name', 'shift']) && $count < 2)
                                                    <div class="italic hidden md:block">
                                                        <p class="text-[8px] text-slate-400 uppercase mb-1">{{ str_replace('_', ' ', $key) }}</p>
                                                        <p class="text-[11px] font-bold mkt-text truncate uppercase">{{ is_array($val) ? '...' : $val }}</p>
                                                    </div>
                                                    @php $count++; @endphp
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                   <div class="pt-4">
                        @if($selectedLog)
                            <div class="mkt-surface-alt backdrop-blur-md p-8 rounded-[3rem] border-t-8 border-indigo-600 shadow-xl mt-4">
                                <h3 class="text-indigo-600 font-black mb-6 uppercase italic tracking-tighter text-lg flex items-center">
                                    <span class="mr-2">📝</span> Data Input Operator Tersimpan
                                </h3>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-6 font-bold text-xs">
                                    @foreach($selectedLog->technical_data ?? [] as $key => $value)
                                        <div class="border-b mkt-border pb-2">
                                            <p class="text-[9px] text-slate-400 uppercase mb-1 tracking-widest">{{ str_replace('_', ' ', $key) }}</p>
                                            <p class="text-base mkt-text uppercase italic">{{ is_array($value) ? json_encode($value) : ($value ?: '-') }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else


                            {{-- Mengubah dari <a> tag menjadi button wire:click agar WIP status bisa diupdate sebelum redirect --}}
                            <button wire:click="startProcessAndRedirect({{ $selectedOrder->id }})" 
                                class="w-full bg-indigo-600 text-white py-6 rounded-[2rem] font-black uppercase text-sm flex items-center justify-center gap-3 hover:bg-indigo-500 transition-all shadow-xl tracking-[0.2em]">
                                
                                {{-- Nama tombol otomatis berubah sesuai divisi --}}
                                ⚙️ TERIMA & KERJAKAN {{ strtoupper(auth()->user()->role) }}
                            </button>
                        @endif
                    </div>
                </div>
        
            @else
                {{-- TAMPILAN 2: FORM INPUT HASIL --}}
                <form wire:submit.prevent="submitProduction" class="italic text-left font-black">
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div class="col-span-2 bg-indigo-50 dark:bg-indigo-950 p-4 rounded-2xl flex justify-between items-center border border-indigo-100 dark:border-indigo-900">
                            <p class="text-xs text-indigo-800 dark:text-indigo-200 uppercase italic">Shift Kerja Aktif:</p>
                            <span class="bg-indigo-600 text-white px-4 py-1 rounded-full text-xs italic tracking-widest uppercase font-black">Shift {{ $shift }}</span>
                        </div>

                        <div>
                            <label class="text-[10px] text-slate-400 uppercase ml-2">Total Berat (KG)</label>
                            <input type="number" step="0.1" wire:model="qty_kg" 
                                class="w-full mkt-surface border mkt-border rounded-2xl p-4 text-xl font-black mkt-text focus:ring-4 focus:ring-indigo-500/20 {{ $errors->has('qty_kg') ? 'ring-2 ring-red-500' : '' }}">
                            @error('qty_kg') <p class="text-[9px] text-red-500 mt-1 ml-2 font-bold uppercase italic">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-[10px] text-slate-400 uppercase ml-2">Jumlah Roll</label>
                            <input type="number" wire:model="qty_roll" required
                                class="w-full mkt-surface border mkt-border rounded-2xl p-4 text-xl font-black mkt-text focus:ring-4 focus:ring-indigo-500/20 italic" placeholder="0">
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button type="button" wire:click="$set('showInputForm', false)" 
                            class="flex-1 mkt-surface-alt mkt-text-muted py-4 rounded-2xl font-black uppercase text-xs border mkt-border hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">KEMBALI</button>
                        <button type="submit" 
                            class="flex-[2] bg-emerald-600 text-white py-4 rounded-2xl font-black uppercase text-xs shadow-lg shadow-emerald-600/20 hover:bg-emerald-500 transition-all">
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