<?php
use Livewire\Volt\Component;
use App\Models\Setting;
use App\Models\ActivityLog;

new class extends Component {

    protected $listeners = [
        'purge-logs-confirmed' => 'purgeOldLogs',
        'maintenance-confirmed' => 'toggleMaintenance'
    ];
    public $shift_duration, $max_capacity, $target_minimal, $is_maintenance;

    public function mount() {
        $this->shift_duration = Setting::where('key', 'shift_duration')->first()->value ?? '8';
        $this->max_capacity = Setting::where('key', 'max_capacity')->first()->value ?? '1000';
        $this->target_minimal = Setting::where('key', 'target_minimal')->first()->value ?? '400';
        $this->is_maintenance = app()->isDownForMaintenance();
    }

    public function saveParameters() {
        // Validasi dan pembersihan data (null ke 0)
        $data = [
            'shift_duration' => $this->shift_duration ?: 0,
            'max_capacity' => $this->max_capacity ?: 0,
            'target_minimal' => $this->target_minimal ?: 0,
        ];

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => 'global']);
        }

        // Catat di Audit Trail
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'UPDATE',
            'model' => 'SYSTEM_CONFIG',
            'description' => "Admin memperbarui Parameter Produksi: Shift {$data['shift_duration']}h, Kapasitas {$data['max_capacity']}kg, Target {$data['target_minimal']}kg",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        $this->dispatch('show-success-toast', message: "Parameter Produksi Berhasil Disimpan! ✅");
    }

    public function purgeOldLogs() {
        if (auth()->user()->role !== 'super-admin') return;

        try {
            $deletedCount = ActivityLog::where('created_at', '<', now()->subMonths(6))->delete();
            $message = $deletedCount > 0 
                ? "Pembersihan berhasil. {$deletedCount} log lama dihapus." 
                : "Tidak ada log lama (> 6 bulan) yang perlu dibersihkan.";

            $this->dispatch('show-success-toast', message: $message);
        } catch (\Exception $e) {
            $this->dispatch('show-error-toast', message: "Error: " . $e->getMessage());
        }
    }

    public function toggleMaintenance() {
        if (auth()->user()->role !== 'super-admin') return;
        $user = auth()->user();

        if (app()->isDownForMaintenance()) {
            \Illuminate\Support\Facades\Artisan::call('up');
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'UPDATE',
                'model' => 'SYSTEM',
                'description' => "Admin {$user->name} menonaktifkan Mode Maintenance.",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            $this->is_maintenance = false;
        } else {
            \Illuminate\Support\Facades\Artisan::call('down', ['--secret' => 'rnd-duniatex-2026']);
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'UPDATE',
                'model' => 'SYSTEM',
                'description' => "Admin {$user->name} mengaktifkan Mode Maintenance.",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            $this->is_maintenance = true;
        }
        $this->dispatch('show-success-toast', message: "Status Maintenance Berhasil Diperbarui.");
    }
}; ?>

<div class="min-h-screen mkt-bg mkt-text p-8 transition-colors duration-300 font-sans italic">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-5xl font-black italic uppercase tracking-tighter mkt-text mb-10">
            SYSTEM <span class="text-red-600">CONFIGURATION</span>
        </h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Production Parameters --}}
            <div class="lg:col-span-2 mkt-surface border mkt-border p-10 rounded-[3rem] shadow-2xl relative overflow-hidden">
                <h3 class="text-2xl font-black italic uppercase text-red-500 mb-8 flex items-center gap-3">
                    <span class="w-2 h-8 bg-red-600 rounded-full"></span>
                    Production Parameters
                </h3>
                
                <form wire:submit.prevent="saveParameters" class="space-y-8 relative z-10">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-3 tracking-[0.2em]">Durasi Shift (Jam)</label>
                            <input type="number" wire:model="shift_duration"
                                class="w-full mkt-input mkt-border rounded-2xl p-5 mkt-text font-black text-xl italic focus:border-red-600 focus:ring-4 focus:ring-red-600/10 transition-all outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-3 tracking-[0.2em]">Kapasitas Mesin Maksimal (KG)</label>
                            <input type="number" wire:model="max_capacity"
                                class="w-full mkt-input mkt-border rounded-2xl p-5 mkt-text font-black text-xl italic focus:border-red-600 focus:ring-4 focus:ring-red-600/10 transition-all outline-none">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-3 tracking-[0.2em]">Target Minimal per Shift (KG)</label>
                            <input type="number" wire:model="target_minimal"
                                class="w-full mkt-input mkt-border rounded-2xl p-5 mkt-text font-black text-xl italic focus:border-red-600 focus:ring-4 focus:ring-red-600/10 transition-all outline-none">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-red-600 hover:bg-black text-white py-6 rounded-2xl font-black uppercase italic tracking-[0.2em] shadow-xl shadow-red-900/20 transition-all transform hover:-translate-y-1">
                        SAVE PRODUCTION PARAMETERS 💾
                    </button>
                </form>
                
                <div class="absolute -right-20 -bottom-20 opacity-[0.03] text-[15rem] font-black italic pointer-events-none">UNIT</div>
            </div>

            {{-- System Health & Actions --}}
            <div class="space-y-8">
                <div class="mkt-surface border mkt-border p-8 rounded-[3rem] shadow-2xl">
                    <h3 class="text-xl font-black italic uppercase text-emerald-500 mb-6 flex items-center gap-3">
                        <span class="w-2 h-6 bg-emerald-500 rounded-full"></span>
                        System Health
                    </h3>
                    <div class="flex items-center gap-4 mkt-input p-5 rounded-2xl border mkt-border mb-6">
                        <div class="w-3 h-3 {{ $is_maintenance ? 'bg-amber-500' : 'bg-green-500' }} rounded-full animate-pulse shadow-[0_0_10px_rgba(16,185,129,0.5)]"></div>
                        <span class="text-[10px] font-black uppercase italic tracking-widest mkt-text">
                            STATUS: {{ $is_maintenance ? 'MAINTENANCE MODE' : 'SYSTEM ONLINE' }}
                        </span>
                    </div>

                    <div class="space-y-3">
                        <button type="button" onclick="confirmMaintenance({{ $is_maintenance ? 'true' : 'false' }})" 
                            class="w-full py-4 rounded-2xl font-black uppercase italic text-[10px] tracking-widest transition {{ $is_maintenance ? 'bg-amber-600 text-white animate-pulse' : 'mkt-surface-alt mkt-text border mkt-border hover:bg-slate-800' }}">
                            {{ $is_maintenance ? 'DISABLE MAINTENANCE' : 'ENABLE MAINTENANCE' }}
                        </button>
                        
                        <a href="{{ route('admin.backup') }}" class="block w-full bg-slate-900 text-white text-center py-4 rounded-2xl font-black uppercase italic text-[10px] tracking-widest hover:bg-red-600 transition shadow-lg">
                            DOWNLOAD DB BACKUP
                        </a>

                        <button type="button" onclick="confirmPurgeLogs()" class="block w-full mkt-surface-alt mkt-text-muted border mkt-border py-4 rounded-2xl font-black uppercase italic text-[10px] tracking-widest hover:text-red-500 transition">
                            PURGE AUDIT LOGS
                        </button>
                    </div>
                </div>

                <div class="mkt-surface-alt p-6 rounded-[2.5rem] border mkt-border">
                    <p class="text-[9px] text-slate-500 font-bold uppercase italic leading-relaxed">
                        <span class="text-red-600 font-black italic">PRO TIP:</span> 
                        Gunakan tombol simpan setelah mengubah parameter produksi untuk memastikan seluruh dashboard operator terupdate secara serentak.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    window.confirmMaintenance = (isCurrentMaintenance) => {
        Swal.fire({
            title: `<span style="color: #fff; font-style: italic; font-weight: 900; text-transform: uppercase; letter-spacing: -1px;">${isCurrentMaintenance ? 'OFFLINE KEMBALI?' : 'AKTIFKAN MAINTENANCE?'}</span>`,
            background: '#0f172a',
            showCancelButton: true,
            confirmButtonColor: isCurrentMaintenance ? '#10b981' : '#f59e0b',
            confirmButtonText: 'YA, LANJUTKAN',
            customClass: { popup: 'rounded-[2rem] border border-white/10' }
        }).then((result) => { if (result.isConfirmed) { $wire.dispatch('maintenance-confirmed'); } })
    }

    window.confirmPurgeLogs = () => {
        Swal.fire({
            title: '<span style="color: #fff; font-style: italic; font-weight: 900; text-transform: uppercase;">BERSIHKAN LOG?</span>',
            background: '#0f172a',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'YA, HAPUS'
        }).then((result) => { if (result.isConfirmed) { $wire.dispatch('purge-logs-confirmed'); } })
    }

    $wire.on('show-success-toast', (event) => {
        const data = Array.isArray(event) ? event[0] : event;
        Swal.fire({
            toast: true, position: 'top-end', icon: 'success',
            title: `<span class="text-xs font-bold uppercase">${data.message}</span>`,
            showConfirmButton: false, timer: 3000, background: '#0f172a', color: '#fff'
        });
    });
</script>
@endscript