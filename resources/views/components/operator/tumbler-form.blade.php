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
                    <div class="bg-sky-500 p-3 rounded-2xl shadow-lg shadow-sky-200 text-white">🌀</div>
                    <div>
                        <h2 class="text-xl font-black italic uppercase tracking-tighter text-slate-800">Tumbler Process</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Drying & Softening Control</p>
                    </div>
                </div>

                <form wire:submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-sky-50/30 rounded-[2rem] border border-dashed border-sky-200">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Nomor SAP</label>
                            <input type="text" wire:model.live.debounce.500ms="sap_no" class="w-full rounded-xl border-slate-200 focus:ring-sky-500 font-bold">
                        </div>
                        <div class="flex flex-col justify-center">
                            <span class="text-[9px] font-black text-slate-400 uppercase">Art / Warna</span>
                            <span class="text-sm font-black text-sky-700 uppercase">{{ $art_no ?? '-' }} / {{ $warna ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">No. Mesin</label>
                            <input type="text" wire:model="no_mesin" class="w-full rounded-xl border-slate-200">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Shift</label>
                            <select wire:model="shift" class="w-full rounded-xl border-slate-200">
                                <option value="">Pilih</option>
                                <option value="1">1</option><option value="2">2</option><option value="3">3</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Suhu Set (°C)</label>
                            <input type="number" wire:model="suhu_set" class="w-full rounded-xl border-slate-200 font-bold text-red-600">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Waktu Proses (Menit)</label>
                            <input type="number" wire:model="waktu_proses" class="w-full rounded-xl border-slate-200">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Jumlah Roll</label>
                            <input type="number" wire:model="jumlah_roll" class="w-full rounded-xl border-slate-200">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Berat Total (KG)</label>
                            <input type="number" step="0.01" wire:model="berat_kg" class="w-full rounded-xl border-slate-200 font-black">
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" class="w-full bg-sky-600 text-white py-4 rounded-2xl font-black italic uppercase tracking-tighter hover:bg-sky-700 transition shadow-xl shadow-sky-100">
                            Simpan Data Tumbler 🌀
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>