<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-slate-100">
                <div class="flex items-center gap-4 mb-8">
                    <div class="bg-indigo-600 p-3 rounded-2xl shadow-lg shadow-indigo-200 text-white">🧪</div>
                    <div>
                        <h2 class="text-xl font-black italic uppercase tracking-tighter text-slate-800">Dyeing Process</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Input Proses Pencelupan Warna</p>
                    </div>
                </div>

                <form wire:submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-indigo-50/30 rounded-[2rem] border border-dashed border-indigo-100">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Input SAP NO</label>
                            <input type="text" wire:model.live.debounce.500ms="sap_no" class="w-full rounded-xl border-slate-200 focus:ring-indigo-500 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Target Warna</label>
                            <div class="px-4 py-2 bg-white rounded-lg border border-slate-100 text-sm font-black text-indigo-600 uppercase">
                                {{ $warna_target ?? 'MENUNGGU INPUT...' }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">No. Mesin</label>
                            <input type="text" wire:model="no_mesin" class="w-full rounded-xl border-slate-200">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">LOT Number</label>
                            <input type="text" wire:model="lot_no" class="w-full rounded-xl border-slate-200 font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Shift</label>
                            <select wire:model="shift" class="w-full rounded-xl border-slate-200">
                                <option value="">Pilih Shift</option>
                                <option value="1">1</option><option value="2">2</option><option value="3">3</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Berat Bahan (KG)</label>
                            <input type="number" step="0.01" wire:model="berat_bahan" class="w-full rounded-xl border-slate-200">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Suhu Actual (°C)</label>
                            <input type="number" wire:model="suhu_actual" class="w-full rounded-xl border-slate-200" placeholder="Contoh: 80">
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" class="w-full bg-indigo-900 text-white py-4 rounded-2xl font-black italic uppercase tracking-tighter hover:bg-indigo-950 transition shadow-xl">
                            Simpan Proses Dyeing 🧪
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>