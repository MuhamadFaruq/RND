{{-- resources/views/livewire/operator/qe-form.blade.php --}}

<div x-data="{ showSpec: false }">
    <div class="py-12 mkt-bg min-h-screen font-sans italic tracking-tighter text-left mkt-text">
        <div class="max-w-4xl mx-auto px-4">
            <div class="mkt-surface p-8 rounded-[2.5rem] shadow-sm border mkt-border">

                <div class="flex items-center gap-4 mb-8">
                    <div class="bg-violet-600/80 p-3 rounded-2xl text-white shadow-lg shadow-violet-600/20">
                        🛡️
                    </div>
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-tighter mkt-text">QE (Final Description)</h2>
                        <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-widest">Quality Engineering & Validation #{{ $order->art_no }}</p>
                    </div>
                </div>

                {{-- TRACEABILITY PREVIEW --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">

                    {{-- History Card --}}
                    <div class="mkt-surface-alt border mkt-border p-6 rounded-[2rem] italic overflow-y-auto max-h-[160px]">
                        <div class="flex items-center justify-between mb-3 border-b mkt-border pb-2">
                            <h4 class="text-[9px] font-black uppercase mkt-text-muted tracking-widest">Jejak Produksi</h4>
                            <button @click="showSpec = true" type="button" class="text-[8px] font-black text-indigo-500 uppercase hover:underline">Lihat Detail Spek</button>
                        </div>
                        @forelse($productionHistory as $hist)
                            <div class="flex justify-between items-center mb-2 border-b border-dashed mkt-border pb-1">
                                <div>
                                    <p class="text-[9px] font-black mkt-text uppercase leading-none">
                                        {{ $hist->division_name }}</p>
                                    <p class="text-[8px] mkt-text-muted uppercase opacity-70">
                                        {{ $hist->operator->name ?? 'Operator' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[9px] font-black text-violet-400 italic tabular-nums leading-none">
                                        {{ number_format($hist->kg, 1) }} KG</p>
                                    <p class="text-[7px] mkt-text-muted uppercase mt-0.5">
                                        {{ $hist->created_at->format('d/m H:i') }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-[9px] mkt-text-muted text-center py-4 uppercase">Belum ada history.</p>
                        @endforelse
                    </div>
                </div>

                {{-- PIPELINE ERROR ALERT --}}
                @if(!empty($pipelineErrors))
                    <div class="mb-6 bg-amber-950/50 backdrop-blur-md border-2 border-amber-500 rounded-2xl p-5 space-y-2">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-amber-400 text-lg">⚠️</span>
                            <p class="text-amber-400 font-black uppercase text-xs tracking-widest">Pipeline Produksi Belum Lengkap</p>
                        </div>
                        @foreach($pipelineErrors as $err)
                            <div class="flex items-start gap-2">
                                <span class="text-indigo-600 mt-0.5 shrink-0">▸</span>
                                <p class="text-amber-300 text-xs font-bold">{{ $err }}</p>
                            </div>
                        @endforeach
                        <p class="text-indigo-600 text-[10px] font-black uppercase mt-3 pt-3 border-t border-amber-800">QE Final tidak dapat dilanjutkan sampai semua proses di atas selesai.</p>
                    </div>
                @endif

                <form wire:submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Operator QE</label>
                            <input type="text" wire:model="operator" placeholder="MASUKKAN Nama Operator QE"
                                class="w-full mkt-surface-alt border mkt-border rounded-xl px-4 py-3 font-bold mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none placeholder-slate-400">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Fabric Name</label>
                            <input type="text" wire:model="fabric_name" placeholder="MASUKKAN Nama Kain (Fabric)"
                                class="w-full mkt-surface-alt border mkt-border rounded-xl px-4 py-3 font-bold mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none placeholder-slate-400">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t mkt-border">
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Lebar (Int)</label>
                            <input type="number" wire:model="lebar" placeholder="MASUKKAN Lebar"
                                class="w-full mkt-surface-alt border mkt-border rounded-xl px-4 py-3 font-bold text-violet-400 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none placeholder-slate-400">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Gramasi (Int)</label>
                            <input type="number" wire:model="gramasi" placeholder="MASUKKAN Gramasi"
                                class="w-full mkt-surface-alt border mkt-border rounded-xl px-4 py-3 font-bold text-violet-400 focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none placeholder-slate-400">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Shrinkage (Int)</label>
                            <input type="number" wire:model="shrinkage" placeholder="MASUKKAN Shrinkage"
                                class="w-full mkt-surface-alt border mkt-border rounded-xl px-4 py-3 font-bold mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none placeholder-slate-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black mkt-text-muted uppercase mb-2 ml-1">Note (Catatan Final)</label>
                        <textarea wire:model="note" rows="4"
                            class="w-full mkt-surface-alt border mkt-border rounded-2xl px-4 py-3 font-bold mkt-text focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all outline-none italic placeholder-slate-400"
                            placeholder="Tuliskan keterangan akhir kain di sini..."></textarea>
                    </div>

                    <button type="submit"
                        class="w-full bg-violet-600/80 backdrop-blur-md text-white py-5 rounded-2xl font-black uppercase text-xs shadow-lg transition-all hover:bg-violet-600 hover:scale-105 group">
                        Simpan Deskripsi Final QE <span class="group-hover:ml-2 transition-all">🚀</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    {{-- MODAL SPECIFICATION --}}
    <div x-show="showSpec" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-950/95 backdrop-blur-md"
         @click.away="showSpec = false"
         style="display: none;">
        
        <div class="mkt-surface w-full max-w-3xl rounded-[3rem] border mkt-border shadow-2xl relative overflow-hidden italic flex flex-col max-h-[85vh]">
            {{-- Header Modal --}}
            <div class="px-10 pt-10 pb-6 border-b mkt-border mkt-surface-alt sticky top-0 z-10 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-600/80 rounded-2xl flex items-center justify-center text-white text-xl shadow-lg shadow-indigo-500/20">🛡️</div>
                    <div>
                        <h3 class="text-lg font-black uppercase mkt-text leading-none">Order Tracking Detail</h3>
                        <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-widest mt-1">Artikel #{{ $order->art_no }} • Full Technical Specification</p>
                    </div>
                </div>
                <button @click="showSpec = false" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-400 hover:bg-indigo-600 hover:text-white transition-all text-xl">✕</button>
            </div>
            
            {{-- Tab Navigation --}}
            <div class="px-10 py-4 mkt-surface-alt border-b mkt-border flex items-center gap-3 overflow-x-auto no-scrollbar">
                <button wire:click="$set('activeDetailTab', 'marketing')" 
                    class="flex-none px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all {{ $activeDetailTab === 'marketing' ? 'bg-indigo-600 text-white shadow-xl scale-105' : 'mkt-surface mkt-text hover:bg-slate-100' }}">
                    Marketing Req.
                </button>
                @foreach($productionHistory as $index => $history)
                    <button wire:click="$set('activeDetailTab', 'step_{{ $index }}')" 
                        class="flex-none px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all {{ $activeDetailTab === 'step_'.$index ? 'bg-emerald-600 text-white shadow-xl scale-105' : 'mkt-surface mkt-text hover:bg-slate-100' }}">
                        {{ $history['division_name'] }}
                    </button>
                @endforeach
            </div>
            
            {{-- Content Area --}}
            <div class="overflow-y-auto flex-1 p-10 custom-scrollbar bg-transparent">
                {{-- TAB: MARKETING --}}
                @if($activeDetailTab === 'marketing')
                    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-2">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white text-lg">🛡️</div>
                            <div>
                                <h3 class="text-sm font-black uppercase mkt-text leading-none">Order Specification</h3>
                                <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-widest mt-1">Final Validation Specs #{{ $order->art_no }}</p>
                            </div>
                        </div>

                        <div class="p-6 mkt-surface-alt border mkt-border rounded-3xl relative overflow-hidden">
                            @if($order->is_urgent)
                                <div class="absolute top-0 right-0 bg-indigo-600 text-white text-[8px] font-black px-4 py-1 uppercase tracking-tighter transform rotate-12 translate-x-2 -translate-y-1 shadow-lg">URGENT</div>
                            @endif
                            <p class="text-[9px] font-black uppercase text-indigo-500 mb-2 tracking-[0.2em]">Customer / Brand</p>
                            <p class="text-xl font-black mkt-text uppercase">{{ $order->pelanggan }}</p>
                            <p class="text-[10px] font-bold mkt-text-muted mt-1 uppercase">Keperluan: {{ $order->keperluan ?? '-' }}</p>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-6 py-6 border-y mkt-border">
                            <div>
                                <p class="text-[8px] font-black uppercase mkt-text-muted mb-1 tracking-widest">Artikel</p>
                                <p class="text-sm font-black text-yellow-500 uppercase">{{ $order->art_no }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black uppercase mkt-text-muted mb-1 tracking-widest">Warna</p>
                                <p class="text-sm font-black mkt-text uppercase">{{ $order->warna }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black uppercase mkt-text-muted mb-1 tracking-widest">Target Produksi</p>
                                <p class="text-sm font-black text-indigo-600 italic">
                                    {{ number_format($order->kg_target, 1) }} KG
                                </p>
                                <p class="text-[9px] mkt-text-muted uppercase font-bold">{{ $order->roll_target }} ROLL</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6 py-6 border-b mkt-border">
                            <div>
                                <p class="text-[8px] font-black uppercase mkt-text-muted mb-1 tracking-widest">Material</p>
                                <p class="text-[11px] font-bold mkt-text uppercase leading-tight">{{ $order->material }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black uppercase mkt-text-muted mb-1 tracking-widest">Konstruksi Greige</p>
                                <p class="text-[11px] font-bold mkt-text uppercase leading-tight">{{ $order->konstruksi_greige ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-6 border-b mkt-border">
                            <div>
                                <p class="text-[8px] font-black uppercase mkt-text-muted mb-1 tracking-widest">T. Lebar</p>
                                <p class="text-[11px] font-bold text-emerald-400 uppercase italic">{{ $order->target_lebar ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black uppercase mkt-text-muted mb-1 tracking-widest">T. Gramasi</p>
                                <p class="text-[11px] font-bold text-emerald-400 uppercase italic">{{ $order->target_gramasi ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black uppercase mkt-text-muted mb-1 tracking-widest">Handfeel</p>
                                <p class="text-[11px] font-bold mkt-text uppercase">{{ $order->handfeel ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black uppercase mkt-text-muted mb-1 tracking-widest">B / B</p>
                                <p class="text-[11px] font-bold mkt-text uppercase">{{ $order->belah_bulat ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="p-6 mkt-surface-alt border mkt-border rounded-3xl">
                            <p class="text-[9px] font-black uppercase text-indigo-600 mb-3 tracking-widest border-b mkt-border pb-2 flex items-center gap-2">
                                <span class="text-lg">🧪</span> R&D RECOMMENDATION
                            </p>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <p class="text-[8px] font-black uppercase mkt-text-muted mb-1 tracking-widest">GSM Greige</p>
                                    <p class="text-[11px] font-bold mkt-text uppercase">{{ $order->rnd_gramasi_greige ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[8px] font-black uppercase mkt-text-muted mb-1 tracking-widest">No. Mesin</p>
                                    <p class="text-[11px] font-bold mkt-text uppercase">{{ $order->rnd_mesin_rajut ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[8px] font-black uppercase mkt-text-muted mb-1 tracking-widest">Jenis Mesin</p>
                                    <p class="text-[11px] font-bold mkt-text uppercase">{{ $order->rnd_jenis_mesin_rajut ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 pt-4">
                            <div>
                                <p class="text-[8px] font-black uppercase mkt-text-muted mb-1 tracking-widest">Spesifikasi Benang</p>
                                <p class="text-[11px] font-black text-emerald-400 uppercase leading-relaxed italic">{{ $order->benang }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black uppercase mkt-text-muted mb-1 tracking-widest">Treatment Khusus</p>
                                <p class="text-[10px] font-bold mkt-text-muted uppercase italic">{{ $order->treatment_khusus ?? 'Tidak ada instruksi khusus.' }}</p>
                            </div>
                            @if($order->keterangan_artikel)
                            <div>
                                <p class="text-[8px] font-black uppercase mkt-text-muted mb-1 tracking-widest">Keterangan Artikel</p>
                                <p class="text-[10px] font-bold mkt-text-muted uppercase">{{ $order->keterangan_artikel }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- TAB: PRODUCTION STEPS --}}
                @foreach($productionHistory as $index => $history)
                    @if($activeDetailTab === 'step_'.$index)
                        <div class="space-y-6 animate-in fade-in slide-in-from-bottom-2">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white text-lg">⚙️</div>
                                    <div>
                                        <h3 class="text-sm font-black uppercase mkt-text leading-none">{{ $history['division_name'] }} Technical Data</h3>
                                        <p class="text-[9px] font-bold mkt-text-muted uppercase tracking-widest mt-1">
                                            Operator: {{ $history['operator']['name'] ?? 'System' }} | {{ \Carbon\Carbon::parse($history['created_at'])->format('d M Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-[9px] font-black mkt-text-muted uppercase mb-1">Output</p>
                                    <p class="text-xl font-black text-emerald-500 leading-none">{{ number_format($history['kg'], 1) }} KG</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mkt-surface-alt p-6 rounded-3xl border mkt-border">
                                @foreach($history['technical_data'] as $key => $value)
                                    @if($value && !in_array($key, ['nama_input']))
                                        <div class="space-y-1">
                                            <p class="text-[8px] font-black uppercase mkt-text-muted tracking-widest opacity-60">
                                                {{ ucwords(str_replace('_', ' ', $key)) }}
                                            </p>
                                            <p class="text-[11px] font-bold mkt-text uppercase leading-tight">{{ is_array($value) ? json_encode($value) : $value }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Footer Modal --}}
            <div class="px-10 py-8 mkt-surface border-t mkt-border flex justify-end">
                <button @click="showSpec = false" class="px-12 py-4 bg-slate-800 text-white rounded-2xl font-black uppercase text-xs tracking-[0.2em] hover:bg-indigo-600 hover:shadow-xl transition-all active:scale-95 border border-white/5">
                    Tutup Detail
                </button>
            </div>
        </div>
    </div>
</div>