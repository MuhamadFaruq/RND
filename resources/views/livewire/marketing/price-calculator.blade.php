<div x-data="{
    formatIDR(val) {
        if (!val) return '';
        let number = val.toString().replace(/[^0-9]/g, '');
        return number ? new Intl.NumberFormat('id-ID').format(number) : '';
    },
    stripFormatting(val) {
        return val.toString().replace(/[^0-9]/g, '');
    }
}" class="mkt-surface p-4 md:p-8 rounded-xl md:rounded-[2.5rem] border mkt-border shadow-2xl relative overflow-hidden animate-in fade-in duration-500">
    
    {{-- Tabs Navigation --}}
    <div class="flex flex-wrap items-center gap-2 mb-4 md:mb-8 bg-slate-100 dark:bg-slate-900/50 p-1.5 rounded-2xl w-full md:w-fit border mkt-border overflow-x-auto">
        <button wire:click="setTab('calculator')" 
            class="flex-1 md:flex-none px-4 md:px-6 py-2.5 rounded-xl text-[10px] md:text-xs font-black uppercase tracking-widest transition-all {{ $activeTab === 'calculator' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'mkt-text-muted hover:mkt-text' }}">
            Calculator
        </button>
        <button wire:click="setTab('history')" 
            class="flex-1 md:flex-none px-4 md:px-6 py-2.5 rounded-xl text-[10px] md:text-xs font-black uppercase tracking-widest transition-all {{ $activeTab === 'history' ? 'bg-brand text-white shadow-lg shadow-brand/20' : 'mkt-text-muted hover:mkt-text' }}">
            Riwayat Quotation
        </button>
    </div>

    @if($activeTab === 'calculator')
        <div class="animate-in fade-in slide-in-from-left-4 duration-500">
            {{-- Header --}}
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3 md:gap-4 mb-6 md:mb-8 pb-4 md:pb-6 border-b mkt-border">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-emerald-600 rounded-xl md:rounded-2xl flex items-center justify-center text-white text-xl md:text-2xl shadow-lg shadow-emerald-600/20 shrink-0">
                                            </div>
                    <div>
                        <h3 class="text-base md:text-xl font-black uppercase tracking-tighter mkt-text leading-tight italic">HPP & Price Calculator</h3>
                        <p class="text-[8px] md:text-[10px] font-bold mkt-text-muted uppercase tracking-widest">Simulasi Biaya Produksi & Harga Jual</p>
                    </div>
                </div>
                <div class="text-left md:text-right w-full md:w-auto">
                    <span class="bg-emerald-600/10 text-emerald-600 text-[8px] md:text-[10px] px-3 md:px-4 py-1.5 rounded-full font-black italic inline-block">REAL-TIME SIMULATION</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                {{-- INPUT SECTION --}}
                <div class="lg:col-span-2 space-y-8">
                    {{-- I. KOMPONEN BIAYA LANGSUNG --}}
                    <div>
                        <p class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.3em] mb-4 border-l-4 border-emerald-500 pl-3">I. Komponen Biaya Langsung (Bahan & Proses)</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Yarn Price --}}
                            <div>
                                <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Harga Benang (Rp/kg)</label>
                                <div class="relative group">
                                    <span class="absolute left-5 top-3.5 text-xs font-black text-emerald-500 transition-colors group-focus-within:text-emerald-400">Rp</span>
                                    <input type="text" 
                                        x-data="{ display: $wire.entangle('yarn_price') }"
                                        x-init="$watch('display', v => display = formatIDR(v))"
                                        x-on:input="display = formatIDR($event.target.value); $wire.set('yarn_price', stripFormatting($event.target.value))"
                                        x-bind:value="formatIDR(display)"
                                        class="w-full mkt-surface border-2 mkt-border rounded-xl pl-12 pr-5 py-3 font-bold text-sm mkt-text focus:border-emerald-500 outline-none transition-all"
                                        placeholder="0">
                                </div>
                            </div>
                            {{-- Chemical Price --}}
                            <div>
                                <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Biaya Obat/Kimia (Rp/kg)</label>
                                <div class="relative group">
                                    <span class="absolute left-5 top-3.5 text-xs font-black text-emerald-500">Rp</span>
                                    <input type="text" 
                                        x-data="{ display: $wire.entangle('chemical_price') }"
                                        x-init="$watch('display', v => display = formatIDR(v))"
                                        x-on:input="display = formatIDR($event.target.value); $wire.set('chemical_price', stripFormatting($event.target.value))"
                                        x-bind:value="formatIDR(display)"
                                        class="w-full mkt-surface border-2 mkt-border rounded-xl pl-12 pr-5 py-3 font-bold text-sm mkt-text focus:border-emerald-500 outline-none transition-all"
                                        placeholder="0">
                                </div>
                            </div>
                            {{-- Knitting Fee --}}
                            <div>
                                <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Ongkos Rajut/Knitting (Rp/kg)</label>
                                <div class="relative group">
                                    <span class="absolute left-5 top-3.5 text-xs font-black text-emerald-500">Rp</span>
                                    <input type="text" 
                                        x-data="{ display: $wire.entangle('knitting_fee') }"
                                        x-init="$watch('display', v => display = formatIDR(v))"
                                        x-on:input="display = formatIDR($event.target.value); $wire.set('knitting_fee', stripFormatting($event.target.value))"
                                        x-bind:value="formatIDR(display)"
                                        class="w-full mkt-surface border-2 mkt-border rounded-xl pl-12 pr-5 py-3 font-bold text-sm mkt-text focus:border-emerald-500 outline-none transition-all"
                                        placeholder="0">
                                </div>
                            </div>
                            {{-- Dyeing Fee --}}
                            <div>
                                <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Ongkos Dyeing/Finishing (Rp/kg)</label>
                                <div class="relative group">
                                    <span class="absolute left-5 top-3.5 text-xs font-black text-emerald-500">Rp</span>
                                    <input type="text" 
                                        x-data="{ display: $wire.entangle('dyeing_fee') }"
                                        x-init="$watch('display', v => display = formatIDR(v))"
                                        x-on:input="display = formatIDR($event.target.value); $wire.set('dyeing_fee', stripFormatting($event.target.value))"
                                        x-bind:value="formatIDR(display)"
                                        class="w-full mkt-surface border-2 mkt-border rounded-xl pl-12 pr-5 py-3 font-bold text-sm mkt-text focus:border-emerald-500 outline-none transition-all"
                                        placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- II. OVERHEAD & FAKTOR SUSUT --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 pt-4">
                        <div class="space-y-6">
                            <p class="text-[10px] font-black mkt-text-muted uppercase tracking-[0.3em] mb-4 border-l-4 border-mkt-border pl-3">II. Overhead & Energi</p>
                            <div>
                                <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Biaya Overhead (Rp/kg)</label>
                                <div class="relative group">
                                    <span class="absolute left-5 top-3.5 text-xs font-black mkt-text-muted">Rp</span>
                                    <input type="text" 
                                        x-data="{ display: $wire.entangle('overhead') }"
                                        x-init="$watch('display', v => display = formatIDR(v))"
                                        x-on:input="display = formatIDR($event.target.value); $wire.set('overhead', stripFormatting($event.target.value))"
                                        x-bind:value="formatIDR(display)"
                                        class="w-full mkt-surface border-2 mkt-border rounded-xl pl-12 pr-5 py-3 font-bold text-sm mkt-text focus:border-brand outline-none transition-all"
                                        placeholder="0">
                                </div>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <p class="text-[10px] font-black text-amber-500 uppercase tracking-[0.3em] mb-4 border-l-4 border-amber-500 pl-3">III. Faktor Susut (Waste %)</p>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Knitting (%)</label>
                                    <div class="relative group">
                                        <input type="number" wire:model.live="waste_knitting" class="w-full mkt-surface border-2 mkt-border rounded-xl px-5 py-3 font-bold text-sm mkt-text focus:border-amber-500 outline-none transition-all">
                                        <span class="absolute right-5 top-3.5 text-amber-500 font-bold">%</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Dyeing (%)</label>
                                    <div class="relative group">
                                        <input type="number" wire:model.live="waste_dyeing" class="w-full mkt-surface border-2 mkt-border rounded-xl px-5 py-3 font-bold text-sm mkt-text focus:border-amber-500 outline-none transition-all">
                                        <span class="absolute right-5 top-3.5 text-amber-500 font-bold">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- IV. MARGIN & PAJAK --}}
                    <div class="pt-4">
                        <p class="text-[10px] font-black text-rose-500 uppercase tracking-[0.3em] mb-4 border-l-4 border-rose-500 pl-3">IV. Margin Keuntungan & Pajak</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Target Margin (%)</label>
                                <div class="relative group">
                                    <input type="number" wire:model.live="margin" class="w-full mkt-surface border-2 mkt-border rounded-xl px-5 py-3 font-bold text-sm mkt-text focus:border-rose-500 outline-none transition-all">
                                    <span class="absolute right-5 top-3.5 text-rose-500 font-bold">%</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">PPN (%)</label>
                                <div class="relative group">
                                    <input type="number" wire:model.live="ppn" class="w-full mkt-surface border-2 mkt-border rounded-xl px-5 py-3 font-bold text-sm mkt-text focus:border-slate-500 outline-none transition-all">
                                    <span class="absolute right-5 top-3.5 text-slate-500 font-bold">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RESULT SUMMARY SECTION --}}
                <div class="bg-slate-900 rounded-2xl md:rounded-[2.5rem] p-5 md:p-8 text-white shadow-2xl relative overflow-hidden flex flex-col justify-between border border-white/5 mt-6 lg:mt-0">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-emerald-600/10 rounded-full -mr-20 -mt-20 blur-3xl"></div>
                    
                    <div class="relative z-10">
                        <h4 class="text-[10px] md:text-xs font-black uppercase tracking-widest text-emerald-400 mb-6 md:mb-8 flex items-center gap-2 italic">
                            <span class="w-3 md:w-4 h-1 bg-emerald-400"></span> Calculation Result
                        </h4>

                        <div class="space-y-6 md:space-y-8">
                            <div>
                                <p class="text-[8px] md:text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">HARGA POKOK PRODUKSI (MODAL)</p>
                                <h2 class="text-2xl md:text-3xl font-black text-white italic tracking-tighter leading-none">Rp {{ number_format($hpp, 0, ',', '.') }} <span class="text-[10px] md:text-xs text-slate-500">/kg</span></h2>
                            </div>

                            <div class="bg-white/5 p-4 md:p-6 rounded-xl md:rounded-3xl border border-white/10">
                                <p class="text-[8px] md:text-[9px] font-black uppercase text-emerald-400 tracking-widest mb-1">REKOMENDASI HARGA JUAL (NETT)</p>
                                <h2 class="text-3xl md:text-4xl font-black text-emerald-400 italic tracking-tighter leading-none">Rp {{ number_format($selling_price, 0, ',', '.') }}</h2>
                                <div class="flex items-center gap-2 mt-4 text-[10px] font-bold text-slate-400 uppercase">
                                    <span>Estimasi Profit:</span>
                                    <span class="text-emerald-400">Rp {{ number_format($profit_per_kg, 0, ',', '.') }} /kg</span>
                                </div>
                            </div>

                            <div class="space-y-3 pt-4">
                                <div class="flex justify-between items-center text-[11px] font-bold border-b border-white/5 pb-2">
                                    <span class="text-slate-500">Total Biaya Dasar</span>
                                    <span>Rp {{ number_format($basic_cost, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center text-[11px] font-bold border-b border-white/5 pb-2">
                                    <span class="text-slate-500">Beban Susut (Faktor Yield)</span>
                                    <span class="text-amber-500">x{{ number_format($waste_factor, 3) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative z-10 pt-10">
                        <button type="button" wire:click="openSaveModal" class="w-full bg-white text-slate-900 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-emerald-400 transition-all shadow-xl flex items-center justify-center gap-2">
                            SIMPAN SEBAGAI QUOTATION
                        </button>
                        <p class="text-[8px] text-slate-500 text-center mt-4 italic">*Hasil perhitungan ini adalah estimasi internal simulasi marketing.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- History Tab --}}
    @if($activeTab === 'history')
        <div class="animate-in fade-in slide-in-from-right-4 duration-500">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3 md:gap-4 mb-6 md:mb-8 pb-4 md:pb-6 border-b mkt-border">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-brand rounded-xl md:rounded-2xl flex items-center justify-center text-white text-xl md:text-2xl shadow-lg shadow-brand/20 shrink-0">
                                            </div>
                    <div>
                        <h3 class="text-base md:text-xl font-black uppercase tracking-tighter mkt-text leading-tight italic">Saved Quotations</h3>
                        <p class="text-[8px] md:text-[10px] font-bold mkt-text-muted uppercase tracking-widest">Daftar Penawaran Harga Yang Pernah Disimpan</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse(\App\Models\MarketingQuotation::where('user_id', auth()->id())->latest()->get() as $q)
                    <div class="mkt-surface border mkt-border rounded-[2rem] p-6 shadow-sm hover:shadow-xl transition-all relative overflow-hidden group">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <p class="text-[9px] font-black mkt-text uppercase tracking-widest leading-none mb-1">{{ $q->customer_name }}</p>
                                <h4 class="text-lg font-black mkt-text italic uppercase leading-tight">{{ $q->article_name }}</h4>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] font-bold text-slate-400 uppercase leading-none">{{ $q->created_at->format('d M Y') }}</p>
                            </div>
                        </div>

                        <div class="space-y-3 mb-6 bg-slate-50 dark:bg-slate-900/30 p-4 rounded-2xl">
                            <div class="flex justify-between items-center text-[11px] font-bold">
                                <span class="text-slate-500">Harga Pokok</span>
                                <span class="mkt-text italic">Rp {{ number_format($q->hpp, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm font-black border-t mkt-border pt-2">
                                <span class="text-emerald-600 italic uppercase">Jual (Nett)</span>
                                <span class="text-emerald-600">Rp {{ number_format($q->selling_price, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button wire:click="loadQuotation({{ $q->id }})" class="flex-1 bg-slate-900 text-white py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-brand transition-all flex items-center justify-center gap-2">
                                MUAT DATA
                            </button>
                            <button wire:click="deleteQuotation({{ $q->id }})" class="w-10 h-10 bg-red-100 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition-all flex items-center justify-center">
                                                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-20 text-center">
                        <div class="text-6xl mb-4"></div>
                        <p class="text-sm font-black mkt-text-muted uppercase tracking-widest italic">Belum ada quotation yang disimpan.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    {{-- SAVE MODAL --}}
    @if($showSaveModal)
        <div class="fixed inset-0 z-[120] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-md" wire:click="$set('showSaveModal', false)"></div>
            <div class="mkt-surface w-full max-w-md rounded-[2.5rem] shadow-2xl relative z-10 overflow-hidden border mkt-border animate-in zoom-in-95 duration-300">
                <div class="p-8">
                    <h3 class="text-xl font-black italic uppercase tracking-tighter mkt-text leading-none mb-2">Simpan Quotation</h3>
                    <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-widest mb-6">Lengkapi detail untuk identifikasi di riwayat</p>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Nama Pelanggan / Customer</label>
                            <input type="text" wire:model="customer_name" class="w-full mkt-surface border-2 mkt-border rounded-xl px-5 py-3 font-bold text-sm mkt-text focus:border-emerald-500 outline-none" placeholder="Contoh: PT. Sumber Makmur">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2">Nama Artikel / Item</label>
                            <input type="text" wire:model="article_name" class="w-full mkt-surface border-2 mkt-border rounded-xl px-5 py-3 font-bold text-sm mkt-text focus:border-emerald-500 outline-none" placeholder="Contoh: Cotton Combed 30s">
                        </div>
                    </div>

                    <div class="flex gap-3 mt-8">
                        <button wire:click="$set('showSaveModal', false)" class="flex-1 mkt-surface-alt mkt-text-muted py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:opacity-80 transition-all border mkt-border">BATAL</button>
                        <button wire:click="saveQuotation" class="flex-1 bg-emerald-600 text-white py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-emerald-700 shadow-lg shadow-emerald-600/20 transition-all">SIMPAN DATA</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
