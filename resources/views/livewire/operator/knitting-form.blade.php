<div x-data="{ showDetail: false }" class="py-12 mkt-bg min-h-screen font-sans italic tracking-tighter text-left mkt-text">
    <div class="max-w-6xl mx-auto px-4">

        {{-- HEADER --}}
        <div class="grid grid-cols-1 md:grid-cols-3 items-center mb-12 gap-6">
            {{-- Kiri: Judul --}}
            <div class="text-left">
                <h2 class="text-5xl font-black uppercase mkt-text leading-none tracking-tighter">
                    Knitting <span class="text-violet-500 italic">Logbook</span>
                </h2>
                <div class="flex items-center gap-2 mt-3">
                    <span class="h-2 w-2 rounded-full bg-violet-500 animate-pulse"></span>
                    <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-[0.3em]">Formulir Input Produksi Mesin Rajut</p>
                </div>
            </div>

            {{-- Tengah: Tombol Detail --}}
            <div class="flex justify-center">
                @if($order_detail)
                    <button type="button" @click="showDetail = true"
                        class="bg-indigo-600/80 backdrop-blur-md border border-white/10 px-10 py-4 rounded-2xl text-[11px] font-black uppercase text-white hover:bg-indigo-600 hover:scale-105 transition-all shadow-lg flex items-center gap-2">
                        <span>ℹ️</span> DETAIL ORDER ARTIKEL
                    </button>
                @endif
            </div>

            {{-- Kanan: Navigasi --}}
            <div class="flex justify-end">
                <a href="{{ route('operator.logbook') }}"
                    class="group mkt-surface-alt border mkt-border px-8 py-4 rounded-2xl text-[10px] font-black uppercase mkt-text-muted hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all shadow-xl">
                    ← Kembali ke Logbook
                </a>
            </div>
        </div>

        @if (session()->has('message'))
            <div
                class="mb-8 p-6 bg-emerald-600/80 backdrop-blur-md text-white rounded-3xl font-black uppercase text-xs italic shadow-lg animate-pulse">
                🚀 {{ session('message') }}
            </div>
        @endif

        <form wire:submit.prevent="save" class="space-y-8">

            {{-- SECTION 01: SPESIFIKASI MESIN --}}
            <div class="mkt-surface p-10 rounded-[3.5rem] shadow-sm border mkt-border relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-600/10 rounded-bl-full -mr-10 -mt-10 opacity-50">
                </div>

                <div class="flex items-center gap-4 mb-10 relative z-10">
                    <div
                        class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-lg shadow-indigo-600/20">
                        01</div>
                    <div>
                        <h3 class="text-sm font-black uppercase mkt-text tracking-widest">Spesifikasi Mesin</h3>
                        <p class="text-[9px] mkt-text-muted font-bold uppercase italic">Identitas mesin dan nomor marketing order</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-x-12 gap-y-8 relative z-10">
                    {{-- NOMOR ARTIKEL --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-violet-400 ml-2 tracking-widest">Nomor Artikel (Keyword Produk)</label>
                        <div class="relative">
                            <input value="{{ $order_detail['art_no'] ?? '-' }}" type="text" readonly
                                class="w-full mkt-surface-alt border mkt-border rounded-2xl py-5 px-6 font-black text-sm mkt-text-muted cursor-not-allowed italic uppercase">
                            <span class="absolute right-4 top-5 text-indigo-600">🔖</span>
                        </div>
                    </div>

                    {{-- LEGACY ID (Read-only, tidak diinput operator) --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase mkt-text-muted ml-2 tracking-widest">Artikel Order No (Legacy)</label>
                        <div class="relative">
                            <input wire:model.live="sap_no" type="text" readonly
                                class="w-full mkt-surface-alt border mkt-border rounded-2xl py-5 px-6 font-black text-xs mkt-text-muted cursor-not-allowed italic">
                            <span class="absolute right-4 top-5 text-slate-400">🔒</span>
                        </div>
                    </div>

                    {{-- OPERATOR NAME --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase mkt-text-muted ml-2 tracking-widest">Operator Bertugas</label>
                        <div class="relative">
                            <span class="absolute left-6 top-5 text-indigo-600 font-bold">👤</span>
                            <input type="text" wire:model="operator_name" placeholder="TULIS NAMA LENGKAP..."
                                class="w-full pl-14 pr-6 py-5 mkt-surface-alt border mkt-border rounded-2xl text-sm font-black mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none uppercase italic placeholder-slate-400">
                        </div>
                        @error('operator_name') <span
                            class="text-[9px] text-violet-400 font-black italic ml-2 uppercase">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- TANGGAL --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase mkt-text-muted ml-2 tracking-widest">Tanggal Produksi</label>
                        <input wire:model="tanggal" type="date"
                            class="w-full mkt-surface-alt border mkt-border rounded-2xl py-5 px-6 font-black text-sm mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none">
                        @error('tanggal') <span
                            class="text-[9px] text-violet-400 font-black italic ml-2 uppercase">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- DETAIL MESIN (ROW 2) --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase mkt-text-muted ml-2">No Mesin (DD)</label>
                        <input wire:model="no_mesin" type="text" placeholder="CONTOH: K01"
                            class="w-full mkt-surface-alt border mkt-border rounded-2xl py-4 px-6 font-black text-sm mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none placeholder-slate-400">
                        @error('no_mesin') <span
                            class="text-[9px] text-violet-400 font-black italic ml-2 uppercase">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase mkt-text-muted ml-2">Type Mesin (DD)</label>
                        <input wire:model="type_mesin" type="text" placeholder="PAI LUNG / DLL"
                            class="w-full mkt-surface-alt border mkt-border rounded-2xl py-4 px-6 font-black text-sm mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none uppercase placeholder-slate-400">
                        @error('type_mesin') <span
                            class="text-[9px] text-violet-400 font-black italic ml-2 uppercase">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase mkt-text-muted ml-2 tracking-widest">Gauge / Inch (DD)</label>
                        <input wire:model="gauge_inch" type="text" placeholder="CONTOH: 28G.30"
                            class="w-full mkt-surface-alt border mkt-border rounded-2xl py-4 px-6 font-black text-sm mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none placeholder-slate-400">
                        @error('gauge_inch') <span
                            class="text-[9px] text-violet-400 font-black italic ml-2 uppercase">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase mkt-text-muted ml-2">Jml Feeder (INT)</label>
                        <input wire:model="jml_feeder" type="number" placeholder="MASUKKAN JML FEEDER"
                            class="w-full mkt-surface-alt border mkt-border rounded-2xl py-4 px-6 font-black text-sm mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none placeholder-slate-400">
                        @error('jml_feeder') <span
                            class="text-[9px] text-violet-400 font-black italic ml-2 uppercase">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase mkt-text-muted ml-2">Jml Jarum (INT)</label>
                        <input wire:model="jml_jarum" type="number" placeholder="MASUKKAN JML JARUM"
                            class="w-full mkt-surface-alt border mkt-border rounded-2xl py-4 px-6 font-black text-sm mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none placeholder-slate-400">
                        @error('jml_jarum') <span
                            class="text-[9px] text-violet-400 font-black italic ml-2 uppercase">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- SECTION 02: HASIL PRODUKSI --}}
            <div class="mkt-surface p-10 rounded-[3.5rem] shadow-sm border mkt-border">
                <div class="flex items-center gap-4 mb-10">
                    <div
                        class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-lg shadow-indigo-600/20">
                        02</div>
                    <div>
                        <h3 class="text-sm font-black uppercase mkt-text tracking-widest">Hasil Produksi Greige</h3>
                        <p class="text-[9px] mkt-text-muted font-bold uppercase italic">Input data fisik kain yang dihasilkan</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="mkt-surface-alt border mkt-border p-6 rounded-3xl space-y-4">
                        <label class="text-[9px] font-black uppercase mkt-text-muted block text-center tracking-widest">Lebar</label>
                        <input wire:model="lebar" type="number" step="0.01" placeholder="LEBAR"
                            class="w-full mkt-surface border mkt-border rounded-2xl py-4 text-center font-black text-2xl mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none placeholder-slate-400">
                        @error('lebar') <p class="text-[8px] text-violet-400 font-black text-center mt-1 uppercase">{{ $message }}</p> @enderror
                    </div>
                    <div class="mkt-surface-alt border mkt-border p-6 rounded-3xl space-y-4">
                        <label class="text-[9px] font-black uppercase mkt-text-muted block text-center tracking-widest">Gramasi</label>
                        <input wire:model="gramasi" type="number" placeholder="GRAMASI"
                            class="w-full mkt-surface border mkt-border rounded-2xl py-4 text-center font-black text-2xl mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none placeholder-slate-400">
                        @error('gramasi') <p class="text-[8px] text-violet-400 font-black text-center mt-1 uppercase">{{ $message }}</p> @enderror
                    </div>
                    <div class="mkt-surface-alt border-2 border-indigo-600/30 p-6 rounded-3xl space-y-4 relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-8 h-8 bg-indigo-600 text-white flex items-center justify-center text-[10px] rounded-bl-xl font-black">KG</div>
                        <label class="text-[9px] font-black uppercase text-violet-400 block text-center tracking-widest">Weight (KG)</label>
                        <input wire:model.live="kg" type="text" placeholder="BERAT KG"
                            class="w-full mkt-surface border border-indigo-600/20 rounded-2xl py-4 text-center font-black text-4xl text-violet-400 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none placeholder-slate-400">
                        @error('kg') <p class="text-[8px] text-violet-400 font-black text-center mt-1 uppercase">{{ $message }}</p> @enderror
                        @if($kgDeviation)
                            <div class="mt-2 p-2 bg-amber-500/20 border border-amber-500 rounded-xl text-[10px] text-amber-500 font-black text-center uppercase">
                                ⚠️ Deviasi KG Melebihi 10%!
                            </div>
                        @endif
                    </div>
                    <div class="mkt-surface-alt border-2 border-indigo-600/30 p-6 rounded-3xl space-y-4 relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-8 h-8 bg-indigo-600 text-white flex items-center justify-center text-[10px] rounded-bl-xl font-black">RL</div>
                        <label class="text-[9px] font-black uppercase text-violet-400 block text-center tracking-widest">Roll Count</label>
                        <input wire:model.live="roll" type="number" placeholder="JML ROLL"
                            class="w-full mkt-surface border border-indigo-600/20 rounded-2xl py-4 text-center font-black text-4xl text-violet-400 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none placeholder-slate-400">
                        @error('roll') <p class="text-[8px] text-violet-400 font-black text-center mt-1 uppercase">{{ $message }}</p> @enderror
                        @if($rollDeviation)
                            <div class="mt-2 p-2 bg-amber-500/20 border border-amber-500 rounded-xl text-[10px] text-amber-500 font-black text-center uppercase">
                                ⚠️ Deviasi Roll Melebihi 10%!
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- SECTION 03: TEKNIS BENANG --}}
            <div class="mkt-surface p-12 rounded-[4rem] shadow-sm border mkt-border overflow-hidden relative">
                <div class="absolute top-0 right-0 p-12 opacity-5 select-none pointer-events-none">
                    <span class="text-[120px] font-black uppercase italic leading-none block text-slate-800">DUNIATEX</span>
                    <span class="text-[120px] font-black uppercase italic leading-none block text-slate-800">KNITTING</span>
                </div>

                <div class="flex items-center gap-4 mb-12 relative z-10">
                    <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl italic shadow-xl shadow-indigo-600/20">03</div>
                    <div>
                        <h3 class="text-sm font-black uppercase mkt-text tracking-[0.2em]">Penggunaan Benang & YL</h3>
                        <p class="text-[9px] mkt-text-muted font-bold uppercase italic">Persentase campuran benang dan Yarn Length</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative z-10">
                    @foreach(range(1, 4) as $i)
                        <div class="p-6 mkt-surface-alt border mkt-border rounded-[2.5rem] space-y-6 hover:border-violet-500/50 transition-all group shadow-sm">
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <label class="text-[9px] font-black uppercase mkt-text-muted group-hover:text-violet-400 transition-colors tracking-widest block ml-2">Benang {{ $i }} (Type)</label>
                                    @php $b_field = 'benang_' . $i; @endphp
                                    <input wire:model="{{ $b_field }}" type="text" placeholder="MISAL: POLY 75/72..."
                                        class="w-full mkt-surface border mkt-border rounded-2xl py-4 px-6 font-black text-xs mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition-all italic uppercase placeholder-slate-400">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[9px] font-black uppercase mkt-text-muted group-hover:text-violet-400 transition-colors tracking-widest block ml-2">Penggunaan {{ $i }} (%)</label>
                                    @php $p_field = 'benang_' . $i . '_percent'; @endphp
                                    <input wire:model="{{ $p_field }}" type="text" placeholder="MISAL: 88%"
                                        class="w-full mkt-surface border mkt-border rounded-2xl py-4 px-6 font-black text-xs text-violet-400 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition-all placeholder-slate-400">
                                </div>
                            </div>

                            <div class="space-y-2 pt-4 border-t mkt-border">
                                <label class="text-[9px] font-black uppercase mkt-text-muted group-hover:text-violet-400 transition-colors tracking-widest block ml-2">YL {{ $i }} (INT)</label>
                                @php $y_field = 'yl_' . $i; @endphp
                                <input wire:model="{{ $y_field }}" type="number" placeholder="YL VALUE"
                                    class="w-full mkt-surface border mkt-border rounded-2xl py-4 px-6 font-black text-xs mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition-all placeholder-slate-400">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mt-16 pt-12 border-t mkt-border relative z-10">
                    <div class="space-y-3">
                        <label class="text-[11px] font-black uppercase mkt-text-muted ml-4 italic tracking-[0.2em]">Note / Keterangan</label>
                        <textarea wire:model="note" rows="3"
                            class="w-full mkt-surface border mkt-border rounded-[2.5rem] py-6 px-8 font-bold text-sm mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition-all placeholder-slate-400"
                            placeholder="Catatan kondisi mesin, benang, atau instruksi khusus..."></textarea>
                    </div>
                    <div class="flex flex-col justify-end">
                        <div class="mkt-surface-alt border mkt-border p-8 rounded-[3rem] group hover:border-violet-500 transition-all">
                            <label class="text-[10px] font-black uppercase text-violet-400 mb-4 block tracking-[0.2em] italic text-center">Target Produksi / Day (KG)</label>
                            <input wire:model="produksi_per_day" type="number"
                                class="w-full bg-transparent border-none font-black text-5xl text-violet-400 text-center focus:ring-0 placeholder:text-slate-300 italic"
                                placeholder="000">
                        </div>
                    </div>
                </div>
            </div>

            {{-- SUBMIT --}}
            <div class="flex justify-center md:justify-end pt-10 pb-24">
                <button type="submit" wire:loading.attr="disabled"
                    class="group relative w-full md:w-auto overflow-hidden bg-indigo-600 px-20 py-7 rounded-[2.5rem] font-black uppercase italic tracking-[0.3em] hover:scale-105 transition-all shadow-lg text-white hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove class="relative z-10">Submit Production Log</span>
                    <span wire:loading class="relative z-10">Processing Log...</span>
                    <div
                        class="absolute inset-0 bg-black translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                    </div>
                </button>
            </div>
        </form>
    </div>

    {{-- MODAL DETAIL ORDER --}}
    @if($order_detail)
    <div x-show="showDetail" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-950/90 backdrop-blur-md"
         @click.away="showDetail = false"
         style="display: none;">
        
        <div class="mkt-surface w-full max-w-3xl rounded-[3rem] border mkt-border shadow-2xl relative overflow-hidden italic flex flex-col max-h-[85vh]">
            {{-- Header Modal --}}
            <div class="px-10 pt-10 pb-6 border-b mkt-border mkt-surface-alt sticky top-0 z-10 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-600/80 rounded-2xl flex items-center justify-center text-white text-xl shadow-lg shadow-indigo-500/20">ℹ️</div>
                    <div>
                        <h3 class="text-lg font-black uppercase mkt-text leading-none">Order Tracking Detail</h3>
                        <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-widest mt-1">Artikel #{{ $order_detail['art_no'] }} • Full Technical Specification</p>
                    </div>
                </div>
                <button @click="showDetail = false" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:bg-indigo-600 hover:text-white transition-all text-xl">✕</button>
            </div>
            
            {{-- Tab Navigation --}}
            <div class="px-10 py-4 mkt-surface-alt border-b mkt-border flex items-center gap-3 overflow-x-auto no-scrollbar">
                <button wire:click="$set('activeDetailTab', 'marketing')" 
                    class="flex-none px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all {{ $activeDetailTab === 'marketing' ? 'bg-indigo-600 text-white shadow-xl scale-105' : 'mkt-surface mkt-text hover:bg-slate-100' }}">
                    Marketing Req.
                </button>
                @foreach($productionHistory as $index => $history)
                    <button wire:click="$set('activeDetailTab', 'step_{{ $index }}')" 
                        class="flex-none px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all {{ $activeDetailTab === 'step_'.$index ? 'bg-indigo-600 text-white shadow-xl scale-105' : 'mkt-surface mkt-text hover:bg-slate-100' }}">
                        {{ $history['division_name'] }}
                    </button>
                @endforeach
            </div>

            {{-- Content Area --}}
            <div class="overflow-y-auto flex-1 p-10 custom-scrollbar bg-transparent">
                {{-- TAB: MARKETING --}}
                @if($activeDetailTab === 'marketing')
                    <div class="space-y-10 animate-in fade-in slide-in-from-bottom-4 duration-500 text-left">
                        {{-- HEADER BADGE --}}
                        <div class="flex items-center gap-4 border-b mkt-border pb-6">
                            <div class="w-12 h-12 bg-indigo-600/80 rounded-2xl flex items-center justify-center text-white font-black text-sm shadow-lg shadow-red-600/20">MO</div>
                            <div>
                                <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-0.5">MARKETING SPECIFICATIONS</p>
                                <h3 class="text-xl font-black mkt-text uppercase tracking-tighter italic">MARKETING RESULT</h3>
                            </div>
                        </div>

                        {{-- I. IDENTITAS ORDER --}}
                        <div class="space-y-4">
                            <p class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.3em] border-l-4 border-red-500 pl-3">I. IDENTITAS ORDER</p>
                            <div class="grid grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-6 rounded-3xl">
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">ART NO</p>
                                    <p class="text-[11px] font-black mkt-text italic">{{ $order_detail['sap_no'] ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">ART NO</p>
                                    <p class="text-[11px] font-black mkt-text">{{ $order_detail['art_no'] }}</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TANGGAL ORDER</p>
                                    <p class="text-[11px] font-black mkt-text">{{ $order_detail['created_at'] ?? now()->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">PELANGGAN</p>
                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $order_detail['pelanggan'] }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- II. KLASIFIKASI & MATERIAL --}}
                        <div class="space-y-4">
                            <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">II. KLASIFIKASI & MATERIAL</p>
                            <div class="grid grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-6 rounded-3xl">
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">MKT (SALES)</p>
                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $order_detail['marketing_name'] ?? 'ADMIN' }}</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">KEPERLUAN</p>
                                    <p class="text-[11px] font-black mkt-text uppercase italic">{{ $order_detail['keperluan'] ?? 'NEW ORDER' }}</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">MATERIAL</p>
                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $order_detail['material'] }}</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">BENANG</p>
                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $order_detail['benang'] ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- III. SPESIFIKASI TEKNIS --}}
                        <div class="space-y-4">
                            <p class="text-[9px] font-black text-indigo-500 uppercase tracking-[0.3em] border-l-4 border-indigo-500 pl-3">III. SPESIFIKASI TEKNIS</p>
                            <div class="grid grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-6 rounded-3xl">
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TARGET GRAMASI</p>
                                    <p class="text-[11px] font-black text-indigo-400">{{ $order_detail['target_gramasi'] }} GSM</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">HANDFEEL</p>
                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $order_detail['handfeel'] ?? '-' }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TREATMENT KHUSUS</p>
                                    <p class="text-[11px] font-black text-indigo-600 uppercase italic leading-none">{{ $order_detail['treatment'] ?? '-' }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">KONSTRUKSI GREIGE</p>
                                    <p class="text-[11px] font-black mkt-text italic uppercase leading-tight">{{ $order_detail['konstruksi'] ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TARGET LEBAR</p>
                                    <p class="text-[11px] font-black mkt-text">{{ $order_detail['target_lebar'] }}"</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">BELAH/BULAT</p>
                                    <p class="text-[11px] font-black mkt-text uppercase">{{ $order_detail['belah_bulat'] }}</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">WARNA KAIN</p>
                                    <p class="text-[11px] font-black text-emerald-400 uppercase leading-none">{{ $order_detail['warna'] }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- IV. QUANTITY & KETERANGAN --}}
                        <div class="space-y-4">
                            <p class="text-[9px] font-black text-orange-500 uppercase tracking-[0.3em] border-l-4 border-orange-500 pl-3">IV. QUANTITY & KETERANGAN</p>
                            <div class="grid grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-6 rounded-3xl">
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TARGET KG</p>
                                    <p class="text-lg font-black text-orange-500 italic">{{ number_format($order_detail['kg_target'], 1) }} KG</p>
                                </div>
                                <div>
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TARGET ROLL</p>
                                    <p class="text-lg font-black text-orange-500 italic">{{ $order_detail['roll_target'] }} RL</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">KETERANGAN ARTIKEL</p>
                                    <p class="text-[11px] font-bold mkt-text-muted leading-relaxed">{{ $order_detail['keterangan'] ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- V. R&D RECOMMENDATION --}}
                        @if(!empty($order_detail['rnd_mesin']))
                            <div class="space-y-4">
                                <p class="text-[9px] font-black text-yellow-500 uppercase tracking-[0.3em] border-l-4 border-yellow-500 pl-3">V. R&D RECOMMENDATION</p>
                                <div class="grid grid-cols-4 gap-6 bg-yellow-500/5 border border-yellow-500/10 p-6 rounded-3xl text-left">
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">REKOMENDASI MESIN</p>
                                        <p class="text-[11px] font-black mkt-text uppercase italic leading-none">{{ $order_detail['rnd_mesin'] }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">JENIS MESIN</p>
                                        <p class="text-[11px] font-black mkt-text uppercase italic leading-none">{{ $order_detail['rnd_jenis'] ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">GSM GREIGE</p>
                                        <p class="text-[11px] font-black text-yellow-500 italic leading-none">{{ $order_detail['rnd_gramasi'] ?? '-' }} GSM</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- TAB: PRODUCTION STEPS --}}
                @foreach($productionHistory as $index => $history)
                    @if($activeDetailTab === 'step_'.$index)
                        <div class="space-y-10 animate-in fade-in slide-in-from-bottom-4 duration-500 text-left">
                            {{-- NEW HIGH-FIDELITY HEADER CARD --}}
                            @php
                                $operatorActual = !empty($history['technical_data']['nama_input']) ? $history['technical_data']['nama_input'] : ($history['operator']['name'] ?? 'UNKNOWN');
                            @endphp
                            <div class="flex items-center justify-between p-6 mkt-surface-alt border mkt-border rounded-3xl group hover:border-emerald-500/50 transition-all duration-500">
                                <div class="flex items-center gap-5 text-left">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center border border-emerald-500/20">
                                        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1 italic">ACTUAL OPERATOR</p>
                                        <p class="text-xl font-black mkt-text italic tracking-tighter leading-none">{{ strtoupper($operatorActual) }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-[8px] mkt-text-muted font-black uppercase tracking-widest mb-1">MACHINE UNIT</p>
                                    <p class="text-3xl font-black text-indigo-600 italic leading-none">{{ $history['machine_no'] ?? 'M-01' }}</p>
                                </div>
                            </div>

                            @if($history['division_name'] === 'knitting')
                                {{-- REFINED SLEEK LAYOUT FOR KNITTING --}}
                                <div class="space-y-10 animate-in fade-in duration-700">
                                    
                                    {{-- I. IDENTITAS & SPESIFIKASI MESIN --}}
                                    <div class="space-y-4">
                                        <p class="text-[9px] font-black text-indigo-500 uppercase tracking-[0.3em] border-l-4 border-indigo-500 pl-3">I. IDENTITAS & SPESIFIKASI MESIN</p>
                                        <div class="grid grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                            <div class="col-span-2 border-r mkt-border pr-6">
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">ARTIKEL NUMBER</p>
                                                        <p class="text-[11px] font-black text-indigo-400 italic">{{ $history['technical_data']['sap_no'] ?? ($sap_no ?? '-') }}</p>
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
                                                <p class="text-[11px] font-black mkt-text uppercase">{{ $history['technical_data']['jml_feeder'] ?? '0' }} <span class="text-[8px] mkt-text-muted">FDR</span></p>
                                            </div>
                                            <div>
                                                <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">JML JARUM</p>
                                                <p class="text-[11px] font-black mkt-text uppercase">{{ $history['technical_data']['jml_jarum'] ?? '0' }} <span class="text-[8px] mkt-text-muted">JRM</span></p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- II. HASIL PRODUKSI GREIGE --}}
                                    <div class="space-y-4">
                                        <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">II. HASIL PRODUKSI GREIGE</p>
                                        <div class="grid grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                            <div>
                                                <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">LEBAR / GRAMASI</p>
                                                <p class="text-[11px] font-black mkt-text uppercase italic">{{ $history['technical_data']['lebar'] ?? '-' }} x {{ $history['technical_data']['gramasi'] ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TOTAL OUTPUT</p>
                                                <p class="text-[11px] font-black text-emerald-500 uppercase">{{ $history['roll'] ?? '0' }} ROLL</p>
                                            </div>
                                            <div class="col-span-2 mkt-surface-alt p-4 rounded-xl border border-emerald-500/10">
                                                <p class="text-[7px] mkt-text-muted font-black uppercase mb-1 italic">ACTUAL WEIGHT (KG)</p>
                                                <p class="text-2xl font-black text-emerald-500 italic">{{ number_format($history['kg'], 1) }} <span class="text-[10px] mkt-text-muted">KG</span></p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- III. PENGGUNAAN BENANG & YL --}}
                                    <div class="space-y-4">
                                        <p class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.3em] border-l-4 border-red-500 pl-3">III. PENGGUNAAN BENANG & YL</p>
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl">
                                            @foreach(range(1, 4) as $i)
                                                @if(!empty($history['technical_data']['benang_'.$i]))
                                                    <div class="space-y-2 border-l border-red-500/20 pl-4 group/item hover:bg-slate-100 dark:hover:bg-slate-700/50 border border-white/5 p-2 rounded-lg transition-all">
                                                        <p class="text-[7px] mkt-text-muted font-black uppercase mb-0.5">SLOT {{ $i }}</p>
                                                        <p class="text-[10px] font-black mkt-text uppercase leading-tight truncate">
                                                            {{ $history['technical_data']['benang_'.$i] }}
                                                        </p>
                                                        @if(!empty($history['technical_data']['benang_'.$i.'_percent']))
                                                            <p class="text-[11px] font-black text-indigo-600 tracking-tighter leading-none">{{ $history['technical_data']['benang_'.$i.'_percent'] }}</p>
                                                        @endif
                                                        <div class="pt-2 border-t mkt-border">
                                                            <p class="text-[7px] mkt-text-muted font-bold uppercase">YL</p>
                                                            <p class="text-[11px] font-bold text-indigo-400 tracking-tighter leading-none">{{ $history['technical_data']['yl_'.$i] ?? '-' }}</p>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- IV. NOTE & TARGET --}}
                                    <div class="space-y-4">
                                        <p class="text-[9px] font-black mkt-text-muted uppercase tracking-[0.3em] border-l-4 border-slate-500 pl-3">IV. NOTE & TARGET</p>
                                        <div class="grid grid-cols-3 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl text-left">
                                            <div class="col-span-2">
                                                <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">OPERATOR NOTES / KETERANGAN</p>
                                                <div class="mkt-surface p-4 rounded-xl border border-white/5">
                                                    <p class="text-[10px] font-bold mkt-text-muted italic leading-relaxed">"{{ $history['technical_data']['note'] ?? 'Tidak ada catatan tambahan dari operator.' }}"</p>
                                                </div>
                                            </div>
                                            <div class="text-right flex flex-col justify-center">
                                                <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">TARGET PRODUKSI / DAY</p>
                                                <p class="text-2xl font-black mkt-text italic tracking-tighter leading-none">{{ $history['technical_data']['produksi_per_day'] ?? '0' }} <span class="text-sm mkt-text-muted">KG</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- GENERIC LAYOUT IN MARKETING STYLE --}}
                                <div class="space-y-4">
                                    <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.3em] border-l-4 border-emerald-500 pl-3">I. HASIL PRODUKSI ({{ strtoupper($history['division_name']) }})</p>
                                    <div class="grid grid-cols-3 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl text-left">
                                        <div>
                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">ACTUAL WEIGHT</p>
                                            <p class="text-[11px] font-black text-emerald-500 italic leading-none">{{ number_format($history['kg'], 1) }} KG</p>
                                        </div>
                                        <div>
                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">ACTUAL ROLL</p>
                                            <p class="text-[11px] font-black text-emerald-500 italic leading-none">{{ $history['roll'] }} RL</p>
                                        </div>
                                        <div>
                                            <p class="text-[7px] mkt-text-muted font-black uppercase mb-1">MACHINE NO</p>
                                            <p class="text-[11px] font-black text-indigo-600 italic leading-none">{{ $history['machine_no'] ?? 'M-01' }}</p>
                                        </div>
                                    </div>

                                    <p class="text-[9px] font-black text-indigo-500 uppercase tracking-[0.3em] border-l-4 border-indigo-500 pl-3">II. TECHNICAL DATA</p>
                                    <div class="grid grid-cols-3 gap-6 mkt-surface-alt border mkt-border p-6 rounded-2xl text-left">
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
            <div class="px-10 py-8 mkt-surface border-t mkt-border flex justify-end">
                <button @click="showDetail = false" class="px-12 py-4 bg-slate-800 text-white rounded-2xl font-black uppercase text-xs tracking-[0.2em] hover:bg-indigo-600 hover:shadow-xl transition-all active:scale-95 border border-white/5">
                    Tutup Detail
                </button>
            </div>
        </div>
    </div>
    @endif

    <script>
        document.addEventListener('livewire:init', () => {
            const storageKey = 'knitting_form_draft';

            // Load draft
            const draft = JSON.parse(localStorage.getItem(storageKey));
            if (draft) {
                Object.keys(draft).forEach(key => {
                    @this.set(key, draft[key], true); // true for defer if needed, or just set
                });
            }

            // Save draft on change
            Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                if (component.name === 'operator.knitting-form') {
                    const data = {
                        operator_name: @this.get('operator_name'),
                        artikelNo: @this.get('artikelNo'),
                        kg: @this.get('kg'),
                        roll: @this.get('roll'),
                        lebar: @this.get('lebar'),
                        gramasi: @this.get('gramasi'),
                    };
                    localStorage.setItem(storageKey, JSON.stringify(data));
                }
            });

            // Clear draft on successful save
            window.addEventListener('show-success-toast', () => {
                localStorage.removeItem(storageKey);
            });
        });
    </script>
</div>