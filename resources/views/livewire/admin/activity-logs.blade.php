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

        // Filter Pencarian (Art No, Nama, Aksi, Deskripsi)
        if ($this->search) {
            $query->where(function($q) {
                $q->where('art_no', 'like', '%' . $this->search . '%')
                  ->orWhere('sap_no', 'like', '%' . $this->search . '%')
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
                <h1 class="text-4xl md:text-5xl font-black italic tracking-tighter uppercase text-indigo-600 leading-none">
                    Audit <span class="mkt-text">Trail</span>
                </h1>
                <p class="mkt-text-muted font-bold tracking-widest uppercase text-[10px] md:text-xs mt-3 italic">Duniatex Group - Security & Activity Logs</p>
            </div>
            <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                {{-- Search Bar --}}
                <div class="relative group">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 mkt-text-muted opacity-40 group-focus-within:text-indigo-600 group-focus-within:opacity-100 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari ARTIKEL / SAP / Admin / Aksi..." 
                        class="w-full md:w-64 pl-12 pr-4 py-3 mkt-input border mkt-border rounded-2xl text-[10px] font-black uppercase italic focus:ring-2 focus:ring-indigo-600/20 outline-none transition-all">
                </div>

                {{-- Action Filter --}}
                <select wire:model.live="actionFilter" class="mkt-input border mkt-border rounded-2xl px-6 py-3 text-[10px] font-black uppercase italic outline-none focus:ring-2 focus:ring-indigo-600/20 transition-all">
                    <option value="">Semua Aksi</option>
                    <option value="LOGIN">LOGIN</option>
                    <option value="LOGOUT">LOGOUT</option>
                    <option value="CREATE">CREATE</option>
                    <option value="UPDATE">UPDATE</option>
                    <option value="DELETE">DELETE</option>
                </select>

                {{-- Date Filter --}}
                <input type="date" wire:model.live="dateFilter" class="mkt-input border mkt-border rounded-2xl px-6 py-3 text-[10px] font-black uppercase italic outline-none focus:ring-2 focus:ring-indigo-600/20 transition-all">
                
                @if($search || $actionFilter || $dateFilter)
                    <button wire:click="$set('search', ''); $set('actionFilter', ''); $set('dateFilter', '');" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase italic hover:bg-black transition-all">
                        Reset
                    </button>
                @endif
            </div>
        </div>

        {{-- MOBILE AUDIT FEED (Visible only on mobile/tablet < md) --}}
        <div class="block md:hidden space-y-4 mb-6">
            @forelse($logs as $log)
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
                <div class="mkt-surface p-4 rounded-2xl border mkt-border shadow-md relative overflow-hidden flex flex-col gap-3">
                    {{-- Left accent color indicator --}}
                    <div class="absolute left-0 top-0 w-1.5 h-full bg-{{ $color }}-500 shadow-[0_0_10px_rgba(var(--color-{{ $color }}),0.5)]"></div>
                    
                    <div class="flex justify-between items-start pl-2">
                        <div class="flex flex-col">
                            <span class="text-xs font-black mkt-text uppercase tracking-tight italic">{{ $log->user->name ?? 'SYSTEM' }}</span>
                            <span class="text-[8px] mkt-text-muted font-bold opacity-60 tracking-wider">IP: {{ $log->ip_address ?? '0.0.0.0' }}</span>
                        </div>
                        <span class="px-2.5 py-1 bg-{{ $color }}-500/10 text-{{ $color }}-600 dark:text-{{ $color }}-400 border border-{{ $color }}-500/20 rounded-lg uppercase text-[8px] font-black tracking-widest inline-block shadow-sm">
                            {{ $log->action }}
                        </span>
                    </div>

                    <div class="border-t border-dashed mkt-border pl-2 pt-2 flex flex-col gap-2">
                        <div class="flex justify-between items-center text-[8px] font-black text-slate-500 uppercase italic">
                            <span>Waktu</span>
                            <span class="font-mono text-emerald-500 text-xs font-bold leading-none">{{ $log->created_at->format('H:i:s') }} <span class="mkt-text-muted font-black uppercase text-[8px] ml-1">{{ $log->created_at->format('d M Y') }}</span></span>
                        </div>
                        
                        <div class="flex justify-between items-center mt-0.5">
                            <span class="text-[8px] font-black text-slate-500 uppercase italic">Artikel / SAP</span>
                            <div class="text-right">
                                <span class="text-indigo-600 dark:text-indigo-500 font-black text-xs tracking-tighter uppercase italic leading-none">
                                    {{ $log->art_no ?? '#' . ($log->sap_no ?? ($log->model ?? 'SYSTEM')) }}
                                </span>
                                @if($log->art_no && $log->sap_no)
                                    <span class="text-[8px] mkt-text-muted font-bold opacity-50 uppercase tracking-widest block">SAP: {{ $log->sap_no }}</span>
                                @endif
                            </div>
                        </div>
                        
                        <p class="mkt-text-muted text-[10px] font-bold leading-relaxed opacity-95 italic bg-slate-950/10 dark:bg-slate-950/20 p-2.5 rounded-xl border mkt-border mt-1">
                            {{ $log->description }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="mkt-surface p-12 rounded-2xl border mkt-border text-center text-slate-500 font-black uppercase text-[10px] italic tracking-widest">
                    Tidak ada aktivitas yang sesuai dengan filter Anda...
                </div>
            @endforelse

            {{-- PAGINATION MOBILE --}}
            @if($logs->hasPages())
            <div class="p-4 mkt-surface rounded-2xl border mkt-border shadow-sm">
                {{ $logs->links() }}
            </div>
            @endif
        </div>

        {{-- DESKTOP AUDIT TRAIL TABEL (Visible only on md and larger) --}}
        <div class="hidden md:block mkt-surface rounded-[2rem] md:rounded-[3rem] overflow-hidden shadow-2xl border mkt-border">
            <div class="overflow-x-auto">
                <table class="w-full text-left italic font-bold min-w-[900px]">
                    <thead>
                        <tr class="mkt-surface-alt text-[9px] font-black uppercase mkt-text-muted tracking-[0.2em] border-b mkt-border">
                            <th class="px-8 py-6">Waktu</th>
                            <th class="px-8 py-6">Operator</th>
                            <th class="px-8 py-6 text-center">Aksi</th>
                            <th class="px-8 py-6 text-center">IDENTITAS / ARTIKEL</th>
                            <th class="px-8 py-6">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
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

                            {{-- Identitas Artikel --}}
                            <td class="px-8 py-7 text-center">
                                <div class="flex flex-col">
                                    <span class="text-indigo-600 dark:text-indigo-500 font-black text-sm tracking-tighter uppercase italic">
                                        {{ $log->art_no ?? '#' . ($log->sap_no ?? ($log->model ?? 'SYSTEM')) }}
                                    </span>
                                    @if($log->art_no && $log->sap_no)
                                        <span class="text-[8px] mkt-text-muted font-bold opacity-50 uppercase tracking-widest mt-1">LEGACY SAP: {{ $log->sap_no }}</span>
                                    @endif
                                </div>
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

            {{-- PAGINATION DESKTOP --}}
            @if($logs->hasPages())
            <div class="p-6 mkt-surface border-t mkt-border">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>