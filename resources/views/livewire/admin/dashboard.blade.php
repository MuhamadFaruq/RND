<?php

use Livewire\Volt\Component;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Division;
use App\Models\Setting;
use Carbon\Carbon;

new class extends Component {
    public function with()
    {
        // Mengambil data dengan penamaan variabel yang sangat jelas
        $countUsers = User::count();
        $countDivs = Division::count();
        $countLogsToday = ActivityLog::whereDate('created_at', Carbon::today())->count();
        $limitCapacity = Setting::where('key', 'max_capacity')->value('value') ?? 1000;
        
        // Asumsi nilai output 0 sampai Logbook Produksi dibuat
        $currentWeight = 0; 
        $pct = ($limitCapacity > 0) ? ($currentWeight / $limitCapacity) * 100 : 0;

        return [
            'totalUsers' => $countUsers, // Pastikan ini terpanggil di Blade
            'totalDivisions' => $countDivs,
            'totalLogsToday' => $countLogsToday,
            'maxCapacity' => $limitCapacity,
            'currentOutput' => $currentWeight,
            'percentage' => min($pct, 100),
            'recentActivities' => ActivityLog::with('user')->latest()->take(5)->get(),
        ];
    }
}; ?>

<div class="min-h-screen w-full bg-slate-950 text-white font-sans italic flex flex-col">
    <div class="p-4 md:p-8 flex-grow container mx-auto">
        
        {{-- HEADER SECTION --}}
        <div class="mb-10 border-b border-white/5 pb-8 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <h1 class="text-5xl md:text-7xl font-black italic tracking-tighter uppercase text-white leading-none">
                    System <span class="text-red-600">Overview</span>
                </h1>
                <p class="text-slate-500 font-bold tracking-widest uppercase text-[10px] mt-3 italic">
                    Logistik & Produksi System <span class="text-white mx-2">|</span> User: <span class="text-emerald-400">{{ auth()->user()->name }}</span>
                </p>
            </div>
            <div class="bg-slate-900 px-6 py-4 rounded-3xl border border-white/5 shadow-2xl text-right">
                <span class="text-[9px] font-black text-slate-500 uppercase block tracking-[0.3em] mb-1">Server Time</span>
                <span class="text-3xl font-mono font-bold text-red-600">{{ now()->format('H:i') }}</span>
            </div>
        </div>

        {{-- QUICK STATS GRID --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            
            {{-- 1. OUTPUT --}}
            <div class="bg-slate-900 p-7 rounded-[2.5rem] border border-white/5 shadow-2xl relative overflow-hidden group">
                <p class="text-[10px] font-black text-slate-500 uppercase mb-3 italic tracking-widest">Output Hari Ini</p>
                <div class="flex items-baseline gap-2 mb-4">
                    <h3 class="text-4xl font-black italic tracking-tighter text-white">{{ number_format($currentOutput, 2) }}</h3>
                    <span class="text-xs font-bold text-slate-500 uppercase italic">KG</span>
                </div>
                <div class="w-full bg-slate-950 h-2 rounded-full overflow-hidden border border-white/5">
                    <div class="bg-red-600 h-full transition-all duration-1000" style="width: {{ $percentage }}%"></div>
                </div>
                <p class="text-[8px] font-bold text-slate-600 mt-3 uppercase italic tracking-widest">Max: {{ number_format($maxCapacity) }} KG</p>
                <div class="absolute left-0 top-0 w-1.5 h-full bg-red-600"></div>
            </div>

            {{-- 2. PERSONNEL --}}
            <div class="bg-slate-900 p-7 rounded-[2.5rem] border border-white/5 shadow-2xl relative overflow-hidden">
                <p class="text-[10px] font-black text-slate-500 uppercase mb-3 italic tracking-widest">Total Personel</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-4xl font-black italic tracking-tighter text-white">{{ $totalUsers ?? 0 }}</h3>
                    <span class="text-xs font-bold text-slate-500 uppercase italic">User</span>
                </div>
                <div class="absolute left-0 top-0 w-1.5 h-full bg-blue-600"></div>
            </div>

            {{-- 3. LOGS --}}
            <div class="bg-slate-900 p-7 rounded-[2.5rem] border border-white/5 shadow-2xl relative overflow-hidden">
                <p class="text-[10px] font-black text-slate-500 uppercase mb-3 italic tracking-widest">Log Aktivitas</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-4xl font-black italic tracking-tighter text-white">{{ $totalLogsToday ?? 0 }}</h3>
                    <span class="text-xs font-bold text-slate-500 uppercase italic">Event</span>
                </div>
                <div class="absolute left-0 top-0 w-1.5 h-full bg-emerald-600"></div>
            </div>

            {{-- 4. DIVISIONS --}}
            <div class="bg-slate-900 p-7 rounded-[2.5rem] border border-white/5 shadow-2xl relative overflow-hidden">
                <p class="text-[10px] font-black text-slate-500 uppercase mb-3 italic tracking-widest">Unit Divisi</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-4xl font-black italic tracking-tighter text-white">{{ $totalDivisions ?? 0 }}</h3>
                    <span class="text-xs font-bold text-slate-500 uppercase italic">Unit</span>
                </div>
                <div class="absolute left-0 top-0 w-1.5 h-full bg-amber-600"></div>
            </div>
        </div>

        <div class="mt-8">
            <livewire:admin.production-chart />
        </div>

        {{-- FOOTER / MAIN CONTENT GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="{{ route('admin.monitoring') }}" class="group bg-slate-900 p-10 rounded-[3rem] border border-white/5 hover:border-red-600/50 transition-all shadow-2xl">
                    <div class="text-5xl mb-6 group-hover:scale-110 transition-transform duration-500">📊</div>
                    <h4 class="text-2xl font-black uppercase italic tracking-tighter mb-3 text-white">Production Monitor</h4>
                    <p class="text-[11px] text-slate-500 font-bold leading-relaxed uppercase tracking-widest italic">Pantau real-time output mesin harian.</p>
                </a>
                <a href="{{ route('admin.activity-logs') }}" class="group bg-slate-900 p-10 rounded-[3rem] border border-white/5 hover:border-emerald-600/50 transition-all shadow-2xl">
                    <div class="text-5xl mb-6 group-hover:scale-110 transition-transform duration-500">🛡️</div>
                    <h4 class="text-2xl font-black uppercase italic tracking-tighter mb-3 text-white">Security Audit</h4>
                    <p class="text-[11px] text-slate-500 font-bold leading-relaxed uppercase tracking-widest italic">Lihat riwayat aktivitas operator sistem.</p>
                </a>
            </div>

            {{-- RECENT FEED --}}
            <div class="bg-slate-900 rounded-[3rem] p-8 border border-white/5 shadow-2xl">
                <h4 class="text-lg font-black uppercase italic tracking-tighter mb-8 text-red-600 border-b border-white/5 pb-5">Recent Feed</h4>
                <div class="space-y-7">
                    @forelse($recentActivities ?? [] as $log)
                    <div class="flex gap-5 items-start">
                        <div class="w-1 h-10 bg-slate-800 rounded-full"></div>
                        <div class="flex-grow">
                            <p class="text-xs font-black text-white italic uppercase tracking-tighter leading-none mb-1">{{ $log->user->name ?? 'System' }}</p>
                            <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest mb-2">{{ strtoupper($log->action) }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-slate-700 font-black uppercase text-[10px] tracking-widest text-center py-10 italic">No Feed Available</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>