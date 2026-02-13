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
                    <div class="bg-yellow-500 p-3 rounded-2xl shadow-lg shadow-yellow-200 text-white">🧪</div>
                    <div>
                        <h2 class="text-xl font-black italic uppercase tracking-tighter text-slate-800">Lab Testing / Pengujian</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Quality Control Physical Test</p>
                    </div>
                </div>

                <form wire:submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-yellow-50/30 rounded-[2rem] border border-dashed border-yellow-200">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Nomor SAP</label>
                            <input type="text" wire:model.live.debounce.500ms="sap_no" class="w-full rounded-xl border-slate-200 focus:ring-yellow-500 font-bold">
                        </div>
                        <div class="flex flex-col justify-center">
                            <span class="text-[9px] font-black text-slate-400 uppercase">Art & Warna</span>
                            <span class="text-sm font-black text-yellow-700 uppercase">{{ $art_no ?? '-' }} / {{ $warna ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Gramasi (gr/m²)</label>
                            <input type="number" wire:model="gramasi_actual" class="w-full rounded-xl border-slate-200 font-bold text-slate-800">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Lebar (Inch)</label>
                            <input type="number" wire:model="lebar_actual" class="w-full rounded-xl border-slate-200 font-bold text-slate-800">
                        </div>
                    </div>

                    <div class="bg-slate-50 p-6 rounded-[2rem]">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase mb-4 tracking-widest text-center">Data Shrinkage (%)</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1 text-center">Susut Lebar</label>
                                <input type="number" step="0.1" wire:model="shrinkage_lebar" class="w-full rounded-xl border-slate-200 text-center">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1 text-center">Susut Panjang</label>
                                <input type="number" step="0.1" wire:model="shrinkage_panjang" class="w-full rounded-xl border-slate-200 text-center">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Washing Test / Color Fastness</label>
                        <input type="text" wire:model="washing_test" class="w-full rounded-xl border-slate-200" placeholder="Contoh: Grade 4-5">
                    </div>

                    <div class="pt-6">
                        <button type="submit" class="w-full bg-yellow-600 text-white py-4 rounded-2xl font-black italic uppercase tracking-tighter hover:bg-yellow-700 transition shadow-xl">
                            Simpan Hasil Pengujian Lab 🧪
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>