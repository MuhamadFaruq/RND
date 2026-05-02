<div>
    @if(!$isProcessing)
        {{-- TAMPILAN LIST PERMINTAAN --}}
        <div class="space-y-4">
            <div class="mb-6">
                <h1 class="text-3xl font-black mkt-text italic uppercase">Duniatex <span class="text-violet-600">Execution</span></h1>
                <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-[0.2em]">Divisi: Relax-Dryer</p>
            </div>

            <div class="mkt-surface p-6 rounded-[2.5rem] shadow-sm border mkt-border flex justify-between items-center group italic hover:border-violet-300 transition-all">
                <div class="flex items-center gap-6">
                    <div class="bg-violet-50 text-violet-600 w-14 h-14 rounded-2xl flex items-center justify-center font-black text-xl shadow-sm">
                        ☁️
                    </div>
                    <div class="text-left">
                        <span class="text-[10px] font-black text-violet-600 uppercase tracking-widest">#{{ $job->sap_no }}</span>
                        <h4 class="text-xl font-black mkt-text leading-none uppercase">{{ $job->art_no }}</h4>
                        <p class="text-[10px] font-bold mkt-text-muted uppercase mt-1">{{ $job->pelanggan }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-8">
                    <div class="text-right">
                        <p class="text-[9px] font-black mkt-text-muted uppercase leading-none">Target Berat</p>
                        <p class="text-base font-black mkt-text italic">{{ number_format($job->kg_target, 1) }} KG</p>
                    </div>
                    
                    <button wire:click="showOrderDetail({{ $job->id }})" 
                        class="bg-slate-900 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase hover:bg-blue-600 transition-all shadow-lg shadow-slate-200">
                        DETAIL & PROSES
                    </button>
                </div>
            </div>
        </div>

    @else
        {{-- TAMPILAN FORM INPUT (SETELAH KLIK PROSES) --}}
        <div class="mkt-surface p-8 rounded-[2.5rem] shadow-xl border mkt-border max-w-4xl mx-auto">
            <div class="flex justify-between items-start mb-8">
                <div class="flex items-center gap-4">
                    <div class="bg-violet-100 text-violet-600 p-3 rounded-2xl shadow-sm cursor-pointer" wire:click="$set('isProcessing', false)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black mkt-text italic uppercase leading-none">Relax Dryer</h2>
                        <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-widest mt-1">#{{ $selectedJob->sap_no }} - {{ $selectedJob->art_no }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[9px] font-black mkt-text-muted uppercase">Status Saat Ini</p>
                    <p class="text-violet-600 font-black italic uppercase text-sm">Proses Pengeringan</p>
                </div>
            </div>

            <form wire:submit.prevent="save" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Operator</label>
                        <input type="text" wire:model="operator" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 focus:border-violet-400 focus:outline-none font-bold mkt-text">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Tanggal</label>
                        <input type="date" wire:model="tanggal" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 focus:border-violet-400 focus:outline-none font-bold mkt-text">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Chemical</label>
                        <input type="text" wire:model="chemical" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 focus:border-violet-400 focus:outline-none font-bold mkt-text">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Handfeel</label>
                        <select wire:model="handfeel" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 focus:border-violet-400 focus:outline-none font-bold mkt-text">
                            <option value="">Pilih Handfeel</option>
                            <option value="Soft">Soft</option>
                            <option value="Hard">Hard</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">No. Mesin</label>
                        <select wire:model="mesin" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 focus:border-violet-400 focus:outline-none font-bold mkt-text">
                            <option value="">Pilih Mesin</option>
                            <option value="RD-01">RD-01</option>
                            <option value="RD-02">RD-02</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Overfeed (%)</label>
                        <input type="number" wire:model="overfeed" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 focus:border-violet-400 focus:outline-none font-bold mkt-text">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Temperatur (°C)</label>
                        <input type="number" wire:model="temperatur" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 focus:border-violet-400 focus:outline-none font-bold mkt-text">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Speed (M/Min)</label>
                        <input type="number" wire:model="speed" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 focus:border-violet-400 focus:outline-none font-bold mkt-text">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Hasil Lebar</label>
                        <input type="text" wire:model="hasil_lebar" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 focus:border-violet-400 focus:outline-none font-bold mkt-text">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Hasil Gramasi</label>
                        <input type="text" wire:model="hasil_gramasi" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 focus:border-violet-400 focus:outline-none font-bold mkt-text">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Shrinkage (V x H)</label>
                    <input type="number" step="0.01" wire:model="shrinkage" class="w-full mkt-surface border-2 mkt-border rounded-xl px-4 py-3 focus:border-violet-400 focus:outline-none font-bold mkt-text">
                </div>

                <button type="submit" class="w-full bg-violet-600 text-white py-4 rounded-2xl font-black text-xs uppercase hover:bg-black transition-all shadow-xl shadow-violet-200 mt-4">
                    SIMPAN & TERUSKAN KE COMPACTOR 🚀
                </button>
            </form>
        </div>
    @endif
</div>