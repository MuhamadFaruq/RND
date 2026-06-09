<?php

use Livewire\Volt\Component;
use App\Models\ActivityLog;
use App\Models\ProductionActivity;
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
        
        // Hitung Output Hari Ini dari ProductionActivity (deduplicated per order per alur)
        $warnaDivs = ['DYEING', 'RELAX-DRYER', 'COMPACTOR', 'HEAT-SETTING', 'STENTER', 'TUMBLER', 'FLEECE', 'FINISHING'];
        $todayActivities = ProductionActivity::whereDate('created_at', Carbon::today())->get();

        $knitting = $todayActivities->filter(fn($a) => strtoupper($a->division_name) === 'KNITTING')
            ->groupBy('marketing_order_id')->map(fn($g) => $g->sortByDesc('created_at')->first());
        $warna = $todayActivities->filter(fn($a) => in_array(strtoupper($a->division_name), $warnaDivs))
            ->groupBy('marketing_order_id')->map(fn($g) => $g->sortByDesc('created_at')->first());
        $others = $todayActivities->filter(fn($a) =>
            strtoupper($a->division_name) !== 'KNITTING' &&
            !in_array(strtoupper($a->division_name), $warnaDivs)
        )->groupBy('marketing_order_id')->map(fn($g) => $g->sortByDesc('created_at')->first());

        $currentWeight = $knitting->sum('kg') + $warna->sum('kg') + $others->sum('kg');
        $pct = ($limitCapacity > 0) ? ($currentWeight / $limitCapacity) * 100 : 0;

        return [
            'totalUsers' => $countUsers, 
            'totalDivisions' => $countDivs,
            'totalLogsToday' => $countLogsToday,
            'maxCapacity' => $limitCapacity,
            'currentOutput' => $currentWeight,
            'percentage' => min($pct, 100),
            'recentActivities' => ActivityLog::with('user')->latest()->take(5)->get(),
            'machineStatuses' => Setting::where('key', 'like', 'machine_status_%')->get()->mapWithKeys(function($item) {
                return [str_replace('machine_status_', '', $item->key) => $item->value];
            }),
        ];
    }
}; ?>

<div class="min-h-screen w-full mkt-bg mkt-text font-sans italic flex flex-col transition-colors duration-300">
    <div class="p-4 md:p-8 flex-grow container mx-auto w-full max-w-full">
        
        {{-- HEADER SECTION --}}
        <div class="mb-8 md:mb-10 border-b border-white/5 pb-6 md:pb-8 flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
            <div class="w-full md:w-auto">
                <h1 class="text-3xl sm:text-4xl md:text-7xl font-black italic tracking-tighter uppercase mkt-text leading-tight md:leading-none">
                    System <span class="text-brand-600">Control</span>
                </h1>
                <p class="mkt-text-muted font-bold tracking-widest uppercase text-[9px] md:text-[10px] mt-2 md:mt-3 italic">
                    Logistik & Produksi System <span class="mkt-text mx-2 hidden sm:inline">|</span> <br class="sm:hidden"> User: <span class="text-emerald-500">{{ auth()->user()->name }}</span>
                </p>
            </div>
            <div x-data="{ 
                time: '',
                updateTime() {
                    this.time = new Date().toLocaleTimeString('id-ID', { 
                        hour: '2-digit', 
                        minute: '2-digit', 
                        second: '2-digit',
                        hour12: false 
                    }).replace(/\./g, ':');
                }
            }" 
            x-init="updateTime(); setInterval(() => updateTime(), 1000)"
            class="mkt-surface px-6 md:px-8 py-4 md:py-5 rounded-2xl md:rounded-[2rem] mkt-border border shadow-2xl text-left md:text-right min-w-full md:min-w-[180px]">
                <span class="text-[8px] md:text-[9px] font-black text-slate-500 uppercase block tracking-[0.2em] md:tracking-[0.4em] mb-1 opacity-60 italic">Server Time</span>
                <span class="text-2xl md:text-3xl font-mono font-bold text-brand-600 tabular-nums" x-text="time"></span>
            </div>
        </div>

        {{-- QUICK STATS GRID (2-COLUMN ON MOBILE FOR ELEGANT CONSOLE VIEW) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6 mb-8 md:mb-12">
            
            {{-- 1. OUTPUT --}}
            <div class="mkt-surface p-4 md:p-6 lg:p-7 rounded-2xl md:rounded-[2.5rem] mkt-border border shadow-lg relative overflow-hidden group">
                <p class="text-[7px] md:text-[10px] font-black text-slate-500 uppercase mb-1 md:mb-3 italic tracking-wider md:tracking-widest leading-none">Output Hari Ini</p>
                <div class="flex items-baseline gap-1 md:gap-2 mb-2 md:mb-4">
                    <h3 class="text-base sm:text-xl md:text-4xl font-black italic tracking-tighter mkt-text">{{ (float)$currentOutput }}</h3>
                    <span class="text-[7px] md:text-xs font-bold mkt-text-muted uppercase italic">KG</span>
                </div>
                <div class="w-full mkt-bg h-1 rounded-full overflow-hidden mkt-border border">
                    <div class="bg-brand-600 h-full transition-all duration-1000 shadow-[0_0_10px_rgba(237,28,36,0.3)]" style="width: {{ $percentage }}%"></div>
                </div>
                <p class="text-[6px] md:text-[8px] font-bold text-slate-600 mt-2 md:mt-3 uppercase italic tracking-wider md:tracking-widest">Max: {{ number_format($maxCapacity) }} KG</p>
                <div class="absolute left-0 top-0 w-1 h-full bg-brand-600 shadow-[0_0_10px_rgba(237,28,36,0.5)]"></div>
            </div>

            {{-- 2. PERSONNEL --}}
            <div class="mkt-surface p-4 md:p-6 lg:p-7 rounded-2xl md:rounded-[2.5rem] mkt-border border shadow-lg relative overflow-hidden">
                <p class="text-[7px] md:text-[10px] font-black text-slate-500 uppercase mb-1 md:mb-3 italic tracking-wider md:tracking-widest leading-none">Total Personel</p>
                <div class="flex items-baseline gap-1 md:gap-2">
                    <h3 class="text-base sm:text-xl md:text-4xl font-black italic tracking-tighter mkt-text">{{ $totalUsers ?? 0 }}</h3>
                    <span class="text-[7px] md:text-xs font-bold mkt-text-muted uppercase italic">User</span>
                </div>
                <div class="absolute left-0 top-0 w-1 h-full bg-brand"></div>
            </div>

            {{-- 3. LOGS --}}
            <div class="mkt-surface p-4 md:p-6 lg:p-7 rounded-2xl md:rounded-[2.5rem] mkt-border border shadow-lg relative overflow-hidden">
                <p class="text-[7px] md:text-[10px] font-black text-slate-500 uppercase mb-1 md:mb-3 italic tracking-wider md:tracking-widest leading-none">Log Aktivitas</p>
                <div class="flex items-baseline gap-1 md:gap-2">
                    <h3 class="text-base sm:text-xl md:text-4xl font-black italic tracking-tighter mkt-text">{{ $totalLogsToday ?? 0 }}</h3>
                    <span class="text-[7px] md:text-xs font-bold mkt-text-muted uppercase italic">Event</span>
                </div>
                <div class="absolute left-0 top-0 w-1 h-full bg-emerald-600"></div>
            </div>

            {{-- 4. DIVISIONS --}}
            <div class="mkt-surface p-4 md:p-6 lg:p-7 rounded-2xl md:rounded-[2.5rem] mkt-border border shadow-lg relative overflow-hidden">
                <p class="text-[7px] md:text-[10px] font-black text-slate-500 uppercase mb-1 md:mb-3 italic tracking-wider md:tracking-widest leading-none">Unit Divisi</p>
                <div class="flex items-baseline gap-1 md:gap-2">
                    <h3 class="text-base sm:text-xl md:text-4xl font-black italic tracking-tighter mkt-text">{{ $totalDivisions ?? 0 }}</h3>
                    <span class="text-[7px] md:text-xs font-bold mkt-text-muted uppercase italic">Unit</span>
                </div>
                <div class="absolute left-0 top-0 w-1 h-full bg-amber-600"></div>
            </div>
        </div>

        <div class="mt-8">
            <livewire:admin.production-chart />
        </div>

        {{-- NEW: MACHINE MONITORING GRID --}}
        <div class="mb-10 md:mb-12 mt-6 md:mt-8">
            <div class="flex items-center gap-3 mb-6 md:mb-8">
                <div class="w-8 md:w-10 h-1 bg-brand-600 rounded-full"></div>
                <h3 class="text-xs md:text-sm font-black uppercase italic mkt-text tracking-[0.2em]">Real-Time Production Monitoring</h3>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 md:gap-4">
                @php
                    $divs = ['knitting', 'dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'stenter', 'tumbler', 'fleece', 'finishing', 'pengujian', 'qe'];
                @endphp
                @foreach($divs as $div)
                    @php 
                        $status = $machineStatuses[$div] ?? 'unknown'; 
                        $bgColor = match($status) {
                            'running' => 'bg-green-600/10 border-green-500/30',
                            'downtime' => 'bg-amber-600/10 border-amber-500/30',
                            'maintenance' => 'bg-orange-600/10 border-orange-500/30',
                            default => 'mkt-surface border mkt-border'
                        };
                        $dotColor = match($status) {
                            'running' => 'bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.5)]',
                            'downtime' => 'bg-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.5)]',
                            'maintenance' => 'bg-orange-600 shadow-[0_0_10px_rgba(234,88,12,0.5)]',
                            default => 'bg-slate-500'
                        };
                    @endphp
                    <div class="mkt-surface p-4 md:p-5 rounded-2xl md:rounded-[2rem] border {{ $bgColor }} transition-all hover:scale-105 group relative overflow-hidden">
                        <div class="flex flex-col items-center gap-2 relative z-10">
                            <div class="w-1.5 h-1.5 rounded-full {{ $dotColor }} {{ $status === 'running' ? 'animate-pulse' : '' }}"></div>
                            <p class="text-[8px] md:text-[9px] font-black uppercase italic mkt-text leading-none">{{ $div }}</p>
                            <p class="text-[6px] md:text-[7px] font-bold uppercase {{ $status === 'running' ? 'text-green-500' : ($status === 'downtime' ? 'text-amber-500' : ($status === 'maintenance' ? 'text-orange-600' : 'text-slate-500')) }} italic leading-none">
                                {{ strtoupper($status) }}
                            </p>
                        </div>
                        <div class="absolute -right-3 -bottom-3 text-3xl md:text-4xl opacity-5 font-black italic">{{ strtoupper(substr($div, 0, 1)) }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- FOOTER / MAIN CONTENT GRID --}}
        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6 md:gap-8">
            <div class="xl:col-span-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
                <a href="{{ route('admin.monitoring') }}" class="group mkt-surface p-6 md:p-8 lg:p-10 rounded-2xl md:rounded-[3rem] mkt-border border hover:border-red-600/50 transition-all shadow-xl md:shadow-2xl">
                    <h4 class="text-lg md:text-xl lg:text-2xl font-black uppercase italic tracking-tighter mb-2 md:mb-3 mkt-text">Production Monitor</h4>
                    <p class="text-[9px] lg:text-[11px] mkt-text-muted font-bold leading-relaxed uppercase tracking-widest italic">Pantau real-time output mesin harian.</p>
                </a>
                <a href="{{ route('admin.activity-logs') }}" class="group mkt-surface p-6 md:p-8 lg:p-10 rounded-2xl md:rounded-[3rem] mkt-border border hover:border-emerald-600/50 transition-all shadow-xl md:shadow-2xl">
                    <h4 class="text-lg md:text-xl lg:text-2xl font-black uppercase italic tracking-tighter mb-2 md:mb-3 mkt-text">Security Audit</h4>
                    <p class="text-[9px] lg:text-[11px] mkt-text-muted font-bold leading-relaxed uppercase tracking-widest italic">Lihat riwayat aktivitas operator sistem.</p>
                </a>
                <a href="{{ route('admin.recycle-bin') }}" class="group mkt-surface p-6 md:p-8 lg:p-10 rounded-2xl md:rounded-[3rem] mkt-border border hover:border-rose-600/50 transition-all shadow-xl md:shadow-2xl">
                    <h4 class="text-lg md:text-xl lg:text-2xl font-black uppercase italic tracking-tighter mb-2 md:mb-3 mkt-text">Cold Storage</h4>
                    <p class="text-[9px] lg:text-[11px] mkt-text-muted font-bold leading-relaxed uppercase tracking-widest italic">Tempat sampah data (Archived Orders).</p>
                </a>
            </div>

            {{-- RECENT FEED --}}
            <div class="mkt-surface rounded-[2rem] md:rounded-[3rem] p-6 md:p-8 mkt-border border shadow-xl md:shadow-2xl">
                <h4 class="text-base md:text-lg font-black uppercase italic tracking-tighter mb-6 md:mb-8 text-brand-600 border-b mkt-border pb-4 md:pb-5 leading-none">Recent Feed</h4>
                <div class="space-y-5 md:space-y-7">
                    @forelse($recentActivities ?? [] as $log)
                    <div class="flex gap-4 md:gap-5 items-start">
                        <div class="w-1 h-8 md:h-10 mkt-bg rounded-full"></div>
                        <div class="flex-grow">
                            <p class="text-[10px] md:text-xs font-black mkt-text italic uppercase tracking-tighter leading-none mb-1">{{ $log->user->name ?? 'System' }}</p>
                            <p class="text-[8px] md:text-[9px] mkt-text-muted font-bold uppercase tracking-widest mb-2 leading-none">{{ strtoupper($log->action) }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-slate-700 font-black uppercase text-[9px] md:text-[10px] tracking-widest text-center py-8 md:py-10 italic">No Feed Available</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>