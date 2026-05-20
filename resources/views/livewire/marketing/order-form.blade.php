<?php
use Livewire\Component;
new class extends Component { };
?>
<div x-data="{ openDetail: false, selected: {} }" class="bg-transparent italic tracking-tighter">
    {{-- Tailwind sudah dikompilasi via Vite, tidak perlu CDN --}}

    <div class="max-w-[1600px] mx-auto">

        {{-- Header --}}
        <div class="mb-4 md:mb-6 flex justify-between items-end">
            <div class="flex items-center px-2 md:px-0">
                <div>
                    <h2 class="text-xl md:text-3xl font-black italic uppercase tracking-tighter mkt-text leading-none">
                        Marketing <span class="text-red-600">Input Order</span>
                    </h2>
                    <p class="text-[8px] md:text-[10px] font-bold mkt-text-muted uppercase tracking-widest mt-1">Duniatex Production Monitoring</p>
                </div>
            </div>
        </div>

        <div class="animate-in fade-in duration-500 px-2 md:px-0">
            <form wire:submit.prevent="submit" class="mkt-surface p-4 md:p-8 rounded-2xl md:rounded-[3rem] shadow-xl mkt-border border">

                @if (session()->has('message'))
                    <div
                        class="mb-8 p-5 bg-green-600 text-white rounded-2xl font-bold text-sm shadow-lg shadow-green-200 flex items-center">
                        <span class="mr-2"></span> {{ session('message') }}
                    </div>
                @endif                {{-- ===================================== --}}
                {{-- I. IDENTITAS & TRANSAKSI (COLUMNS 1-6) --}}
                {{-- ===================================== --}}
                <div class="mb-6 md:mb-8">
                    <div class="flex items-center mb-4 md:mb-6">
                        <div class="w-1.5 md:w-2 h-6 md:h-8 bg-red-600 rounded-full mr-3 md:mr-4"></div>
                        <h3 class="mkt-text font-black uppercase italic tracking-tighter text-base md:text-xl">I. Identitas & <span class="text-red-600">Transaksi</span></h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-8">
                        <div class="relative" x-data="{ showSuggestions: false }" @click.away="showSuggestions = false">
                            <label
                                class="block text-[11px] font-black uppercase mkt-text-muted mb-2 tracking-widest">NO ARTIKEL (PRIMARY)</label>
                            <input type="text" 
                                wire:model.live.debounce.300ms="art_no" 
                                @focus="showSuggestions = true"
                                @input="showSuggestions = true"
                                placeholder="Masukkan Nomor Artikel"
                                class="w-full px-4 md:px-5 py-3 md:py-4 rounded-xl md:rounded-2xl mkt-border mkt-input font-bold text-sm focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none border @error('art_no') border-red-500 @enderror">
                            
                            {{-- Dropdown Rekomendasi --}}
                            <div x-show="showSuggestions && $wire.recommendations && $wire.recommendations.length > 0"
                                class="absolute z-50 left-0 right-0 mt-2 mkt-surface border mkt-border rounded-2xl shadow-2xl overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200"
                                style="display: none;">
                                <div class="p-3 bg-red-600 text-white flex justify-between items-center">
                                    <p class="text-[9px] font-black uppercase tracking-widest">Riwayat Artikel (Klik untuk Order Ulang)</p>
                                    <button type="button" @click="showSuggestions = false" class="text-white/80 hover:text-white font-bold text-[10px]">✕ TUTUP</button>
                                </div>
                                <div class="max-h-[220px] overflow-y-auto">
                                    @foreach($recommendations as $rec)
                                        <button type="button" wire:click="loadArticleTemplate({{ $rec['id'] }}); showSuggestions = false"
                                            class="w-full text-left px-5 py-3 hover:bg-red-50 dark:hover:bg-red-900/10 transition-all border-b mkt-border last:border-0 group">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <p class="text-sm font-black text-red-600 uppercase group-hover:scale-105 transition-transform origin-left">{{ $rec['art_no'] }}</p>
                                                    <p class="text-[10px] font-bold mkt-text-muted mt-1 uppercase">{{ $rec['warna'] }} • {{ $rec['kelompok_kain'] }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-tighter">PELANGGAN</p>
                                                    <p class="text-[10px] font-bold mkt-text uppercase">{{ $rec['pelanggan'] }}</p>
                                                </div>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            @if($exists)
                                <div class="mt-2 p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center gap-2">
                                    <span class="text-xs"></span>
                                    <p class="text-emerald-500 text-[10px] font-black uppercase tracking-wider">Artikel terdaftar di riwayat. Gunakan fitur diatas untuk repeat order cepat!</p>
                                </div>
                            @endif
                            @error('art_no') <p class="text-red-600 text-[10px] mt-2 font-bold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-black uppercase mkt-text-muted mb-2 tracking-widest">SAP ID (LEGACY/OPTIONAL)</label>
                            <input type="number" wire:model="sap_no" placeholder="Masukkan Nomor SAP (Opsional)"
                                class="w-full px-4 md:px-5 py-3 md:py-4 rounded-xl md:rounded-2xl mkt-border mkt-input font-bold text-sm focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none border @error('sap_no') border-red-500 @enderror">
                             @error('sap_no') <p class="text-red-600 text-[10px] mt-2 font-bold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-black uppercase mkt-text-muted mb-2 tracking-widest">Tanggal Order</label>
                            <input type="date" wire:model="tanggal"
                                class="w-full px-4 md:px-5 py-3 md:py-4 rounded-xl md:rounded-2xl mkt-border mkt-input font-bold text-sm focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none border">
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-black uppercase mkt-text-muted mb-2 tracking-widest">Pelanggan</label>
                            <input type="text" wire:model="pelanggan" placeholder="Masukkan Nama Pelanggan"
                                class="w-full px-4 md:px-5 py-3 md:py-4 rounded-xl md:rounded-2xl mkt-border mkt-input font-bold text-sm focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none border">
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-black uppercase mkt-text-muted mb-2 tracking-widest">MKT (Sales)</label>
                            <input type="text" wire:model="mkt" placeholder="Masukkan Nama Marketing"
                                class="w-full px-4 md:px-5 py-3 md:py-4 rounded-xl md:rounded-2xl mkt-border mkt-input font-bold text-sm focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none border">
                            @error('mkt') <p class="text-red-600 text-[10px] mt-2 font-bold uppercase italic">
                            {{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-black uppercase mkt-text-muted mb-2 tracking-widest">Keperluan</label>
                            <select wire:model="keperluan"
                                class="w-full px-4 md:px-5 py-3 md:py-4 rounded-xl md:rounded-2xl mkt-border mkt-input font-bold text-sm focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none appearance-none border">
                                <option value="">Pilih Keperluan</option>
                                <option value="Sample">Sample</option>
                                <option value="Repeat Order">Repeat Order</option>
                                <option value="New Order">New Order</option>
                                <option value="Develop Color">Develop</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- =================================== --}}
                {{-- II. GREIGE TECHNICALS (COLUMNS 7-10) --}}
                {{-- =================================== --}}
                <div class="mb-6 md:mb-12">
                    <div class="flex items-center mb-4 md:mb-6">
                        <div class="w-1.5 md:w-2 h-6 md:h-8 bg-slate-600 rounded-full mr-3 md:mr-4"></div>
                        <h3 class="mkt-text font-black uppercase italic tracking-tighter text-base md:text-xl">II. Greige <span class="text-red-600">Technicals</span></h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 md:gap-8">
                        <div>
                            <label
                                class="block text-[11px] font-black uppercase mkt-text-muted mb-2 tracking-widest">Konstruksi Greige</label>
                            <input type="text" wire:model="konstruksi_greige" placeholder="Masukkan Konstruksi Greige"
                                class="w-full px-4 md:px-5 py-3 md:py-4 rounded-xl md:rounded-2xl mkt-border mkt-input font-bold text-sm focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none border">
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-black uppercase mkt-text-muted mb-2 tracking-widest">Material</label>
                            <select wire:model="material"
                                class="w-full px-4 md:px-5 py-3 md:py-4 rounded-xl md:rounded-2xl mkt-border mkt-input font-bold text-sm focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none appearance-none border">
                                <option value="">Pilih Material</option>
                                <option value="TR">TR</option>
                                <option value="CVC">CVC</option>
                                <option value="CMC">CMC</option>
                                <option value="PE">PE</option>
                                <option value="Cotton Combed">Cotton Combed</option>
                                <option value="Polyester">Polyester</option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-black uppercase mkt-text-muted mb-2 tracking-widest">Benang</label>
                            <input type="text" wire:model="benang"
                                class="w-full px-4 md:px-5 py-3 md:py-4 rounded-xl md:rounded-2xl mkt-border mkt-input font-bold text-sm focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none border"
                                placeholder="Masukkan Benang">
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-black uppercase mkt-text-muted mb-2 tracking-widest">Kelompok Kain</label>
                            <select wire:model="kelompok_kain"
                                class="w-full px-4 md:px-5 py-3 md:py-4 rounded-xl md:rounded-2xl mkt-border mkt-input font-bold text-sm focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none appearance-none border">
                                <option value="">Pilih Kelompok</option>
                                <option value="Single Jersey">Single Jersey</option>
                                <option value="Pique">Pique</option>
                                <option value="Fleece">Fleece</option>
                                <option value="CORAK DK">CORAK DK</option>
                                <option value="SPX BAL">SPX BAL</option>
                                <option value="VTR">VTR</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ===================================== --}}
                {{-- III. FINISHING TARGETS (COLUMNS 11-16) --}}
                {{-- ===================================== --}}
                <div class="mb-6 md:mb-8">
                    <div class="flex items-center mb-4 md:mb-6">
                        <div class="w-1.5 md:w-2 h-6 md:h-8 bg-red-600 rounded-full mr-3 md:mr-4"></div>
                        <h3 class="mkt-text font-black uppercase italic tracking-tighter text-base md:text-xl">III. Finishing <span class="text-red-600">Targets</span></h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-8">
                        <div>
                            <label
                                class="block text-[11px] font-black uppercase mkt-text-muted mb-2 tracking-widest">Target Lebar</label>
                            <input type="number" wire:model="target_lebar" placeholder="Masukkan Target Lebar"
                                class="w-full px-4 md:px-5 py-3 md:py-4 rounded-xl md:rounded-2xl mkt-border mkt-input font-bold text-sm focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none border">
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-black uppercase mkt-text-muted mb-2 tracking-widest">Belah/Bulat</label>
                            <select wire:model="belah_bulat"
                                class="w-full px-4 md:px-5 py-3 md:py-4 rounded-xl md:rounded-2xl mkt-border mkt-input font-bold text-sm focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none appearance-none border">
                                <option value="">Pilih Jenis</option>
                                <option value="Belah">Belah (Open Width)</option>
                                <option value="Bulat">Bulat (Tubular)</option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-black uppercase mkt-text-muted mb-2 tracking-widest">Target Gramasi</label>
                            <input type="text" wire:model="target_gramasi"
                                class="w-full px-4 md:px-5 py-3 md:py-4 rounded-xl md:rounded-2xl mkt-border mkt-input font-bold text-sm focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none border"
                                placeholder="Contoh: 280-290">
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-black uppercase mkt-text-muted mb-2 tracking-widest">Warna Kain</label>
                            <input type="text" wire:model="warna"
                                class="w-full px-4 md:px-5 py-3 md:py-4 rounded-xl md:rounded-2xl mkt-border mkt-input font-bold text-sm focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none border @error('warna') border-red-500 @enderror"
                                placeholder="Contoh: Hitam Pekat">
                            @error('warna') <span class="text-red-500 text-[10px] mt-2 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-black uppercase mkt-text-muted mb-2 tracking-widest">Handfeel</label>
                            <select wire:model="handfeel"
                                class="w-full px-4 md:px-5 py-3 md:py-4 rounded-xl md:rounded-2xl mkt-border mkt-input font-bold text-sm focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none appearance-none border">
                                <option value="">Pilih Handfeel</option>
                                <option value="Super Soft">Super Soft</option>
                                <option value="Soft">Soft</option>
                                <option value="Medium">Medium</option>
                                <option value="Hard/Stiff">Hard / Stiff</option>
                                <option value="Ikuti Sample">Ikuti Sample</option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-black uppercase mkt-text-muted mb-2 tracking-widest">Treatment Khusus</label>
                            <input type="text" wire:model="treatment_khusus"
                                placeholder="Contoh: Anti-Bacterial, UV Protection..."
                                class="w-full px-4 md:px-5 py-3 md:py-4 rounded-xl md:rounded-2xl mkt-border mkt-input font-bold text-sm focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none border">
                        </div>
                    </div>
                </div>

                {{-- ======================================= --}}
                {{-- IV. QUANTITY & KETERANGAN (COLUMNS 17-19) --}}
                {{-- ======================================= --}}
                <div class="mb-6 md:mb-12">
                    <div class="flex items-center mb-4 md:mb-6">
                        <div class="w-1.5 md:w-2 h-6 md:h-8 bg-slate-600 rounded-full mr-3 md:mr-4"></div>
                        <h3 class="mkt-text font-black uppercase italic tracking-tighter text-base md:text-xl">IV. Quantity & <span class="text-red-600">Keterangan</span></h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-8">
                        <div>
                            <label
                                class="block text-[11px] font-black uppercase mkt-text-muted mb-2 tracking-widest">Quantity (Roll)</label>
                            <input type="number" wire:model="roll_target" placeholder="Masukkan Jumlah Roll"
                                class="w-full px-4 md:px-5 py-3 md:py-4 rounded-xl md:rounded-2xl mkt-border mkt-input font-bold text-sm focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none border">
                        </div>
                        <div>
                            <label
                                class="block text-[11px] font-black uppercase mkt-text-muted mb-2 tracking-widest">Quantity (KG)</label>
                            <input type="number" wire:model="kg_target" placeholder="Masukkan Jumlah KG"
                                class="w-full px-4 md:px-5 py-3 md:py-4 rounded-xl md:rounded-2xl mkt-border mkt-input font-bold text-sm focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none border">
                        </div>
                        <div>
                            <label
                                class="block text-[11px] font-black uppercase mkt-text-muted mb-2 tracking-widest">Keterangan Artikel</label>
                            <textarea wire:model="keterangan_artikel" rows="1" placeholder="Masukkan Keterangan Artikel"
                                class="w-full px-4 md:px-5 py-3 md:py-4 rounded-xl md:rounded-2xl mkt-border mkt-input font-bold text-sm focus:ring-4 focus:ring-red-500/10 focus:border-red-600 transition-all outline-none border"></textarea>
                        </div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 p-4 rounded-2xl mb-4 border border-red-100">
                        <p class="text-red-600 text-[10px] font-black uppercase italic mb-2">Terjadi Kesalahan Input:</p>
                        <ul class="list-disc ml-4 text-[10px] text-red-500 font-bold italic">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex justify-end pt-6 md:pt-10 border-t-2 mkt-border mt-8 md:mt-12">
                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full md:w-auto bg-red-600 text-white px-8 md:px-16 py-4 md:py-5 rounded-2xl md:rounded-[2rem] font-black text-[10px] md:text-xs uppercase italic tracking-tighter md:tracking-wider hover:bg-slate-900 hover:-translate-y-1 active:scale-95 transition-all duration-300 shadow-2xl shadow-red-600/20 dark:shadow-none flex items-center justify-center">
                        <span wire:loading.remove>{{ isset($orderId) ? 'UPDATE ORDER' : 'PUBLISH ORDER' }}</span>
                        <span wire:loading class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-2 md:mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            ⌛ PROSES...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>