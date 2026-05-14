
<div class="py-12 bg-[#0f172a] min-h-screen font-sans italic tracking-tighter text-left">
    <div class="max-w-6xl mx-auto px-4">
        
        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 gap-6">
            <div>
                <h2 class="text-5xl font-black uppercase text-white leading-none tracking-tighter">
                    Knitting <span class="text-red-600 italic">Logbook</span>
                </h2>
                <div class="flex items-center gap-2 mt-3">
                    <span class="h-2 w-2 rounded-full bg-red-600 animate-pulse"></span>
                    <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-[0.3em]">Formulir Input Produksi Mesin Rajut</p>
                </div>
            </div>
            <a href="{{ route('operator.logbook') }}" class="group bg-slate-800 border border-slate-700 px-8 py-4 rounded-2xl text-[10px] font-black uppercase mkt-text-muted hover:bg-red-600 hover:text-white hover:border-red-600 transition-all shadow-xl">
                ← Kembali ke Logbook
            </a>
        </div>

        @if (session()->has('message'))
            <div class="mb-8 p-6 bg-emerald-600 text-white rounded-3xl font-black uppercase text-xs italic shadow-2xl shadow-emerald-900/20 animate-bounce">
                🚀 {{ session('message') }}
            </div>
        @endif

        <form wire:submit.prevent="save" class="space-y-8">
            
            {{-- SECTION 01: SPESIFIKASI MESIN --}}
            <div class="mkt-surface p-10 rounded-[3.5rem] shadow-2xl border mkt-border relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 mkt-surface rounded-bl-full -mr-10 -mt-10 opacity-50"></div>
                
                <div class="flex items-center gap-4 mb-10 relative z-10">
                    <div class="w-12 h-12 bg-red-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-lg shadow-red-200">01</div>
                    <div>
                        <h3 class="text-sm font-black uppercase mkt-text tracking-widest">Spesifikasi Mesin</h3>
                        <p class="text-[9px] mkt-text-muted font-bold uppercase italic">Identitas mesin dan nomor marketing order</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-x-12 gap-y-8 relative z-10">
                    {{-- SAP NO --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-red-600 ml-2 tracking-widest">SAP NO (Marketing Order)</label>
                        <div class="relative">
                            <input wire:model.live="sap_no" type="text" readonly
                                class="w-full mkt-input border-2 mkt-border rounded-2xl py-5 px-6 font-black text-sm text-slate-500 cursor-not-allowed italic">
                            <span class="absolute right-4 top-5 mkt-text-muted">🔒</span>
                        </div>
                        
                        @if($order_detail)
                            <div class="mt-4 p-5 bg-gradient-to-br from-blue-600 to-indigo-700 text-white rounded-3xl shadow-lg border-b-4 border-blue-800">
                                <p class="text-[8px] font-black uppercase text-blue-200 mb-2 tracking-widest">Konfirmasi Artikel:</p>
                                <p class="text-xs font-black uppercase italic leading-tight">
                                    {{ $order_detail['pelanggan'] }}<br>
                                    <span class="text-yellow-400 text-sm tracking-tighter">{{ $order_detail['art_no'] }}</span>
                                    <span class="block text-[10px] text-blue-100 mt-1 opacity-80">{{ $order_detail['warna'] }}</span>
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- OPERATOR NAME --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase mkt-text-muted ml-2 tracking-widest">Operator Bertugas</label>
                        <div class="relative">
                            <span class="absolute left-6 top-5 text-red-600 font-bold">👤</span>
                            <input type="text" wire:model="operator_name" placeholder="TULIS NAMA LENGKAP..." 
                                class="w-full pl-14 pr-6 py-5 mkt-surface border-2 mkt-border rounded-2xl text-sm font-black mkt-text focus:border-red-600 focus:mkt-surface transition-all outline-none uppercase italic">
                        </div>
                        @error('operator_name') <span class="text-[9px] text-red-600 font-black italic ml-2 uppercase">{{ $message }}</span> @enderror
                    </div>

                    {{-- TANGGAL --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase mkt-text-muted ml-2 tracking-widest">Tanggal Produksi</label>
                        <input wire:model="tanggal" type="date" 
                            class="w-full mkt-surface border-2 mkt-border rounded-2xl py-5 px-6 font-black text-sm mkt-text focus:border-red-600 transition-all outline-none">
                        @error('tanggal') <span class="text-[9px] text-red-600 font-black italic ml-2 uppercase">{{ $message }}</span> @enderror
                    </div>

                    {{-- DETAIL MESIN (ROW 2) --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase mkt-text-muted ml-2">No Mesin (DD)</label>
                        <input wire:model="no_mesin" type="text" placeholder="CONTOH: K01" 
                            class="w-full mkt-surface border-2 mkt-border rounded-2xl py-4 px-6 font-black text-sm mkt-text focus:border-red-600 transition-all outline-none">
                        @error('no_mesin') <span class="text-[9px] text-red-600 font-black italic ml-2 uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase mkt-text-muted ml-2">Type Mesin (DD)</label>
                        <input wire:model="type_mesin" type="text" placeholder="PAI LUNG / DLL" 
                            class="w-full mkt-surface border-2 mkt-border rounded-2xl py-4 px-6 font-black text-sm mkt-text focus:border-red-600 transition-all outline-none uppercase">
                        @error('type_mesin') <span class="text-[9px] text-red-600 font-black italic ml-2 uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase mkt-text-muted ml-2 tracking-widest">Gauge / Inch (DD)</label>
                        <input wire:model="gauge_inch" 
                            type="text" 
                            placeholder="CONTOH: 28G.30"
                            class="w-full mkt-surface border-2 mkt-border rounded-2xl py-4 px-6 font-black text-sm mkt-text focus:border-red-600 transition-all outline-none">
                        @error('gauge_inch') <span class="text-[9px] text-red-600 font-black italic ml-2 uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase mkt-text-muted ml-2">Jml Feeder (INT)</label>
                        <input wire:model="jml_feeder" type="number" 
                            class="w-full mkt-surface border-2 mkt-border rounded-2xl py-4 px-6 font-black text-sm mkt-text focus:border-red-600 transition-all outline-none">
                        @error('jml_feeder') <span class="text-[9px] text-red-600 font-black italic ml-2 uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase mkt-text-muted ml-2">Jml Jarum (INT)</label>
                        <input wire:model="jml_jarum" type="number" 
                            class="w-full mkt-surface border-2 mkt-border rounded-2xl py-4 px-6 font-black text-sm mkt-text focus:border-red-600 transition-all outline-none">
                        @error('jml_jarum') <span class="text-[9px] text-red-600 font-black italic ml-2 uppercase">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- SECTION 02: HASIL PRODUKSI --}}
            <div class="mkt-surface p-10 rounded-[3.5rem] shadow-2xl border mkt-border">
                <div class="flex items-center gap-4 mb-10">
                    <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-lg shadow-slate-200">02</div>
                    <div>
                        <h3 class="text-sm font-black uppercase mkt-text tracking-widest">Hasil Produksi Greige</h3>
                        <p class="text-[9px] mkt-text-muted font-bold uppercase italic">Input data fisik kain yang dihasilkan</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-10">
                    <div class="mkt-surface p-6 rounded-3xl border mkt-border space-y-2">
                        <label class="text-[10px] font-black uppercase mkt-text-muted block text-center">Lebar</label>
                        <input wire:model="lebar" type="number" step="0.01" placeholder="0.00"
                            class="w-full mkt-surface border-2 mkt-border rounded-2xl py-4 text-center font-black text-xl mkt-text focus:ring-4 focus:ring-red-50">
                        @error('lebar') <p class="text-[8px] text-red-600 font-black text-center mt-1 uppercase">{{ $message }}</p> @enderror
                    </div>
                    <div class="mkt-surface p-6 rounded-3xl border mkt-border space-y-2">
                        <label class="text-[10px] font-black uppercase mkt-text-muted block text-center">Gramasi</label>
                        <input wire:model="gramasi" type="number" placeholder="0"
                            class="w-full mkt-surface border-2 mkt-border rounded-2xl py-4 text-center font-black text-xl mkt-text focus:ring-4 focus:ring-red-50">
                        @error('gramasi') <p class="text-[8px] text-red-600 font-black text-center mt-1 uppercase">{{ $message }}</p> @enderror
                    </div>
                    <div class="bg-red-50 p-6 rounded-3xl border border-red-100 space-y-2 shadow-inner">
                        <label class="text-[10px] font-black uppercase text-red-600 block text-center tracking-widest">KG (Weight)</label>
                        <input wire:model="kg" type="text" placeholder="0"
                            class="w-full mkt-surface border-2 border-red-200 rounded-2xl py-4 text-center font-black text-3xl text-red-600 focus:ring-4 focus:ring-red-100">
                        @error('kg') <p class="text-[8px] text-red-600 font-black text-center mt-1 uppercase">{{ $message }}</p> @enderror
                    </div>
                    <div class="bg-slate-900 p-6 rounded-3xl border border-slate-800 space-y-2 shadow-2xl">
                        <label class="text-[10px] font-black uppercase text-slate-500 block text-center tracking-widest">Roll Count</label>
                        <input wire:model="roll" type="number" placeholder="0"
                            class="w-full bg-white/5 border-2 border-white/10 rounded-2xl py-4 text-center font-black text-3xl text-white focus:border-red-600 outline-none">
                        @error('roll') <p class="text-[8px] text-red-400 font-black text-center mt-1 uppercase">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- SECTION 03: TEKNIS BENANG (DARK THEME) --}}
            <div class="bg-slate-900 p-12 rounded-[4rem] shadow-[0_35px_60px_-15px_rgba(0,0,0,0.5)] text-white overflow-hidden relative">
                <div class="absolute top-0 right-0 p-12 opacity-[0.03] select-none">
                    <span class="text-[120px] font-black uppercase italic leading-none block">DUNIATEX</span>
                    <span class="text-[120px] font-black uppercase italic leading-none block">KNITTING</span>
                </div>

                <div class="flex items-center gap-4 mb-12 relative z-10">
                    <div class="w-12 h-12 bg-red-600 rounded-2xl flex items-center justify-center text-white font-black text-xl italic shadow-xl shadow-red-900/50">03</div>
                    <div>
                        <h3 class="text-sm font-black uppercase text-red-600 tracking-[0.2em]">Penggunaan Benang & YL</h3>
                        <p class="text-[9px] text-slate-500 font-bold uppercase italic">Persentase campuran benang dan Yarn Length</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative z-10">
                    @foreach(range(1,4) as $i)
                    <div class="p-8 bg-white/5 rounded-[2.5rem] border border-white/5 space-y-6 hover:bg-white/10 transition-all group backdrop-blur-sm">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-500 group-hover:text-red-500 transition-colors tracking-widest block ml-2">Benang {{ $i }} (%)</label>
                            @php $b_field = 'benang_' . $i; @endphp
                            <input wire:model="{{ $b_field }}" type="text" placeholder="..." 
                                class="w-full bg-slate-950/50 border-2 border-white/5 rounded-2xl py-4 px-6 font-black text-xs text-white placeholder:mkt-text focus:border-red-600 outline-none transition-all italic">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-500 group-hover:text-red-500 transition-colors tracking-widest block ml-2">YL {{ $i }} (INT)</label>
                            @php $y_field = 'yl_' . $i; @endphp
                            <input wire:model="{{ $y_field }}" type="number" placeholder="0" 
                                class="w-full bg-slate-950/50 border-2 border-white/5 rounded-2xl py-4 px-6 font-black text-xs text-white focus:border-red-600 outline-none transition-all">
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mt-16 pt-12 border-t border-white/5 relative z-10">
                    <div class="space-y-3">
                        <label class="text-[11px] font-black uppercase text-slate-500 ml-4 italic tracking-[0.2em]">Note / Keterangan</label>
                        <textarea wire:model="note" rows="3" 
                            class="w-full bg-slate-950/50 border-2 border-white/5 rounded-[2.5rem] py-6 px-8 font-bold text-sm text-white focus:border-red-600 outline-none transition-all" 
                            placeholder="Catatan kondisi mesin, benang, atau instruksi khusus..."></textarea>
                    </div>
                    <div class="flex flex-col justify-end">
                        <div class="bg-gradient-to-r from-red-600/20 to-transparent p-8 rounded-[3rem] border border-red-600/20">
                            <label class="text-[11px] font-black uppercase text-red-500 mb-4 block tracking-[0.2em] italic text-center">Target Produksi / Day (KG)</label>
                            <input wire:model="produksi_per_day" type="number" 
                                class="w-full bg-transparent border-none font-black text-7xl text-red-600 text-center focus:ring-0 placeholder:text-red-900/30" placeholder="000">
                        </div>
                    </div>
                </div>
            </div>

            {{-- SUBMIT --}}
            <div class="flex justify-center md:justify-end pt-10 pb-24">
                <button type="submit" wire:loading.attr="disabled"
                    class="group relative w-full md:w-auto overflow-hidden bg-red-600 text-white px-20 py-7 rounded-[2.5rem] font-black uppercase italic tracking-[0.3em] hover:scale-105 transition-all shadow-2xl shadow-red-900/40 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove class="relative z-10">Submit Production Log</span>
                    <span wire:loading class="relative z-10">Processing Log...</span>
                    <div class="absolute inset-0 bg-black translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                </button>
            </div>
        </form>
    </div>
</div>