<x-layouts.app>
    <div class="min-h-screen w-full bg-slate-900 text-white font-sans flex flex-col italic">
        <div class="p-4 md:p-8 flex-grow container mx-auto">
            
            {{-- HEADER --}}
            <div class="flex justify-between items-end mb-10 border-b border-slate-800 pb-6">
                <div>
                    <h1 class="text-5xl font-black italic tracking-tighter uppercase text-emerald-500 leading-none">
                        System <span class="text-white">Health</span>
                    </h1>
                    <p class="text-slate-400 font-bold tracking-widest uppercase text-xs mt-2 italic">Duniatex Group - Server Metrics & Status</p>
                </div>
                <div class="text-right hidden md:block">
                    <div class="text-xs font-black text-slate-500 uppercase italic tracking-widest">Server Time</div>
                    <div class="text-emerald-400 font-mono">{{ $server_time }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- STORAGE METRICS --}}
                <div class="lg:col-span-2 bg-slate-800 rounded-[3rem] border border-slate-700 shadow-2xl p-8 relative overflow-hidden">
                    <h3 class="text-2xl font-black italic uppercase tracking-tighter mb-8 border-b border-slate-700 pb-4">Storage Usage</h3>
                    
                    <div class="flex items-end justify-between mb-4">
                        <div>
                            <p class="text-slate-500 text-[10px] uppercase font-black tracking-widest mb-1">Total Capacity</p>
                            <h4 class="text-4xl font-black">{{ $storage['total'] }} <span class="text-sm text-slate-500">GB</span></h4>
                        </div>
                        <div class="text-right">
                            <p class="text-slate-500 text-[10px] uppercase font-black tracking-widest mb-1">Free Space</p>
                            <h4 class="text-2xl font-black text-emerald-400">{{ $storage['free'] }} <span class="text-sm">GB</span></h4>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="w-full bg-slate-900 h-6 rounded-full overflow-hidden border border-slate-700 relative mb-4">
                        <div class="{{ $storage['percentage'] > 80 ? 'bg-red-500' : 'bg-emerald-500' }} h-full transition-all duration-1000 flex items-center justify-end px-2" style="width: {{ $storage['percentage'] }}%">
                            <span class="text-[9px] font-black text-white mix-blend-difference">{{ $storage['percentage'] }}%</span>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center mt-6 p-4 bg-slate-900/50 rounded-2xl border border-slate-700/50">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full {{ $storage['percentage'] > 80 ? 'bg-red-500 animate-pulse' : 'bg-emerald-500' }}"></div>
                            <span class="text-xs font-bold uppercase text-slate-400">Disk Status</span>
                        </div>
                        <span class="text-xs font-black uppercase {{ $storage['percentage'] > 80 ? 'text-red-500' : 'text-emerald-500' }}">
                            {{ $storage['percentage'] > 80 ? 'CRITICAL' : 'OPTIMAL' }}
                        </span>
                    </div>
                </div>

                {{-- SYSTEM SPECS --}}
                <div class="bg-slate-800 rounded-[3rem] border border-slate-700 shadow-2xl p-8 flex flex-col gap-6">
                    <h3 class="text-xl font-black italic uppercase tracking-tighter border-b border-slate-700 pb-4">Software Stack</h3>
                    
                    <div class="bg-slate-900 p-5 rounded-2xl border border-slate-700/50 flex justify-between items-center group hover:border-red-500/50 transition-colors">
                        <div>
                            <p class="text-slate-500 text-[10px] uppercase font-black tracking-widest mb-1">PHP Version</p>
                            <p class="text-lg font-black text-white italic">v{{ $php_version }}</p>
                        </div>
                        <div class="text-3xl opacity-50 group-hover:opacity-100 transition-opacity">🐘</div>
                    </div>

                    <div class="bg-slate-900 p-5 rounded-2xl border border-slate-700/50 flex justify-between items-center group hover:border-red-500/50 transition-colors">
                        <div>
                            <p class="text-slate-500 text-[10px] uppercase font-black tracking-widest mb-1">Laravel Framework</p>
                            <p class="text-lg font-black text-white italic">v{{ $laravel_version }}</p>
                        </div>
                        <div class="text-3xl opacity-50 group-hover:opacity-100 transition-opacity">🔥</div>
                    </div>

                    <div class="mt-auto pt-4 border-t border-slate-700 flex justify-between items-center">
                        <span class="text-[9px] uppercase font-bold text-slate-500">Service Status</span>
                        <span class="px-3 py-1 bg-emerald-900/50 text-emerald-400 rounded-lg text-[9px] font-black uppercase border border-emerald-800">
                            ONLINE
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-layouts.app>
