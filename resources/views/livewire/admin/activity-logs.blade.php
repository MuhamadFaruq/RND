<?php

use Livewire\Volt\Component;
use App\Models\ActivityLog;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $actionFilter = '';
    public $dateFilter = '';

    public function updatedSearch() { $this->resetPage(); }
    public function updatedActionFilter() { $this->resetPage(); }
    public function updatedDateFilter() { $this->resetPage(); }

    public function with()
    {
        $query = ActivityLog::with('user')->latest();

        // Filter Pencarian (SAP, Nama, Deskripsi)
        if ($this->search) {
            $query->where(function($q) {
                $q->where('sap_no', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('action', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function($sq) {
                      $sq->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Filter Aksi
        if ($this->actionFilter) {
            $query->where('action', $this->actionFilter);
        }

        // Filter Tanggal
        if ($this->dateFilter) {
            $query->whereDate('created_at', $this->dateFilter);
        }

        return [
            'logs' => $query->paginate(15),
        ];
    }
}; ?>

<div class="min-h-screen w-full mkt-bg mkt-text font-sans flex flex-col italic transition-colors duration-300">
    <div class="p-4 md:p-8 flex-grow container mx-auto">
        
        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 border-b mkt-border pb-8 gap-6">
            <div>
                <h1 class="text-4xl md:text-5xl font-black italic tracking-tighter uppercase text-red-600 leading-none">
                    Audit <span class="mkt-text">Trail</span>
                </h1>
                <p class="mkt-text-muted font-bold tracking-widest uppercase text-[10px] md:text-xs mt-3 italic">Duniatex Group - Security & Activity Logs</p>
            </div>
            <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                {{-- Search Bar --}}
                <div class="relative group">
                    <span class="absolute left-4 top-3.5 opacity-40">🔍</span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari SAP / Admin / Aksi..." 
                        class="w-full md:w-64 pl-12 pr-4 py-3 mkt-surface border mkt-border rounded-2xl text-[10px] font-black uppercase italic focus:ring-2 focus:ring-red-600/20 outline-none transition-all">
                </div>

                {{-- Action Filter --}}
                <select wire:model.live="actionFilter" class="mkt-surface border mkt-border rounded-2xl px-6 py-3 text-[10px] font-black uppercase italic outline-none focus:ring-2 focus:ring-red-600/20 transition-all">
                    <option value="">Semua Aksi</option>
                    <option value="LOGIN">LOGIN</option>
                    <option value="LOGOUT">LOGOUT</option>
                    <option value="CREATE">CREATE</option>
                    <option value="UPDATE">UPDATE</option>
                    <option value="DELETE">DELETE</option>
                </select>

                {{-- Date Filter --}}
                <input type="date" wire:model.live="dateFilter" class="mkt-surface border mkt-border rounded-2xl px-6 py-3 text-[10px] font-black uppercase italic outline-none focus:ring-2 focus:ring-red-600/20 transition-all">
                
                @if($search || $actionFilter || $dateFilter)
                    <button wire:click="$set('search', ''); $set('actionFilter', ''); $set('dateFilter', '');" class="bg-red-600 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase italic hover:bg-black transition-all">
                        Reset
                    </button>
                @endif
            </div>
        </div>

        {{-- TABEL AUDIT TRAIL --}}
        <div class="mkt-surface rounded-[2rem] md:rounded-[3rem] overflow-hidden shadow-2xl border mkt-border">
            <div class="overflow-x-auto">
                <table class="w-full text-left italic font-bold min-w-[900px]">
                    <thead>
                        <tr class="mkt-surface-alt text-[9px] font-black uppercase mkt-text-muted tracking-[0.2em] border-b mkt-border">
                            <th class="px-8 py-6">Waktu</th>
                            <th class="px-8 py-6">Operator</th>
                            <th class="px-8 py-6 text-center">Aksi</th>
                            <th class="px-8 py-6 text-center">Model / SAP</th>
                            <th class="px-8 py-6">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                        @forelse($logs as $log)
                        <tr class="hover:mkt-surface-alt/50 transition-colors border-b mkt-border last:border-0">
                            {{-- Waktu --}}
                            <td class="px-8 py-7">
                                <div class="flex flex-col">
                                    <span class="font-mono text-emerald-500 text-sm font-bold tracking-tight">{{ $log->created_at->format('H:i:s') }}</span>
                                    <span class="text-[9px] mkt-text-muted font-black uppercase tracking-widest mt-1">{{ $log->created_at->format('d M Y') }}</span>
                                </div>
                            </td>
                            
                            {{-- Operator --}}
                            <td class="px-8 py-7">
                                <div class="flex flex-col">
                                    <span class="text-blue-600 dark:text-blue-400 font-black text-sm tracking-tight italic">{{ $log->user->name ?? 'SYSTEM' }}</span>
                                    <span class="text-[9px] mkt-text-muted font-bold opacity-60 tracking-widest mt-1">IP: {{ $log->ip_address ?? '0.0.0.0' }}</span>
                                </div>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-8 py-7 text-center">
                                @php
                                    $action = strtoupper($log->action);
                                    $color = match(true) {
                                        str_contains($action, 'CREATE') => 'emerald',
                                        str_contains($action, 'UPDATE') => 'blue',
                                        str_contains($action, 'DELETE') => 'red',
                                        $action === 'LOGIN'             => 'amber',
                                        $action === 'LOGOUT'            => 'slate',
                                        default                         => 'slate'
                                    };
                                @endphp
                                <span class="px-4 py-1.5 bg-{{ $color }}-500/10 text-{{ $color }}-600 dark:text-{{ $color }}-400 border border-{{ $color }}-500/20 rounded-xl uppercase text-[9px] font-black tracking-widest inline-block min-w-[100px] shadow-sm">
                                    {{ $log->action }}
                                </span>
                            </td>

                            {{-- Model / SAP --}}
                            <td class="px-8 py-7 text-center">
                                <span class="text-red-600 dark:text-red-500 font-black text-sm tracking-tighter uppercase italic">
                                    #{{ $log->sap_no ?? ($log->model ?? 'SYSTEM') }}
                                </span>
                            </td>

                            {{-- Keterangan --}}
                            <td class="px-8 py-7">
                                <div class="mkt-text-muted text-[11px] font-bold leading-relaxed max-w-xs opacity-80 italic">
                                    {{ $log->description }}
                                </div>
                                @if($log->user_agent)
                                    <div class="text-[8px] opacity-40 mt-1 uppercase font-black truncate max-w-[200px]">
                                        {{ $log->user_agent }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-20 text-center text-slate-500 font-black uppercase text-xs italic tracking-widest">
                                Tidak ada aktivitas yang sesuai dengan filter Anda...
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if($logs->hasPages())
            <div class="p-6 mkt-surface border-t mkt-border">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>