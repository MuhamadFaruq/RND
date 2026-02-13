<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    <div wire:poll.10s class="min-h-screen bg-slate-900 p-8 text-white">
        <div class="flex justify-between items-end mb-10 border-b border-slate-800 pb-6">
            <div>
                <h1 class="text-5xl font-black italic tracking-tighter uppercase text-red-600">
                    Production <span class="text-white">Live Monitor</span>
                </h1>
                <p class="text-slate-400 font-bold tracking-widest uppercase text-sm mt-2">Duniatex Group - Realtime Manufacturing Data</p>
            </div>
            <div class="text-right">
                <div class="text-4xl font-mono font-bold text-emerald-400">{{ $currentTime }}</div>
                <div class="text-xs font-black text-slate-500 uppercase">{{ Carbon\Carbon::now()->format('l, d F Y') }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-slate-800 p-8 rounded-[2rem] border-l-8 border-red-600 shadow-2xl">
                <p class="text-xs font-black text-slate-500 uppercase mb-2">Produksi Hari Ini (Total)</p>
                <h3 class="text-4xl font-black">{{ number_format($todayProduction, 2) }} <span class="text-lg text-slate-500">KG</span></h3>
            </div>
            
            @foreach($divisionStats as $stat)
            <div class="bg-slate-800 p-8 rounded-[2rem] border-l-8 border-blue-500 shadow-2xl">
                <p class="text-xs font-black text-slate-500 uppercase mb-2">{{ $stat->name }}</p>
                <h3 class="text-4xl font-black">{{ $stat->production_activities_count }} <span class="text-lg text-slate-500">Batch</span></h3>
            </div>
            @endforeach
        </div>

        <div class="bg-slate-800 rounded-[3rem] overflow-hidden shadow-2xl border border-slate-700">
            <div class="p-8 border-b border-slate-700 flex justify-between items-center">
                <h3 class="text-xl font-black italic uppercase">Aktivitas Terkini (Live Feed)</h3>
                <span class="flex h-3 w-3 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
            </div>
            <table class="w-full text-left">
                <thead class="bg-slate-900/50">
                    <tr>
                        <th class="p-6 text-xs font-black uppercase text-slate-500">Jam</th>
                        <th class="p-6 text-xs font-black uppercase text-slate-500">Divisi</th>
                        <th class="p-6 text-xs font-black uppercase text-slate-500">SAP NO</th>
                        <th class="p-6 text-xs font-black uppercase text-slate-500">Artikel</th>
                        <th class="p-6 text-xs font-black uppercase text-slate-500 text-center">Mesin</th>
                        <th class="p-6 text-xs font-black uppercase text-slate-500 text-right">Hasil (KG)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($latestActivities as $activity)
                    <tr class="hover:bg-slate-700/50 transition duration-300">
                        <td class="p-6 font-mono text-emerald-400 font-bold">{{ $activity->created_at->format('H:i') }}</td>
                        <td class="p-6">
                            <span class="px-3 py-1 bg-blue-900/50 text-blue-400 rounded-lg text-[10px] font-black uppercase border border-blue-800">
                                {{ $activity->type }}
                            </span>
                        </td>
                        <td class="p-6 font-black text-white italic tracking-tighter">{{ $activity->marketingOrder->sap_no ?? 'N/A' }}</td>
                        <td class="p-6 text-slate-300 font-bold uppercase text-xs">{{ $activity->marketingOrder->art_no ?? 'N/A' }}</td>
                        <td class="p-6 text-center font-bold">{{ $activity->no_mesin ?? '-' }}</td>
                        <td class="p-6 text-right font-black text-2xl text-red-500 tracking-tighter">{{ number_format($activity->berat_kg, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-20 text-center text-slate-500 font-bold italic">Belum ada aktivitas produksi hari ini...</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>