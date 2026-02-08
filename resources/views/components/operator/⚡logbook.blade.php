<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-6xl mx-auto px-4">
            
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-2xl font-black italic uppercase tracking-tighter text-slate-800">Operator <span class="text-red-600">Logbook</span></h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Riwayat Input Produksi Anda</p>
                </div>
                <a href="{{ route('operator.knitting') }}" class="bg-slate-900 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-tighter hover:bg-black transition shadow-lg">
                    + Input Baru
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="md:col-span-2 relative">
                    <input wire:model.live="search" type="text" placeholder="Cari berdasarkan No SAP..." class="w-full pl-12 pr-4 py-4 rounded-2xl border-none shadow-sm focus:ring-red-500 font-bold text-sm">
                    <span class="absolute left-5 top-4">🔍</span>
                </div>
                <select wire:model.live="filterType" class="w-full py-4 rounded-2xl border-none shadow-sm focus:ring-red-500 font-bold text-sm">
                    <option value="">Semua Proses</option>
                    <option value="knitting">Knitting (Rajut)</option>
                    <option value="dyeing">Dyeing (Warna)</option>
                    <option value="stenter">Stenter</option>
                </select>
            </div>

            @if (session()->has('message'))
                <div class="mb-6 p-4 bg-green-500 text-white rounded-2xl font-bold text-xs shadow-lg shadow-green-100">
                    ✅ {{ session('message') }}
                </div>
            @endif

            <div class="space-y-4">
                @forelse($activities as $item)
                    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 hover:shadow-md transition">
                        <div class="flex items-center gap-4">
                            <div class="bg-slate-50 p-4 rounded-2xl text-xl">
                                {{ $item->type == 'knitting' ? '🧶' : ($item->type == 'dyeing' ? '🧪' : '⚙️') }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-black text-blue-600 uppercase">{{ $item->marketingOrder->sap_no ?? 'N/A' }}</span>
                                    <span class="text-[9px] font-black px-2 py-0.5 bg-slate-100 text-slate-500 rounded-full uppercase tracking-tighter">{{ $item->type }}</span>
                                </div>
                                <h4 class="font-black text-slate-800 uppercase tracking-tighter">{{ $item->marketingOrder->art_no ?? 'No Art' }}</h4>
                                <p class="text-[10px] font-bold text-slate-400 italic">{{ $item->created_at->format('d M Y | H:i') }} • Shift {{ $item->shift }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-8 px-4 border-l border-slate-50">
                            <div class="text-center">
                                <p class="text-[9px] font-black text-slate-400 uppercase">Jumlah Roll</p>
                                <p class="font-black text-slate-800 italic">{{ $item->jumlah_roll }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-[9px] font-black text-slate-400 uppercase">Berat (KG)</p>
                                <p class="font-black text-red-600 italic">{{ number_format($item->berat_kg, 2) }}</p>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button wire:click="deleteEntry({{ $item->id }})" 
                                    onclick="confirm('Hapus data ini dari logbook?') || event.stopImmediatePropagation()"
                                    class="p-3 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 transition">
                                🗑️
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-20 bg-white rounded-[2rem] border border-dashed border-slate-200">
                        <p class="text-slate-400 font-bold italic uppercase tracking-widest text-xs">Belum ada data input untuk hari ini</p>
                    </div>
                @endforelse

                <div class="mt-8">
                    {{ $activities->links() }}
                </div>
            </div>
        </div>
    </div>
</div>