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
                    <div class="bg-emerald-600 p-3 rounded-2xl shadow-lg shadow-emerald-200 text-white">🏆</div>
                    <div>
                        <h2 class="text-xl font-black italic uppercase tracking-tighter text-slate-800">Quality Engineering (QE)</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Final Inspection & Grading</p>
                    </div>
                </div>

                <form wire:submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-emerald-50/30 rounded-[2rem] border border-dashed border-emerald-200">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Nomor SAP</label>
                            <input type="text" wire:model.live.debounce.500ms="sap_no" class="w-full rounded-xl border-slate-200 focus:ring-emerald-500 font-bold">
                        </div>
                        <div class="flex flex-col justify-center">
                            <span class="text-[9px] font-black text-slate-400 uppercase">Status Kain</span>
                            <span class="text-sm font-black text-emerald-700 uppercase">{{ $art_no ?? 'Menunggu SAP...' }}</span>
                        </div>
                    </div>

                    <div class="p-6 bg-slate-50 rounded-[2rem]">
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-4 text-center tracking-widest">Tentukan Grade Akhir</label>
                        <div class="flex justify-center gap-4">
                            @foreach(['A', 'B', 'C'] as $g)
                            <label class="cursor-pointer">
                                <input type="radio" wire:model="grade" value="{{ $g }}" class="hidden peer">
                                <div class="w-16 h-16 flex items-center justify-center rounded-2xl border-2 border-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-500 peer-checked:text-white font-black transition">
                                    {{ $g }}
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="col-span-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Defect Points / Notes</label>
                            <input type="text" wire:model="defect_points" class="w-full rounded-xl border-slate-200" placeholder="Misal: 4 point system">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Final Gramasi</label>
                            <input type="number" wire:model="final_gramasi" class="w-full rounded-xl border-slate-200">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Final Lebar</label>
                            <input type="number" wire:model="final_lebar" class="w-full rounded-xl border-slate-200">
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" class="w-full bg-emerald-900 text-white py-4 rounded-2xl font-black italic uppercase tracking-tighter hover:bg-black transition shadow-xl">
                            Simpan Hasil Inspeksi Akhir (QE) 🏆
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>