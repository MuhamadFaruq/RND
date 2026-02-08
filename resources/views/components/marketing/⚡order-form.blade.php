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
            <form wire:submit.prevent="submit" class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                
                @if (session()->has('message'))
                    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-xl font-bold">
                        {{ session('message') }}
                    </div>
                @endif

                <div class="mb-8">
                    <h3 class="text-red-600 font-bold mb-4 border-b pb-2 uppercase italic tracking-tighter">I. IDENTITAS ORDER</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">SAP NO (BIGINT)</label>
                            <input type="number" wire:model.blur="sap_no" class="w-full rounded-lg border-gray-300 focus:ring-red-500 @error('sap_no') border-red-500 @enderror">
                            @if($sapError) <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $sapError }}</p> @endif
                            @error('sap_no') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">ART NO</label>
                            <input type="text" wire:model="art_no" class="w-full rounded-lg border-gray-300">
                            @error('art_no') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal</label>
                            <input type="date" wire:model="tanggal" class="w-full rounded-lg border-gray-300">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pelanggan</label>
                            <input type="text" wire:model="pelanggan" class="w-full rounded-lg border-gray-300">
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-red-600 font-bold mb-4 border-b pb-2 uppercase italic tracking-tighter">II. KLASIFIKASI & MATERIAL</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">MKT (Sales)</label>
                            <select wire:model="mkt" class="w-full rounded-lg border-gray-300">
                                <option value="">Pilih MKT</option>
                                <option value="Sales 1">Sales 1</option>
                                <option value="Sales 2">Sales 2</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Keperluan</label>
                            <select wire:model="keperluan" class="w-full rounded-lg border-gray-300">
                                <option value="">Pilih Keperluan</option>
                                <option value="Sample">Sample</option>
                                <option value="Repeat Order">Repeat Order</option>
                                <option value="New Order">New Order</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Material</label>
                            <select wire:model="material" class="w-full rounded-lg border-gray-300">
                                <option value="">Pilih Material</option>
                                <option value="Cotton Combed">Cotton Combed</option>
                                <option value="CVC">CVC</option>
                                <option value="Polyester">Polyester</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Benang</label>
                            <input type="text" wire:model="benang" class="w-full rounded-lg border-gray-300">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t mt-8">
                    <button type="submit" wire:loading.attr="disabled" class="bg-[#ED1C24] text-white px-12 py-3 rounded-xl font-bold hover:bg-red-700 transition shadow-lg shadow-red-200">
                        <span wire:loading.remove>PUBLISH ORDER</span>
                        <span wire:loading>PROSES...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>