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
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-black text-slate-800 uppercase italic">Edit Order SAP: {{ $sap_no }}</h2>
                <a href="{{ route('marketing.orders.index') }}" class="text-slate-500 font-bold hover:text-slate-700">← KEMBALI</a>
            </div>

            <form wire:submit.prevent="update" class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <div class="mb-8">
                    <h3 class="text-red-600 font-bold mb-4 border-b pb-2 uppercase italic">I. IDENTITAS ORDER</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">SAP NO</label>
                            <input type="number" wire:model="sap_no" class="w-full rounded-lg border-gray-300 focus:ring-red-500">
                            @error('sap_no') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                        </div>
                </div>

                <div class="flex justify-end pt-6 border-t mt-8">
                    <button type="submit" class="bg-blue-600 text-white px-12 py-3 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                        SIMPAN PERUBAHAN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>