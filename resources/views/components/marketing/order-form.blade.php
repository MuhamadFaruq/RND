<?php
use Livewire\Component;
new class extends Component {};
?>
<div>
    <div class="mb-8 flex justify-between items-end">
            <div class="flex items-center">
                <div>
                    <h2 class="text-3xl font-black italic uppercase tracking-tighter text-slate-800 leading-none">
                        Marketing <span class="text-red-600">Input Order</span>
                    </h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Duniatex Production Monitoring System</p>
                </div>
            </div>
        </div>
    <div class="py-16 bg-slate-50 min-h-screen"> 
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <form wire:submit.prevent="submit" class="bg-white p-12 rounded-[3rem] shadow-xl shadow-slate-200/60 border border-slate-100">
                
                @if (session()->has('message'))
                    <div class="mb-8 p-5 bg-green-600 text-white rounded-2xl font-bold text-sm shadow-lg shadow-green-200 flex items-center">
                        <span class="mr-2">✅</span> {{ session('message') }}
                    </div>
                @endif

                <div class="mb-12">
                    <div class="flex items-center mb-6">
                        <div class="w-2 h-8 bg-red-600 rounded-full mr-4"></div>
                        <h3 class="text-slate-800 font-black uppercase italic tracking-tighter text-xl">I. Identitas <span class="text-red-600">Order</span></h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8"> 
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-2 tracking-widest">SAP NO</label>
                            <input type="number" wire:model.blur="sap_no" class="w-full px-5 py-4 rounded-2xl border-slate-200 bg-slate-50 font-bold text-sm text-slate-700 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none @error('sap_no') border-red-500 @enderror">
                            @if(isset($sapError) && $sapError) <p class="text-red-600 text-[10px] mt-2 font-bold">{{ $sapError }}</p> @endif
                            @error('sap_no') <p class="text-red-600 text-[10px] mt-2 font-bold">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-2 tracking-widest">ART NO</label>
                            <input type="text" wire:model="art_no" class="w-full px-5 py-4 rounded-2xl border-slate-200 bg-slate-50 font-bold text-sm text-slate-700 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-2 tracking-widest">Tanggal</label>
                            <input type="date" wire:model="tanggal" class="w-full px-5 py-4 rounded-2xl border-slate-200 bg-slate-50 font-bold text-sm text-slate-700 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-2 tracking-widest">Pelanggan</label>
                            <input type="text" wire:model="pelanggan" class="w-full px-5 py-4 rounded-2xl border-slate-200 bg-slate-50 font-bold text-sm text-slate-700 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none">
                        </div>
                    </div>
                </div>

                <div class="mb-12">
                    <div class="flex items-center mb-6">
                        <div class="w-2 h-8 bg-slate-800 rounded-full mr-4"></div>
                        <h3 class="text-slate-800 font-black uppercase italic tracking-tighter text-xl">II. Klasifikasi & <span class="text-red-600">Material</span></h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-2 tracking-widest">MKT (Sales)</label>
                            <select wire:model="mkt" class="w-full px-5 py-4 rounded-2xl border-slate-200 bg-slate-50 font-bold text-sm text-slate-700 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none appearance-none">
                                <option value="">Pilih MKT</option>
                                <option value="Sales 1">Sales 1</option>
                                <option value="Sales 2">Sales 2</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-2 tracking-widest">Keperluan</label>
                            <select wire:model="keperluan" class="w-full px-5 py-4 rounded-2xl border-slate-200 bg-slate-50 font-bold text-sm text-slate-700 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none appearance-none">
                                <option value="">Pilih Keperluan</option>
                                <option value="Sample">Sample</option>
                                <option value="Repeat Order">Repeat Order</option>
                                <option value="New Order">New Order</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-2 tracking-widest">Material</label>
                            <select wire:model="material" class="w-full px-5 py-4 rounded-2xl border-slate-200 bg-slate-50 font-bold text-sm text-slate-700 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none appearance-none">
                                <option value="">Pilih Material</option>
                                <option value="Cotton Combed">Cotton Combed</option>
                                <option value="CVC">CVC</option>
                                <option value="Polyester">Polyester</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-2 tracking-widest">Benang</label>
                            <input type="text" wire:model="benang" class="w-full px-5 py-4 rounded-2xl border-slate-200 bg-slate-50 font-bold text-sm text-slate-700 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none" placeholder="...">
                        </div>
                    </div>
                </div>

                <div class="mb-12">
                    <div class="flex items-center mb-6">
                        <div class="w-2 h-8 bg-red-600 rounded-full mr-4"></div>
                        <h3 class="text-slate-800 font-black uppercase italic tracking-tighter text-xl">III. Spesifikasi <span class="text-red-600">Teknis</span></h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-2 tracking-widest">Konstruksi Greige</label>
                            <input type="text" wire:model="konstruksi_greige" class="w-full px-5 py-4 rounded-2xl border-slate-200 bg-slate-50 font-bold text-sm text-slate-700 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-2 tracking-widest">Kelompok Kain</label>
                            <select wire:model="kelompok_kain" class="w-full px-5 py-4 rounded-2xl border-slate-200 bg-slate-50 font-bold text-sm text-slate-700 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none appearance-none">
                                <option value="">Pilih Kelompok</option>
                                <option value="Single Jersey">Single Jersey</option>
                                <option value="Pique">Pique</option>
                                <option value="Fleece">Fleece</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-2 tracking-widest">Target Lebar</label>
                            <input type="number" wire:model="target_lebar" class="w-full px-5 py-4 rounded-2xl border-slate-200 bg-slate-50 font-bold text-sm text-slate-700 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-2 tracking-widest">Belah/Bulat</label>
                            <select wire:model="belah_bulat" class="w-full px-5 py-4 rounded-2xl border-slate-200 bg-slate-50 font-bold text-sm text-slate-700 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none appearance-none">
                                <option value="">Pilih Jenis</option>
                                <option value="Belah">Belah (Open Width)</option>
                                <option value="Bulat">Bulat (Tubular)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-12">
                    <div class="flex items-center mb-6">
                        <div class="w-2 h-8 bg-slate-800 rounded-full mr-4"></div>
                        <h3 class="text-slate-800 font-black uppercase italic tracking-tighter text-xl">IV. Quantity & <span class="text-red-600">Keterangan</span></h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-2 tracking-widest">Quantity (Roll)</label>
                            <input type="number" wire:model="roll_target" class="w-full px-5 py-4 rounded-2xl border-slate-200 bg-slate-50 font-bold text-sm text-slate-700 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-2 tracking-widest">Quantity (KG)</label>
                            <input type="number" wire:model="kg_target" class="w-full px-5 py-4 rounded-2xl border-slate-200 bg-slate-50 font-bold text-sm text-slate-700 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-400 mb-2 tracking-widest">Keterangan Artikel</label>
                            <textarea wire:model="keterangan_artikel" rows="1" class="w-full px-5 py-4 rounded-2xl border-slate-200 bg-slate-50 font-bold text-sm text-slate-700 focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-10 border-t-2 border-slate-50 mt-12">
                    <button type="submit" wire:loading.attr="disabled" class="bg-red-600 text-white px-16 py-5 rounded-[2rem] font-black text-xs uppercase italic tracking-tighter hover:bg-slate-900 hover:-translate-y-1 active:scale-95 transition-all duration-300 shadow-2xl shadow-red-200">
                        <span wire:loading.remove>{{ isset($orderId) ? 'UPDATE ORDER' : 'PUBLISH ORDER' }}</span>
                        <span wire:loading class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            ⌛ SEDANG PROSES...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>