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
        $user = auth()->user();
        $isImpersonating = session()->has('impersonator_id');

        if (!$user->isAdmin() && !$user->isSuperAdmin() && !$isImpersonating) {
            $this->dispatch('show-toast', [
                'message' => 'Hanya Supervisor atau Admin yang memiliki otorisasi untuk menghapus log produksi.',
                'type' => 'error'
            ]);
            return;
        }

        $log = ProductionActivity::find($id);

        if ($log) {
            $order = MarketingOrder::find($log->marketing_order_id);
            $role = auth()->user()->role;
            
            $adminName = $user->name;
            if ($isImpersonating) {
                $impersonator = \App\Models\User::find(session('impersonator_id'));
                $adminName = $impersonator ? $impersonator->name : 'Super Admin';
            }

            // 1. Simpan ke Riwayat Penghapusan (Audit Trail)
            \App\Models\ActivityLog::create([
                'user_id' => $isImpersonating ? session('impersonator_id') : auth()->id(),
                'action' => 'DELETE_PRODUCTION_LOG',
                'division' => $role,
                'art_no' => $order->art_no,
                'details' => "Menghapus data: {$log->kg} KG / {$log->roll} Roll. Alasan: Dihapus oleh Admin " . $adminName,
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
        session()->flash('message', 'Pesan berhasil dikirim ke seluruh tim! ');
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
                "DETEKSI INPUT TINGGI: Operator " . auth()->user()->name . 
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
                ? 'Data produksi berhasil diperbarui! ' 
                : 'Berhasil! Data dikirim ke divisi berikutnya. ';

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
            $user = auth()->user();
            if (!$user->isAdmin() && !$user->isSuperAdmin()) {
                if ($log->created_at->diffInMinutes(now()) > 15) {
                    $this->dispatch('show-toast', [
                        'message' => 'Batas waktu revisi mandiri (15 menit) telah habis. Silakan hubungi Supervisor/Admin untuk melakukan koreksi data.',
                        'type' => 'error'
                    ]);
                    return;
                }
            }

            // PENTING: Arahkan ke rute full-page yang sesuai, karena komponen form inline sudah dihapus
            $middlePath = ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'stenter', 'tumbler', 'fleece', 'finishing'];
            $role = auth()->user()->role;
            $targetRoute = in_array($role, $middlePath) ? 'operator.single' : 'operator.' . $role;

            if (!\Illuminate\Support\Facades\Route::has($targetRoute)) $targetRoute = 'operator.logbook';

            return redirect()->route($targetRoute, ['artikel' => $log->marketingOrder->art_no]);
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
    <div class="py-4 md:py-6 bg-transparent min-h-screen font-sans tracking-tighter italic text-left">
    <div class="max-w-[1440px] w-full mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
        <div class="min-h-[400px]">

            {{-- 1. TAMPILAN DASHBOARD --}}
            @if($currentMenu === 'dashboard')
                <div class="animate-in fade-in duration-500">
                    {{-- HEADER --}}
                    <div class="flex flex-col lg:flex-row justify-between items-stretch lg:items-end mb-6 md:mb-8 gap-4">
                        <div class="text-left">
                            <h2 class="text-2xl md:text-4xl font-black uppercase tracking-tighter mkt-text leading-tight italic">
                                @if(auth()->user()->role === 'knitting') 
                                    Knitting
                                @elseif(auth()->user()->role === 'dyeing') 
                                    Dyeing 
                                @elseif(auth()->user()->role === 'relax-dryer') 
                                    Relax Dryer 
                                @elseif(auth()->user()->role === 'finishing') 
                                    Compactor / HT 
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
                            <div class="mt-3 flex flex-wrap items-center gap-2 md:gap-3">
                                <div class="mkt-surface-alt backdrop-blur-md px-4 py-2 rounded-xl shadow-lg border mkt-border">
                                    <p class="real-time-clock text-xs md:text-sm font-black tracking-[0.2em] leading-none mkt-text drop-shadow-[0_0_8px_rgba(237,28,36,0.3)]">00:00:00</p>
                                </div>
                                <p class="real-time-date text-[9px] md:text-[11px] font-bold text-slate-500 uppercase tracking-widest italic"></p>
                            </div>
                        </div>
                        
                        <div class="mkt-surface-alt backdrop-blur-md px-5 py-3 rounded-2xl shadow-xl border mkt-border flex items-center justify-between lg:justify-end gap-4">
                            <div class="text-left lg:text-right italic">
                                <p class="text-[8px] md:text-[10px] font-black text-slate-500 uppercase leading-none tracking-widest">Machine Status</p>
                                <p class="text-xs md:text-sm font-black {{ $machineStatus === 'running' ? 'text-green-500' : ($machineStatus === 'downtime' ? 'text-amber-500' : 'text-orange-600') }} uppercase mt-1.5 tracking-tight">
                                    {{ $machineStatus === 'running' ? 'Optimal Performance' : ($machineStatus === 'downtime' ? 'Machine Downtime' : 'Under Maintenance') }}
                                </p>
                            </div>
                            <div class="relative flex shrink-0">
                                <span class="animate-ping absolute inline-flex h-4 w-4 rounded-full {{ $machineStatus === 'running' ? 'bg-green-400' : ($machineStatus === 'downtime' ? 'bg-amber-400' : 'bg-orange-400') }} opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 {{ $machineStatus === 'running' ? 'bg-green-500' : ($machineStatus === 'downtime' ? 'bg-amber-500' : 'bg-orange-600') }}"></span>
                            </div>
                        </div>
                    </div>


                    {{-- WIDGETS --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mb-6 md:mb-10">
                        <div class="md:col-span-2 mkt-surface p-6 md:p-8 rounded-[1.5rem] md:rounded-[3rem] shadow-xl relative overflow-hidden border mkt-border group">
                            <div class="relative z-10 text-left">
                                <div class="flex justify-between items-start mb-6 md:mb-8">
                                    <div>
                                        <p class="text-[9px] md:text-[11px] font-black mkt-text-muted uppercase tracking-[0.2em] mb-1 italic">Shift Achievement</p>
                                        <h4 class="text-4xl md:text-6xl font-black leading-none italic mkt-text tracking-tighter">{{ (float)$progress }}%</h4>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[8px] md:text-[10px] mkt-text-muted uppercase font-bold tracking-widest mb-1 italic">Target: {{ $targetShift }} KG</p>
                                        <p class="text-sm md:text-lg font-black mkt-text italic tabular-nums">{{ (float)$totalKgToday }} <span class="text-[10px] opacity-50">/</span> {{ $targetShift }}</p>
                                    </div>
                                </div>
                                <div class="relative flex items-center justify-center group-hover:scale-105 transition-transform duration-500">
                                    {{-- SVG Gauge with Scale Marks --}}
                                    <svg class="w-40 h-20 md:w-56 md:h-28" viewBox="0 0 100 50">
                                        <path d="M 10 50 A 40 40 0 0 1 90 50" fill="none" stroke="currentColor" class="text-slate-100 dark:text-white/5" stroke-width="8" stroke-linecap="round" />
                                        <path d="M 10 50 A 40 40 0 0 1 90 50" fill="none" 
                                            stroke="{{ $progress < 100 ? '#ED1C24' : '#10b981' }}" 
                                            stroke-width="8" stroke-linecap="round" 
                                            stroke-dasharray="{{ $progress * 1.25 }}, 125" 
                                            class="transition-all duration-1000 shadow-2xl" />
                                    </svg>
                                    
                                    <div class="absolute bottom-1 md:bottom-2 text-center">
                                        <p class="text-[7px] md:text-[9px] font-black text-slate-500 uppercase tracking-[0.3em] leading-none mb-1">Live Output</p>
                                        <p class="text-xl md:text-3xl font-black mkt-text italic leading-none tabular-nums">
                                            {{ (float)$totalKgToday }} 
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute -right-8 -bottom-8 opacity-5 text-[10rem] md:text-[15rem] font-black italic select-none pointer-events-none">UNIT</div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-1 gap-4">
                            <div class="mkt-surface p-5 md:p-6 rounded-[1.5rem] md:rounded-[2rem] border mkt-border shadow-lg text-left hover:scale-[1.02] transition-transform duration-300 relative overflow-hidden">
                                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest italic leading-none mb-3">Antrean</p>
                                <h4 class="text-3xl md:text-5xl font-black mkt-text-muted italic leading-none tabular-nums">{{ $totalKnitting }}</h4>
                                <div class="absolute right-4 bottom-4 w-2 h-2 rounded-full bg-brand-600 animate-pulse"></div>
                            </div>
                            <div class="mkt-surface p-5 md:p-6 rounded-[1.5rem] md:rounded-[2rem] border mkt-border shadow-lg text-left hover:scale-[1.02] transition-transform duration-300 relative overflow-hidden">
                                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest italic leading-none mb-3">Selesai</p>
                                <h4 class="text-3xl md:text-5xl font-black text-emerald-500 italic leading-none tabular-nums">{{ $totalDone }}</h4>
                                <div class="absolute right-4 bottom-4 w-2 h-2 rounded-full bg-emerald-500"></div>
                            </div>
                        </div>
                    </div>

                    {{-- MACHINE STATUS CONTROL (MOVED TO MIDDLE) --}}
                    <div class="mkt-surface p-5 md:p-8 rounded-[2rem] md:rounded-[3rem] border mkt-border shadow-2xl mb-6 md:mb-10 flex flex-col xl:flex-row items-center justify-between gap-6 relative overflow-hidden">
                        <div class="relative z-10 text-center xl:text-left">
                            <h3 class="text-sm md:text-base font-black uppercase italic mkt-text mb-2 tracking-tight">Machine Control Center</h3>
                            <p class="text-[9px] md:text-[11px] font-bold mkt-text-muted uppercase italic tracking-wider">Kelola status operasional unit divisi Anda secara real-time</p>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-3 md:gap-6 relative z-10 w-full xl:w-auto">
                            <button wire:click="setMachineStatus('running')" 
                                class="flex flex-col items-center gap-2 p-4 rounded-2xl md:rounded-[2rem] transition-all duration-500 {{ $machineStatus === 'running' ? 'bg-gradient-to-br from-green-600/30 to-emerald-600/5 border-green-500 shadow-2xl shadow-green-500/20 scale-105' : 'mkt-surface-alt mkt-border hover:opacity-80' }} border group">
                                <div class="w-10 h-10 md:w-14 md:h-14 rounded-2xl flex items-center justify-center {{ $machineStatus === 'running' ? 'bg-green-600 text-white shadow-xl' : 'mkt-surface-alt text-slate-500' }} transition-all">
                                    <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                    </svg>
                                </div>
                                <span class="text-[8px] md:text-[10px] font-black uppercase italic mkt-text tracking-widest">Running</span>
                            </button>
                            
                            <button wire:click="setMachineStatus('downtime')" 
                                class="flex flex-col items-center gap-2 p-4 rounded-2xl md:rounded-[2rem] transition-all duration-500 {{ $machineStatus === 'downtime' ? 'bg-gradient-to-br from-amber-600/30 to-yellow-600/5 border-amber-500 shadow-2xl shadow-amber-500/20 scale-105' : 'mkt-surface-alt mkt-border hover:opacity-80' }} border group">
                                <div class="w-10 h-10 md:w-14 md:h-14 rounded-2xl flex items-center justify-center {{ $machineStatus === 'downtime' ? 'bg-amber-500 text-white shadow-xl' : 'mkt-surface-alt text-slate-500' }} transition-all">
                                    <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5" />
                                    </svg>
                                </div>
                                <span class="text-[8px] md:text-[10px] font-black uppercase italic mkt-text tracking-widest">Downtime</span>
                            </button>
                            
                            <button wire:click="setMachineStatus('maintenance')" 
                                class="flex flex-col items-center gap-2 p-4 rounded-2xl md:rounded-[2rem] transition-all duration-500 {{ $machineStatus === 'maintenance' ? 'bg-gradient-to-br from-orange-600/30 to-orange-600/5 border-orange-500 shadow-2xl shadow-orange-500/20 scale-105' : 'mkt-surface-alt mkt-border hover:opacity-80' }} border group">
                                <div class="w-10 h-10 md:w-14 md:h-14 rounded-2xl flex items-center justify-center {{ $machineStatus === 'maintenance' ? 'bg-orange-600 text-white shadow-xl' : 'mkt-surface text-slate-500' }} transition-all">
                                    <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.67 2.67 0 1121 17.25l-5.83-5.83m-3.75 3.75a3.75 3.75 0 11-5.3-5.3 3.75 3.75 0 015.3 5.3z" />
                                    </svg>
                                </div>
                                <span class="text-[8px] md:text-[10px] font-black uppercase italic mkt-text tracking-widest">Repair</span>
                            </button>
                        </div>
                        <div class="absolute -right-20 -top-20 text-[15rem] font-black italic opacity-5 select-none tracking-tighter mkt-text pointer-events-none hidden lg:block">STATUS</div>
                    </div>

                    {{-- SHIFT COMMUNICATION (RESTORED) --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">
                        <div class="lg:col-span-2 mkt-surface p-4 md:p-5 rounded-2xl border mkt-border shadow-lg">
                            <h3 class="text-xs font-black uppercase italic mkt-text mb-4 flex items-center gap-2">
                                <span class="w-2 h-4 bg-brand-600 rounded-full shadow-[0_0_10px_rgba(237, 28, 36,0.5)]"></span>
                                Pesan Antar Shift / Kendala
                            </h3>
                            <div class="space-y-3 mb-4">
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
                                <input wire:model="messageText" type="text" placeholder="Tulis pesan atau laporkan kendala..." 
                                    class="w-full mkt-surface-alt border mkt-border rounded-xl px-4 py-3 text-[11px] font-bold pr-28 focus:ring-2 focus:ring-brand-600 focus:border-transparent transition-all italic mkt-text placeholder-slate-400">
                                <button type="submit" class="absolute right-2 top-2 bg-brand-600 text-white px-5 py-2 rounded-xl text-[9px] font-black uppercase italic shadow-lg hover:bg-brand-700 hover:shadow-brand-500/30 transition-all">
                                    KIRIM PESAN                                 </button>
                            </form>
                            @error('messageText') <p class="text-[9px] text-red-500 font-bold mt-2 uppercase italic">{{ $message }}</p> @enderror
                        </div>

                        <div class="mkt-surface p-4 md:p-5 rounded-2xl border mkt-border shadow-lg flex flex-col justify-center items-center text-center group">
                            <div class="w-12 h-12 rounded-2xl bg-brand-600/10 text-brand-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-black uppercase italic mkt-text mb-2">Arsip Pesan</h4>
                            <p class="text-[9px] font-bold mkt-text-muted uppercase mb-6">Lihat seluruh riwayat komunikasi antar shift</p>
                            <button wire:click="setMenu('notes')" class="w-full mkt-surface-alt border mkt-border py-3 rounded-2xl text-[10px] font-black uppercase italic mkt-text hover:bg-brand-600 hover:text-white hover:border-transparent transition-all duration-300 shadow-md">
                                BUKA ARSIP
                            </button>
                        </div>
                    </div>

                    @if (session()->has('message'))
                        <div class="mb-6 p-4 bg-green-600 text-white rounded-2xl font-black uppercase text-xs italic shadow-lg animate-bounce">
                            {{ session('message') }}
                        </div>
                    @endif

                </div>

            {{-- 2. TAMPILAN PERMINTAAN --}}
            @elseif($currentMenu === 'orders')
                <div class="animate-in slide-in-from-bottom-4 duration-500 text-left italic">
                    
                    {{-- SAKLAR UTAMA: JIKA TIDAK SEDANG PROSES (TAMPILKAN LIST) --}}
                    @if(!$isProcessing)
                        <div class="mb-5 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-2">
                            <div>
                                <h2 class="text-xl md:text-2xl font-black uppercase tracking-tighter mkt-text leading-none">
                                    DUNIATEX <span class="text-brand-600">Execution</span>
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
                                    <div class="text-center py-12 mkt-surface-alt rounded-2xl border-2 border-dashed border-white/10 italic">
                                        <p class="text-slate-400 font-black uppercase text-xs">Tidak ada antrean relax dryer</p>
                                    </div>
                                @endforelse
                            @elseif(auth()->user()->role === 'knitting')
                                @include('livewire.operator.partials.knitting-table')
                            @elseif(auth()->user()->role === 'dyeing')
                                @include('livewire.operator.partials.dyeing-table')
                            @elseif(auth()->user()->role === 'finishing')
                                @forelse($workQueue as $job)
                                    @include('livewire.operator.partials.finishing-table', ['job' => $job])
                                @empty
                                    <div class="text-center py-12 mkt-surface-alt rounded-2xl border-2 border-dashed border-white/10 italic">
                                        <p class="text-slate-400 font-black uppercase text-xs">Tidak ada antrean finishing</p>
                                    </div>
                                @endforelse
                            @elseif(auth()->user()->role === 'stenter')
                                @forelse($workQueue as $job)
                                    @include('livewire.operator.partials.stenter-table', ['job' => $job])
                                @empty
                                    <div class="text-center py-12 mkt-surface-alt rounded-2xl border-2 border-dashed border-white/10 italic">
                                        <p class="text-slate-400 font-black uppercase text-xs">Tidak ada antrean stenter</p>
                                    </div>
                                @endforelse
                            @elseif(auth()->user()->role === 'tumbler')
                                @forelse($workQueue as $job)
                                    @include('livewire.operator.partials.tumbler-table', ['job' => $job])
                                @empty
                                    <div class="text-center py-12 mkt-surface-alt rounded-2xl border-2 border-dashed border-white/10 italic">
                                        <p class="text-slate-400 font-black uppercase text-xs">Tidak ada antrean tumbler</p>
                                    </div>
                                @endforelse
                            @elseif(auth()->user()->role === 'fleece')
                                @forelse($workQueue as $job)
                                    @include('livewire.operator.partials.fleece-table', ['job' => $job])
                                @empty
                                    <div class="text-center py-12 mkt-surface-alt rounded-2xl border-2 border-dashed border-white/10 italic">
                                        <p class="text-slate-400 font-black uppercase text-xs">Tidak ada antrean fleece</p>
                                    </div>
                                @endforelse
                            @elseif(auth()->user()->role === 'pengujian')
                                @forelse($workQueue as $job)
                                    @include('livewire.operator.partials.pengujian-table', ['job' => $job])
                                @empty
                                    <div class="text-center py-12 mkt-surface-alt rounded-2xl border-2 border-dashed border-white/10 italic">
                                        <p class="text-slate-400 font-black uppercase text-xs">Tidak ada antrean pengujian</p>
                                    </div>
                                @endforelse
                            @elseif(auth()->user()->role === 'qe')
                                @forelse($workQueue as $job)
                                    @include('livewire.operator.partials.qe-table', ['job' => $job])
                                @empty
                                    <div class="text-center py-12 mkt-surface-alt rounded-2xl border-2 border-dashed border-white/10 italic">
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
                    <div class="mb-5 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-3">
                        <h2 class="text-xl md:text-2xl mkt-text leading-none italic">Production <span class="text-red-600">Logs</span></h2>
                        <div class="relative" x-data="{ open: false }">
                            <input wire:model.live="search" 
                                   @focus="open = true" 
                                   @click.away="open = false"
                                   type="text" 
                                   placeholder="CARI NO ARTIKEL..." 
                                   class="px-4 py-2 mkt-surface-alt border mkt-border rounded-xl text-[11px] mkt-text outline-none focus:ring-2 focus:ring-red-600/20 italic w-full sm:w-auto">
                            
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
                            <div class="mkt-surface p-4 rounded-2xl shadow-sm border mkt-border flex flex-col sm:flex-row justify-between sm:items-center gap-3 group italic">
                                <div class="text-left flex items-center gap-4">
                                    <div class="text-2xl">
                                        @if($item->division_name === 'knitting')                                         @elseif($item->division_name === 'dyeing')                                         @else 
                                        @endif
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-lg mkt-text font-black leading-none italic">{{ $item->marketingOrder->art_no ?? 'UNKNOWN' }} <span class="text-[10px] mkt-text-muted uppercase font-black ml-2 px-2 py-1 bg-brand-500/10 rounded-lg">{{ str_replace('-', ' ', $item->division_name) }}</span></h4>
                                        <p class="text-[9px] text-brand-600 font-bold mt-1 uppercase italic tracking-widest">ARTIKEL NO: {{ $item->marketingOrder->art_no ?? 'N/A' }}</p>
                                        {{-- Info Operator --}}
                                        <p class="text-[8px] text-slate-400 mt-1 uppercase">
                                            PIC: {{ $item->technical_data['nama_input'] ?? 'OPERATOR' }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex gap-4 border-l mkt-border pl-10 items-center">
                                    <div class="text-right mr-4">
                                        <p class="text-[9px] text-slate-400 mb-1 italic">Output</p>
                                        <p class="text-xl mkt-text leading-none italic">
                                            {{ (float)$item->kg ?? 0 }} KG
                                        </p>
                                    </div>

                                    {{-- ACTION BUTTONS --}}
                                    <div class="flex gap-2">
                                        {{-- Tombol Detail --}}
                                        <button wire:click="viewLogDetail({{ $item->id }})" 
                                                title="Lihat Detail Data"
                                                class="w-10 h-10 mkt-surface-alt text-slate-400 border mkt-border rounded-xl hover:mkt-surface hover:mkt-text-muted transition-all flex items-center justify-center shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </button>

                                        @php
                                            $isPastGrace = !auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin() && $item->created_at->diffInMinutes(now()) > 15;
                                        @endphp

                                        {{-- Tombol Edit --}}
                                        <button wire:click="editLog({{ $item->id }})" 
                                                title="{{ $isPastGrace ? 'Batas Waktu Revisi Habis (15 Menit)' : 'Edit Log Data' }}"
                                                class="w-10 h-10 {{ $isPastGrace ? 'bg-slate-100 dark:bg-slate-900 text-slate-400 border border-slate-200 dark:border-slate-800 opacity-60 cursor-not-allowed' : 'bg-brand-50 dark:bg-brand-950/40 text-brand-600 dark:text-brand-400 border border-brand-100 dark:border-brand-900/50 hover:bg-brand-600 hover:text-white dark:hover:bg-brand-600' }} rounded-xl transition-all flex items-center justify-center shadow-sm">
                                            @if($isPastGrace)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                                </svg>
                                            @endif
                                        </button>
                                        
                                        @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || session()->has('impersonator_id'))
                                            {{-- Tombol Hapus --}}
                                            <button type="button" 
                                                    onclick="Swal.fire({
                                                        title: 'HAPUS LOG INI?',
                                                        text: 'Menghapus log dapat memundurkan alur status pesanan kembali ke operator.',
                                                        icon: 'warning',
                                                        showCancelButton: true,
                                                        confirmButtonColor: '#dc2626',
                                                        confirmButtonText: 'YA, HAPUS',
                                                        cancelButtonText: 'BATAL',
                                                        background: '#0f172a',
                                                        color: '#fff',
                                                        customClass: { popup: 'rounded-[2rem] border border-white/10 backdrop-blur-xl', title: 'font-black italic uppercase tracking-tighter', confirmButton: 'rounded-xl font-bold uppercase text-xs px-6 py-3', cancelButton: 'rounded-xl font-bold uppercase text-xs px-6 py-3' }
                                                    }).then((result) => { if (result.isConfirmed) { @this.deleteEntry({{ $item->id }}) } })"
                                                    title="Hapus Log Data"
                                                    class="w-10 h-10 bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border border-red-100 dark:border-red-900/50 rounded-xl hover:bg-red-600 hover:text-white hover:border-transparent dark:hover:bg-red-600 transition-all flex items-center justify-center shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 mkt-surface-alt rounded-2xl border-2 border-dashed border-white/10 opacity-60">
                                <div class="text-5xl mb-4"></div>
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
                    <div class="mb-5 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-3">
                        <div>
                            <h2 class="text-xl md:text-2xl font-black uppercase tracking-tighter mkt-text leading-none">
                                Message <span class="text-brand-600">Archive</span>
                            </h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Seluruh riwayat pesan dan handover</p>
                        </div>
                        <button wire:click="setMenu('dashboard')" class="mkt-surface-alt mkt-text px-6 py-3 rounded-2xl text-[10px] font-black uppercase italic shadow-lg border mkt-border">
                            Kembali ke Dashboard
                        </button>
                    </div>

                    <div class="space-y-4">
                        @forelse($allNotes as $note)
                            <div class="mkt-surface-alt p-4 rounded-2xl shadow-sm border mkt-border flex flex-col sm:flex-row justify-between sm:items-center gap-3 group {{ $note->is_read ? 'opacity-60' : 'border-l-4 border-l-red-500' }}">
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
                            <div class="py-20 text-center mkt-surface-alt backdrop-blur-md rounded-2xl md:rounded-[3rem] border-2 border-dashed border-white/10">
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
    <div class="mkt-surface rounded-2xl md:rounded-3xl w-full max-w-4xl my-auto overflow-hidden shadow-2xl border-2 md:border-4 mkt-border animate-in zoom-in duration-300">
        
        {{-- Header Modal dengan Info Artikel --}}
        <div class="mkt-surface-alt p-4 md:p-6 flex justify-between items-center italic border-b mkt-border">
            <div>
                <h3 class="mkt-text text-base md:text-lg font-black uppercase tracking-tighter leading-none">
                    @if($showInputForm) INPUT HASIL PRODUKSI @else DETAIL ORDER MARKETING @endif 
                    <span class="mkt-text-muted">#{{ $selectedOrder->art_no }}</span>
                </h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Internal Tracking ID: {{ $selectedOrder->id }}</p>
            </div>
            <button wire:click="closeModal" class="mkt-surface-alt hover:bg-red-600 p-3 rounded-2xl mkt-text hover:text-white transition-all border mkt-border">&times;</button>
        </div>

        <div class="p-4 md:p-6 mkt-surface max-h-[70vh] overflow-y-auto">
            @if(!$showInputForm)
                {{-- TAMPILAN 1: DETAIL LENGKAP UNTUK EKSEKUSI OPERATOR --}}
                <div class="space-y-8">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="mkt-surface-alt backdrop-blur-md p-6 rounded-[2rem] border mkt-border shadow-sm italic">
                            <h3 class="mkt-text-muted font-black mb-4 border-b mkt-border pb-2 uppercase text-xs flex items-center">
                                <span class="w-2 h-4 bg-brand-600 mr-2 rounded-full"></span>I. Identity & Sales
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
                            <h3 class="mkt-text-muted font-black mb-4 border-b mkt-border pb-2 uppercase text-xs flex items-center">
                                <span class="w-2 h-4 bg-brand-600 mr-2 rounded-full"></span>II. Technical Specs
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
                                    <span class="mkt-text-muted uppercase">Warna Finishing</span>
                                    <span class="mkt-text-muted uppercase font-black italic">{{ $selectedOrder->warna }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mkt-surface-alt p-4 md:p-6 rounded-2xl shadow-lg border mkt-border">
                        <h3 class="mkt-text-muted font-black mb-4 uppercase italic tracking-tighter text-center underline underline-offset-4 text-sm">Production Spec</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center font-bold">
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
                        <div class="bg-brand-600 text-white p-4 md:p-6 rounded-2xl shadow-lg flex justify-between items-center">
                            <p class="text-[10px] uppercase tracking-wider">Total Roll Target</p>
                            <h4 class="text-2xl md:text-3xl underline decoration-2 underline-offset-4">{{ $selectedOrder->roll_target ?? '0' }}</h4>
                        </div>
                        <div class="bg-emerald-600 text-white p-4 md:p-6 rounded-2xl shadow-lg flex justify-between items-center">
                            <p class="text-[10px] uppercase tracking-wider">Total Net Weight (KG)</p>
                            <h4 class="text-2xl md:text-3xl underline decoration-2 underline-offset-4">{{ (float)$selectedOrder->kg_target }}</h4>
                        </div>
                    </div>

                    <div class="mkt-surface-alt p-4 md:p-6 rounded-2xl border-l-8 border-red-600 shadow-sm space-y-3 text-left italic border-y border-r mkt-border">
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
                            <span class="w-8 h-8 bg-brand-600 text-white rounded-full flex items-center justify-center text-xs">III</span>
                            JEJAK PRODUKSI <span class="text-red-600">(HISTORY)</span>
                        </h3>
                        
                        <div class="space-y-4 relative before:absolute before:left-[19px] before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                            @foreach($productionHistory as $history)
                                <div class="relative pl-12">
                                    {{-- DOT INDICATOR --}}
                                    <div class="absolute left-0 top-1 w-10 h-10 rounded-full {{ $history->division_name === 'knitting' ? 'bg-brand-600' : 'bg-emerald-600' }} flex items-center justify-center z-10 shadow-sm font-black text-[10px] text-white">
                                        {{ substr(strtoupper($history->division_name), 0, 1) }}
                                    </div>
                                    
                                    <div class="mkt-surface backdrop-blur-md p-6 rounded-[2rem] border mkt-border shadow-sm group hover:border-red-600 transition-all">
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <span class="text-[9px] font-black {{ $history->division_name === 'knitting' ? 'text-brand-600 bg-brand-50' : 'text-emerald-600 bg-emerald-50' }} px-3 py-1 rounded-full uppercase tracking-widest">
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
                                                <p class="text-sm font-black mkt-text">{{ (float)$history->kg }} KG</p>
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

                   <div class="pt-4">@if($selectedLog)
                             @php $techData = $selectedLog->technical_data ?? []; @endphp
                             <div class="mkt-surface-alt backdrop-blur-md p-8 rounded-2xl md:rounded-[3rem] border-t-8 border-brand-600 shadow-xl mt-4">
                                 <h3 class="text-brand-600 font-black mb-6 uppercase italic tracking-tighter text-lg flex items-center">
                                     <span class="mr-2"></span> Data Input Operator Tersimpan ({{ strtoupper(str_replace('-', ' ', $selectedLog->division_name)) }})
                                 </h3>
                                 
                                 @if($selectedLog->division_name === 'knitting')
                                     {{-- HIGH FIDELITY LAYOUT FOR KNITTING --}}
                                     <div class="space-y-8 text-left">
                                         {{-- I. IDENTITAS & SPESIFIKASI MESIN --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black mkt-text-muted uppercase tracking-[0.3em] border-l-4 border-mkt-border pl-3">I. IDENTITAS & SPESIFIKASI MESIN</p>
                                             <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <div>
                                                      <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TGL PRODUKSI</p>
                                                      <p class="text-[11px] font-black mkt-text">{{ !empty($techData['tgl_input']) ? date('d/m/Y', strtotime($techData['tgl_input'])) : $selectedLog->created_at->format('d/m/Y') }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">NO MESIN / TYPE</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase italic">{{ $techData['no_mesin'] ?? '-' }} / {{ $techData['type_mesin'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">GAUGE / INCH</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['gauge_inch'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">FEEDER / JARUM</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['jml_feeder'] ?? '0' }} FDR / {{ $techData['jml_jarum'] ?? '0' }} JRM</p>
                                                 </div>
                                             </div>
                                         </div>

                                         {{-- II. HASIL PRODUKSI GREIGE --}}
                                         <div class="space-y-4">
                                              <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">II. HASIL PRODUKSI GREIGE</p>
                                              <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                  <div>
                                                      <p class="text-[7px] text-slate-400 font-black uppercase mb-1">LEBAR / GRAMASI</p>
                                                      <p class="text-[11px] font-black mkt-text uppercase italic">{{ $techData['lebar'] ?? '-' }} x {{ $techData['gramasi'] ?? '-' }}</p>
                                                  </div>
                                                  <div>
                                                      <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TOTAL OUTPUT</p>
                                                      <p class="text-[11px] font-black text-emerald-500 uppercase">{{ $selectedLog->roll ?? '0' }} ROLL</p>
                                                  </div>
                                                  <div class="col-span-2 mkt-surface-alt p-4 rounded-xl border border-brand-600/10 flex justify-between items-center">
                                                      <div>
                                                          <p class="text-[7px] text-slate-400 font-black uppercase mb-1 italic">ACTUAL WEIGHT</p>
                                                          <p class="text-2xl font-black text-brand-600 italic">{{ (float)$selectedLog->kg ?? 0 }} <span class="text-[10px] text-slate-400">KG</span></p>
                                                      </div>
                                                      @if(isset($techData['produksi_per_day']))
                                                      <div class="text-right">
                                                          <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TARGET / DAY</p>
                                                          <p class="text-sm font-black mkt-text italic">{{ $techData['produksi_per_day'] }} KG</p>
                                                      </div>
                                                      @endif
                                                  </div>
                                             </div>
                                         </div>

                                         {{-- III. PENGGUNAAN BENANG & YL --}}
                                         <div class="space-y-4">
                                              <p class="text-[9px] font-black mkt-text-muted uppercase tracking-[0.3em] border-l-4 border-mkt-border pl-3">III. PENGGUNAAN BENANG & YL</p>
                                              <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                  @foreach(range(1, 4) as $i)
                                                      @if(!empty($techData['benang_'.$i]))
                                                          <div class="space-y-2 border-l border-brand-600/20 pl-4 bg-white/5 p-3 rounded-xl transition-all">
                                                              <p class="text-[7px] text-slate-400 font-black uppercase mb-0.5">SLOT {{ $i }}</p>
                                                              <p class="text-[10px] font-black mkt-text uppercase leading-tight truncate">
                                                                  {{ $techData['benang_'.$i] }}
                                                              </p>
                                                              @if(!empty($techData['benang_'.$i.'_lot']))
                                                                  <p class="text-[9px] font-black text-slate-500 uppercase leading-none">LOT: {{ $techData['benang_'.$i.'_lot'] }}</p>
                                                              @endif
                                                              @if(!empty($techData['benang_'.$i.'_percent']))
                                                                  <p class="text-[11px] font-black mkt-text tracking-tighter">{{ $techData['benang_'.$i.'_percent'] }}%</p>
                                                              @endif
                                                              <div class="pt-2 border-t border-black/5">
                                                                  <p class="text-[7px] text-slate-400 font-bold uppercase">YL</p>
                                                                  <p class="text-[11px] font-bold mkt-text tracking-tighter">{{ $techData['yl_'.$i] ?? '-' }}</p>
                                                                </div>
                                                          </div>
                                                      @endif
                                                  @endforeach
                                              </div>
                                          </div>

                                          {{-- IV. NOTE --}}
                                          <div class="space-y-4">
                                              <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] border-l-4 border-slate-500 pl-3">IV. NOTE & KETERANGAN</p>
                                              <div class="mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                  <p class="text-[7px] text-slate-400 font-black uppercase mb-1">OPERATOR NOTES</p>
                                                  <div class="mkt-surface-alt p-4 rounded-xl border border-black/5">
                                                      <p class="text-[10px] font-bold text-slate-400 italic leading-relaxed">"{{ $techData['note'] ?? 'Tidak ada catatan tambahan dari operator.' }}"</p>
                                                  </div>
                                              </div>
                                          </div>
                                     </div>
                                 @elseif($selectedLog->division_name === 'dyeing')
                                     {{-- Dyeing layout --}}
                                     <div class="space-y-8 text-left">
                                         {{-- I. CEK GREIGE --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">I. CEK GREIGE</p>
                                             <div class="grid grid-cols-3 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">CEK GREIGE</p>
                                                     <p class="text-[11px] font-black text-brand-600 uppercase italic">{{ $techData['cek_greige'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">LEBAR</p>
                                                     <p class="text-[11px] font-black text-brand-600 italic">{{ $techData['lebar'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">GRAMASI</p>
                                                     <p class="text-[11px] font-black text-brand-600 italic">{{ $techData['gramasi'] ?? '-' }}</p>
                                                 </div>
                                             </div>
                                         </div>

                                         {{-- II. PARAMETER TEKNIS --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black mkt-text-muted uppercase tracking-[0.3em] border-l-4 border-mkt-border pl-3">II. PARAMETER LAINNYA</p>
                                             <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">OPERATOR</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['operator'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TANGGAL</p>
                                                     <p class="text-[11px] font-black mkt-text">{{ !empty($techData['tanggal']) ? date('d/m/Y', strtotime($techData['tanggal'])) : '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">JENIS MESIN / NO MESIN</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase italic">{{ $techData['jenis_mesin'] ?? '-' }} / {{ $techData['no_mesin'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">WARNA / KODE</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['warna'] ?? '-' }} / {{ $techData['kode_warna'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">DYE SYSTEM</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['dye_system'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TREATMENT (CHEMICAL)</p>
                                                     <p class="text-[11px] font-black text-emerald-500 uppercase">{{ $techData['treatment'] ?? '-' }}</p>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                 @elseif($selectedLog->division_name === 'relax-dryer')
                                     {{-- Relax Dryer layout --}}
                                     <div class="space-y-8 text-left">
                                         {{-- I. IDENTITAS & WAKTU --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">I. IDENTITAS & WAKTU</p>
                                             <div class="grid grid-cols-2 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">OPERATOR</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['operator'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TANGGAL</p>
                                                     <p class="text-[11px] font-black mkt-text">{{ !empty($techData['tanggal']) ? date('d/m/Y', strtotime($techData['tanggal'])) : '-' }}</p>
                                                 </div>
                                             </div>
                                         </div>

                                         {{-- II. PARAMETER TEKNIS & HASIL FISIK --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black mkt-text-muted uppercase tracking-[0.3em] border-l-4 border-mkt-border pl-3">II. PARAMETER TEKNIS & HASIL FISIK</p>
                                             <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">CHEMICAL</p>
                                                     <p class="text-[11px] font-black text-emerald-500 uppercase italic">{{ $techData['chemical'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">HANDFEEL</p>
                                                     <p class="text-[11px] font-black text-emerald-500 uppercase italic">{{ $techData['handfeel'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">MESIN</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase italic">{{ $techData['no_mesin'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">OVERFEED</p>
                                                     <p class="text-[11px] mkt-text-muted font-black">{{ $techData['overfeed'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TEMPERATUR</p>
                                                     <p class="text-[11px] mkt-text-muted font-black">{{ $techData['suhu'] ?? '-' }}°C</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">SPEED</p>
                                                     <p class="text-[11px] mkt-text-muted font-black">{{ $techData['speed'] ?? '-' }} m/min</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">HASIL LEBAR</p>
                                                     <p class="text-[11px] font-black text-emerald-500 italic">{{ $techData['lebar'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">HASIL GRAMASI</p>
                                                     <p class="text-[11px] font-black text-emerald-500 italic">{{ $techData['gramasi'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">SHRINKAGE (V X H)</p>
                                                     <p class="text-[11px] font-black text-emerald-500 italic">{{ $techData['shrinkage'] ?? '-' }}</p>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                 @elseif($selectedLog->division_name === 'compactor')
                                     {{-- Compactor layout --}}
                                     <div class="space-y-8 text-left">
                                         {{-- I. IDENTITAS & WAKTU --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">I. IDENTITAS & WAKTU</p>
                                             <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">OPERATOR</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['operator'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TANGGAL</p>
                                                     <p class="text-[11px] font-black mkt-text">{{ !empty($techData['tanggal']) ? date('d/m/Y', strtotime($techData['tanggal'])) : '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">NO MESIN</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase italic">{{ $techData['no_mesin'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">RANGKA</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase italic">{{ $techData['rangka'] ?? '-' }}</p>
                                                 </div>
                                             </div>
                                         </div>

                                         {{-- II. PARAMETER MESIN --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black mkt-text-muted uppercase tracking-[0.3em] border-l-4 border-mkt-border pl-3">II. PARAMETER MESIN & DRIVE SETTING</p>
                                             <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TEMPERATURE</p>
                                                     <p class="text-[11px] font-black mkt-text-muted">{{ $techData['suhu'] ?? '-' }}°C</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">SPEED</p>
                                                     <p class="text-[11px] font-black mkt-text-muted">{{ $techData['speed'] ?? '-' }} m/min</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">OVERFEED</p>
                                                     <p class="text-[11px] font-black mkt-text-muted">{{ $techData['overfeed'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">FELT</p>
                                                     <p class="text-[11px] font-black mkt-text">{{ $techData['felt'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">DELIVERY SPEED</p>
                                                     <p class="text-[11px] font-black mkt-text">{{ $techData['delivery_speed'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">FOLDING SPEED</p>
                                                     <p class="text-[11px] font-black mkt-text">{{ $techData['folding_speed'] ?? '-' }}</p>
                                                 </div>
                                             </div>
                                         </div>

                                         {{-- III. HASIL FISIK & OUTCOME --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">III. HASIL FISIK & OUTCOME</p>
                                             <div class="grid grid-cols-3 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">HASIL LEBAR</p>
                                                     <p class="text-[11px] font-black text-emerald-500 italic">{{ $techData['lebar'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">HASIL GRAMASI</p>
                                                     <p class="text-[11px] font-black text-emerald-500 italic">{{ $techData['gramasi'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">SHRINKAGE (V X H)</p>
                                                     <p class="text-[11px] font-black text-emerald-500 italic">{{ $techData['shrinkage'] ?? '-' }}</p>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                 @elseif($selectedLog->division_name === 'heat-setting')
                                     {{-- Heat Setting layout --}}
                                     <div class="space-y-8 text-left">
                                         {{-- I. IDENTITAS & WAKTU --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">I. IDENTITAS & WAKTU</p>
                                             <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">OPERATOR</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['operator'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TANGGAL</p>
                                                     <p class="text-[11px] font-black mkt-text">{{ !empty($techData['tanggal']) ? date('d/m/Y', strtotime($techData['tanggal'])) : '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">NO MESIN</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase italic">{{ $techData['no_mesin'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">RANGKA</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase italic">{{ $techData['rangka'] ?? '-' }}</p>
                                                 </div>
                                             </div>
                                         </div>

                                         {{-- II. PARAMETER MESIN --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black mkt-text-muted uppercase tracking-[0.3em] border-l-4 border-mkt-border pl-3">II. PARAMETER MESIN & DRIVE SETTING</p>
                                             <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TEMPERATUR</p>
                                                     <p class="text-[11px] font-black mkt-text-muted">{{ $techData['suhu'] ?? '-' }}°C</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">SPEED</p>
                                                     <p class="text-[11px] font-black mkt-text-muted">{{ $techData['speed'] ?? '-' }} m/min</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">OVERFEED</p>
                                                     <p class="text-[11px] font-black mkt-text-muted">{{ $techData['overfeed'] ?? '-' }}</p>
                                                 </div>
                                                 <div class="col-span-2 md:col-span-3"></div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">DELIVERY SPEED</p>
                                                     <p class="text-[11px] font-black mkt-text">{{ $techData['delivery_speed'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">FOLDING SPEED</p>
                                                     <p class="text-[11px] font-black mkt-text">{{ $techData['folding_speed'] ?? '-' }}</p>
                                                 </div>
                                             </div>
                                         </div>

                                         {{-- III. HASIL FISIK & OUTCOME --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">III. HASIL FISIK & OUTCOME</p>
                                             <div class="grid grid-cols-2 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">HASIL LEBAR</p>
                                                     <p class="text-[11px] font-black text-emerald-500 italic">{{ $techData['lebar'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">HASIL GRAMASI</p>
                                                     <p class="text-[11px] font-black text-emerald-500 italic">{{ $techData['gramasi'] ?? '-' }}</p>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                 @elseif($selectedLog->division_name === 'stenter')
                                     {{-- Stenter layout --}}
                                     <div class="space-y-8 text-left" x-data="{ subPhase: 'preset' }">
                                         {{-- I. GLOBAL IDENTITAS --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">I. IDENTITAS GLOBAL</p>
                                             <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">OPERATOR</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['operator'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">NO MESIN</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase italic">{{ $techData['no_mesin'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TANGGAL SUBMIT</p>
                                                     <p class="text-[11px] font-black mkt-text">{{ !empty($selectedLog->created_at) ? date('d/m/Y H:i', strtotime($selectedLog->created_at)) : '-' }}</p>
                                                 </div>
                                             </div>
                                         </div>

                                         {{-- II. TABS --}}
                                         <div class="space-y-6">
                                             <div class="flex gap-2 p-1 mkt-surface rounded-2xl w-fit border mkt-border">
                                                 <button type="button" @click="subPhase = 'preset'"
                                                     :class="subPhase === 'preset' ? 'bg-brand-600 text-white shadow-lg' : 'text-slate-400 hover:text-white'"
                                                     class="px-5 py-3 rounded-xl text-[10px] font-black uppercase transition-all">PRESET PHASE</button>
                                                 <button type="button" @click="subPhase = 'drying'"
                                                     :class="subPhase === 'drying' ? 'bg-brand-600 text-white shadow-lg' : 'text-slate-400 hover:text-white'"
                                                     class="px-5 py-3 rounded-xl text-[10px] font-black uppercase transition-all">DRYING PHASE</button>
                                                 <button type="button" @click="subPhase = 'finishing'"
                                                     :class="subPhase === 'finishing' ? 'bg-brand-600 text-white shadow-lg' : 'text-slate-400 hover:text-white'"
                                                     class="px-5 py-3 rounded-xl text-[10px] font-black uppercase transition-all">FINISHING PHASE</button>
                                             </div>

                                             @php
                                                 $preset = $techData['preset'] ?? [];
                                                 $drying = $techData['drying'] ?? [];
                                                 $finishing = $techData['finishing'] ?? [];
                                             @endphp

                                             <!-- Preset Phase -->
                                             <div x-show="subPhase === 'preset'" class="space-y-8 animate-in fade-in duration-300">
                                                 <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                     <div class="col-span-2">
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TANGGAL & RANGKA</p>
                                                         <p class="text-[11px] font-black mkt-text uppercase">{{ !empty($preset['tanggal']) ? date('d/m/Y', strtotime($preset['tanggal'])) : '-' }} / {{ $preset['rangka'] ?? '-' }}</p>
                                                     </div>
                                                     <div>
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TEMPERATURE</p>
                                                         <p class="text-[11px] font-black mkt-text-muted">{{ $preset['suhu'] ?? '-' }} °C</p>
                                                     </div>
                                                     <div>
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">SPEED</p>
                                                         <p class="text-[11px] font-black mkt-text-muted">{{ $preset['speed'] ?? '-' }} m/min</p>
                                                     </div>
                                                     <div>
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">OVERFEED (A/B)</p>
                                                         <p class="text-[11px] font-black mkt-text">{{ $preset['overfeed_a'] ?? '-' }} / {{ $preset['overfeed_b'] ?? '-' }}</p>
                                                     </div>
                                                     <div>
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">PADDER / FAN</p>
                                                         <p class="text-[11px] font-black mkt-text">{{ $preset['padder'] ?? '-' }} / {{ $preset['fan'] ?? '-' }}</p>
                                                     </div>
                                                     <div>
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">DELIVERY / FOLDING</p>
                                                         <p class="text-[11px] font-black mkt-text">{{ $preset['delivery'] ?? '-' }} / {{ $preset['folding'] ?? '-' }}</p>
                                                     </div>
                                                     <div>
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">CHEMICALS</p>
                                                         <p class="text-[11px] font-black mkt-text uppercase">{{ $preset['chem1'] ?? '-' }} , {{ $preset['chem2'] ?? '-' }}</p>
                                                     </div>
                                                     <div class="col-span-4 border-t mkt-border pt-4">
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">HASIL FISIK (LEBAR / GRAMASI / SHRINKAGE)</p>
                                                         <p class="text-[11px] font-black mkt-text-muted uppercase italic">{{ $preset['lebar'] ?? '-' }} cm / {{ $preset['gramasi'] ?? '-' }} gsm / {{ $preset['shrinkage'] ?? '-' }} %</p>
                                                     </div>
                                                 </div>
                                             </div>

                                             <!-- Drying Phase -->
                                             <div x-show="subPhase === 'drying'" class="space-y-8 animate-in fade-in duration-300">
                                                 <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                     <div class="col-span-2">
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TANGGAL & RANGKA</p>
                                                         <p class="text-[11px] font-black mkt-text uppercase">{{ !empty($drying['tanggal']) ? date('d/m/Y', strtotime($drying['tanggal'])) : '-' }} / {{ $drying['rangka'] ?? '-' }}</p>
                                                     </div>
                                                     <div>
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TEMPERATURE</p>
                                                         <p class="text-[11px] font-black mkt-text-muted">{{ $drying['suhu'] ?? '-' }} °C</p>
                                                     </div>
                                                     <div>
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">SPEED</p>
                                                         <p class="text-[11px] font-black mkt-text-muted">{{ $drying['speed'] ?? '-' }} m/min</p>
                                                     </div>
                                                     <div>
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">OVERFEED (A/B)</p>
                                                         <p class="text-[11px] font-black mkt-text">{{ $drying['overfeed_a'] ?? '-' }} / {{ $drying['overfeed_b'] ?? '-' }}</p>
                                                     </div>
                                                     <div>
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">PADDER / FAN</p>
                                                         <p class="text-[11px] font-black mkt-text">{{ $drying['padder'] ?? '-' }} / {{ $drying['fan'] ?? '-' }}</p>
                                                     </div>
                                                     <div>
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">DELIVERY / FOLDING</p>
                                                         <p class="text-[11px] font-black mkt-text">{{ $drying['delivery'] ?? '-' }} / {{ $drying['folding'] ?? '-' }}</p>
                                                     </div>
                                                     <div>
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">CHEMICALS</p>
                                                         <p class="text-[11px] font-black mkt-text uppercase">{{ $drying['chem1'] ?? '-' }} , {{ $drying['chem2'] ?? '-' }}</p>
                                                     </div>
                                                     <div class="col-span-4 border-t mkt-border pt-4">
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">HASIL FISIK (LEBAR / GRAMASI / SHRINKAGE)</p>
                                                         <p class="text-[11px] font-black mkt-text-muted uppercase italic">{{ $drying['lebar'] ?? '-' }} cm / {{ $drying['gramasi'] ?? '-' }} gsm / {{ $drying['shrinkage'] ?? '-' }} %</p>
                                                     </div>
                                                 </div>
                                             </div>

                                             <!-- Finishing Phase -->
                                             <div x-show="subPhase === 'finishing'" class="space-y-8 animate-in fade-in duration-300">
                                                 <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                     <div class="col-span-2">
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TANGGAL & RANGKA</p>
                                                         <p class="text-[11px] font-black mkt-text uppercase">{{ !empty($finishing['tanggal']) ? date('d/m/Y', strtotime($finishing['tanggal'])) : '-' }} / {{ $finishing['rangka'] ?? '-' }}</p>
                                                     </div>
                                                     <div>
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TEMPERATURE</p>
                                                         <p class="text-[11px] font-black mkt-text-muted">{{ $finishing['suhu'] ?? '-' }} °C</p>
                                                     </div>
                                                     <div>
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">SPEED</p>
                                                         <p class="text-[11px] font-black mkt-text-muted">{{ $finishing['speed'] ?? '-' }} m/min</p>
                                                     </div>
                                                     <div>
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">OVERFEED (A/B)</p>
                                                         <p class="text-[11px] font-black mkt-text">{{ $finishing['overfeed_a'] ?? '-' }} / {{ $finishing['overfeed_b'] ?? '-' }}</p>
                                                     </div>
                                                     <div>
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">PADDER / FAN</p>
                                                         <p class="text-[11px] font-black mkt-text">{{ $finishing['padder'] ?? '-' }} / {{ $finishing['fan'] ?? '-' }}</p>
                                                     </div>
                                                     <div>
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">DELIVERY / FOLDING</p>
                                                         <p class="text-[11px] font-black mkt-text">{{ $finishing['delivery'] ?? '-' }} / {{ $finishing['folding'] ?? '-' }}</p>
                                                     </div>
                                                     <div>
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">CHEMICALS</p>
                                                         <p class="text-[11px] font-black mkt-text uppercase">{{ $finishing['chem1'] ?? '-' }} , {{ $finishing['chem2'] ?? '-' }}</p>
                                                     </div>
                                                     <div class="col-span-4 border-t mkt-border pt-4">
                                                         <p class="text-[7px] text-slate-400 font-black uppercase mb-1">HASIL FISIK (LEBAR / GRAMASI / SHRINKAGE)</p>
                                                         <p class="text-[11px] font-black mkt-text-muted uppercase italic">{{ $finishing['lebar'] ?? '-' }} cm / {{ $finishing['gramasi'] ?? '-' }} gsm / {{ $finishing['shrinkage'] ?? '-' }} %</p>
                                                     </div>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                 @elseif($selectedLog->division_name === 'tumbler')
                                     {{-- Tumbler layout --}}
                                     <div class="space-y-8 text-left">
                                         {{-- I. IDENTITAS & WAKTU --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">I. IDENTITAS & WAKTU (TUMBLER DRY)</p>
                                             <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">OPERATOR</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['operator'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TANGGAL</p>
                                                     <p class="text-[11px] font-black mkt-text">{{ !empty($techData['tanggal']) ? date('d/m/Y', strtotime($techData['tanggal'])) : '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">NO MESIN</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase italic">{{ $techData['no_mesin'] ?? '-' }}</p>
                                                 </div>
                                             </div>
                                         </div>

                                         {{-- II. PARAMETER MESIN --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black mkt-text-muted uppercase tracking-[0.3em] border-l-4 border-mkt-border pl-3">II. PARAMETER SETTING MESIN</p>
                                             <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TEMPERATURE</p>
                                                     <p class="text-[11px] font-black mkt-text-muted">{{ $techData['suhu'] ?? '-' }}°C</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">STEAM INJECT</p>
                                                     <p class="text-[11px] font-black mkt-text-muted">{{ $techData['steam_inject'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">HOTWIND</p>
                                                     <p class="text-[11px] font-black mkt-text-muted">{{ $techData['hotwind'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">COLDWIND</p>
                                                     <p class="text-[11px] font-black mkt-text-muted">{{ $techData['coldwind'] ?? '-' }}</p>
                                                 </div>
                                             </div>
                                         </div>

                                         {{-- III. HASIL FISIK & OUTCOME --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">III. HASIL FISIK & OUTCOME</p>
                                             <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">LEBAR</p>
                                                     <p class="text-[11px] font-black text-emerald-500 italic">{{ $techData['lebar'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">GRAMASI</p>
                                                     <p class="text-[11px] font-black text-emerald-500 italic">{{ $techData['gramasi'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">SHRINKAGE (V x H)</p>
                                                     <p class="text-[11px] font-black text-emerald-500 italic">{{ $techData['shrinkage'] ?? '-' }}</p>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                 @elseif($selectedLog->division_name === 'fleece')
                                     {{-- Fleece layout --}}
                                     <div class="space-y-8 text-left">
                                         {{-- I. GLOBAL INFO --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">I. IDENTITAS MESIN (FLEECE)</p>
                                             <div class="mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <p class="text-[7px] text-slate-400 font-black uppercase mb-1">NO MESIN</p>
                                                 <p class="text-[11px] font-black mkt-text uppercase italic">{{ $techData['no_mesin'] ?? '-' }}</p>
                                             </div>
                                         </div>

                                         {{-- II. DETIL PROSES SIDE-BY-SIDE --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black mkt-text-muted uppercase tracking-[0.3em] border-l-4 border-mkt-border pl-3">II. DETAIL PARAMETER PROSES</p>
                                             <div class="overflow-x-auto">
                                                 <div class="min-w-[700px] space-y-4">
                                                     <div class="grid grid-cols-12 gap-4 bg-white/5 px-6 py-4 rounded-2xl items-center text-[9px] font-black uppercase tracking-widest text-slate-400 text-center">
                                                         <div class="col-span-3 text-left">PARAMETER</div>
                                                         <div class="col-span-3 text-amber-500">RAISING</div>
                                                         <div class="col-span-3 text-sky-500">BRUSHING</div>
                                                         <div class="col-span-3 text-emerald-500">SHEARING</div>
                                                     </div>

                                                     @php
                                                         $raising = $techData['raising'] ?? [];
                                                         $brushing = $techData['brushing'] ?? [];
                                                         $shearing = $techData['shearing'] ?? [];

                                                         $fleeceParams = [
                                                             ['label' => 'OPERATOR', 'r' => $raising['operator'] ?? '-', 'b' => $brushing['operator'] ?? '-', 's' => $shearing['operator'] ?? '-'],
                                                             ['label' => 'TANGGAL', 'r' => !empty($raising['tanggal']) ? date('d/m/Y', strtotime($raising['tanggal'])) : '-', 'b' => !empty($brushing['tanggal']) ? date('d/m/Y', strtotime($brushing['tanggal'])) : '-', 's' => !empty($shearing['tanggal']) ? date('d/m/Y', strtotime($shearing['tanggal'])) : '-'],
                                                             ['label' => 'STANDAR BULU', 'r' => $raising['standar_bulu'] ?? '-', 'b' => $brushing['standar_bulu'] ?? '-', 's' => '-'],
                                                             ['label' => 'SPEED', 'r' => $raising['speed'] ?? '-', 'b' => $brushing['cloth_speed'] ?? '-', 's' => $shearing['speed'] ?? '-'],
                                                             ['label' => 'CLOTH OUT', 'r' => $raising['cloth_out'] ?? '-', 'b' => $brushing['cloth_out'] ?? '-', 's' => $shearing['cloth_out'] ?? '-'],
                                                             ['label' => 'BEND / STRIGHT PIN', 'r' => ($raising['bend_pin'] ?? '-') . ' / ' . ($raising['stright_pin'] ?? '-'), 'b' => '-', 's' => '-'],
                                                             ['label' => 'RPM DRUM', 'r' => $raising['rpm_drum'] ?? '-', 'b' => $brushing['rpm_drum'] ?? '-', 's' => '-'],
                                                             ['label' => 'DRUM BRUSH', 'r' => $raising['drum_brush'] ?? '-', 'b' => '-', 's' => '-'],
                                                             ['label' => 'LEFT / RIGHT BRUSH', 'r' => '-', 'b' => ($brushing['left_brush'] ?? '-') . ' / ' . ($brushing['right_brush'] ?? '-'), 's' => '-'],
                                                             ['label' => 'TENSION', 'r' => '-', 'b' => $brushing['tension'] ?? '-', 's' => '-'],
                                                             ['label' => 'EXPENDING / SHEAR', 'r' => '-', 'b' => '-', 's' => ($shearing['expending'] ?? '-') . ' / ' . ($shearing['shear'] ?? '-')],
                                                             ['label' => 'LEBAR / GRAMASI', 'r' => $raising['lebar_gsm'] ?? '-', 'b' => $brushing['lebar_gramasi'] ?? '-', 's' => $shearing['lebar_gramasi'] ?? '-'],
                                                         ];
                                                     @endphp

                                                     <div class="space-y-2">
                                                         @foreach($fleeceParams as $param)
                                                             <div class="grid grid-cols-12 gap-4 mkt-surface border mkt-border px-6 py-4 rounded-xl items-center text-center font-bold text-xs">
                                                                 <div class="col-span-3 text-left">
                                                                     <span class="text-[10px] text-slate-400 uppercase">
                                                                         {{ $param['label'] }}
                                                                     </span>
                                                                 </div>
                                                                 <div class="col-span-3 text-amber-500 italic uppercase">
                                                                     {{ $param['r'] }}
                                                                 </div>
                                                                 <div class="col-span-3 text-sky-500 italic uppercase">
                                                                     {{ $param['b'] }}
                                                                 </div>
                                                                 <div class="col-span-3 text-emerald-500 italic uppercase">
                                                                     {{ $param['s'] }}
                                                                 </div>
                                                             </div>
                                                         @endforeach
                                                     </div>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                 @elseif($selectedLog->division_name === 'pengujian')
                                     {{-- Pengujian layout --}}
                                     <div class="space-y-8 text-left">
                                         {{-- I. IDENTITAS & WAKTU --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black text-cyan-500 uppercase tracking-[0.3em] border-l-4 border-cyan-500 pl-3">I. IDENTITAS & WAKTU (PENGUJIAN QC & LAB)</p>
                                             <div class="grid grid-cols-2 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">OPERATOR PENGUJI</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase italic">{{ $techData['operator'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">TANGGAL UJI</p>
                                                     <p class="text-[11px] font-black mkt-text italic">{{ !empty($techData['tanggal']) ? date('d/m/Y', strtotime($techData['tanggal'])) : '-' }}</p>
                                                 </div>
                                             </div>
                                         </div>

                                         {{-- II. HASIL PENGUJIAN FISIK --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black mkt-text-muted uppercase tracking-[0.3em] border-l-4 border-mkt-border pl-3">II. HASIL PENGUJIAN FISIK</p>
                                             <div class="grid grid-cols-2 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">HASIL LEBAR</p>
                                                     <p class="text-[11px] font-black mkt-text-muted italic">{{ $techData['lebar'] ?? '-' }} cm</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">HASIL GRAMASI</p>
                                                     <p class="text-[11px] font-black mkt-text-muted italic">{{ $techData['gramasi'] ?? '-' }} gsm</p>
                                                 </div>
                                             </div>
                                         </div>

                                         {{-- III. METRIK PENGUJIAN KUALITAS --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">III. METRIK PENGUJIAN KUALITAS</p>
                                             <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">SHRINKAGE</p>
                                                     <p class="text-[11px] font-black text-emerald-500 italic">{{ $techData['shrinkage'] ?? '-' }}%</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">SPIRALITY</p>
                                                     <p class="text-[11px] font-black text-emerald-500 italic">{{ $techData['spirality'] ?? '-' }}%</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">SKEWNESS</p>
                                                     <p class="text-[11px] font-black text-emerald-500 italic">{{ $techData['skewness'] ?? '-' }}%</p>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                 @elseif($selectedLog->division_name === 'qe')
                                     {{-- QE layout --}}
                                     <div class="space-y-8 text-left">
                                         {{-- I. IDENTITAS & OPERATOR --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black mkt-text-muted uppercase tracking-[0.3em] border-l-4 border-mkt-border pl-3">I. IDENTITAS KAIN & OPERATOR (QE)</p>
                                             <div class="grid grid-cols-2 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">OPERATOR QE</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase italic">{{ $techData['operator'] ?? '-' }}</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">FABRIC NAME</p>
                                                     <p class="text-[11px] font-black mkt-text uppercase italic">{{ $techData['fabric_name'] ?? '-' }}</p>
                                                 </div>
                                             </div>
                                         </div>

                                         {{-- II. HASIL VALIDASI FISIK --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black text-cyan-500 uppercase tracking-[0.3em] border-l-4 border-cyan-500 pl-3">II. HASIL VALIDASI FISIK (FINAL SPECIFICATION)</p>
                                             <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">LEBAR</p>
                                                     <p class="text-[11px] font-black text-cyan-500 italic">{{ $techData['lebar'] ?? '-' }} cm</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">GRAMASI</p>
                                                     <p class="text-[11px] font-black text-cyan-500 italic">{{ $techData['gramasi'] ?? '-' }} gsm</p>
                                                 </div>
                                                 <div>
                                                     <p class="text-[7px] text-slate-400 font-black uppercase mb-1">SHRINKAGE</p>
                                                     <p class="text-[11px] font-black text-cyan-500 italic">{{ $techData['shrinkage'] ?? '-' }}%</p>
                                                 </div>
                                             </div>
                                         </div>

                                         {{-- III. REKOMENDASI & CATATAN --}}
                                         <div class="space-y-4">
                                             <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">III. REKOMENDASI & CATATAN (NOTE)</p>
                                             <div class="mkt-surface border mkt-border p-6 rounded-2xl font-bold text-xs">
                                                 <p class="text-[7px] text-slate-400 font-black uppercase mb-1">FINAL NOTE</p>
                                                 <p class="text-[11px] font-black text-emerald-500 uppercase italic">{{ $techData['note'] ?? '-' }}</p>
                                             </div>
                                         </div>
                                     </div>
                                 @else
                                     {{-- FALLBACK GENERIC FLAT GRID --}}
                                     <div class="grid grid-cols-2 md:grid-cols-3 gap-6 font-bold text-xs">
                                         @foreach($selectedLog->technical_data ?? [] as $key => $value)
                                             <div class="border-b mkt-border pb-2">
                                                 <p class="text-[9px] text-slate-400 uppercase mb-1 tracking-widest">{{ str_replace('_', ' ', $key) }}</p>
                                                 <p class="text-base mkt-text uppercase italic">{{ is_array($value) ? json_encode($value) : ($value ?: '-') }}</p>
                                             </div>
                                         @endforeach
                                     </div>
                                 @endif
                             </div>
                         @else


                            {{-- Mengubah dari <a> tag menjadi button wire:click agar WIP status bisa diupdate sebelum redirect --}}
                            <button wire:click="startProcessAndRedirect({{ $selectedOrder->id }})" 
                                class="w-full bg-brand-600 text-white py-6 rounded-[2rem] font-black uppercase text-sm flex items-center justify-center gap-3 hover:bg-brand-500 transition-all shadow-xl tracking-[0.2em]">
                                
                                {{-- Nama tombol otomatis berubah sesuai divisi --}}
                                TERIMA & KERJAKAN {{ strtoupper(auth()->user()->role) }}
                            </button>
                        @endif
                    </div>
                </div>
        
            @else
                {{-- TAMPILAN 2: FORM INPUT HASIL --}}
                <form wire:submit.prevent="submitProduction" class="italic text-left font-black">
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div class="col-span-2 bg-brand-50 dark:bg-brand-950 p-4 rounded-2xl flex justify-between items-center border border-brand-100 dark:border-brand-900">
                            <p class="text-xs text-brand-800 dark:text-brand-200 uppercase italic">Shift Kerja Aktif:</p>
                            <span class="bg-brand-600 text-white px-4 py-1 rounded-full text-xs italic tracking-widest uppercase font-black">Shift {{ $shift }}</span>
                        </div>

                        <div>
                            <label class="text-[10px] text-slate-400 uppercase ml-2">Total Berat (KG)</label>
                            <input type="number" step="0.1" wire:model="qty_kg" 
                                class="w-full mkt-surface border mkt-border rounded-2xl p-4 text-xl font-black mkt-text focus:ring-4 focus:ring-brand-500/20 {{ $errors->has('qty_kg') ? 'ring-2 ring-red-500' : '' }}">
                            @error('qty_kg') <p class="text-[9px] text-red-500 mt-1 ml-2 font-bold uppercase italic">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-[10px] text-slate-400 uppercase ml-2">Jumlah Roll</label>
                            <input type="number" wire:model="qty_roll" required
                                class="w-full mkt-surface border mkt-border rounded-2xl p-4 text-xl font-black mkt-text focus:ring-4 focus:ring-brand-500/20 italic" placeholder="0">
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button type="button" wire:click="$set('showInputForm', false)" 
                            class="flex-1 mkt-surface-alt mkt-text-muted py-4 rounded-2xl font-black uppercase text-xs border mkt-border hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">KEMBALI</button>
                        <button type="submit" 
                            class="flex-[2] bg-emerald-600 text-white py-4 rounded-2xl font-black uppercase text-xs shadow-lg shadow-emerald-600/20 hover:bg-emerald-500 transition-all">
                            SIMPAN HASIL PRODUKSI                         </button>
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