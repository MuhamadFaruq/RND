<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-black text-slate-800 italic uppercase">Daftar Marketing Order</h2>
                <a href="{{ route('marketing.orders.create') }}" class="bg-[#ED1C24] text-white px-6 py-2 rounded-xl font-bold shadow-lg hover:bg-red-700 transition">
                    + BUAT ORDER BARU
                </a>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm mb-6 flex gap-4 items-center">
                <div class="flex-1 relative">
                    <span class="absolute left-4 top-3">🔍</span>
                    <input wire:model.live="search" type="text" placeholder="Cari nomor SAP, Pelanggan, atau Art..." class="w-full pl-10 pr-4 py-2 border-slate-200 rounded-xl text-sm">
                </div>
                <select wire:model.live="statusFilter" class="border-slate-200 rounded-xl text-sm">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="in-progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            @if (session()->has('message'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-xl">
                    {{ session('message') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="p-4 text-[10px] font-black uppercase text-slate-400">SAP NO</th>
                            <th class="p-4 text-[10px] font-black uppercase text-slate-400">ART NO</th>
                            <th class="p-4 text-[10px] font-black uppercase text-slate-400">Pelanggan</th>
                            <th class="p-4 text-[10px] font-black uppercase text-slate-400 text-center">Status</th>
                            <th class="p-4 text-[10px] font-black uppercase text-slate-400 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($orders as $order)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-4 font-bold text-blue-600">{{ $order->sap_no }}</td>
                            <td class="p-4 font-bold uppercase">{{ $order->art_no }}</td>
                            <td class="p-4 font-bold uppercase text-slate-600">{{ $order->pelanggan }}</td>
                            <td class="p-4 text-center">
                                <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase {{ $order->status == 'completed' ? 'bg-green-100 text-green-600' : 'bg-amber-100 text-amber-600' }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('marketing.orders.edit', $order->id) }}" class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100">✏️</a>
                                    <button onclick="confirm('Yakin hapus?') || event.stopImmediatePropagation()" wire:click="deleteOrder({{ $order->id }})" class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100">🗑️</button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <div class="p-4 bg-slate-50">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>