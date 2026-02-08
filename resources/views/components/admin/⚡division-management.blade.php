<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-black text-slate-800 uppercase italic">Division Management</h2>
                <button wire:click="openModal" class="bg-slate-900 text-white px-6 py-2 rounded-xl font-bold shadow-lg hover:bg-black transition">
                    + TAMBAH DIVISI
                </button>
            </div>

            @if (session()->has('message'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-xl font-bold text-sm">
                    {{ session('message') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($divisions as $div)
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-black text-slate-800 uppercase tracking-tighter">{{ $div->name }}</h3>
                        <p class="text-xs text-slate-400 mt-1">{{ $div->description ?: 'Tidak ada deskripsi' }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="edit({{ $div->id }})" class="text-blue-500 hover:text-blue-700">✏️</button>
                        <button wire:click="delete({{ $div->id }})" onclick="confirm('Hapus divisi ini?') || event.stopImmediatePropagation()" class="text-red-500 hover:text-red-700">🗑️</button>
                    </div>
                </div>
                @endforeach
            </div>

            @if($isModalOpen)
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                <div class="bg-white w-full max-w-md p-8 rounded-[2.5rem] shadow-2xl">
                    <h3 class="text-xl font-black uppercase italic mb-6">{{ $divisionId ? 'Edit Divisi' : 'Divisi Baru' }}</h3>
                    <form wire:submit.prevent="save" class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Nama Divisi</label>
                            <input type="text" wire:model="name" class="w-full rounded-xl border-slate-200 focus:ring-slate-900" placeholder="Contoh: Knitting">
                            @error('name') <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Deskripsi (Opsional)</label>
                            <textarea wire:model="description" class="w-full rounded-xl border-slate-200 focus:ring-slate-900" rows="3"></textarea>
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" wire:click="closeModal" class="px-6 py-2 text-slate-400 font-bold uppercase text-xs">Batal</button>
                            <button type="submit" class="bg-red-600 text-white px-8 py-2 rounded-xl font-black uppercase italic tracking-tighter shadow-lg shadow-red-100">
                                Simpan Divisi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>