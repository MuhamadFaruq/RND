<?php
use Livewire\Volt\Component;
use App\Models\Setting;
use App\Models\ActivityLog;

new class extends Component {

    protected $listeners = ['purge-logs-confirmed' => 'purgeOldLogs'];
    
    public $shift_duration, $max_capacity, $server_status;

    public function mount() {
        $this->shift_duration = Setting::where('key', 'shift_duration')->first()->value ?? '8';
        $this->max_capacity = Setting::where('key', 'max_capacity')->first()->value ?? '1000';
    }

    public function updateSetting($key, $value) {
        Setting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => 'global']);
        $this->dispatch('show-success-toast', message: "Parameter " . strtoupper($key) . " diperbarui.");
    }

    public function purgeOldLogs()
    {
        // Hanya Super-Admin yang bisa mengeksekusi ini di tingkat server
        if (auth()->user()->role !== 'super-admin') return;

        // Menghapus log yang lebih tua dari 6 bulan
        $deletedCount = \App\Models\ActivityLog::where('created_at', '<', now()->subMonths(6))->delete();

        $this->dispatch('show-success-toast', 
            message: $deletedCount > 0 
                ? "Pembersihan berhasil. {$deletedCount} log lama dihapus." 
                : "Tidak ada log lama yang perlu dibersihkan."
        );
    }
}; ?>

<div class="min-h-screen bg-slate-950 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-5xl font-black italic uppercase tracking-tighter text-white mb-10">
            SYSTEM <span class="text-red-600">CONFIGURATION</span>
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-slate-900 border border-white/5 p-8 rounded-[3rem] shadow-2xl">
                <h3 class="text-xl font-black italic uppercase text-red-500 mb-6">Production Parameters</h3>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-2 tracking-[0.2em]">Durasi Shift (Jam)</label>
                        <input type="number" wire:model.live="shift_duration" wire:change="updateSetting('shift_duration', $shift_duration)"
                            class="w-full bg-slate-950 border-white/5 rounded-2xl p-4 text-white font-black italic focus:border-red-600 focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-2 tracking-[0.2em]">Kapasitas Mesin Maksimal (KG)</label>
                        <input type="number" wire:model.live="max_capacity" wire:change="updateSetting('max_capacity', $max_capacity)"
                            class="w-full bg-slate-950 border-white/5 rounded-2xl p-4 text-white font-black italic focus:border-red-600 focus:ring-0">
                    </div>
                </div>
            </div>

            <div class="bg-slate-900 border border-white/5 p-8 rounded-[3rem] shadow-2xl flex flex-col justify-between">
                <div>
                    <h3 class="text-xl font-black italic uppercase text-emerald-500 mb-6">System Health</h3>
                    <div class="flex items-center gap-4 bg-slate-950 p-4 rounded-2xl border border-white/5">
                        <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></div>
                        <span class="text-xs font-black uppercase italic tracking-widest text-slate-300">Server Status: Online</span>
                    </div>
                </div>

                <div class="mt-8 space-y-3">
                    <a href="{{ route('admin.backup') }}" class="block w-full bg-red-600 text-center py-4 rounded-2xl font-black uppercase italic text-xs tracking-widest hover:bg-red-700 transition">
                        DOWNLOAD DATABASE BACKUP
                    </a>
                    <button type="button" onclick="confirmPurgeLogs()" class="block w-full bg-slate-800 text-slate-400 py-4 rounded-2xl font-black uppercase italic text-xs tracking-widest hover:bg-red-900/30 hover:text-red-500 transition">
                        PURGE AUDIT LOGS (> 6 Months)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>