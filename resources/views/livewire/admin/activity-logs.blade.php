<?php

use Livewire\Volt\Component;
use App\Models\ActivityLog;
use Livewire\WithPagination;

new class extends Component {
    // Menambahkan Trait Pagination agar navigasi halaman berfungsi
    use WithPagination;

    public function with()
    {
        return [
            // Mengambil log terbaru dan membaginya per 15 data per halaman
            'logs' => ActivityLog::latest()->paginate(15),
        ];
    }
}; ?>

<div class="min-h-screen w-full bg-slate-900 text-white font-sans flex flex-col italic">
    {{-- Polling setiap 30 detik untuk memantau aktivitas secara realtime --}}
    <div wire:poll.30s class="p-4 md:p-8 flex-grow container mx-auto">
        
        {{-- HEADER --}}
        <div class="flex justify-between items-end mb-10 border-b border-slate-800 pb-6">
            <div>
                <h1 class="text-5xl font-black italic tracking-tighter uppercase text-red-600 leading-none">
                    Audit <span class="text-white">Trail</span>
                </h1>
                <p class="text-slate-400 font-bold tracking-widest uppercase text-xs mt-2 italic">Duniatex Group - Security & Activity Logs</p>
            </div>
            <div class="text-right hidden md:block">
                <div class="text-xs font-black text-slate-500 uppercase italic tracking-widest">Live Monitoring Active</div>
            </div>
        </div>

        {{-- TABEL AUDIT TRAIL --}}
        <div class="bg-slate-800 rounded-[2rem] md:rounded-[3rem] overflow-hidden shadow-2xl border border-slate-700">
            <div class="overflow-x-auto">
                <table class="w-full text-left italic font-bold min-w-[900px]">
                    <thead class="bg-slate-900/50 uppercase text-[10px] text-slate-500 tracking-widest">
                        <tr>
                            <th class="p-6">Waktu</th>
                            <th class="p-6">Operator</th>
                            <th class="p-6 text-center">Aksi</th>
                            <th class="p-6">Model / SAP</th>
                            <th class="p-6">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($logs as $log)
                        <tr class="hover:bg-slate-700/30 transition duration-300">
                            {{-- Waktu --}}
                            <td class="p-6 font-mono text-emerald-400 text-xs">
                                {{ $log->created_at->format('H:i:s') }}
                                <span class="block text-[9px] text-slate-500 font-normal uppercase">{{ $log->created_at->format('d M Y') }}</span>
                            </td>
                            
                            {{-- Operator --}}
                            <td class="p-6 text-blue-400">
                                {{ $log->user_name }}
                                <span class="block text-[9px] text-slate-500 font-normal italic">IP: {{ $log->ip_address ?? '0.0.0.0' }}</span>
                            </td>

                            {{-- Aksi --}}
                            <td class="p-6 text-center">
                                @php
                                    $color = match(strtoupper($log->action)) {
                                        'CREATE' => 'emerald',
                                        'UPDATE' => 'blue',
                                        'DELETE' => 'red',
                                        'LOGIN'  => 'amber',
                                        default  => 'slate'
                                    };
                                @endphp
                                <span class="px-3 py-1 bg-{{ $color }}-900/50 text-{{ $color }}-400 rounded-lg border border-{{ $color }}-800 uppercase text-[9px] font-black">
                                    {{ $log->action }}
                                </span>
                            </td>

                            {{-- Model / SAP --}}
                            <td class="p-6 text-red-500 font-black italic tracking-tighter">
                                #{{ $log->model ?? 'SYSTEM' }}
                            </td>

                            {{-- Keterangan --}}
                            <td class="p-6 text-slate-400 text-xs leading-relaxed max-w-xs">
                                {{ $log->description }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-20 text-center text-slate-500 font-black uppercase text-xs italic tracking-widest">
                                Belum ada aktivitas yang tercatat di database...
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if($logs->hasPages())
            <div class="p-6 bg-slate-900/30 border-t border-slate-700">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>