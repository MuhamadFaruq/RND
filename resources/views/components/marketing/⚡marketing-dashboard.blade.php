<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-[#ED1C24] rounded-[2rem] p-8 mb-8 text-white shadow-xl flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-black italic tracking-tighter uppercase">Marketing Command Center</h2>
                    <p class="text-sm font-bold opacity-80 uppercase tracking-widest mt-1">Duniatex Production System</p>
                </div>
                <div class="text-5xl opacity-20">📊</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <x-marketing-stat-card title="Total Pesanan" value="{{ $totalOrder }}" color="border-blue-500" icon="📋" />
                <x-marketing-stat-card title="Menunggu" value="{{ $pendingOrder }}" color="border-amber-500" icon="⏳" />
                <x-marketing-stat-card title="Dalam Proses" value="{{ $activeOrder }}" color="border-indigo-500" icon="⚡" />
                <x-marketing-stat-card title="Selesai" value="{{ $completedOrder }}" color="border-green-500" icon="✅" />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100">
                    <h3 class="font-black italic uppercase text-slate-800 mb-6">Order Terbaru</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="text-[10px] font-black uppercase text-slate-400 border-b">
                                <tr>
                                    <th class="pb-4 text-left">SAP NO</th>
                                    <th class="pb-4 text-left">Pelanggan</th>
                                    <th class="pb-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($recentOrders as $order)
                                <tr>
                                    <td class="py-4 font-bold text-blue-600">{{ $order->sap_no }}</td>
                                    <td class="py-4 font-bold uppercase text-slate-700 text-xs">{{ $order->pelanggan }}</td>
                                    <td class="py-4 text-center">
                                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase {{ $order->status == 'completed' ? 'bg-green-100 text-green-600' : 'bg-amber-100 text-amber-600' }}">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-slate-900 rounded-[2rem] p-8 shadow-xl text-white">
                    <h3 class="font-black italic uppercase text-xs mb-6 tracking-widest">Aksi Cepat</h3>
                    <div class="space-y-4">
                        <a href="{{ route('marketing.orders.create') }}" class="block w-full text-center bg-[#ED1C24] py-4 rounded-2xl font-black italic uppercase tracking-tighter hover:bg-red-700 transition">
                            + Buat Order Baru
                        </a>
                        <a href="{{ route('marketing.orders.index') }}" class="block w-full text-center bg-white/10 py-4 rounded-2xl font-black italic uppercase tracking-tighter hover:bg-white/20 transition">
                            Lihat Semua Order
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>