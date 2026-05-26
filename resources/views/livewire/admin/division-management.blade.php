<div class="min-h-screen w-full mkt-bg mkt-text font-sans italic flex flex-col transition-colors duration-300">
    <div class="p-4 md:p-8 flex-grow container mx-auto">
        
        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 border-b mkt-border pb-8 gap-6 text-left">
            <div>
                <h1 class="text-4xl md:text-6xl font-black italic tracking-tighter uppercase text-brand-600 leading-none">
                    Division <span class="mkt-text">Master</span>
                </h1>
                <p class="mkt-text-muted font-bold tracking-widest uppercase text-xs mt-3 italic">Duniatex Group - Factory Configuration</p>
            </div>
            <button wire:click="openModal" class="bg-brand-600 text-white px-8 py-3 rounded-2xl font-black uppercase italic shadow-2xl hover:bg-brand-700 transition transform hover:scale-105">
                + UNIT BARU
            </button>
        </div>

        {{-- SEARCH BAR DARK --}}
        <div class="mkt-surface p-2 rounded-2xl border mkt-border mb-8 shadow-xl">
            <input wire:model.live="search" type="text" placeholder="CARI NAMA UNIT..." 
                class="w-full bg-transparent border-none text-emerald-400 font-black uppercase italic placeholder-slate-600 focus:ring-0">
        </div>

        @if (session()->has('message'))
            <div class="bg-emerald-900/50 border-l-4 border-emerald-500 text-emerald-400 p-4 mb-8 rounded-r-2xl font-bold uppercase text-xs italic text-left">
                {{ session('message') }}
            </div>
        @endif

        {{-- GRID DIVISI --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
            @forelse($divisions as $div)
                <div class="mkt-surface p-5 md:p-8 rounded-[2rem] md:rounded-[3rem] border mkt-border shadow-2xl flex justify-between items-center group hover:border-brand-600 transition-all duration-300 text-left animate-in fade-in duration-300">
                    <div class="flex flex-col">
                        <h3 class="text-lg md:text-2xl font-black uppercase italic tracking-tighter mkt-text">{{ $div->name }}</h3>
                        <p class="text-[9px] md:text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1 md:mt-2 leading-relaxed">
                            {{ $div->description ?: 'INTEGRATED UNIT' }}
                        </p>
                    </div>
                    <div class="flex gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300 shrink-0">
                        <button wire:click="edit({{ $div->id }})" class="p-2.5 md:p-3 mkt-surface mkt-text rounded-xl md:rounded-2xl hover:bg-brand-600 hover:text-white transition-colors border mkt-border shadow-sm flex items-center justify-center" title="Edit Unit">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>
                        <button type="button" 
                                wire:click="confirmDelete({{ $div->id }}, '{{ $div->name }}')" 
                                class="p-2.5 md:p-3 bg-red-500/10 text-red-500 rounded-xl md:rounded-2xl hover:bg-red-600 hover:text-white transition-all shadow-sm flex items-center justify-center" title="Hapus Unit">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 flex flex-col items-center justify-center border-2 border-dashed mkt-border rounded-[3rem]">
                    <span class="text-4xl mb-6">📂</span>
                    <h3 class="text-xl font-black uppercase italic mkt-text-muted tracking-tighter">Data Tidak Ditemukan</h3>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $divisions->links() }}
        </div>

        {{-- MODAL KONFIRMASI HAPUS --}}
        @if($showDeleteModal)
        <div class="fixed inset-0 z-[110] flex items-center justify-center bg-black/90 backdrop-blur-sm p-4">
            <div class="mkt-surface border mkt-border p-10 rounded-[2.5rem] max-w-lg w-full text-center shadow-2xl animate-in zoom-in duration-300">
                <div class="w-24 h-24 bg-brand-600/10 border-4 border-brand-600/20 text-brand-600 rounded-full flex items-center justify-center mx-auto mb-8">
                    <span class="text-5xl font-black italic">!</span>
                </div>
                
                <h3 class="text-3xl font-black uppercase italic mkt-text mb-4 tracking-tighter">Konfirmasi Hapus</h3>
                
                <p class="mkt-text-muted text-sm font-bold uppercase mb-10 leading-relaxed tracking-wide italic">
                    Apakah Anda yakin ingin menghapus unit <span class="text-brand-600">{{ $selectedDivisionName }}</span>?<br>
                    Data ini akan dihapus permanen dari sistem RND.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button wire:click="delete({{ $selectedDivisionId }})" 
                        class="bg-brand-600 text-white px-10 py-4 rounded-2xl font-black uppercase italic shadow-xl shadow-brand-900/40 hover:bg-black transition-all transform hover:scale-105">
                        YA, HAPUS DATA
                    </button>
                    <button wire:click="$set('showDeleteModal', false)" 
                        class="mkt-surface-alt mkt-text-muted border mkt-border px-10 py-4 rounded-2xl font-black uppercase italic hover:mkt-text hover:bg-slate-200 dark:hover:bg-slate-700 transition-all transform hover:scale-105">
                        BATAL
                    </button>
                </div>
            </div>
        </div>
        @endif

        {{-- MODAL TAMBAH/EDIT --}}
        @if($isModalOpen)
        <div class="fixed inset-0 z-[100] overflow-y-auto">
            <div class="fixed inset-0 bg-black/80 backdrop-blur-md transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-md transform overflow-hidden rounded-[2.5rem] mkt-surface border mkt-border shadow-2xl transition-all">
                    <div class="p-10 text-left">
                        <h3 class="text-3xl font-black uppercase italic mb-8 mkt-text tracking-tighter">
                            {{ $divisionId ? 'Update' : 'Register' }} <span class="text-brand-600">Unit</span>
                        </h3>
                        <form wire:submit.prevent="save" class="space-y-6">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-500 mb-2 tracking-[0.2em]">Nama Divisi / Unit</label>
                                <input type="text" wire:model="name" class="w-full mkt-input border mkt-border rounded-2xl px-5 py-4 text-lg font-black mkt-text focus:border-brand-600 focus:ring-0 uppercase italic placeholder:mkt-text-muted">
                                @error('name') <span class="mkt-text-muted text-[10px] font-bold mt-2 block uppercase italic">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-500 mb-2 tracking-[0.2em]">Deskripsi Operasional</label>
                                <textarea wire:model="description" class="w-full mkt-input border mkt-border rounded-2xl px-5 py-4 text-sm font-bold mkt-text focus:border-brand-600 focus:ring-0 placeholder:mkt-text-muted" rows="3"></textarea>
                            </div>
                            <div class="flex flex-row-reverse gap-4 pt-4">
                                <button type="submit" class="bg-brand-600 text-white px-10 py-3 rounded-xl font-black uppercase italic hover:bg-brand-700 transition">SIMPAN UNIT</button>
                                <button type="button" wire:click="closeModal" class="text-xs font-black uppercase mkt-text-muted italic hover:mkt-text transition">BATAL</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>