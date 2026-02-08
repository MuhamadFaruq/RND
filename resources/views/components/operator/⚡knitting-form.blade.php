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
                    <div class="bg-red-600 p-3 rounded-2xl shadow-lg shadow-red-200">🧶</div>
                    <div>
                        <h2 class="text-xl font-black italic uppercase tracking-tighter text-slate-800">Knitting Production</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Input Hasil Produksi Rajut</p>
                    </div>
                </div>

                <form wire:submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-slate-50 rounded-[2rem] border border-dashed border-slate-200">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Scan/Input SAP NO</label>
                            <input type="text" wire:model.live.debounce.500ms="sap_no" class="w-full rounded-xl border-slate-200 focus:ring-red-500 font-bold text-blue-600">
                            @error('sap_no') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Art / Warna</label>
                            <div class="text-sm font-black text-slate-700">
                                {{ $art_no ?? '-' }} / {{ $warna ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">No. Mesin</label>
                            <input type="text" wire:model="no_mesin" class="w-full rounded-xl border-slate-200">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Shift</label>
                            <select wire:model="shift" class="w-full rounded-xl border-slate-200">
                                <option value="">Pilih Shift</option>
                                <option value="1">Shift 1</option>
                                <option value="2">Shift 2</option>
                                <option value="3">Shift 3</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Roll</label>
                            <input type="number" wire:model="jumlah_roll" class="w-full rounded-xl border-slate-200">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Berat (KG)</label>
                            <input type="number" step="0.01" wire:model="berat_kg" class="w-full rounded-xl border-slate-200">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Actual Gramasi</label>
                            <input type="number" wire:model="gramasi_actual" class="w-full rounded-xl border-slate-200">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Actual Lebar</label>
                            <input type="number" wire:model="lebar_actual" class="w-full rounded-xl border-slate-200">
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black italic uppercase tracking-tighter hover:bg-black transition shadow-xl">
                            Simpan Hasil Rajut 🧶
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>