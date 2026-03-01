<div class="min-h-screen w-full bg-slate-900 text-white font-sans italic flex flex-col">
    <div class="p-4 md:p-8 flex-grow container mx-auto">
        
        {{-- HEADER: Mengikuti gaya Monitoring --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 border-b border-slate-800 pb-8 gap-6">
            <div>
                <h1 class="text-4xl md:text-6xl font-black italic tracking-tighter uppercase text-red-600 leading-none">
                    Users <span class="text-white">Control</span>
                </h1>
                <p class="text-slate-400 font-bold tracking-widest uppercase text-[10px] md:text-xs mt-3 italic">Duniatex Group - Access Management</p>
            </div>
            <button wire:click="openModal" class="w-full md:w-auto bg-red-600 text-white px-8 py-3 rounded-2xl font-black uppercase italic shadow-2xl hover:bg-red-700 transition transform hover:scale-105 tracking-tighter">
                + Tambah Personel
            </button>
        </div>

        {{-- SEARCH BAR DARK --}}
        <div class="bg-slate-800 p-2 rounded-2xl border border-slate-700 mb-8 shadow-xl">
            <input wire:model.live="search" type="text" placeholder="CARI NAMA ATAU EMAIL PERSONEL..." 
                class="w-full bg-transparent border-none text-emerald-400 font-black uppercase italic placeholder-slate-600 focus:ring-0">
        </div>

        @if (session()->has('message'))
            <div class="bg-emerald-900/50 border-l-4 border-emerald-500 text-emerald-400 p-4 mb-8 rounded-r-2xl font-bold uppercase text-xs italic animate-pulse">
                {{ session('message') }}
            </div>
        @endif

        {{-- TABLE DARK MODE --}}
        <div class="bg-slate-800 rounded-[2rem] md:rounded-[3rem] overflow-hidden shadow-2xl border border-slate-700">
            <div class="overflow-x-auto">
                <table class="w-full text-left italic font-bold min-w-[1000px]">
                    <thead class="bg-slate-900/50 border-b border-slate-700">
                        <tr class="text-[10px] font-black uppercase text-slate-500 tracking-widest">
                            <th class="p-6">Identitas Personel</th>
                            <th class="p-6 text-center">Hak Akses</th>
                            <th class="p-6 text-center">Unit Divisi</th>
                            <th class="p-6 text-center">Aktivitas Terakhir</th> {{-- Kolom Baru --}}
                            <th class="p-6 text-right">Otoritas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @foreach($users as $user)
                        <tr class="hover:bg-slate-700/30 transition duration-300">
                            <td class="p-6">
                                <div class="flex flex-col">
                                    <span class="text-white text-base font-black uppercase tracking-tighter">{{ $user->name }}</span>
                                    <span class="text-[10px] text-slate-500 font-normal lowercase not-italic">{{ $user->email }}</span>
                                </div>
                            </td>
                            <td class="p-6 text-center">
                                <span class="px-4 py-1 rounded-lg text-[10px] font-black uppercase bg-blue-900/50 text-blue-400 border border-blue-800">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="p-6 text-center text-xs font-black uppercase text-slate-400">
                                {{ $user->division->name ?? 'MASTER ADMIN' }}
                            </td>
                            
                            {{-- Kolom Last Login menggunakan kolom last_seen dari DB Anda --}}
                            <td class="p-6 text-center">
                                @if($user->last_seen)
                                    <span class="text-emerald-400 font-mono text-[10px]">
                                        {{ \Carbon\Carbon::parse($user->last_seen)->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-slate-600 font-mono text-[10px]">NEVER ACTIVE</span>
                                @endif
                            </td>

                            <td class="p-6">
                                <div class="flex gap-3 justify-end items-center">
                                    @if(auth()->user()->role === 'super-admin' && auth()->id() !== $user->id)
                                        <a href="{{ route('admin.impersonate', $user->id) }}" 
                                        class="px-4 py-2 bg-amber-900/50 text-amber-500 rounded-xl border border-amber-800 hover:bg-amber-500 hover:text-white transition-all text-[10px] font-black uppercase tracking-tighter">
                                            🎭 Masuk
                                        </a>
                                    @endif
                                    
                                    <button wire:click="edit({{ $user->id }})" 
                                            class="p-2 bg-slate-900 text-slate-400 rounded-xl hover:bg-white hover:text-black transition-all">
                                        ✏️
                                    </button>
                                    @if(auth()->user()->role === 'super-admin')
                                        @if(auth()->user()->role === 'super-admin' && $user->id !== auth()->id())
                                        <button type="button" 
                                                onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}')"
                                                class="p-2 bg-red-600/10 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition-all">
                                            🗑️
                                        </button>
                                    @else
                                            {{-- Tampilan Gembok untuk Akun Sendiri --}}
                                            <span class="p-2 bg-slate-800/50 text-slate-500 rounded-lg cursor-not-allowed">
                                                🔒
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MODAL DARK --}}
        @if($isModalOpen)
        <div class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-[2.5rem] bg-slate-900 border border-white/10 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-red-600 to-transparent"></div>

                    <div class="px-8 pt-8 pb-6">
                        <div class="mb-8">
                            <h3 class="text-3xl font-black italic uppercase tracking-tighter text-white" id="modal-title">
                                {{ $userId ? 'Update' : 'Register' }} <span class="text-red-600">Personel</span>
                            </h3>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mt-1">
                                Duniatex Group - Access Management System
                            </p>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Nama Lengkap</label>
                                <input type="text" wire:model="name" 
                                    class="w-full bg-slate-950 border border-white/5 rounded-2xl px-5 py-4 text-white font-bold focus:border-red-600 focus:ring-0 transition-all placeholder:text-slate-700" 
                                    placeholder="Contoh: Budi Santoso">
                                @error('name') <span class="text-red-500 text-[10px] font-bold uppercase mt-1 ml-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Email Corporate</label>
                                <input type="email" wire:model="email" 
                                    class="w-full bg-slate-950 border border-white/5 rounded-2xl px-5 py-4 text-white font-bold focus:border-red-600 focus:ring-0 transition-all placeholder:text-slate-700" 
                                    placeholder="name@duniatex.com">
                                @error('email') <span class="text-red-500 text-[10px] font-bold uppercase mt-1 ml-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Otoritas / Role</label>
                                    <select wire:model="role" 
                                        class="w-full bg-slate-950 border border-white/5 rounded-2xl px-5 py-4 text-white font-bold focus:border-red-600 focus:ring-0 transition-all uppercase text-xs">
                                        <option value="">Pilih Otoritas</option>
                                        @foreach($divisions as $div)
                                            <option value="{{ strtolower($div->name) }}">{{ strtoupper($div->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Password</label>
                                    <input type="password" wire:model="password" 
                                        class="w-full bg-slate-950 border border-white/5 rounded-2xl px-5 py-4 text-white font-bold focus:border-red-600 focus:ring-0 transition-all placeholder:text-slate-700" 
                                        placeholder="********">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-950/50 px-8 py-6 flex flex-row-reverse gap-3 border-t border-white/5">
                        <button type="button" wire:click="save" 
                            class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-xl font-black italic uppercase tracking-widest text-xs transition-all shadow-[0_0_20px_rgba(220,38,38,0.2)]">
                            {{ $userId ? 'SIMPAN PERUBAHAN' : 'DAFTARKAN PERSONEL' }}
                        </button>
                        <button type="button" wire:click="closeModal" 
                            class="bg-transparent hover:bg-white/5 text-slate-500 hover:text-white px-8 py-3 rounded-xl font-black italic uppercase tracking-widest text-xs transition-all">
                            BATAL
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
</div>

{{-- Letakkan di baris paling akhir file --}}
@push('scripts')
<script>
    // 1. Fungsi untuk Hapus 1 Orang Saja
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'HAPUS PERSONEL?',
            text: "Yakin ingin menghapus " + name + "?",
            icon: 'warning',
            showCancelButton: true,
            background: '#0f172a',
            color: '#ffffff',
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'YA, HAPUS!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Memanggil fungsi public function delete($id) di UserManagement.php
                @this.delete(id); 
            }
        })
    }

    // 2. Fungsi untuk Hapus 1 Divisi (Cascade)
    window.addEventListener('confirm-division-deletion', event => {
        Swal.fire({
            title: 'PERINGATAN KERAS!',
            text: "Menghapus unit ini akan MENGHAPUS SEMUA AKUN di dalamnya. Data tidak bisa dikembalikan!",
            icon: 'error',
            showCancelButton: true,
            background: '#0f172a',
            color: '#ffffff',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#334155',
            confirmButtonText: 'YA, HAPUS SEMUANYA!',
            cancelButtonText: 'BATAL'
        }).then((result) => {
            if (result.isConfirmed) {
                // Gunakan @this.call agar langsung menembak fungsi di UserManagement.php
                Livewire.dispatch('delete-division-confirmed', { id: event.detail.id });
            }
        })
    });
</script>
@endpush