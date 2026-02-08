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
                    <div class="bg-rose-500 p-3 rounded-2xl shadow-lg shadow-rose-200 text-white">🌡️</div>
                    <div>
                        <h2 class="text-xl font-black italic uppercase tracking-tighter text-slate-800">Heat Setting</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Dimensional Stability Control</p>
                    </div>
                </div>

                <form wire:submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-rose-50/30 rounded-[2rem] border border-dashed border-rose-200">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Input SAP NO</label>
                            <input type="text" wire:model.live.debounce.500ms="sap_no" class="w-full rounded-xl border-slate-200 focus:ring-rose-500 font-bold">
                        </div>
                        <div class="flex flex-col justify-center">
                            <span class="text-[9px] font-black text-slate-400 uppercase">Detail Kain</span>
                            <span class="text-sm font-black text-rose-700 uppercase">{{ $art_no ?? '-' }} / {{ $warna ?? '-' }}</span>
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
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Suhu Proses (°C)</label>
                            <input type="number" wire:model="suhu_set" class="w-full rounded-xl border-slate-200 font-black text-rose-600">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Speed</label>
                            <input type="number" wire:model="speed" class="w-full rounded-xl border-slate-200">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Overfeed (%)</label>
                            <input type="number" wire:model="overfeed" class="w-full rounded-xl border-slate-200">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Target Lebar</label>
                            <input type="number" wire:model="lebar_target" class="w-full rounded-xl border-slate-200">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Target Gramasi</label>
                            <input type="number" wire:model="gramasi_target" class="w-full rounded-xl border-slate-200">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Roll</label>
                            <input type="number" wire:model="jumlah_roll" class="w-full rounded-xl border-slate-200">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Berat (KG)</label>
                            <input type="number" step="0.01" wire:model="berat_kg" class="w-full rounded-xl border-slate-200 font-black text-slate-800">
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" class="w-full bg-rose-600 text-white py-4 rounded-2xl font-black italic uppercase tracking-tighter hover:bg-rose-700 transition shadow-xl shadow-rose-100">
                            Simpan Data Heat Setting 🌡️
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>