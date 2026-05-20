<div x-data="{ showMarketing: false }">
    <div class="py-4 md:py-8 mkt-bg min-h-screen italic mkt-text">
        <div class="w-full max-w-[1600px] mx-auto px-3 sm:px-4 md:px-6">
            <div class="mkt-surface rounded-2xl md:rounded-3xl p-4 md:p-8 shadow-sm border mkt-border">

                {{-- Header --}}
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                    <div>
                        <h1 class="text-3xl md:text-4xl font-[1000] italic leading-none mkt-text tracking-tighter">
                            {{ strtoupper($currentStep['key']) }}<br>
                            <span class="text-indigo-600">LOGBOOK</span>
                        </h1>
                        <div class="flex items-center gap-2 mt-4">
                            <div class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></div>
                            <p class="text-[10px] font-black mkt-text-muted uppercase tracking-[0.2em]">Formulir Input Produksi {{ $currentStep['label'] }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        @if($order)
                            <button type="button" @click="showMarketing = true"
                                class="bg-indigo-600 px-6 py-3 rounded-xl text-[10px] font-black uppercase text-white hover:bg-indigo-500 hover:scale-105 transition-all shadow-[0_10px_30px_rgba(79,70,229,0.3)] flex items-center gap-2">
                                DETAIL ORDER
                            </button>
                            <button wire:click="$set('order', null)"
                                class="bg-white dark:bg-slate-800 px-6 py-3 rounded-xl text-[9px] font-black uppercase text-slate-400 hover:text-white transition-all border border-white/10 shadow-sm flex items-center gap-2">
                                KEMBALI
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Artikel Lookup Form --}}
                @if(!$order)
                    <div class="max-w-xl mx-auto text-center space-y-6">
                        <div>
                            <label class="block text-xs font-black mkt-text-muted uppercase mb-3 tracking-widest">Masukkan Nomor Artikel Produk</label>
                            <div class="relative">
                                <input type="text" wire:model.defer="artikelInput" wire:keydown.enter="lookupArtikel"
                                    class="w-full mkt-surface-alt border-2 border-white/10 text-white rounded-xl px-4 py-3 text-lg font-black text-center focus:ring-4 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all placeholder:text-slate-500 uppercase border"
                                    placeholder="CONTOH: ART12345">
                                <button wire:click="lookupArtikel"
                                    class="absolute right-2 top-2 bottom-2 bg-indigo-600 hover:bg-indigo-500 text-white px-4 rounded-lg font-bold uppercase text-[10px] transition-all shadow-md shadow-indigo-600/20">
                                    <span wire:loading.remove wire:target="lookupArtikel">Cari</span>
                                    <span wire:loading wire:target="lookupArtikel">Mencari...</span>
                                </button>
                            </div>
                            @if($artikelError)
                                <p
                                    class="text-indigo-600 font-bold text-xs mt-3 bg-indigo-600/10 py-2 px-4 rounded-lg inline-block border border-red-500/20">
                                    {{ $artikelError }}</p>
                            @endif
                            @error('artikelInput') <span
                            class="text-indigo-600 font-bold text-xs mt-2 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @else
                    {{-- Main Layout: Sidebar & Content --}}
                    <div class="flex flex-col lg:flex-row gap-8 mt-6">

                        {{-- Sidebar Checklist --}}
                        <div class="w-full lg:w-1/3">
                            <div class="mkt-surface-alt backdrop-blur-xl rounded-2xl p-4 md:p-6 shadow-sm border mkt-border sticky top-6">
                                <h3
                                    class="mkt-text font-black uppercase tracking-widest text-xs mb-6 border-b mkt-border pb-4 flex items-center justify-between">
                                    <span>Artikel: {{ $order->art_no }}</span>
                                    <button wire:click="$set('order', null)"
                                        class="text-slate-400 hover:text-white transition-colors"
                                        title="Cari order lain"></button>
                                </h3>

                                {{-- Workflow Configurator (Operator controls finishing flow) --}}
                                <div class="mb-6 p-5 bg-indigo-950/40 border border-indigo-500/20 rounded-2xl">
                                    <p class="text-[9px] font-black uppercase text-indigo-400 mb-3 tracking-widest flex items-center gap-1">
                                        <span></span> KONFIGURASI ALUR FINISHING
                                    </p>
                                    <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                                        @foreach([
                                            'req_compactor' => 'Compactor', 
                                            'req_heat_setting' => 'Heat Setting', 
                                            'req_stenter' => 'Stenter', 
                                            'req_tumbler' => 'Tumbler', 
                                            'req_fleece' => 'Fleece'
                                        ] as $flag => $label)
                                            <label class="flex items-center gap-2 cursor-pointer p-1.5 hover:bg-white/5 rounded-xl transition-all select-none">
                                                <input type="checkbox" 
                                                    wire:click="toggleWorkflowFlag('{{ $flag }}')" 
                                                    {{ $order->$flag ? 'checked' : '' }} 
                                                    class="rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-600 w-3.5 h-3.5">
                                                <span class="text-[9px] font-black uppercase mkt-text tracking-wider">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    @foreach($checklist as $index => $item)
                                        @php
                                            $statusColor = match ($item['status']) {
                                                'saved' => 'text-emerald-600 bg-emerald-500/10 border-emerald-500/30 shadow-[0_0_10px_rgba(16,185,129,0.1)]',
                                                'active' => 'text-white bg-indigo-600 border-indigo-600 shadow-[0_0_15px_rgba(79,70,229,0.3)] scale-[1.02] transform transition-all',
                                                'pending' => 'text-indigo-600 bg-indigo-50 dark:bg-indigo-950 border-indigo-200 dark:border-indigo-800 shadow-sm',
                                                'skipped' => 'text-slate-400 bg-slate-100 dark:bg-slate-900 border-slate-200 dark:border-slate-800 opacity-50 grayscale',
                                            };

                                            $statusIcon = match ($item['status']) {
                                                'saved' => '',
                                                'active' => '▶️',
                                                'pending' => '⏳',
                                                'skipped' => '⏭️',
                                            };
                                        @endphp

                                        <div 
                                            @if($item['status'] !== 'skipped') 
                                                wire:click="goToStep('{{ $item['key'] }}')" 
                                            @endif
                                            class="flex items-center justify-between p-4 rounded-2xl border {{ $statusColor }} transition-all duration-300 {{ $item['status'] !== 'skipped' ? 'cursor-pointer hover:shadow-md hover:scale-[1.01]' : 'cursor-not-allowed' }}">
                                            <div class="flex items-center gap-3">
                                                <span class="text-lg opacity-80">{{ $item['icon'] }}</span>
                                                <div>
                                                    <p class="font-bold text-xs uppercase tracking-wide {{ $item['status'] === 'active' ? 'text-white' : 'mkt-text' }}">{{ $item['label'] }}
                                                    </p>
                                                    <p class="text-[9px] opacity-70 font-medium tracking-widest {{ $item['status'] === 'active' ? 'text-indigo-100' : 'mkt-text-muted' }}">
                                                        @if($item['status'] === 'skipped') Tidak Wajib @else
                                                        {{ ucfirst($item['status']) }} @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <span class="text-xs">{{ $statusIcon }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-6 pt-4 border-t mkt-border">
                                    <button wire:click="submitAll" @if(!$canSubmitAll) disabled @endif
                                        class="w-full py-3 rounded-xl font-black uppercase text-[10px] tracking-widest transition-all shadow-md {{ $canSubmitAll ? 'bg-indigo-600 hover:bg-indigo-500 text-white hover:-translate-y-1' : 'bg-slate-800 text-slate-400 cursor-not-allowed opacity-50' }}">
                                        Simpan Semua ke Tahap Selanjutnya
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Main Form Area --}}
                        <div class="w-full lg:w-2/3 space-y-6">
                            @if($currentStep)
                                <div class="mkt-surface-alt border mkt-border rounded-2xl p-4 md:p-6">
                                    <div class="flex items-center gap-3 mb-6 pb-4 border-b mkt-border">
                                        <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-black text-lg shadow-md shadow-indigo-600/20">01</div>
                                        <div>
                                            <h3 class="text-sm font-black uppercase mkt-text tracking-widest">{{ $currentStep['label'] }}</h3>
                                            <p class="text-[9px] font-bold mkt-text-muted uppercase italic">Input Technical Data Produksi</p>
                                        </div>
                                    </div>

                                    <form wire:submit.prevent="saveCurrentStep" class="space-y-6">
                                         <div class="grid grid-cols-1">
                                                 @if($currentStep['key'] === 'stenter')
                                                <div class="col-span-full space-y-6">
                                                    <!-- Section Header -->
                                                    <div class="mkt-surface p-4 md:p-6 rounded-2xl border mkt-border shadow-sm space-y-4">
                                                        <div class="flex items-center gap-3 border-b mkt-border pb-3">
                                                            <span class="text-2xl"></span>
                                                            <div>
                                                                <h4 class="text-md font-black mkt-text uppercase">Identitas Mesin & Operator</h4>
                                                                <p class="text-[9px] font-bold mkt-text-muted uppercase italic">Detail utama untuk proses Stenter</p>
                                                            </div>
                                                        </div>
                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                            <div>
                                                                <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">No Mesin</label>
                                                                <input type="text" wire:model="stenter_no_mesin" placeholder="MASUKKAN NO MESIN"
                                                                    class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                            </div>
                                                            <div>
                                                                <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Operator</label>
                                                                <input type="text" wire:model="stenter_operator" placeholder="MASUKKAN NAMA OPERATOR"
                                                                    class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Technical Data Grid grouped by Parameter -->
                                                    <div class="space-y-6">
                                                        <!-- Column Titles (Desktop Only) -->
                                                        <div class="hidden md:grid grid-cols-12 gap-6 px-6 py-3 border-b mkt-border bg-slate-900/40 rounded-2xl p-4">
                                                            <div class="col-span-3 text-[10px] font-black uppercase text-indigo-400 tracking-widest">Parameter</div>
                                                            <div class="col-span-3 text-[10px] font-black uppercase text-amber-500 tracking-widest text-center">Preset Phase</div>
                                                            <div class="col-span-3 text-[10px] font-black uppercase text-sky-500 tracking-widest text-center">Drying Phase</div>
                                                            <div class="col-span-3 text-[10px] font-black uppercase text-emerald-500 tracking-widest text-center">Finishing Phase</div>
                                                        </div>

                                                        <!-- PARAMETER 1: TANGGAL -->
                                                        <div class="mkt-surface border mkt-border p-4 md:p-6 rounded-2xl shadow-sm hover:border-violet-500 transition-all duration-300">
                                                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                                                <div class="col-span-3">
                                                                    <h5 class="text-sm font-black mkt-text uppercase leading-none">Tanggal</h5>
                                                                    <p class="text-[9px] font-bold text-slate-500 uppercase italic mt-1">Date of process</p>
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-amber-500">Preset</span>
                                                                    <input type="date" wire:model="stenter_preset.tanggal" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-sky-500">Drying</span>
                                                                    <input type="date" wire:model="stenter_drying.tanggal" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-emerald-500">Finishing</span>
                                                                    <input type="date" wire:model="stenter_finishing.tanggal" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- PARAMETER 2: TEMPERATURE -->
                                                        <div class="mkt-surface border mkt-border p-4 md:p-6 rounded-2xl shadow-sm hover:border-violet-500 transition-all duration-300">
                                                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                                                <div class="col-span-3">
                                                                    <h5 class="text-sm font-black mkt-text uppercase leading-none">Temperature (°C)</h5>
                                                                    <p class="text-[9px] font-bold text-slate-500 uppercase italic mt-1">Suhu proses mesin</p>
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-amber-500">Preset</span>
                                                                    <input type="text" wire:model="stenter_preset.suhu" placeholder="TEMPERATURE" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-sky-500">Drying</span>
                                                                    <input type="text" wire:model="stenter_drying.suhu" placeholder="TEMPERATURE" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-emerald-500">Finishing</span>
                                                                    <input type="text" wire:model="stenter_finishing.suhu" placeholder="TEMPERATURE" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- PARAMETER 3: SPEED -->
                                                        <div class="mkt-surface border mkt-border p-4 md:p-6 rounded-2xl shadow-sm hover:border-violet-500 transition-all duration-300">
                                                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                                                <div class="col-span-3">
                                                                    <h5 class="text-sm font-black mkt-text uppercase leading-none">Speed (m/min)</h5>
                                                                    <p class="text-[9px] font-bold text-slate-500 uppercase italic mt-1">Kecepatan mesin</p>
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-amber-500">Preset</span>
                                                                    <input type="text" wire:model="stenter_preset.speed" placeholder="SPEED" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-sky-500">Drying</span>
                                                                    <input type="text" wire:model="stenter_drying.speed" placeholder="SPEED" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-emerald-500">Finishing</span>
                                                                    <input type="text" wire:model="stenter_finishing.speed" placeholder="SPEED" class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- PARAMETER 4: PADDER -->
                                                        <div class="mkt-surface border mkt-border p-4 md:p-6 rounded-2xl shadow-sm hover:border-violet-500 transition-all duration-300">
                                                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                                                <div class="col-span-3">
                                                                    <h5 class="text-sm font-black mkt-text uppercase leading-none">Padder</h5>
                                                                    <p class="text-[9px] font-bold text-slate-500 uppercase italic mt-1">Tekanan padder</p>
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-amber-500">Preset</span>
                                                                    <input type="text" wire:model="stenter_preset.padder" placeholder="PADDER" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-sky-500">Drying</span>
                                                                    <input type="text" wire:model="stenter_drying.padder" placeholder="PADDER" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-emerald-500">Finishing</span>
                                                                    <input type="text" wire:model="stenter_finishing.padder" placeholder="PADDER" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- PARAMETER 5: RANGKA -->
                                                        <div class="mkt-surface border mkt-border p-4 md:p-6 rounded-2xl shadow-sm hover:border-violet-500 transition-all duration-300">
                                                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                                                <div class="col-span-3">
                                                                    <h5 class="text-sm font-black mkt-text uppercase leading-none">Rangka</h5>
                                                                    <p class="text-[9px] font-bold text-slate-500 uppercase italic mt-1">Lebar rangka penarik</p>
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-amber-500">Preset</span>
                                                                    <input type="text" wire:model="stenter_preset.rangka" placeholder="RANGKA" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-sky-500">Drying</span>
                                                                    <input type="text" wire:model="stenter_drying.rangka" placeholder="RANGKA" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-emerald-500">Finishing</span>
                                                                    <input type="text" wire:model="stenter_finishing.rangka" placeholder="RANGKA" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- PARAMETER 6: OVERFEED A -->
                                                        <div class="mkt-surface border mkt-border p-4 md:p-6 rounded-2xl shadow-sm hover:border-violet-500 transition-all duration-300">
                                                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                                                <div class="col-span-3">
                                                                    <h5 class="text-sm font-black mkt-text uppercase leading-none">Overfeed A</h5>
                                                                    <p class="text-[9px] font-bold text-slate-500 uppercase italic mt-1">Setting overfeed A</p>
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-amber-500">Preset</span>
                                                                    <input type="text" wire:model="stenter_preset.overfeed_a" placeholder="OVERFEED A" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-sky-500">Drying</span>
                                                                    <input type="text" wire:model="stenter_drying.overfeed_a" placeholder="OVERFEED A" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-emerald-500">Finishing</span>
                                                                    <input type="text" wire:model="stenter_finishing.overfeed_a" placeholder="OVERFEED A" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- PARAMETER 7: OVERFEED B -->
                                                        <div class="mkt-surface border mkt-border p-4 md:p-6 rounded-2xl shadow-sm hover:border-violet-500 transition-all duration-300">
                                                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                                                <div class="col-span-3">
                                                                    <h5 class="text-sm font-black mkt-text uppercase leading-none">Overfeed B</h5>
                                                                    <p class="text-[9px] font-bold text-slate-500 uppercase italic mt-1">Setting overfeed B</p>
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-amber-500">Preset</span>
                                                                    <input type="text" wire:model="stenter_preset.overfeed_b" placeholder="OVERFEED B" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-sky-500">Drying</span>
                                                                    <input type="text" wire:model="stenter_drying.overfeed_b" placeholder="OVERFEED B" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-emerald-500">Finishing</span>
                                                                    <input type="text" wire:model="stenter_finishing.overfeed_b" placeholder="OVERFEED B" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- PARAMETER 8: FAN/BLOWER -->
                                                        <div class="mkt-surface border mkt-border p-4 md:p-6 rounded-2xl shadow-sm hover:border-violet-500 transition-all duration-300">
                                                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                                                <div class="col-span-3">
                                                                    <h5 class="text-sm font-black mkt-text uppercase leading-none">Fan/Blower</h5>
                                                                    <p class="text-[9px] font-bold text-slate-500 uppercase italic mt-1">Kecepatan putaran kipas blower</p>
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-amber-500">Preset</span>
                                                                    <input type="text" wire:model="stenter_preset.fan" placeholder="FAN/BLOWER" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-sky-500">Drying</span>
                                                                    <input type="text" wire:model="stenter_drying.fan" placeholder="FAN/BLOWER" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-emerald-500">Finishing</span>
                                                                    <input type="text" wire:model="stenter_finishing.fan" placeholder="FAN/BLOWER" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- PARAMETER 9: DELIVERY SPEED -->
                                                        <div class="mkt-surface border mkt-border p-4 md:p-6 rounded-2xl shadow-sm hover:border-violet-500 transition-all duration-300">
                                                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                                                <div class="col-span-3">
                                                                    <h5 class="text-sm font-black mkt-text uppercase leading-none">Delivery Speed</h5>
                                                                    <p class="text-[9px] font-bold text-slate-500 uppercase italic mt-1">Kecepatan penarikan keluar kain</p>
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-amber-500">Preset</span>
                                                                    <input type="text" wire:model="stenter_preset.delivery" placeholder="DELIVERY SPEED" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-sky-500">Drying</span>
                                                                    <input type="text" wire:model="stenter_drying.delivery" placeholder="DELIVERY SPEED" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-emerald-500">Finishing</span>
                                                                    <input type="text" wire:model="stenter_finishing.delivery" placeholder="DELIVERY SPEED" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- PARAMETER 10: FOLDING SPEED -->
                                                        <div class="mkt-surface border mkt-border p-4 md:p-6 rounded-2xl shadow-sm hover:border-violet-500 transition-all duration-300">
                                                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                                                <div class="col-span-3">
                                                                    <h5 class="text-sm font-black mkt-text uppercase leading-none">Folding Speed</h5>
                                                                    <p class="text-[9px] font-bold text-slate-500 uppercase italic mt-1">Kecepatan folding kain</p>
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-amber-500">Preset</span>
                                                                    <input type="text" wire:model="stenter_preset.folding" placeholder="FOLDING SPEED" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-sky-500">Drying</span>
                                                                    <input type="text" wire:model="stenter_drying.folding" placeholder="FOLDING SPEED" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-emerald-500">Finishing</span>
                                                                    <input type="text" wire:model="stenter_finishing.folding" placeholder="FOLDING SPEED" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- PARAMETER 11: CHEMICAL 1 -->
                                                        <div class="mkt-surface border mkt-border p-4 md:p-6 rounded-2xl shadow-sm hover:border-violet-500 transition-all duration-300">
                                                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                                                <div class="col-span-3">
                                                                    <h5 class="text-sm font-black mkt-text uppercase leading-none">Chemical 1</h5>
                                                                    <p class="text-[9px] font-bold text-slate-500 uppercase italic mt-1">Formulasi chemical 1</p>
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-amber-500">Preset</span>
                                                                    <input type="text" wire:model="stenter_preset.chem1" placeholder="CHEMICAL 1" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-sky-500">Drying</span>
                                                                    <input type="text" wire:model="stenter_drying.chem1" placeholder="CHEMICAL 1" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-emerald-500">Finishing</span>
                                                                    <input type="text" wire:model="stenter_finishing.chem1" placeholder="CHEMICAL 1" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- PARAMETER 12: CHEMICAL 2 -->
                                                        <div class="mkt-surface border mkt-border p-4 md:p-6 rounded-2xl shadow-sm hover:border-violet-500 transition-all duration-300">
                                                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                                                <div class="col-span-3">
                                                                    <h5 class="text-sm font-black mkt-text uppercase leading-none">Chemical 2</h5>
                                                                    <p class="text-[9px] font-bold text-slate-500 uppercase italic mt-1">Formulasi chemical 2</p>
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-amber-500">Preset</span>
                                                                    <input type="text" wire:model="stenter_preset.chem2" placeholder="CHEMICAL 2" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-sky-500">Drying</span>
                                                                    <input type="text" wire:model="stenter_drying.chem2" placeholder="CHEMICAL 2" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-emerald-500">Finishing</span>
                                                                    <input type="text" wire:model="stenter_finishing.chem2" placeholder="CHEMICAL 2" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- PARAMETER 13: HASIL LEBAR -->
                                                        <div class="mkt-surface border mkt-border p-4 md:p-6 rounded-2xl shadow-sm hover:border-violet-500 transition-all duration-300">
                                                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                                                <div class="col-span-3">
                                                                    <h5 class="text-sm font-black mkt-text uppercase leading-none">Hasil Lebar</h5>
                                                                    <p class="text-[9px] font-bold text-slate-500 uppercase italic mt-1">Lebar akhir kain (inch)</p>
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-amber-500">Preset</span>
                                                                    <input type="text" wire:model="stenter_preset.lebar" placeholder="HASIL LEBAR" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-sky-500">Drying</span>
                                                                    <input type="text" wire:model="stenter_drying.lebar" placeholder="HASIL LEBAR" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-emerald-500">Finishing</span>
                                                                    <input type="text" wire:model="stenter_finishing.lebar" placeholder="HASIL LEBAR" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- PARAMETER 14: HASIL GRAMASI -->
                                                        <div class="mkt-surface border mkt-border p-4 md:p-6 rounded-2xl shadow-sm hover:border-violet-500 transition-all duration-300">
                                                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                                                <div class="col-span-3">
                                                                    <h5 class="text-sm font-black mkt-text uppercase leading-none">Hasil Gramasi</h5>
                                                                    <p class="text-[9px] font-bold text-slate-500 uppercase italic mt-1">Gramasi akhir kain (gsm)</p>
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-amber-500">Preset</span>
                                                                    <input type="text" wire:model="stenter_preset.gramasi" placeholder="HASIL GRAMASI" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-sky-500">Drying</span>
                                                                    <input type="text" wire:model="stenter_drying.gramasi" placeholder="HASIL GRAMASI" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-emerald-500">Finishing</span>
                                                                    <input type="text" wire:model="stenter_finishing.gramasi" placeholder="HASIL GRAMASI" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- PARAMETER 15: SHRINKAGE -->
                                                        <div class="mkt-surface border mkt-border p-4 md:p-6 rounded-2xl shadow-sm hover:border-violet-500 transition-all duration-300">
                                                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                                                <div class="col-span-3">
                                                                    <h5 class="text-sm font-black mkt-text uppercase leading-none">Shrinkage</h5>
                                                                    <p class="text-[9px] font-bold text-slate-500 uppercase italic mt-1">Penyusutan kain (%)</p>
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-amber-500">Preset</span>
                                                                    <input type="text" wire:model="stenter_preset.shrinkage" placeholder="SHRINKAGE" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-sky-500">Drying</span>
                                                                    <input type="text" wire:model="stenter_drying.shrinkage" placeholder="SHRINKAGE" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div class="col-span-3 space-y-1">
                                                                    <span class="block md:hidden text-[9px] font-black uppercase text-emerald-500">Finishing</span>
                                                                    <input type="text" wire:model="stenter_finishing.shrinkage" placeholder="SHRINKAGE" class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                 </div>
                                            @elseif($currentStep['key'] === 'fleece')
                                                <div x-data="{ phase: 'raising' }" class="col-span-full">
                                                    <div class="flex flex-wrap gap-2 mb-6 p-1 mkt-surface rounded-xl w-fit border mkt-border">
                                                        <button type="button" @click="phase = 'raising'"
                                                            :class="phase === 'raising' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:text-white'"
                                                            class="px-4 py-2 rounded-lg text-[10px] font-black uppercase transition-all">Raising</button>
                                                        <button type="button" @click="phase = 'brushing'"
                                                            :class="phase === 'brushing' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:text-white'"
                                                            class="px-4 py-2 rounded-lg text-[10px] font-black uppercase transition-all">Brushing</button>
                                                        <button type="button" @click="phase = 'shearing'"
                                                            :class="phase === 'shearing' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:text-white'"
                                                            class="px-4 py-2 rounded-lg text-[10px] font-black uppercase transition-all">Shearing</button>
                                                    </div>

                                                    <div>
                                                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">No Mesin</label>
                                                        <input type="text" wire:model="fleece_no_mesin" placeholder="MASUKKAN NOMOR MESIN"
                                                            class="w-full md:w-fit mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase mb-6 md:mb-8">
                                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mt-4 pt-4 border-t mkt-border">
                                                            <div class="col-span-full text-[10px] font-black text-rose-500 uppercase italic mb-2 tracking-widest"
                                                                x-text="'Process: ' + phase"></div>

                                                            {{-- Raising Fields --}}
                                                            <template x-if="phase === 'raising'">
                                                                <div class="col-span-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                                                                    {{-- Operator --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black text-indigo-600 italic uppercase mb-2">Operator</label>
                                                                        <input type="text" wire:model="fleece_raising.operator" placeholder="NAMA OPERATOR..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Tanggal --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black text-indigo-600 italic uppercase mb-2">Tanggal</label>
                                                                        <input type="date" wire:model="fleece_raising.tanggal"
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Standar Bulu --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Standar Bulu</label>
                                                                        <input type="text" wire:model="fleece_raising.standar_bulu" placeholder="STD BULU..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Speed --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Speed</label>
                                                                        <input type="text" wire:model="fleece_raising.speed" placeholder="SPEED..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Cloth Out --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Cloth Out</label>
                                                                        <input type="text" wire:model="fleece_raising.cloth_out" placeholder="CLOTH OUT..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Bend Pin --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Bend Pin</label>
                                                                        <input type="text" wire:model="fleece_raising.bend_pin" placeholder="BEND PIN..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Straight Pin --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Straight Pin</label>
                                                                        <input type="text" wire:model="fleece_raising.stright_pin" placeholder="STRAIGHT PIN..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- RPM Drum --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">RPM Drum</label>
                                                                        <input type="text" wire:model="fleece_raising.rpm_drum" placeholder="RPM DRUM..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Lebar/GSM --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black text-indigo-600 italic uppercase mb-2">Lebar/GSM</label>
                                                                        <input type="text" wire:model="fleece_raising.lebar_gsm" placeholder="LEBAR/GSM..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Drum Brush --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Drum Brush</label>
                                                                        <input type="text" wire:model="fleece_raising.drum_brush" placeholder="DRUM BRUSH..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                </div>
                                                            </template>

                                                            {{-- Brushing Fields --}}
                                                            <template x-if="phase === 'brushing'">
                                                                <div class="col-span-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                                                                    {{-- Operator --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black text-indigo-600 italic uppercase mb-2">Operator</label>
                                                                        <input type="text" wire:model="fleece_brushing.operator" placeholder="NAMA OPERATOR..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Tanggal --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black text-indigo-600 italic uppercase mb-2">Tanggal</label>
                                                                        <input type="date" wire:model="fleece_brushing.tanggal"
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Standar Bulu --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Standar Bulu</label>
                                                                        <input type="text" wire:model="fleece_brushing.standar_bulu" placeholder="STD BULU..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Cloth Speed --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Cloth Speed</label>
                                                                        <input type="text" wire:model="fleece_brushing.cloth_speed" placeholder="CLOTH SPEED..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Cloth Out --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Cloth Out</label>
                                                                        <input type="text" wire:model="fleece_brushing.cloth_out" placeholder="CLOTH OUT..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Left Brush --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Left Brush</label>
                                                                        <input type="text" wire:model="fleece_brushing.left_brush" placeholder="LEFT BRUSH..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Right Brush --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Right Brush</label>
                                                                        <input type="text" wire:model="fleece_brushing.right_brush" placeholder="RIGHT BRUSH..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- RPM Drum --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">RPM Drum</label>
                                                                        <input type="text" wire:model="fleece_brushing.rpm_drum" placeholder="RPM DRUM..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Tension 1/2/3 --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Tension 1/2/3</label>
                                                                        <input type="text" wire:model="fleece_brushing.tension" placeholder="TENSION..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Lebar/Gramasi --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black text-indigo-600 italic uppercase mb-2">Lebar/Gramasi</label>
                                                                        <input type="text" wire:model="fleece_brushing.lebar_gramasi" placeholder="LEBAR/GRAMASI..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                </div>
                                                            </template>

                                                            {{-- Shearing Fields --}}
                                                            <template x-if="phase === 'shearing'">
                                                                <div class="col-span-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                                                                    {{-- Operator --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black text-indigo-600 italic uppercase mb-2">Operator</label>
                                                                        <input type="text" wire:model="fleece_shearing.operator" placeholder="NAMA OPERATOR..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Tanggal --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black text-indigo-600 italic uppercase mb-2">Tanggal</label>
                                                                        <input type="date" wire:model="fleece_shearing.tanggal"
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Speed --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Speed</label>
                                                                        <input type="text" wire:model="fleece_shearing.speed" placeholder="SPEED..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Cloth Out --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Cloth Out</label>
                                                                        <input type="text" wire:model="fleece_shearing.cloth_out" placeholder="CLOTH OUT..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Expending --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Expending</label>
                                                                        <input type="text" wire:model="fleece_shearing.expending" placeholder="EXPENDING..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Shear --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Shear</label>
                                                                        <input type="text" wire:model="fleece_shearing.shear" placeholder="SHEAR..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                    {{-- Lebar/GSM --}}
                                                                    <div class="space-y-2">
                                                                        <label class="block text-[10px] font-black text-indigo-600 italic uppercase mb-2">Lebar/Gramasi</label>
                                                                        <input type="text" wire:model="fleece_shearing.lebar_gramasi" placeholder="LEBAR/GRAMASI..."
                                                                            class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif($currentStep['key'] === 'dyeing')
                                                <div class="space-y-16 animate-in fade-in duration-500">
                                                    {{-- Group 1: CEK GREIGE & HASIL FISIK --}}
                                                    <div class="space-y-10">
                                                        <div class="flex items-center gap-6">
                                                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-[0_10px_30px_rgba(79,70,229,0.3)]">01</div>
                                                            <div>
                                                                <h3 class="text-lg font-[1000] uppercase mkt-text tracking-widest leading-none">CEK GREIGE & HASIL FISIK</h3>
                                                                <p class="text-[9px] font-black mkt-text-muted uppercase italic mt-1 tracking-widest">Pengecekan fisik awal kain greige</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-8">
                                                            {{-- Cek Greige --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 mkt-text-muted">
                                                                    Cek Greige
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="dyeing_cek_greige" placeholder="TULIS CEK GREIGE..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('dyeing_cek_greige') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Lebar --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Lebar
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="dyeing_lebar" placeholder="TULIS LEBAR..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('dyeing_lebar') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Gramasi --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Gramasi
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="number" wire:model="dyeing_gramasi" placeholder="TULIS GRAMASI..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('dyeing_gramasi') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Group 2: IDENTITAS PETUGAS & WAKTU --}}
                                                    <div class="space-y-10">
                                                        <div class="flex items-center gap-6">
                                                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-[0_10px_30px_rgba(79,70,229,0.3)]">02</div>
                                                            <div>
                                                                <h3 class="text-lg font-[1000] uppercase mkt-text tracking-widest leading-none">IDENTITAS PETUGAS & WAKTU</h3>
                                                                <p class="text-[9px] font-black mkt-text-muted uppercase italic mt-1 tracking-widest">Operator penanggung jawab dan tanggal proses</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
                                                            {{-- Operator --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Operator
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="dyeing_operator" placeholder="TULIS NAMA OPERATOR..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('dyeing_operator') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Tanggal --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 mkt-text-muted">
                                                                    Tanggal
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="date" wire:model="dyeing_tanggal"
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('dyeing_tanggal') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Group 3: MESIN PRODUKSI --}}
                                                    <div class="space-y-10">
                                                        <div class="flex items-center gap-6">
                                                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-[0_10px_30px_rgba(79,70,229,0.3)]">03</div>
                                                            <div>
                                                                <h3 class="text-lg font-[1000] uppercase mkt-text tracking-widest leading-none">MESIN PRODUKSI</h3>
                                                                <p class="text-[9px] font-black mkt-text-muted uppercase italic mt-1 tracking-widest">Detail mesin dyeing yang digunakan</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
                                                            {{-- Jenis Mesin --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 mkt-text-muted">
                                                                    Jenis Mesin
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="dyeing_jenis_mesin" placeholder="TULIS JENIS MESIN..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('dyeing_jenis_mesin') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- No Mesin --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    No Mesin
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="dyeing_no_mesin" placeholder="TULIS NOMOR MESIN..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('dyeing_no_mesin') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Group 4: WARNA & TREATMENT KIMIA --}}
                                                    <div class="space-y-10">
                                                        <div class="flex items-center gap-6">
                                                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-[0_10px_30px_rgba(79,70,229,0.3)]">04</div>
                                                            <div>
                                                                <h3 class="text-lg font-[1000] uppercase mkt-text tracking-widest leading-none">WARNA & TREATMENT KIMIA</h3>
                                                                <p class="text-[9px] font-black mkt-text-muted uppercase italic mt-1 tracking-widest">Detail warna dan formula kimia dyeing</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8">
                                                            {{-- Warna --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Warna
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="dyeing_warna" placeholder="TULIS WARNA..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('dyeing_warna') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Kode Warna --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 mkt-text-muted">
                                                                    Kode Warna
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="dyeing_kode_warna" placeholder="TULIS KODE WARNA..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('dyeing_kode_warna') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Dye System --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 mkt-text-muted">
                                                                    Dye System
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="dyeing_dye_system" placeholder="TULIS DYE SYSTEM..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('dyeing_dye_system') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Treatment --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 mkt-text-muted">
                                                                    Treatment (Chemical)
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="dyeing_treatment" placeholder="TULIS TREATMENT..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('dyeing_treatment') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif($currentStep['key'] === 'relax-dryer')
                                                <div class="space-y-16 animate-in fade-in duration-500">
                                                    {{-- Group 1: IDENTITAS OPERATOR & TANGGAL --}}
                                                    <div class="space-y-10">
                                                        <div class="flex items-center gap-6">
                                                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-[0_10px_30px_rgba(79,70,229,0.3)]">01</div>
                                                            <div>
                                                                <h3 class="text-lg font-[1000] uppercase mkt-text tracking-widest leading-none">IDENTITAS & WAKTU</h3>
                                                                <p class="text-[9px] font-black mkt-text-muted uppercase italic mt-1 tracking-widest">Operator penanggung jawab dan tanggal proses</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
                                                            {{-- Operator --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 mkt-text-muted">
                                                                    Operator
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="relax_operator" placeholder="TULIS NAMA OPERATOR..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('relax_operator') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Tanggal --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Tanggal
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="date" wire:model="relax_tanggal"
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('relax_tanggal') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Group 2: CHEMICAL & HANDFEEL --}}
                                                    <div class="space-y-10">
                                                        <div class="flex items-center gap-6">
                                                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-[0_10px_30px_rgba(79,70,229,0.3)]">02</div>
                                                            <div>
                                                                <h3 class="text-lg font-[1000] uppercase mkt-text tracking-widest leading-none">CHEMICAL & HANDFEEL</h3>
                                                                <p class="text-[9px] font-black mkt-text-muted uppercase italic mt-1 tracking-widest">Formula kimia dan target kelembutan kain</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
                                                            {{-- Chemical --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Chemical
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="relax_chemical" placeholder="TULIS CHEMICAL..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('relax_chemical') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Handfeel --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Handfeel
                                                                </label>
                                                                <div class="relative">
                                                                    <select wire:model="relax_handfeel"
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase appearance-none">
                                                                        <option value="">-- PILIH HANDFEEL --</option>
                                                                        <option value="Super Soft">Super Soft</option>
                                                                        <option value="Soft">Soft</option>
                                                                        <option value="Medium">Medium</option>
                                                                        <option value="Hard/Stiff">Hard / Stiff</option>
                                                                        <option value="Ikuti Sample">Ikuti Sample</option>
                                                                    </select>
                                                                </div>
                                                                @error('relax_handfeel') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Group 3: MESIN & SETTING PARAMETER --}}
                                                    <div class="space-y-10">
                                                        <div class="flex items-center gap-6">
                                                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-[0_10px_30px_rgba(79,70,229,0.3)]">03</div>
                                                            <div>
                                                                <h3 class="text-lg font-[1000] uppercase mkt-text tracking-widest leading-none">MESIN & PARAMETER SETTING</h3>
                                                                <p class="text-[9px] font-black mkt-text-muted uppercase italic mt-1 tracking-widest">Pengaturan teknis mesin dryer</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-x-6 gap-y-8">
                                                            {{-- Mesin --}}
                                                            <div class="space-y-3 group col-span-1 md:col-span-2">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Mesin
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="relax_no_mesin" placeholder="TULIS NAMA MESIN..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('relax_no_mesin') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Overfeed --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Overfeed
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="relax_overfeed" placeholder="TULIS OVERFEED..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('relax_overfeed') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Temperatur / Suhu --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Temperatur
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="number" wire:model="relax_suhu" placeholder="TULIS TEMPERATURE..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('relax_suhu') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Group 4: OUTCOME & HASIL FISIK KAIN --}}
                                                    <div class="space-y-10">
                                                        <div class="flex items-center gap-6">
                                                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-[0_10px_30px_rgba(79,70,229,0.3)]">04</div>
                                                            <div>
                                                                <h3 class="text-lg font-[1000] uppercase mkt-text tracking-widest leading-none">OUTCOME & HASIL FISIK</h3>
                                                                <p class="text-[9px] font-black mkt-text-muted uppercase italic mt-1 tracking-widest">Spesifikasi fisik kain greige setelah pengeringan</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-x-6 gap-y-8">
                                                            {{-- Speed --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Speed
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="number" wire:model="relax_speed" placeholder="TULIS SPEED..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('relax_speed') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Hasil Lebar --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Hasil Lebar
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="relax_lebar" placeholder="TULIS HASIL LEBAR..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('relax_lebar') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Hasil Gramasi --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Hasil Gramasi
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="relax_gramasi" placeholder="TULIS HASIL GRAMASI..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('relax_gramasi') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Shrinkage (V x H) --}}
                                                            <div class="space-y-3 group col-span-1">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Shrinkage (V x H)
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="relax_shrinkage" placeholder="TULIS SHRINKAGE..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('relax_shrinkage') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif($currentStep['key'] === 'compactor')
                                                <div class="space-y-16 animate-in fade-in duration-700">
                                                    {{-- I. IDENTITAS & WAKTU --}}
                                                    <div class="space-y-10">
                                                        <div class="flex items-center gap-6">
                                                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-[0_10px_30px_rgba(79,70,229,0.3)]">
                                                                <span class="text-xl"></span>
                                                            </div>
                                                            <div>
                                                                <h3 class="text-lg font-[1000] uppercase mkt-text tracking-widest leading-none">IDENTITAS & WAKTU</h3>
                                                                <p class="text-[9px] font-black mkt-text-muted uppercase italic mt-1 tracking-widest">Informasi petugas, waktu pengerjaan, dan detail mesin</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-x-6 gap-y-8">
                                                            {{-- Operator --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Operator
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="compactor_operator" placeholder="NAMA OPERATOR..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('compactor_operator') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Tanggal --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Tanggal
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="date" wire:model="compactor_tanggal"
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('compactor_tanggal') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- No Mesin --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    No Mesin
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="compactor_no_mesin" placeholder="TULIS NO MESIN..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('compactor_no_mesin') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Rangka --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Rangka
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="compactor_rangka" placeholder="TULIS RANGKA..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('compactor_rangka') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- II. PARAMETER MESIN COMPACTOR --}}
                                                    <div class="space-y-10">
                                                        <div class="flex items-center gap-6">
                                                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-[0_10px_30px_rgba(79,70,229,0.3)]">
                                                                <span class="text-xl"></span>
                                                            </div>
                                                            <div>
                                                                <h3 class="text-lg font-[1000] uppercase mkt-text tracking-widest leading-none">PARAMETER MESIN COMPACTOR</h3>
                                                                <p class="text-[9px] font-black mkt-text-muted uppercase italic mt-1 tracking-widest">Kondisi operasional pemadatan suhu dan overfeed</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-x-6 gap-y-8">
                                                            {{-- Temperature --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Temperature (°C)
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="compactor_suhu" placeholder="TULIS SUHU..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('compactor_suhu') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Speed --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Speed (m/min)
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="compactor_speed" placeholder="TULIS SPEED..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('compactor_speed') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Overfeed --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Overfeed
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="compactor_overfeed" placeholder="TULIS OVERFEED..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('compactor_overfeed') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Felt --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Felt
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="compactor_felt" placeholder="TULIS FELT..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('compactor_felt') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- III. DRIVE & FOLDING SETTING --}}
                                                    <div class="space-y-10">
                                                        <div class="flex items-center gap-6">
                                                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-[0_10px_30px_rgba(79,70,229,0.3)]">

                                                            </div>
                                                            <div>
                                                                <h3 class="text-lg font-[1000] uppercase mkt-text tracking-widest leading-none">DRIVE & FOLDING SETTING</h3>
                                                                <p class="text-[9px] font-black mkt-text-muted uppercase italic mt-1 tracking-widest">Kecepatan penghantaran dan lipatan kain bulat</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
                                                            {{-- Delivery Speed --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Delivery Speed
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="compactor_delivery_speed" placeholder="TULIS DELIVERY SPEED..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('compactor_delivery_speed') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Folding Speed --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Folding Speed
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="compactor_folding_speed" placeholder="TULIS FOLDING SPEED..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('compactor_folding_speed') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- IV. OUTCOME & HASIL FISIK --}}
                                                    <div class="space-y-10">
                                                        <div class="flex items-center gap-6">
                                                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-[0_10px_30px_rgba(79,70,229,0.3)]">
                                                           </div>
                                                            <div>
                                                                <h3 class="text-lg font-[1000] uppercase mkt-text tracking-widest leading-none">OUTCOME & HASIL FISIK</h3>
                                                                <p class="text-[9px] font-black mkt-text-muted uppercase italic mt-1 tracking-widest">Dimensi fisik final pasca pemadatan</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-8">
                                                            {{-- Hasil Lebar --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Hasil Lebar
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="compactor_lebar" placeholder="TULIS HASIL LEBAR..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('compactor_lebar') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Hasil Gramasi --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Hasil Gramasi
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="compactor_gramasi" placeholder="TULIS HASIL GRAMASI..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('compactor_gramasi') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Shrinkage --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Shrinkage (V x H)
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="compactor_shrinkage" placeholder="TULIS SHRINKAGE..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('compactor_shrinkage') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif($currentStep['key'] === 'heat-setting')
                                                <div class="space-y-16 animate-in fade-in duration-700">
                                                    {{-- I. IDENTITAS & WAKTU --}}
                                                    <div class="space-y-10">
                                                        <div class="flex items-center gap-6">
                                                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-[0_10px_30px_rgba(79,70,229,0.3)]">
                                                           </div>
                                                            <div>
                                                                <h3 class="text-lg font-[1000] uppercase mkt-text tracking-widest leading-none">IDENTITAS & WAKTU</h3>
                                                                <p class="text-[9px] font-black mkt-text-muted uppercase italic mt-1 tracking-widest">Informasi petugas, waktu pengerjaan, dan detail mesin</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-x-6 gap-y-8">
                                                            {{-- Operator --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Operator
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="heat_operator" placeholder="NAMA OPERATOR..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('heat_operator') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Tanggal --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Tanggal
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="date" wire:model="heat_tanggal"
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('heat_tanggal') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- No Mesin --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    No Mesin
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="heat_no_mesin" placeholder="TULIS NO MESIN..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('heat_no_mesin') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Rangka --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Rangka
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="heat_rangka" placeholder="TULIS RANGKA..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('heat_rangka') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- II. PARAMETER MESIN HEAT SETTING --}}
                                                    <div class="space-y-10">
                                                        <div class="flex items-center gap-6">
                                                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-[0_10px_30px_rgba(79,70,229,0.3)]">
                                                           </div>
                                                            <div>
                                                                <h3 class="text-lg font-[1000] uppercase mkt-text tracking-widest leading-none">PARAMETER MESIN HEAT SETTING</h3>
                                                                <p class="text-[9px] font-black mkt-text-muted uppercase italic mt-1 tracking-widest">Kondisi operasional pemantapan suhu dan overfeed</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-8">
                                                            {{-- Temperatur --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Temperatur (°C)
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="heat_suhu" placeholder="TULIS TEMPERATUR..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('heat_suhu') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Speed --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Speed (m/min)
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="heat_speed" placeholder="TULIS SPEED..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('heat_speed') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Overfeed --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Overfeed
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="heat_overfeed" placeholder="TULIS OVERFEED..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('heat_overfeed') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- III. DRIVE & FOLDING SETTING --}}
                                                    <div class="space-y-10">
                                                        <div class="flex items-center gap-6">
                                                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-[0_10px_30px_rgba(79,70,229,0.3)]">
                                                           </div>
                                                            <div>
                                                                <h3 class="text-lg font-[1000] uppercase mkt-text tracking-widest leading-none">DRIVE & FOLDING SETTING</h3>
                                                                <p class="text-[9px] font-black mkt-text-muted uppercase italic mt-1 tracking-widest">Kecepatan penghantaran dan lipatan kain bulat</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
                                                            {{-- Delivery Speed --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Delivery Speed
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="heat_delivery_speed" placeholder="TULIS DELIVERY SPEED..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('heat_delivery_speed') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Folding Speed --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Folding Speed
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="heat_folding_speed" placeholder="TULIS FOLDING SPEED..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('heat_folding_speed') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- IV. OUTCOME & HASIL FISIK --}}
                                                    <div class="space-y-10">
                                                        <div class="flex items-center gap-6">
                                                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-[0_10px_30px_rgba(79,70,229,0.3)]">
                                                            </div>
                                                            <div>
                                                                <h3 class="text-lg font-[1000] uppercase mkt-text tracking-widest leading-none">OUTCOME & HASIL FISIK</h3>
                                                                <p class="text-[9px] font-black mkt-text-muted uppercase italic mt-1 tracking-widest">Dimensi fisik final pasca heat setting</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
                                                            {{-- Hasil Lebar --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Hasil Lebar
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="heat_lebar" placeholder="TULIS HASIL LEBAR..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('heat_lebar') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Hasil Gramasi --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Hasil Gramasi
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="heat_gramasi" placeholder="TULIS HASIL GRAMASI..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('heat_gramasi') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif($currentStep['key'] === 'tumbler')
                                                <div class="space-y-16 animate-in fade-in duration-700">
                                                    {{-- I. IDENTITAS & WAKTU --}}
                                                    <div class="space-y-10">
                                                        <div class="flex items-center gap-6">
                                                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-[0_10px_30px_rgba(79,70,229,0.3)]">
                                                            </div>
                                                            <div>
                                                                <h3 class="text-lg font-[1000] uppercase mkt-text tracking-widest leading-none">IDENTITAS & WAKTU (TUMBLER DRY)</h3>
                                                                <p class="text-[9px] font-black mkt-text-muted uppercase italic mt-1 tracking-widest">Informasi petugas, tanggal pengerjaan, dan no mesin</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-8">
                                                            {{-- Operator --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Operator
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="tumbler_operator" placeholder="NAMA OPERATOR..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('tumbler_operator') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Tanggal --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Tanggal
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="date" wire:model="tumbler_tanggal"
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('tumbler_tanggal') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- No Mesin --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    No Mesin
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="tumbler_no_mesin" placeholder="NO MESIN..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('tumbler_no_mesin') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- II. SETTING MESIN --}}
                                                    <div class="space-y-10">
                                                        <div class="flex items-center gap-6">
                                                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-[0_10px_30px_rgba(79,70,229,0.3)]">
                                                            </div>
                                                            <div>
                                                                <h3 class="text-lg font-[1000] uppercase mkt-text tracking-widest leading-none">SETTING MESIN</h3>
                                                                <p class="text-[9px] font-black mkt-text-muted uppercase italic mt-1 tracking-widest">Parameter suhu dan udara mesin</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-x-6 gap-y-8">
                                                            {{-- Temperature (suhu) --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 mkt-text-muted">
                                                                    Temperature (°C)
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="tumbler_suhu" placeholder="SUHU..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('tumbler_suhu') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Steam Inject --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 mkt-text-muted">
                                                                    Steam Inject
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="tumbler_steam_inject" placeholder="STEAM INJECT..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('tumbler_steam_inject') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Hotwind --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 mkt-text-muted">
                                                                    Hotwind
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="tumbler_hotwind" placeholder="HOTWIND..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('tumbler_hotwind') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Coldwind --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 mkt-text-muted">
                                                                    Coldwind
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="tumbler_coldwind" placeholder="COLDWIND..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('tumbler_coldwind') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- III. HASIL PROSES --}}
                                                    <div class="space-y-10">
                                                        <div class="flex items-center gap-6">
                                                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-[0_10px_30px_rgba(79,70,229,0.3)]">
                                                           </div>
                                                            <div>
                                                                <h3 class="text-lg font-[1000] uppercase mkt-text tracking-widest leading-none">HASIL PROSES</h3>
                                                                <p class="text-[9px] font-black mkt-text-muted uppercase italic mt-1 tracking-widest">Spesifikasi fisik hasil proses tumbler</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-8">
                                                            {{-- Lebar --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Lebar
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="tumbler_lebar" placeholder="TULIS LEBAR..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('tumbler_lebar') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Gramasi --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Gramasi
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="tumbler_gramasi" placeholder="TULIS GRAMASI..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('tumbler_gramasi') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>

                                                            {{-- Shrinkage --}}
                                                            <div class="space-y-3 group">
                                                                <label class="block text-[10px] font-black uppercase tracking-widest ml-1 text-indigo-600 italic">
                                                                    Shrinkage (V x H)
                                                                </label>
                                                                <div class="relative">
                                                                    
                                                                    <input type="text" wire:model="tumbler_shrinkage" placeholder="SHRINKAGE..."
                                                                        class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                @error('tumbler_shrinkage') <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                            <div class="space-y-16">
                                                @php
                                                    $fields = $currentStep['fields'];
                                                    
                                                    // Map fields to categories manually for high-precision UX
                                                    $categories = [
                                                        '01' => [
                                                            'title' => 'IDENTITAS & SPESIFIKASI',
                                                            'subtitle' => 'IDENTITAS MESIN DAN OPERATOR BERTUGAS',
                                                            'fields' => []
                                                        ],
                                                        '02' => [
                                                            'title' => 'KONDISI TEKNIS',
                                                            'subtitle' => 'PARAMETER SETTING MESIN SELAMA PROSES',
                                                            'fields' => []
                                                        ],
                                                        '03' => [
                                                            'title' => 'HASIL PRODUKSI',
                                                            'subtitle' => 'HASIL AKHIR FISIK KAIN DAN TREATMENT',
                                                            'fields' => []
                                                        ]
                                                    ];

                                                    foreach($fields as $f) {
                                                        $parts = explode('_', $f, 2);
                                                        $k = $parts[1] ?? $f;
                                                        
                                                        if(in_array($k, ['operator', 'no_mesin', 'tanggal', 'jenis_mesin', 'cek_greige'])) {
                                                            $categories['01']['fields'][] = $f;
                                                        } elseif(in_array($k, ['suhu', 'speed', 'durasi', 'overfeed', 'shrinkage', 'fan', 'hotwind', 'coldwind', 'steam_inject', 'delivery_speed', 'folding_speed', 'rangka', 'felt'])) {
                                                            $categories['02']['fields'][] = $f;
                                                        } else {
                                                            $categories['03']['fields'][] = $f;
                                                        }
                                                    }

                                                    $fieldIcons = [
                                                        'operator' => '', 'no_mesin' => '', 'tanggal' => '',
                                                        'jenis_mesin' => '', 'cek_greige' => '', 'suhu' => '',
                                                        'speed' => '', 'lebar' => '', 'gramasi' => '',
                                                        'warna' => '', 'kode_warna' => '', 'dye_system' => '',
                                                        'treatment' => '', 'chemical' => '', 'handfeel' => ''
                                                    ];
                                                @endphp

                                                @foreach($categories as $num => $cat)
                                                    @if(count($cat['fields']) > 0)
                                                        <div class="space-y-10">
                                                            {{-- SECTION HEADER --}}
                                                            <div class="flex items-center gap-6">
                                                                <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-[0_10px_30px_rgba(79,70,229,0.3)]">{{ $num }}</div>
                                                                <div>
                                                                    <h3 class="text-lg font-[1000] uppercase mkt-text tracking-widest leading-none">{{ $cat['title'] }}</h3>
                                                                    <p class="text-[9px] font-black mkt-text-muted uppercase italic mt-1 tracking-widest">{{ $cat['subtitle'] }}</p>
                                                                </div>
                                                            </div>

                                                            {{-- INPUT GRID --}}
                                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8">
                                                                @foreach($cat['fields'] as $field)
                                                                    @php
                                                                        $parts = explode('_', $field, 2);
                                                                        $key = $parts[1] ?? $field;
                                                                        $fieldLabel = $customLabels[$key] ?? ucwords(str_replace('_', ' ', $key));
                                                                        $isDate = str_contains($field, 'tanggal');
                                                                        $isNumber = in_array($key, ['suhu', 'speed', 'lebar', 'durasi', 'overfeed', 'gramasi', 'shrinkage', 'delivery_speed', 'folding_speed', 'rangka', 'fan', 'hotwind', 'coldwind', 'steam_inject']);
                                                                        $icon = $fieldIcons[$key] ?? '';
                                                                        
                                                                        // Primary Style (Red Label) like the image
                                                                        $isPrimary = in_array($key, ['operator', 'no_mesin', 'lebar', 'gramasi', 'warna']);
                                                                    @endphp
                                                                    <div class="space-y-3 group">
                                                                        <label class="block text-[10px] font-black uppercase tracking-widest ml-1 {{ $isPrimary ? 'text-indigo-600 italic' : 'mkt-text-muted' }}">
                                                                            {{ $fieldLabel }}
                                                                        </label>
                                                                        <div class="relative">
                                                                            
                                                                            <input type="{{ $isDate ? 'date' : ($isNumber ? 'number' : 'text') }}"
                                                                                wire:model="{{ $field }}"
                                                                                placeholder="TULIS {{ $fieldLabel }}..."
                                                                                class="w-full mkt-surface border mkt-border rounded-xl px-4 py-3 font-black text-xs mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                        </div>
                                                                        @error($field) <span class="text-indigo-600 text-[9px] font-black mt-1 block uppercase italic">{{ $message }}</span> @enderror
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>

                                        <div class="flex items-center justify-between pt-8 border-t mkt-border mt-8">
                                            @if($currentStepIndex > 0)
                                                <button type="button" wire:click="prevStep"
                                                    class="px-8 py-4 bg-slate-800 border mkt-border text-slate-400 rounded-xl font-black text-xs uppercase hover:bg-indigo-600 hover:text-white transition-all shadow-md">
                                                    KEMBALI
                                                </button>
                                            @else
                                                <div></div>
                                            @endif

                                            <button type="submit"
                                                class="px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-indigo-600/30 hover:-translate-y-1 transition-all flex items-center gap-2">
                                                Simpan {{ $currentStep['label'] }} <span></span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @else
                                <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-[2rem] p-6 md:p-12 text-center">
                                    <div class="text-6xl mb-4"></div>
                                    <h3 class="text-xl font-black text-emerald-400 uppercase tracking-widest mb-2">Semua Tahap Selesai!</h3>
                                    <p class="text-slate-400 font-medium text-sm">Anda bisa mengecek kembali data atau langsung klik tombol "Simpan Semua ke QA/QE" di sidebar.</p>
                                </div>
                            @endif
                        </div>

                    </div>
                @endif

            </div>
        </div>
    </div>
    {{-- MODAL DETAIL ORDER --}}
    @if($order)
    <div x-show="showMarketing" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-950/90 backdrop-blur-sm"
         @click.away="showMarketing = false"
         style="display: none;">
        
        <div class="mkt-surface w-full max-w-4xl rounded-2xl md:rounded-[3rem] border mkt-border shadow-2xl relative overflow-hidden italic flex flex-col max-h-[90vh]">
            {{-- Header Modal --}}
            <div class="px-10 pt-10 pb-6 border-b mkt-border sticky top-0 z-10 flex items-center justify-between mkt-surface-alt">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white text-xl shadow-lg shadow-indigo-500/20"></div>
                    <div>
                        <h3 class="text-lg font-black uppercase mkt-text leading-none">Order Tracking Detail</h3>
                        <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-widest mt-1">Artikel #{{ $order->art_no }} • Full Technical Specification</p>
                    </div>
                </div>
                <button @click="showMarketing = false" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:bg-indigo-600 hover:text-white transition-all text-xl border mkt-border">✕</button>
            </div>
            
            {{-- Tab Navigation --}}
            <div class="px-10 py-4 border-b mkt-border flex items-center gap-3 overflow-x-auto no-scrollbar mkt-surface-alt">
                <button wire:click="$set('activeDetailTab', 'marketing')" 
                    class="flex-none px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all {{ $activeDetailTab === 'marketing' ? 'bg-indigo-600 text-white shadow-xl scale-105' : 'mkt-surface mkt-text hover:bg-slate-100 border mkt-border' }}">
                    Marketing Req.
                </button>
                @foreach($productionHistory as $index => $history)
                    <button wire:click="$set('activeDetailTab', 'step_{{ $index }}')" 
                        class="flex-none px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all {{ $activeDetailTab === 'step_'.$index ? 'bg-indigo-600 text-white shadow-xl scale-105' : 'mkt-surface mkt-text hover:bg-slate-100 border mkt-border' }}">
                        {{ $history['division_name'] }}
                    </button>
                @endforeach
            </div>

            {{-- Content Area --}}
            <div class="overflow-y-auto flex-1 p-6 md:p-10 custom-scrollbar bg-transparent">
                {{-- TAB: MARKETING --}}
                @if($activeDetailTab === 'marketing')
                    <div class="space-y-10 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        {{-- HEADER BADGE --}}
                        <div class="flex items-center gap-4 border-b mkt-border pb-6">
                            <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-sm shadow-lg shadow-indigo-600/20">MO</div>
                            <div>
                                <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-0.5">MARKETING SPECIFICATIONS</p>
                                <h3 class="text-xl font-black mkt-text uppercase tracking-tighter italic">MARKETING RESULT</h3>
                            </div>
                        </div>

                        {{-- I. IDENTITAS ORDER --}}
                        <div class="space-y-4">
                            <p class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.3em] border-l-4 border-indigo-600 pl-3">I. IDENTITAS ORDER</p>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6 mkt-surface-alt p-4 sm:p-6 rounded-2xl sm:rounded-3xl border mkt-border">
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">NOMOR ARTIKEL</p>
                                    <p class="text-[13px] font-black text-indigo-600 uppercase">{{ $order->art_no }}</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">LEGACY ID</p>
                                    <p class="text-[11px] font-black mkt-text opacity-60">#{{ $order->sap_no }}</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TANGGAL ORDER</p>
                                    <p class="text-[11px] font-black mkt-text">{{ $order->created_at->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">PELANGGAN</p>
                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $order->pelanggan }}</p>
                                </div>
                            </div>
                        </div>
 
                        {{-- II. KLASIFIKASI & MATERIAL --}}
                        <div class="space-y-4">
                            <p class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.3em] border-l-4 border-indigo-600 pl-3">II. KLASIFIKASI & MATERIAL</p>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6 mkt-surface-alt border mkt-border p-4 sm:p-6 rounded-2xl sm:rounded-3xl">
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">MKT (SALES)</p>
                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $order->marketing_name ?? 'ADMIN' }}</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">KEPERLUAN</p>
                                    <p class="text-[11px] font-black mkt-text uppercase italic">{{ $order->keperluan ?? 'NEW ORDER' }}</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">MATERIAL</p>
                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $order->material }}</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">BENANG</p>
                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $order->benang ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
 
                        {{-- III. SPESIFIKASI TEKNIS --}}
                        <div class="space-y-4">
                            <p class="text-[9px] font-black text-indigo-500 uppercase tracking-[0.3em] border-l-4 border-indigo-500 pl-3">III. SPESIFIKASI TEKNIS</p>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6 mkt-surface-alt border mkt-border p-4 sm:p-6 rounded-2xl sm:rounded-3xl">
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TARGET GRAMASI</p>
                                    <p class="text-[11px] font-black text-indigo-400">{{ $order->target_gramasi }} GSM</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HANDFEEL</p>
                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $order->handfeel ?? '-' }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TREATMENT KHUSUS</p>
                                    <p class="text-[11px] font-black text-indigo-600 uppercase italic">{{ $order->treatment_khusus ?? '-' }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">KONSTRUKSI GREIGE</p>
                                    <p class="text-[11px] font-black mkt-text italic uppercase leading-tight">{{ $order->konstruksi_greige }}</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">KELOMPOK KAIN</p>
                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $order->kelompok_kain }}</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TARGET LEBAR</p>
                                    <p class="text-[11px] font-black mkt-text">{{ $order->target_lebar }}"</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">BELAH/BULAT</p>
                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $order->belah_bulat }}</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">WARNA KAIN</p>
                                    <p class="text-[11px] font-black text-emerald-400 uppercase">{{ $order->warna }}</p>
                                </div>
                            </div>
                        </div>
 
                        {{-- IV. QUANTITY & KETERANGAN --}}
                        <div class="space-y-4">
                            <p class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.3em] border-l-4 border-indigo-600 pl-3">IV. QUANTITY & KETERANGAN</p>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6 mkt-surface-alt border mkt-border p-4 sm:p-6 rounded-2xl sm:rounded-3xl">
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TARGET KG</p>
                                    <p class="text-lg font-black text-indigo-600 italic">{{ (float)$order->kg_target }} KG</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TARGET ROLL</p>
                                    <p class="text-lg font-black text-indigo-600 italic">{{ $order->roll_target }} ROLL</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">KETERANGAN ARTIKEL</p>
                                    <p class="text-[11px] font-bold mkt-text-muted leading-relaxed">{{ $order->keterangan_artikel ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
 
                        {{-- V. R&D RECOMMENDATION --}}
                        @if($order->rnd_mesin_rajut)
                            <div class="space-y-4">
                                <p class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.3em] border-l-4 border-indigo-600 pl-3">V. R&D RECOMMENDATION</p>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6 bg-indigo-600/5 border border-indigo-600/10 p-4 sm:p-6 rounded-2xl sm:rounded-3xl">
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">REKOMENDASI MESIN</p>
                                        <p class="text-[11px] font-black mkt-text uppercase">{{ $order->rnd_mesin_rajut }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">JENIS MESIN</p>
                                        <p class="text-[11px] font-black mkt-text uppercase">{{ $order->rnd_jenis_mesin_rajut ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">GSM GREIGE</p>
                                        <p class="text-[11px] font-black text-indigo-600 italic">{{ $order->rnd_gramasi_greige ?? '-' }} GSM</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
 
                {{-- TAB: PRODUCTION STEPS --}}
                @foreach($productionHistory as $index => $history)
                    @if($activeDetailTab === 'step_'.$index)
                        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                            {{-- NEW HIGH-FIDELITY HEADER CARD --}}
                            @php
                                $operatorActual = !empty($history['technical_data']['nama_input']) ? $history['technical_data']['nama_input'] : ($history['operator']['name'] ?? 'UNKNOWN');
                            @endphp
                            <div class="flex items-center justify-between p-6 mkt-surface-alt border mkt-border rounded-3xl group hover:border-emerald-500/50 transition-all duration-500">
                                <div class="flex items-center gap-5">
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-600/10 flex items-center justify-center border border-indigo-600/20">
                                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1 italic">ACTUAL OPERATOR</p>
                                        <p class="text-xl font-black mkt-text italic tracking-tighter">{{ strtoupper($operatorActual) }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">MACHINE UNIT</p>
                                    <p class="text-3xl font-black text-indigo-600 italic leading-none">{{ $history['machine_no'] ?? 'M-01' }}</p>
                                </div>
                            </div>
 
                            @if($history['division_name'] === 'knitting')
                                {{-- REFINED SLEEK LAYOUT FOR KNITTING (UNIFIED WITH ADMIN STYLE) --}}
                                <div class="space-y-10 animate-in fade-in duration-700">
                                    
                                    {{-- I. IDENTITAS & SPESIFIKASI MESIN --}}
                                    <div class="space-y-4">
                                        <p class="text-[9px] font-black text-indigo-500 uppercase tracking-[0.3em] border-l-4 border-indigo-500 pl-3">I. IDENTITAS & SPESIFIKASI MESIN</p>
                                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 sm:gap-6 mkt-surface-alt border mkt-border p-4 sm:p-6 rounded-2xl">
                                            <div class="col-span-1 sm:col-span-2 sm:border-r mkt-border sm:pr-6">
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                         <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">NO ARTIKEL</p>
                                                         <p class="text-[11px] font-black text-indigo-400 italic">{{ $order->art_no }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TGL PRODUKSI</p>
                                                        <p class="text-[11px] font-black mkt-text">{{ !empty($history['technical_data']['tgl_input']) ? date('d/m/Y', strtotime($history['technical_data']['tgl_input'])) : (isset($history['created_at']) ? date('d/m/Y', strtotime($history['created_at'])) : '-') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">NO MESIN / TYPE</p>
                                                <p class="text-[11px] font-black mkt-text uppercase italic">{{ $history['technical_data']['no_mesin'] ?? '-' }} / {{ $history['technical_data']['type_mesin'] ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">GAUGE / INCH</p>
                                                <p class="text-[11px] font-black mkt-text uppercase">{{ $history['technical_data']['gauge_inch'] ?? '-' }}</p>
                                            </div>
                                            <div class="col-span-1 sm:col-span-2"></div>
                                            <div>
                                                <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">JML FEEDER</p>
                                                <p class="text-[11px] font-black text-white uppercase text-indigo-400">{{ $history['technical_data']['jml_feeder'] ?? '0' }} <span class="text-[8px] mkt-text-muted">FDR</span></p>
                                            </div>
                                            <div>
                                                <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">JML JARUM</p>
                                                <p class="text-[11px] font-black text-white uppercase text-indigo-400">{{ $history['technical_data']['jml_jarum'] ?? '0' }} <span class="text-[8px] mkt-text-muted">JRM</span></p>
                                            </div>
                                        </div>
                                    </div>
 
                                    {{-- II. HASIL PRODUKSI GREIGE --}}
                                    <div class="space-y-4">
                                         <p class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.3em] border-l-4 border-indigo-600 pl-3">II. HASIL PRODUKSI GREIGE</p>
                                         <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 sm:gap-6 mkt-surface-alt border mkt-border p-4 sm:p-6 rounded-2xl">
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">LEBAR / GRAMASI</p>
                                                 <p class="text-[11px] font-black mkt-text uppercase italic">{{ $history['technical_data']['lebar'] ?? '-' }} x {{ $history['technical_data']['gramasi'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TOTAL OUTPUT</p>
                                                 <p class="text-[11px] font-black text-indigo-600 uppercase">{{ $history['roll'] ?? '0' }} ROLL</p>
                                             </div>
                                             <div class="col-span-1 sm:col-span-2 mkt-surface-alt p-4 rounded-xl border border-indigo-600/10">
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1 italic">ACTUAL WEIGHT (KG)</p>
                                                 <p class="text-2xl font-black text-indigo-600 italic">{{ (float)$history['kg'] }} <span class="text-[10px] mkt-text-muted">KG</span></p>
                                             </div>
                                        </div>
                                    </div>

                                    {{-- III. PENGGUNAAN BENANG & YL --}}
                                     <div class="space-y-4">
                                         <p class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.3em] border-l-4 border-indigo-600 pl-3">III. PENGGUNAAN BENANG & YL</p>
                                         <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                             @foreach(range(1, 4) as $i)
                                                 @if(!empty($history['technical_data']['benang_'.$i]))
                                                     <div class="space-y-2 border-l border-indigo-600/20 pl-4 group/item hover:bg-slate-100 dark:hover:bg-slate-700/50 border mkt-border p-2 rounded-lg transition-all">
                                                         <p class="text-[7px] mkt-text-muted font-black uppercase mb-0.5">SLOT {{ $i }}</p>
                                                         <p class="text-[10px] font-black mkt-text uppercase leading-tight truncate">
                                                             {{ $history['technical_data']['benang_'.$i] }}
                                                         </p>
                                                         @if(!empty($history['technical_data']['benang_'.$i.'_lot']))
                                                             <p class="text-[9px] font-black text-slate-500 uppercase leading-none">LOT: {{ $history['technical_data']['benang_'.$i.'_lot'] }}</p>
                                                         @endif
                                                         @if(!empty($history['technical_data']['benang_'.$i.'_percent']))
                                                             <p class="text-[11px] font-black text-indigo-600 tracking-tighter">{{ $history['technical_data']['benang_'.$i.'_percent'] }}</p>
                                                         @endif
                                                         <div class="pt-2 border-t border-white/5">
                                                             <p class="text-[7px] mkt-text-muted font-bold uppercase">YL</p>
                                                             <p class="text-[11px] font-bold text-indigo-400 tracking-tighter">{{ $history['technical_data']['yl_'.$i] ?? '-' }}</p>
                                                         </div>
                                                     </div>
                                                 @endif
                                             @endforeach
                                         </div>
                                     </div>

                                     {{-- IV. NOTE & TARGET --}}
                                     <div class="space-y-4">
                                         <p class="text-[9px] font-black mkt-text-muted uppercase tracking-[0.3em] border-l-4 border-slate-500 pl-3">IV. NOTE & TARGET</p>
                                         <div class="grid grid-cols-3 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                             <div class="col-span-2">
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OPERATOR NOTES / KETERANGAN</p>
                                                 <div class="mkt-surface p-4 rounded-xl border border-white/5">
                                                     <p class="text-[10px] font-bold mkt-text-muted italic leading-relaxed">"{{ $history['technical_data']['note'] ?? 'Tidak ada catatan tambahan dari operator.' }}"</p>
                                                 </div>
                                             </div>
                                             <div class="text-right flex flex-col justify-center">
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TARGET PRODUKSI / DAY</p>
                                                 <p class="text-2xl font-black mkt-text italic tracking-tighter">{{ $history['technical_data']['produksi_per_day'] ?? '0' }} <span class="text-sm mkt-text-muted">KG</span></p>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             @elseif($history['division_name'] === 'dyeing')
                                 @php
                                     $techData = is_array($history['technical_data']) ? $history['technical_data'] : json_decode($history['technical_data'], true);
                                 @endphp
                                 <div class="space-y-10 animate-in fade-in duration-700">
                                     {{-- I. CEK GREIGE --}}
                                     <div class="space-y-4">
                                         <p class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.3em] border-l-4 border-indigo-600 pl-3">I. CEK GREIGE</p>
                                         <div class="grid grid-cols-3 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">CEK GREIGE</p>
                                                 <p class="text-[11px] font-black text-indigo-600 uppercase italic">{{ $techData['cek_greige'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">LEBAR</p>
                                                 <p class="text-[11px] font-black text-indigo-600 italic">{{ $techData['lebar'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">GRAMASI</p>
                                                 <p class="text-[11px] font-black text-indigo-600 italic">{{ $techData['gramasi'] ?? '-' }}</p>
                                             </div>
                                         </div>
                                     </div>

                                     {{-- II. PARAMETER LAINNYA --}}
                                     <div class="space-y-4">
                                         <p class="text-[9px] font-black text-indigo-500 uppercase tracking-[0.3em] border-l-4 border-indigo-500 pl-3">II. PARAMETER LAINNYA</p>
                                         <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OPERATOR</p>
                                                 <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['operator'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TANGGAL</p>
                                                 <p class="text-[11px] font-black mkt-text">{{ !empty($techData['tanggal']) ? date('d/m/Y', strtotime($techData['tanggal'])) : '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">JENIS MESIN</p>
                                                 <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['jenis_mesin'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">NO. MESIN</p>
                                                 <p class="text-[11px] font-black text-indigo-600 uppercase italic">{{ $techData['no_mesin'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">WARNA</p>
                                                 <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['warna'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">KODE WARNA</p>
                                                 <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['kode_warna'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">DYE SYSTEM</p>
                                                 <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['dye_system'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TREATMENT (CHEMICAL)</p>
                                                 <p class="text-[11px] font-black text-indigo-600 uppercase">{{ $techData['treatment'] ?? '-' }}</p>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             @elseif($history['division_name'] === 'relax-dryer')
                                 @php
                                     $techData = is_array($history['technical_data']) ? $history['technical_data'] : json_decode($history['technical_data'], true);
                                 @endphp
                                 <div class="space-y-10 animate-in fade-in duration-700">
                                     {{-- I. IDENTITAS & WAKTU --}}
                                     <div class="space-y-4">
                                         <p class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.3em] border-l-4 border-indigo-600 pl-3">I. IDENTITAS & WAKTU</p>
                                         <div class="grid grid-cols-2 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OPERATOR</p>
                                                 <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['operator'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TANGGAL</p>
                                                 <p class="text-[11px] font-black mkt-text">{{ !empty($techData['tanggal']) ? date('d/m/Y', strtotime($techData['tanggal'])) : '-' }}</p>
                                             </div>
                                         </div>
                                     </div>

                                     {{-- II. PARAMETER TEKNIS & HASIL FISIK --}}
                                     <div class="space-y-4">
                                         <p class="text-[9px] font-black text-indigo-500 uppercase tracking-[0.3em] border-l-4 border-indigo-500 pl-3">II. PARAMETER TEKNIS & HASIL FISIK</p>
                                         <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">CHEMICAL</p>
                                                 <p class="text-[11px] font-black text-indigo-600 uppercase italic">{{ $techData['chemical'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HANDFEEL</p>
                                                 <p class="text-[11px] font-black text-indigo-600 uppercase italic">{{ $techData['handfeel'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">MESIN</p>
                                                 <p class="text-[11px] font-black text-indigo-600 uppercase italic">{{ $techData['no_mesin'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OVERFEED</p>
                                                 <p class="text-[11px] font-black mkt-text">{{ $techData['overfeed'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TEMPERATUR</p>
                                                 <p class="text-[11px] font-black mkt-text">{{ $techData['suhu'] ?? '-' }}°C</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">SPEED</p>
                                                 <p class="text-[11px] font-black mkt-text">{{ $techData['speed'] ?? '-' }} m/min</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HASIL LEBAR</p>
                                                 <p class="text-[11px] font-black text-indigo-600 italic">{{ $techData['lebar'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HASIL GRAMASI</p>
                                                 <p class="text-[11px] font-black text-indigo-600 italic">{{ $techData['gramasi'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">SHRINKAGE (V X H)</p>
                                                 <p class="text-[11px] font-black text-indigo-600 italic">{{ $techData['shrinkage'] ?? '-' }}</p>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             @elseif($history['division_name'] === 'compactor')
                                 @php
                                     $techData = is_array($history['technical_data']) ? $history['technical_data'] : json_decode($history['technical_data'], true);
                                 @endphp
                                 <div class="space-y-10 animate-in fade-in duration-700">
                                     {{-- I. IDENTITAS & WAKTU --}}
                                     <div class="space-y-4">
                                         <p class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.3em] border-l-4 border-indigo-600 pl-3">I. IDENTITAS & WAKTU</p>
                                         <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OPERATOR</p>
                                                 <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['operator'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TANGGAL</p>
                                                 <p class="text-[11px] font-black mkt-text">{{ !empty($techData['tanggal']) ? date('d/m/Y', strtotime($techData['tanggal'])) : '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">NO MESIN</p>
                                                 <p class="text-[11px] font-black text-indigo-600 uppercase italic">{{ $techData['no_mesin'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">RANGKA</p>
                                                 <p class="text-[11px] font-black text-indigo-600 uppercase italic">{{ $techData['rangka'] ?? '-' }}</p>
                                             </div>
                                         </div>
                                     </div>

                                     {{-- II. PARAMETER MESIN --}}
                                     <div class="space-y-4">
                                         <p class="text-[9px] font-black text-indigo-500 uppercase tracking-[0.3em] border-l-4 border-indigo-500 pl-3">II. PARAMETER MESIN & DRIVE SETTING</p>
                                         <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TEMPERATURE</p>
                                                 <p class="text-[11px] font-black text-indigo-600">{{ $techData['suhu'] ?? '-' }}°C</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">SPEED</p>
                                                 <p class="text-[11px] font-black text-indigo-600">{{ $techData['speed'] ?? '-' }} m/min</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OVERFEED</p>
                                                 <p class="text-[11px] font-black text-indigo-600">{{ $techData['overfeed'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">FELT</p>
                                                 <p class="text-[11px] font-black mkt-text">{{ $techData['felt'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">DELIVERY SPEED</p>
                                                 <p class="text-[11px] font-black mkt-text">{{ $techData['delivery_speed'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">FOLDING SPEED</p>
                                                 <p class="text-[11px] font-black mkt-text">{{ $techData['folding_speed'] ?? '-' }}</p>
                                             </div>
                                         </div>
                                     </div>

                                     {{-- III. HASIL FISIK & OUTCOME --}}
                                     <div class="space-y-4">
                                         <p class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.3em] border-l-4 border-indigo-600 pl-3">III. HASIL FISIK & OUTCOME</p>
                                         <div class="grid grid-cols-3 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HASIL LEBAR</p>
                                                 <p class="text-[11px] font-black text-indigo-600 italic">{{ $techData['lebar'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HASIL GRAMASI</p>
                                                 <p class="text-[11px] font-black text-indigo-600 italic">{{ $techData['gramasi'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">SHRINKAGE (V X H)</p>
                                                 <p class="text-[11px] font-black text-indigo-600 italic">{{ $techData['shrinkage'] ?? '-' }}</p>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             @elseif($history['division_name'] === 'heat-setting')
                                 @php
                                     $techData = is_array($history['technical_data']) ? $history['technical_data'] : json_decode($history['technical_data'], true);
                                 @endphp
                                 <div class="space-y-10 animate-in fade-in duration-700">
                                     {{-- I. IDENTITAS & WAKTU --}}
                                     <div class="space-y-4">
                                         <p class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.3em] border-l-4 border-indigo-600 pl-3">I. IDENTITAS & WAKTU</p>
                                         <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OPERATOR</p>
                                                 <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['operator'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TANGGAL</p>
                                                 <p class="text-[11px] font-black mkt-text">{{ !empty($techData['tanggal']) ? date('d/m/Y', strtotime($techData['tanggal'])) : '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">NO MESIN</p>
                                                 <p class="text-[11px] font-black text-indigo-600 uppercase italic">{{ $techData['no_mesin'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">RANGKA</p>
                                                 <p class="text-[11px] font-black text-indigo-600 uppercase italic">{{ $techData['rangka'] ?? '-' }}</p>
                                             </div>
                                         </div>
                                     </div>

                                     {{-- II. PARAMETER MESIN --}}
                                     <div class="space-y-4">
                                         <p class="text-[9px] font-black text-indigo-500 uppercase tracking-[0.3em] border-l-4 border-indigo-500 pl-3">II. PARAMETER MESIN & DRIVE SETTING</p>
                                         <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TEMPERATUR</p>
                                                 <p class="text-[11px] font-black text-indigo-600">{{ $techData['suhu'] ?? '-' }}°C</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">SPEED</p>
                                                 <p class="text-[11px] font-black text-indigo-600">{{ $techData['speed'] ?? '-' }} m/min</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OVERFEED</p>
                                                 <p class="text-[11px] font-black text-indigo-600">{{ $techData['overfeed'] ?? '-' }}</p>
                                             </div>
                                             <div class="col-span-2 md:col-span-3"></div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">DELIVERY SPEED</p>
                                                 <p class="text-[11px] font-black mkt-text">{{ $techData['delivery_speed'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">FOLDING SPEED</p>
                                                 <p class="text-[11px] font-black mkt-text">{{ $techData['folding_speed'] ?? '-' }}</p>
                                             </div>
                                         </div>
                                     </div>

                                     {{-- III. HASIL FISIK & OUTCOME --}}
                                     <div class="space-y-4">
                                         <p class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.3em] border-l-4 border-indigo-600 pl-3">III. HASIL FISIK & OUTCOME</p>
                                         <div class="grid grid-cols-2 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HASIL LEBAR</p>
                                                 <p class="text-[11px] font-black text-indigo-600 italic">{{ $techData['lebar'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HASIL GRAMASI</p>
                                                 <p class="text-[11px] font-black text-indigo-600 italic">{{ $techData['gramasi'] ?? '-' }}</p>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
@elseif($history['division_name'] === 'stenter')
                                  @php
                                      $techData = is_array($history['technical_data']) ? $history['technical_data'] : json_decode($history['technical_data'], true);
                                      $preset = $techData['preset'] ?? [];
                                      $drying = $techData['drying'] ?? [];
                                      $finishing = $techData['finishing'] ?? [];
                                  @endphp
                                  <div x-data="{ subPhase: 'preset' }" class="space-y-10 animate-in fade-in duration-700">
                                      {{-- I. GLOBAL IDENTITAS & WAKTU --}}
                                      <div class="space-y-4">
                                          <p class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.3em] border-l-4 border-indigo-600 pl-3">I. IDENTITAS GLOBAL</p>
                                          <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                              <div>
                                                  <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OPERATOR</p>
                                                  <p class="text-[11px] font-black mkt-text uppercase">{{ $techData['operator'] ?? $history['operator'] ?? '-' }}</p>
                                              </div>
                                              <div>
                                                  <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">NO MESIN</p>
                                                  <p class="text-[11px] font-black text-indigo-600 uppercase italic">{{ $techData['no_mesin'] ?? $history['machine_no'] ?? '-' }}</p>
                                              </div>
                                              <div>
                                                  <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TANGGAL SUBMIT</p>
                                                  <p class="text-[11px] font-black mkt-text">{{ !empty($history['created_at']) ? date('d/m/Y H:i', strtotime($history['created_at'])) : '-' }}</p>
                                              </div>
                                          </div>
                                      </div>

                                      {{-- II. SUB-PHASE WIZARD TABS --}}
                                      <div class="space-y-6">
                                          <div class="flex gap-2 p-1 mkt-surface rounded-2xl w-fit border mkt-border">
                                              <button type="button" @click="subPhase = 'preset'"
                                                  :class="subPhase === 'preset' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:text-white'"
                                                  class="px-5 py-3 rounded-xl text-[10px] font-black uppercase transition-all">PRESET PHASE</button>
                                              <button type="button" @click="subPhase = 'drying'"
                                                  :class="subPhase === 'drying' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:text-white'"
                                                  class="px-5 py-3 rounded-xl text-[10px] font-black uppercase transition-all">DRYING PHASE</button>
                                              <button type="button" @click="subPhase = 'finishing'"
                                                  :class="subPhase === 'finishing' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:text-white'"
                                                  class="px-5 py-3 rounded-xl text-[10px] font-black uppercase transition-all">FINISHING PHASE</button>
                                          </div>

                                          <!-- Preset Details -->
                                          <div x-show="subPhase === 'preset'" class="space-y-8 animate-in fade-in duration-300">
                                              <!-- Group I. IDENTITAS & WAKTU -->
                                              <div class="space-y-4">
                                                  <p class="text-[8px] font-black text-indigo-500 uppercase tracking-widest border-l-4 border-indigo-500 pl-3">I. IDENTITAS & WAKTU (PRESET)</p>
                                                  <div class="grid grid-cols-2 gap-6 mkt-surface-alt border mkt-border p-5 rounded-2xl">
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TANGGAL PRESET</p>
                                                          <p class="text-[10px] font-black mkt-text">{{ !empty($preset['tanggal']) ? date('d/m/Y', strtotime($preset['tanggal'])) : '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">RANGKA PRESET</p>
                                                          <p class="text-[10px] font-black mkt-text uppercase">{{ $preset['rangka'] ?? '-' }}</p>
                                                      </div>
                                                  </div>
                                              </div>

                                              <!-- Group II. PARAMETER MESIN -->
                                              <div class="space-y-4">
                                                  <p class="text-[8px] font-black text-indigo-500 uppercase tracking-widest border-l-4 border-indigo-500 pl-3">II. PARAMETER MESIN (PRESET)</p>
                                                  <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-5 rounded-2xl">
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TEMPERATURE</p>
                                                          <p class="text-[10px] font-black text-indigo-600">{{ $preset['suhu'] ?? '-' }} °C</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">SPEED</p>
                                                          <p class="text-[10px] font-black text-indigo-600">{{ $preset['speed'] ?? '-' }} m/min</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">PADDER</p>
                                                          <p class="text-[10px] font-black mkt-text">{{ $preset['padder'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">FAN / BLOWER</p>
                                                          <p class="text-[10px] font-black mkt-text">{{ $preset['fan'] ?? '-' }}</p>
                                                      </div>
                                                  </div>
                                              </div>

                                              <!-- Group III. DRIVE & OVERFEED SETTING -->
                                              <div class="space-y-4">
                                                  <p class="text-[8px] font-black text-indigo-500 uppercase tracking-widest border-l-4 border-indigo-500 pl-3">III. DRIVE & OVERFEED SETTING (PRESET)</p>
                                                  <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-5 rounded-2xl">
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OVERFEED A</p>
                                                          <p class="text-[10px] font-black text-indigo-600">{{ $preset['overfeed_a'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OVERFEED B</p>
                                                          <p class="text-[10px] font-black text-indigo-600">{{ $preset['overfeed_b'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">DELIVERY SPEED</p>
                                                          <p class="text-[10px] font-black mkt-text">{{ $preset['delivery'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">FOLDING SPEED</p>
                                                          <p class="text-[10px] font-black mkt-text">{{ $preset['folding'] ?? '-' }}</p>
                                                      </div>
                                                  </div>
                                              </div>

                                              <!-- Group IV. CHEMICALS -->
                                              <div class="space-y-4">
                                                  <p class="text-[8px] font-black text-indigo-500 uppercase tracking-widest border-l-4 border-indigo-500 pl-3">IV. CHEMICALS (PRESET)</p>
                                                  <div class="grid grid-cols-2 gap-6 mkt-surface-alt border mkt-border p-5 rounded-2xl">
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">CHEMICAL 1</p>
                                                          <p class="text-[10px] font-black mkt-text uppercase">{{ $preset['chem1'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">CHEMICAL 2</p>
                                                          <p class="text-[10px] font-black mkt-text uppercase">{{ $preset['chem2'] ?? '-' }}</p>
                                                      </div>
                                                  </div>
                                              </div>

                                              <!-- Group V. HASIL FISIK & OUTCOME -->
                                              <div class="space-y-4">
                                                  <p class="text-[8px] font-black text-indigo-600 uppercase tracking-widest border-l-4 border-indigo-600 pl-3">V. HASIL FISIK & OUTCOME (PRESET)</p>
                                                  <div class="grid grid-cols-3 gap-6 mkt-surface-alt border mkt-border p-5 rounded-2xl">
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HASIL LEBAR</p>
                                                          <p class="text-[10px] font-black text-indigo-600 italic">{{ $preset['lebar'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HASIL GRAMASI</p>
                                                          <p class="text-[10px] font-black text-indigo-600 italic">{{ $preset['gramasi'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">SHRINKAGE</p>
                                                          <p class="text-[10px] font-black text-indigo-600 italic">{{ $preset['shrinkage'] ?? '-' }}</p>
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>

                                          <!-- Drying Details -->
                                          <div x-show="subPhase === 'drying'" class="space-y-8 animate-in fade-in duration-300">
                                              <!-- Group I. IDENTITAS & WAKTU -->
                                              <div class="space-y-4">
                                                  <p class="text-[8px] font-black text-indigo-500 uppercase tracking-widest border-l-4 border-indigo-500 pl-3">I. IDENTITAS & WAKTU (DRYING)</p>
                                                  <div class="grid grid-cols-2 gap-6 mkt-surface-alt border mkt-border p-5 rounded-2xl">
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TANGGAL DRYING</p>
                                                          <p class="text-[10px] font-black mkt-text">{{ !empty($drying['tanggal']) ? date('d/m/Y', strtotime($drying['tanggal'])) : '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">RANGKA DRYING</p>
                                                          <p class="text-[10px] font-black mkt-text uppercase">{{ $drying['rangka'] ?? '-' }}</p>
                                                      </div>
                                                  </div>
                                              </div>

                                              <!-- Group II. PARAMETER MESIN -->
                                              <div class="space-y-4">
                                                  <p class="text-[8px] font-black text-indigo-500 uppercase tracking-widest border-l-4 border-indigo-500 pl-3">II. PARAMETER MESIN (DRYING)</p>
                                                  <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-5 rounded-2xl">
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TEMPERATURE</p>
                                                          <p class="text-[10px] font-black text-indigo-600">{{ $drying['suhu'] ?? '-' }} °C</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">SPEED</p>
                                                          <p class="text-[10px] font-black text-indigo-600">{{ $drying['speed'] ?? '-' }} m/min</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">PADDER</p>
                                                          <p class="text-[10px] font-black mkt-text">{{ $drying['padder'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">FAN / BLOWER</p>
                                                          <p class="text-[10px] font-black mkt-text">{{ $drying['fan'] ?? '-' }}</p>
                                                      </div>
                                                  </div>
                                              </div>

                                              <!-- Group III. DRIVE & OVERFEED SETTING -->
                                              <div class="space-y-4">
                                                  <p class="text-[8px] font-black text-indigo-500 uppercase tracking-widest border-l-4 border-indigo-500 pl-3">III. DRIVE & OVERFEED SETTING (DRYING)</p>
                                                  <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-5 rounded-2xl">
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OVERFEED A</p>
                                                          <p class="text-[10px] font-black text-indigo-600">{{ $drying['overfeed_a'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OVERFEED B</p>
                                                          <p class="text-[10px] font-black text-indigo-600">{{ $drying['overfeed_b'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">DELIVERY SPEED</p>
                                                          <p class="text-[10px] font-black mkt-text">{{ $drying['delivery'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">FOLDING SPEED</p>
                                                          <p class="text-[10px] font-black mkt-text">{{ $drying['folding'] ?? '-' }}</p>
                                                      </div>
                                                  </div>
                                              </div>

                                              <!-- Group IV. CHEMICALS -->
                                              <div class="space-y-4">
                                                  <p class="text-[8px] font-black text-indigo-500 uppercase tracking-widest border-l-4 border-indigo-500 pl-3">IV. CHEMICALS (DRYING)</p>
                                                  <div class="grid grid-cols-2 gap-6 mkt-surface-alt border mkt-border p-5 rounded-2xl">
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">CHEMICAL 1</p>
                                                          <p class="text-[10px] font-black mkt-text uppercase">{{ $drying['chem1'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">CHEMICAL 2</p>
                                                          <p class="text-[10px] font-black mkt-text uppercase">{{ $drying['chem2'] ?? '-' }}</p>
                                                      </div>
                                                  </div>
                                              </div>

                                              <!-- Group V. HASIL FISIK & OUTCOME -->
                                              <div class="space-y-4">
                                                  <p class="text-[8px] font-black text-indigo-600 uppercase tracking-widest border-l-4 border-indigo-600 pl-3">V. HASIL FISIK & OUTCOME (DRYING)</p>
                                                  <div class="grid grid-cols-3 gap-6 mkt-surface-alt border mkt-border p-5 rounded-2xl">
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HASIL LEBAR</p>
                                                          <p class="text-[10px] font-black text-indigo-600 italic">{{ $drying['lebar'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HASIL GRAMASI</p>
                                                          <p class="text-[10px] font-black text-indigo-600 italic">{{ $drying['gramasi'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">SHRINKAGE</p>
                                                          <p class="text-[10px] font-black text-indigo-600 italic">{{ $drying['shrinkage'] ?? '-' }}</p>
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>

                                          <!-- Finishing Details -->
                                          <div x-show="subPhase === 'finishing'" class="space-y-8 animate-in fade-in duration-300">
                                              <!-- Group I. IDENTITAS & WAKTU -->
                                              <div class="space-y-4">
                                                  <p class="text-[8px] font-black text-indigo-500 uppercase tracking-widest border-l-4 border-indigo-500 pl-3">I. IDENTITAS & WAKTU (FINISHING)</p>
                                                  <div class="grid grid-cols-2 gap-6 mkt-surface-alt border mkt-border p-5 rounded-2xl">
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TANGGAL FINISHING</p>
                                                          <p class="text-[10px] font-black mkt-text">{{ !empty($finishing['tanggal']) ? date('d/m/Y', strtotime($finishing['tanggal'])) : '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">RANGKA FINISHING</p>
                                                          <p class="text-[10px] font-black mkt-text uppercase">{{ $finishing['rangka'] ?? '-' }}</p>
                                                      </div>
                                                  </div>
                                              </div>

                                              <!-- Group II. PARAMETER MESIN -->
                                              <div class="space-y-4">
                                                  <p class="text-[8px] font-black text-indigo-500 uppercase tracking-widest border-l-4 border-indigo-500 pl-3">II. PARAMETER MESIN (FINISHING)</p>
                                                  <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-5 rounded-2xl">
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TEMPERATURE</p>
                                                          <p class="text-[10px] font-black text-indigo-600">{{ $finishing['suhu'] ?? '-' }} °C</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">SPEED</p>
                                                          <p class="text-[10px] font-black text-indigo-600">{{ $finishing['speed'] ?? '-' }} m/min</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">PADDER</p>
                                                          <p class="text-[10px] font-black mkt-text">{{ $finishing['padder'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">FAN / BLOWER</p>
                                                          <p class="text-[10px] font-black mkt-text">{{ $finishing['fan'] ?? '-' }}</p>
                                                      </div>
                                                  </div>
                                              </div>

                                              <!-- Group III. DRIVE & OVERFEED SETTING -->
                                              <div class="space-y-4">
                                                  <p class="text-[8px] font-black text-indigo-500 uppercase tracking-widest border-l-4 border-indigo-500 pl-3">III. DRIVE & OVERFEED SETTING (FINISHING)</p>
                                                  <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-5 rounded-2xl">
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OVERFEED A</p>
                                                          <p class="text-[10px] font-black text-indigo-600">{{ $finishing['overfeed_a'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OVERFEED B</p>
                                                          <p class="text-[10px] font-black text-indigo-600">{{ $finishing['overfeed_b'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">DELIVERY SPEED</p>
                                                          <p class="text-[10px] font-black mkt-text">{{ $finishing['delivery'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">FOLDING SPEED</p>
                                                          <p class="text-[10px] font-black mkt-text">{{ $finishing['folding'] ?? '-' }}</p>
                                                      </div>
                                                  </div>
                                              </div>

                                              <!-- Group IV. CHEMICALS -->
                                              <div class="space-y-4">
                                                  <p class="text-[8px] font-black text-indigo-500 uppercase tracking-widest border-l-4 border-indigo-500 pl-3">IV. CHEMICALS (FINISHING)</p>
                                                  <div class="grid grid-cols-2 gap-6 mkt-surface-alt border mkt-border p-5 rounded-2xl">
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">CHEMICAL 1</p>
                                                          <p class="text-[10px] font-black mkt-text uppercase">{{ $finishing['chem1'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">CHEMICAL 2</p>
                                                          <p class="text-[10px] font-black mkt-text uppercase">{{ $finishing['chem2'] ?? '-' }}</p>
                                                      </div>
                                                  </div>
                                              </div>

                                              <!-- Group V. HASIL FISIK & OUTCOME -->
                                              <div class="space-y-4">
                                                  <p class="text-[8px] font-black text-indigo-600 uppercase tracking-widest border-l-4 border-indigo-600 pl-3">V. HASIL FISIK & OUTCOME (FINISHING)</p>
                                                  <div class="grid grid-cols-3 gap-6 mkt-surface-alt border mkt-border p-5 rounded-2xl">
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HASIL LEBAR</p>
                                                          <p class="text-[10px] font-black text-indigo-600 italic">{{ $finishing['lebar'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HASIL GRAMASI</p>
                                                          <p class="text-[10px] font-black text-indigo-600 italic">{{ $finishing['gramasi'] ?? '-' }}</p>
                                                      </div>
                                                      <div>
                                                          <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">SHRINKAGE</p>
                                                          <p class="text-[10px] font-black text-indigo-600 italic">{{ $finishing['shrinkage'] ?? '-' }}</p>
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                              @else
                                  {{-- GENERIC LAYOUT IN MARKETING STYLE --}}
                                 <div class="space-y-4">
                                     <p class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.3em] border-l-4 border-indigo-600 pl-3">I. HASIL PRODUKSI ({{ strtoupper($history['division_name']) }})</p>
                                     <div class="grid grid-cols-3 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                         <div>
                                             <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">ACTUAL WEIGHT</p>
                                             <p class="text-[11px] font-black text-indigo-600 italic">{{ (float)$history['kg'] }} KG</p>
                                         </div>
                                         <div>
                                             <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">ACTUAL ROLL</p>
                                             <p class="text-[11px] font-black text-indigo-600 italic">{{ $history['roll'] }} ROLL</p>
                                         </div>
                                         <div>
                                             <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">MACHINE NO</p>
                                             <p class="text-[11px] font-black text-indigo-600 italic">{{ $history['machine_no'] ?? 'M-01' }}</p>
                                         </div>
                                     </div>

                                     <p class="text-[9px] font-black text-indigo-500 uppercase tracking-[0.3em] border-l-4 border-indigo-500 pl-3">II. TECHNICAL DATA</p>
                                     <div class="grid grid-cols-3 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                         @foreach($history['technical_data'] as $key => $value)
                                             @if($value && !in_array($key, ['kg', 'roll', 'machine_no', 'operator', 'nama_input', 'updated_at', 'created_at', 'preset', 'drying', 'finishing', 'raising', 'brushing', 'shearing']))
                                                 <div>
                                                     <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">{{ strtoupper(str_replace('_', ' ', $key)) }}</p>
                                                     <p class="text-[11px] font-black mkt-text italic uppercase leading-none">{{ is_array($value) ? json_encode($value) : $value }}</p>
                                                 </div>
                                             @endif
                                         @endforeach
                                     </div>
                                 </div>
                             @endif
                         </div>
                     @endif
                 @endforeach
             </div>

             {{-- Footer Modal --}}
             <div class="px-10 py-10 mkt-surface border-t mkt-border flex justify-end">
                 <button @click="showMarketing = false" 
                     class="px-14 py-5 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all 
                     bg-indigo-600 
                     text-white 
                     border border-white/10 dark:border-none 
                     shadow-sm dark:shadow-[0_15px_50px_rgba(79,70,229,0.4)] 
                     hover:scale-105 active:scale-95">
                     TUTUP DETAIL
                 </button>
             </div>
         </div>
     </div>
     @endif
</div>