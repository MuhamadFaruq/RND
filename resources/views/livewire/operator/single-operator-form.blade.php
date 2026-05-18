<div x-data="{ showMarketing: false }">
    <div class="py-12 mkt-bg min-h-screen italic mkt-text">
        <div class="max-w-[95%] mx-auto px-4">
            <div class="mkt-surface rounded-[3rem] p-12 shadow-sm border mkt-border">

                {{-- Header --}}
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
                    <div>
                        <h1 class="text-6xl font-[1000] italic leading-none mkt-text tracking-tighter">
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
                                class="bg-indigo-600 px-10 py-5 rounded-2xl text-xs font-black uppercase text-white hover:bg-indigo-500 hover:scale-105 transition-all shadow-[0_20px_50px_rgba(79,70,229,0.3)] flex items-center gap-3">
                                📘 DETAIL ORDER ARTIKEL
                            </button>
                            <button wire:click="$set('order', null)"
                                class="bg-white dark:bg-slate-800 px-8 py-5 rounded-2xl text-[10px] font-black uppercase text-slate-400 hover:text-white transition-all border border-white/10 shadow-sm flex items-center gap-2">
                                ⬅️ KEMBALI KE LOGBOOK
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
                                    class="w-full mkt-surface-alt border-2 border-white/10 text-white rounded-2xl px-6 py-5 text-xl font-black text-center focus:ring-4 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all placeholder:text-slate-500 uppercase border"
                                    placeholder="CONTOH: ART12345">
                                <button wire:click="lookupArtikel"
                                    class="absolute right-3 top-3 bottom-3 bg-indigo-600 hover:bg-indigo-500 text-white px-6 rounded-xl font-bold uppercase text-xs transition-all shadow-lg shadow-indigo-600/20">
                                    <span wire:loading.remove wire:target="lookupArtikel">Cari Artikel</span>
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
                            <div class="mkt-surface-alt backdrop-blur-xl rounded-[2rem] p-6 shadow-sm border mkt-border sticky top-6">
                                <h3
                                    class="mkt-text font-black uppercase tracking-widest text-xs mb-6 border-b mkt-border pb-4 flex items-center justify-between">
                                    <span>Artikel: {{ $order->art_no }}</span>
                                    <button wire:click="$set('order', null)"
                                        class="text-slate-400 hover:text-white transition-colors"
                                        title="Cari order lain">🔄</button>
                                </h3>

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
                                                'saved' => '✅',
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

                                <div class="mt-8 pt-6 border-t mkt-border">
                                    <button wire:click="submitAll" @if(!$canSubmitAll) disabled @endif
                                        class="w-full py-4 rounded-xl font-black uppercase text-[10px] tracking-widest transition-all shadow-xl {{ $canSubmitAll ? 'bg-indigo-600 hover:bg-indigo-500 text-white hover:-translate-y-1' : 'bg-slate-800 text-slate-400 cursor-not-allowed opacity-50' }}">
                                        Simpan Semua ke QA/QE
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Main Form Area --}}
                        <div class="w-full lg:w-2/3 space-y-6">
                            @if($currentStep)
                                <div class="mkt-surface-alt border mkt-border rounded-[2rem] p-8">
                                    <div class="flex items-center gap-4 mb-8 pb-6 border-b mkt-border">
                                        <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-lg shadow-indigo-600/20">01</div>
                                        <div>
                                            <h3 class="text-sm font-black uppercase mkt-text tracking-widest">{{ $currentStep['label'] }}</h3>
                                            <p class="text-[9px] font-bold mkt-text-muted uppercase italic">Input Technical Data Produksi</p>
                                        </div>
                                    </div>

                                    <form wire:submit.prevent="saveCurrentStep" class="space-y-6">
                                        <div class="grid grid-cols-1">
                                            @if($currentStep['key'] === 'stenter')
                                                <div x-data="{ phase: 'preset' }" class="col-span-full">
                                                    <div class="flex gap-2 mb-6 p-1 mkt-surface rounded-2xl w-fit border mkt-border">
                                                        <button type="button" @click="phase = 'preset'"
                                                            :class="phase === 'preset' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:text-white'"
                                                            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase transition-all">Preset</button>
                                                        <button type="button" @click="phase = 'drying'"
                                                            :class="phase === 'drying' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:text-white'"
                                                            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase transition-all">Drying</button>
                                                        <button type="button" @click="phase = 'finishing'"
                                                            :class="phase === 'finishing' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:text-white'"
                                                            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase transition-all">Finishing</button>
                                                    </div>

                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                                        <div>
                                                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">No Mesin</label>
                                                            <input type="text" wire:model="stenter_no_mesin" placeholder="MASUKKAN NO MESIN"
                                                                class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Operator</label>
                                                            <input type="text" wire:model="stenter_operator" placeholder="MASUKKAN Nama Operator"
                                                                class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                        </div>
                                                    </div>

                                                    <div class="mt-8 border-t mkt-border pt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
                                                        <template x-if="true">
                                                            <div class="col-span-full grid grid-cols-1 md:grid-cols-4 gap-6">
                                                                <div class="col-span-full text-[10px] font-black text-indigo-500 uppercase italic mb-2 tracking-widest"
                                                                    x-text="'Phase: ' + phase"></div>
                                                                <div>
                                                                    <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Tanggal</label>
                                                                    <input type="date" :wire:model="'stenter_' + phase + '.tanggal'"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Temperature</label>
                                                                    <input type="number" :wire:model="'stenter_' + phase + '.suhu'" placeholder="MASUKKAN Suhu"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Speed</label>
                                                                    <input type="number" :wire:model="'stenter_' + phase + '.speed'" placeholder="MASUKKAN Speed"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Padder</label>
                                                                    <input type="number" :wire:model="'stenter_' + phase + '.padder'" placeholder="MASUKKAN Padder"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Rangka</label>
                                                                    <input type="number" :wire:model="'stenter_' + phase + '.rangka'" placeholder="MASUKKAN Rangka"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Overfeed A</label>
                                                                    <input type="number" :wire:model="'stenter_' + phase + '.overfeed_a'" placeholder="MASUKKAN Overfeed A"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Overfeed B</label>
                                                                    <input type="number" :wire:model="'stenter_' + phase + '.overfeed_b'" placeholder="MASUKKAN Overfeed B"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Fan / Blower</label>
                                                                    <input type="number" :wire:model="'stenter_' + phase + '.fan'" placeholder="MASUKKAN Fan/Blower"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Delivery Speed</label>
                                                                    <input type="number" :wire:model="'stenter_' + phase + '.delivery'" placeholder="MASUKKAN Delivery Speed"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Folding Speed</label>
                                                                    <input type="number" :wire:model="'stenter_' + phase + '.folding'" placeholder="MASUKKAN Folding Speed"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Chemical 1</label>
                                                                    <input type="text" :wire:model="'stenter_' + phase + '.chem1'" placeholder="MASUKKAN Chemical 1"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Chemical 2</label>
                                                                    <input type="text" :wire:model="'stenter_' + phase + '.chem2'" placeholder="MASUKKAN Chemical 2"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Hasil Lebar</label>
                                                                    <input type="text" :wire:model="'stenter_' + phase + '.lebar'" placeholder="MASUKKAN Hasil Lebar"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Hasil Gramasi</label>
                                                                    <input type="text" :wire:model="'stenter_' + phase + '.gramasi'" placeholder="MASUKKAN Hasil Gramasi"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Shrinkage</label>
                                                                    <input type="text" :wire:model="'stenter_' + phase + '.shrinkage'" placeholder="MASUKKAN Shrinkage"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            @elseif($currentStep['key'] === 'fleece')
                                                <div x-data="{ phase: 'raising' }" class="col-span-full">
                                                    <div class="flex gap-2 mb-6 p-1 mkt-surface rounded-2xl w-fit border mkt-border">
                                                        <button type="button" @click="phase = 'raising'"
                                                            :class="phase === 'raising' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:text-white'"
                                                            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase transition-all">Raising</button>
                                                        <button type="button" @click="phase = 'brushing'"
                                                            :class="phase === 'brushing' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:text-white'"
                                                            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase transition-all">Brushing</button>
                                                        <button type="button" @click="phase = 'shearing'"
                                                            :class="phase === 'shearing' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:text-white'"
                                                            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase transition-all">Shearing</button>
                                                    </div>

                                                    <div>
                                                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">No Mesin</label>
                                                        <input type="text" wire:model="fleece_no_mesin" placeholder="MASUKKAN No Mesin"
                                                            class="w-fit mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase mb-8">
                                                    </div>

                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-4 pt-4 border-t mkt-border">
                                                        <div class="col-span-full text-[10px] font-black text-rose-500 uppercase italic mb-2 tracking-widest"
                                                            x-text="'Process: ' + phase"></div>

                                                        {{-- Raising Fields --}}
                                                        <template x-if="phase === 'raising'">
                                                            <div class="col-span-full grid grid-cols-1 md:grid-cols-4 gap-6">
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Tanggal</label><input
                                                                        type="date" wire:model="fleece_raising.tanggal"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Operator</label><input
                                                                        type="text" wire:model="fleece_raising.operator" placeholder="MASUKKAN Nama Operator"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Std Bulu</label><input type="number"
                                                                        wire:model="fleece_raising.standar_bulu" placeholder="MASUKKAN Std Bulu"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Speed</label><input
                                                                        type="number" wire:model="fleece_raising.speed" placeholder="MASUKKAN Speed"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Cloth Out</label><input type="number"
                                                                        wire:model="fleece_raising.cloth_out" placeholder="MASUKKAN Cloth Out"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Bend Pin</label><input type="number"
                                                                        wire:model="fleece_raising.bend_pin" placeholder="MASUKKAN Bend Pin"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Straight Pin</label><input type="number"
                                                                        wire:model="fleece_raising.stright_pin" placeholder="MASUKKAN Straight Pin"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">RPM Drum</label><input type="number"
                                                                        wire:model="fleece_raising.rpm_drum" placeholder="MASUKKAN RPM Drum"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Lebar/GSM</label><input
                                                                        type="text" wire:model="fleece_raising.lebar_gsm" placeholder="MASUKKAN Lebar/GSM"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Drum Brush</label><input type="text"
                                                                        wire:model="fleece_raising.drum_brush" placeholder="MASUKKAN Drum Brush"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                            </div>
                                                        </template>

                                                        {{-- Brushing Fields --}}
                                                        <template x-if="phase === 'brushing'">
                                                            <div class="col-span-full grid grid-cols-1 md:grid-cols-4 gap-6">
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Tanggal</label><input
                                                                        type="date" wire:model="fleece_brushing.tanggal"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Operator</label><input
                                                                        type="text" wire:model="fleece_brushing.operator" placeholder="MASUKKAN Nama Operator"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Std Bulu</label><input type="number"
                                                                        wire:model="fleece_brushing.standar_bulu" placeholder="MASUKKAN Std Bulu"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Cloth Speed</label><input type="number"
                                                                        wire:model="fleece_brushing.cloth_speed" placeholder="MASUKKAN Cloth Speed"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Cloth Out</label><input type="number"
                                                                        wire:model="fleece_brushing.cloth_out" placeholder="MASUKKAN Cloth Out"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Left Brush</label><input type="number"
                                                                        wire:model="fleece_brushing.left_brush" placeholder="MASUKKAN Left Brush"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Right Brush</label><input type="number"
                                                                        wire:model="fleece_brushing.right_brush" placeholder="MASUKKAN Right Brush"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">RPM Drum</label><input type="number"
                                                                        wire:model="fleece_brushing.rpm_drum" placeholder="MASUKKAN RPM Drum"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Tension 1/2/3</label><input type="number"
                                                                        wire:model="fleece_brushing.tension" placeholder="MASUKKAN Tension"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Lebar/GSM</label><input
                                                                        type="text" wire:model="fleece_brushing.lebar_gramasi" placeholder="MASUKKAN Lebar/GSM"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                            </div>
                                                        </template>

                                                        {{-- Shearing Fields --}}
                                                        <template x-if="phase === 'shearing'">
                                                            <div class="col-span-full grid grid-cols-1 md:grid-cols-4 gap-6">
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Tanggal</label><input
                                                                        type="date" wire:model="fleece_shearing.tanggal"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Operator</label><input
                                                                        type="text" wire:model="fleece_shearing.operator" placeholder="MASUKKAN Nama Operator"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Speed</label><input
                                                                        type="number" wire:model="fleece_shearing.speed" placeholder="MASUKKAN Speed"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Cloth Out</label><input type="number"
                                                                        wire:model="fleece_shearing.cloth_out" placeholder="MASUKKAN Cloth Out"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Expending</label><input
                                                                        type="number" wire:model="fleece_shearing.expending" placeholder="MASUKKAN Expending"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Shear</label><input
                                                                        type="number" wire:model="fleece_shearing.shear" placeholder="MASUKKAN Shear"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                                <div><label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Lebar/GSM</label><input
                                                                        type="text" wire:model="fleece_shearing.lebar_gramasi" placeholder="MASUKKAN Lebar/GSM"
                                                                        class="w-full mkt-surface border mkt-border rounded-2xl px-6 py-4 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
                                                                </div>
                                                            </div>
                                                        </template>
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
                                                        'operator' => '👤', 'no_mesin' => '⚙️', 'tanggal' => '📅',
                                                        'jenis_mesin' => '📠', 'cek_greige' => '🔍', 'suhu' => '🌡️',
                                                        'speed' => '🏎️', 'lebar' => '↔️', 'gramasi' => '⚖️',
                                                        'warna' => '🎨', 'kode_warna' => '🆔', 'dye_system' => '🧪',
                                                        'treatment' => '⚗️', 'chemical' => '💊', 'handfeel' => '✋'
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
                                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-12">
                                                                @foreach($cat['fields'] as $field)
                                                                    @php
                                                                        $parts = explode('_', $field, 2);
                                                                        $key = $parts[1] ?? $field;
                                                                        $fieldLabel = $customLabels[$key] ?? ucwords(str_replace('_', ' ', $key));
                                                                        $isDate = str_contains($field, 'tanggal');
                                                                        $isNumber = in_array($key, ['suhu', 'speed', 'lebar', 'durasi', 'overfeed', 'gramasi', 'shrinkage', 'delivery_speed', 'folding_speed', 'rangka', 'fan', 'hotwind', 'coldwind', 'steam_inject']);
                                                                        $icon = $fieldIcons[$key] ?? '📝';
                                                                        
                                                                        // Primary Style (Red Label) like the image
                                                                        $isPrimary = in_array($key, ['operator', 'no_mesin', 'lebar', 'gramasi', 'warna']);
                                                                    @endphp
                                                                    <div class="space-y-3 group">
                                                                        <label class="block text-[10px] font-black uppercase tracking-widest ml-1 {{ $isPrimary ? 'text-indigo-600 italic' : 'mkt-text-muted' }}">
                                                                            {{ $fieldLabel }}
                                                                        </label>
                                                                        <div class="relative">
                                                                            <span class="absolute left-6 top-1/2 -translate-y-1/2 text-lg opacity-40 group-focus-within:opacity-100 transition-opacity">{{ $icon }}</span>
                                                                            <input type="{{ $isDate ? 'date' : ($isNumber ? 'number' : 'text') }}"
                                                                                wire:model="{{ $field }}"
                                                                                placeholder="TULIS {{ $fieldLabel }}..."
                                                                                class="w-full mkt-surface border mkt-border rounded-2xl pl-16 pr-6 py-5 font-black text-sm mkt-text placeholder-slate-500 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic uppercase">
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
                                                    ⬅️ KEMBALI
                                                </button>
                                            @else
                                                <div></div>
                                            @endif

                                            <button type="submit"
                                                class="px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-indigo-600/30 hover:-translate-y-1 transition-all flex items-center gap-2">
                                                Simpan {{ $currentStep['label'] }} <span>💾</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @else
                                <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-[2rem] p-12 text-center">
                                    <div class="text-6xl mb-4">🎉</div>
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
        
        <div class="mkt-surface w-full max-w-4xl rounded-[3rem] border mkt-border shadow-2xl relative overflow-hidden italic flex flex-col max-h-[90vh]">
            {{-- Header Modal --}}
            <div class="px-10 pt-10 pb-6 border-b mkt-border sticky top-0 z-10 flex items-center justify-between mkt-surface-alt">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white text-xl shadow-lg shadow-indigo-500/20">📋</div>
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
            <div class="overflow-y-auto flex-1 p-10 custom-scrollbar bg-transparent">
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
                            <div class="grid grid-cols-4 gap-6 mkt-surface-alt p-6 rounded-3xl border mkt-border">
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
                            <div class="grid grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-6 rounded-3xl">
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
                            <div class="grid grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-6 rounded-3xl">
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
                            <div class="grid grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-6 rounded-3xl">
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TARGET KG</p>
                                    <p class="text-lg font-black text-indigo-600 italic">{{ number_format($order->kg_target, 1) }} KG</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TARGET ROLL</p>
                                    <p class="text-lg font-black text-indigo-600 italic">{{ $order->roll_target }} RL</p>
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
                                <div class="grid grid-cols-4 gap-6 bg-indigo-600/5 border border-indigo-600/10 p-6 rounded-3xl">
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
                                        <div class="grid grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                            <div class="col-span-2 border-r mkt-border pr-6">
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
                                            <div class="col-span-2"></div>
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
                                         <div class="grid grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">LEBAR / GRAMASI</p>
                                                 <p class="text-[11px] font-black mkt-text uppercase italic">{{ $history['technical_data']['lebar'] ?? '-' }} x {{ $history['technical_data']['gramasi'] ?? '-' }}</p>
                                             </div>
                                             <div>
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TOTAL OUTPUT</p>
                                                 <p class="text-[11px] font-black text-indigo-600 uppercase">{{ $history['roll'] ?? '0' }} ROLL</p>
                                             </div>
                                             <div class="col-span-2 mkt-surface-alt p-4 rounded-xl border border-indigo-600/10">
                                                 <p class="text-[7px] mkt-text-muted font-black uppercase mb-1 italic">ACTUAL WEIGHT (KG)</p>
                                                 <p class="text-2xl font-black text-indigo-600 italic">{{ number_format($history['kg'], 1) }} <span class="text-[10px] mkt-text-muted">KG</span></p>
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
                             @else
                                 {{-- GENERIC LAYOUT IN MARKETING STYLE --}}
                                 <div class="space-y-4">
                                     <p class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.3em] border-l-4 border-indigo-600 pl-3">I. HASIL PRODUKSI ({{ strtoupper($history['division_name']) }})</p>
                                     <div class="grid grid-cols-3 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                         <div>
                                             <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">ACTUAL WEIGHT</p>
                                             <p class="text-[11px] font-black text-indigo-600 italic">{{ number_format($history['kg'], 1) }} KG</p>
                                         </div>
                                         <div>
                                             <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">ACTUAL ROLL</p>
                                             <p class="text-[11px] font-black text-indigo-600 italic">{{ $history['roll'] }} RL</p>
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