<div class="min-h-screen w-full mkt-bg mkt-text font-sans italic flex flex-col transition-colors duration-300">
    <div class="p-4 md:p-8 flex-grow container mx-auto">
        
        {{-- HEADER: Mengikuti gaya Monitoring --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 border-b mkt-border pb-8 gap-6">
            <div>
                <h1 class="text-4xl md:text-6xl font-black italic tracking-tighter uppercase text-red-600 leading-none">
                    Users <span class="mkt-text">Control</span>
                </h1>
                <p class="mkt-text-muted font-bold tracking-widest uppercase text-[10px] md:text-xs mt-3 italic">Duniatex Group - Access Management</p>
            </div>
            <button wire:click="openModal" class="w-full md:w-auto bg-red-600 text-white px-8 py-3 rounded-2xl font-black uppercase italic shadow-2xl hover:bg-red-700 transition transform hover:scale-105 tracking-tighter">
                + Tambah Personel
            </button>
        </div>

        <div class="mkt-surface p-1 rounded-3xl border mkt-border mb-10 shadow-lg flex items-center transition-all focus-within:ring-2 focus-within:ring-red-500/20">
            <div class="pl-6 pr-3">
                <svg class="w-5 h-5 mkt-text-muted opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input wire:model.live="search" type="text" placeholder="Cari nama atau email personel..." 
                class="w-full bg-transparent border-none mkt-text font-black uppercase italic py-4 placeholder-slate-400 dark:placeholder-slate-600 focus:ring-0 text-sm tracking-widest">
        </div>

        @if (session()->has('message'))
            <div class="bg-emerald-900/50 border-l-4 border-emerald-500 text-emerald-400 p-4 mb-8 rounded-r-2xl font-bold uppercase text-xs italic animate-pulse">
                {{ session('message') }}
            </div>
        @endif

        <div class="mkt-surface rounded-[2.5rem] overflow-hidden shadow-2xl border mkt-border transition-all">
            <div class="overflow-x-auto">
                <table class="w-full text-left italic font-bold min-w-[1000px]">
                    <thead>
                        <tr class="mkt-surface-alt text-[9px] font-black uppercase mkt-text-muted tracking-[0.2em] border-b mkt-border">
                            <th class="px-8 py-6">Identitas Personel</th>
                            <th class="px-8 py-6 text-center">Hak Akses</th>
                            <th class="px-8 py-6 text-center">Unit Divisi</th>
                            <th class="px-8 py-6 text-center">Aktivitas</th>
                            <th class="px-8 py-6 text-right">Otoritas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                        @foreach($users as $user)
                        <tr class="hover:mkt-surface-alt/50 transition-colors border-b mkt-border last:border-0">
                            <td class="px-8 py-7">
                                <div class="flex flex-col">
                                    <span class="text-base font-black mkt-text leading-tight mb-0.5 tracking-tight">{{ $user->name }}</span>
                                    <span class="text-[10px] mkt-text-muted font-bold lowercase opacity-70">{{ $user->email }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-7 text-center">
                                <span class="px-5 py-2 rounded-2xl text-[9px] font-black uppercase tracking-widest bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 shadow-sm inline-block min-w-[120px]">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-8 py-7 text-center">
                                <span class="text-[11px] font-black mkt-text uppercase tracking-tighter opacity-80">{{ $user->division->name ?? 'MASTER ADMIN' }}</span>
                            </td>
                            <td class="px-8 py-7 text-center">
                                @if($user->last_seen)
                                    <div class="flex flex-col items-center">
                                        <span class="text-emerald-500 font-mono text-[11px] font-bold">{{ \Carbon\Carbon::parse($user->last_seen)->diffForHumans() }}</span>
                                        <span class="text-[8px] mkt-text-muted uppercase font-black tracking-widest mt-0.5">Online Status</span>
                                    </div>
                                @else
                                    <span class="text-[10px] mkt-text-muted font-black uppercase opacity-30 italic">Offline</span>
                                @endif
                            </td>

                            <td class="px-8 py-7">
                                <div class="flex gap-4 justify-end items-center">
                                    @if(auth()->user()->role === 'super-admin' && auth()->id() !== $user->id)
                                        <a href="{{ route('admin.impersonate', $user->id) }}" 
                                        class="px-4 py-2.5 bg-amber-500/10 text-amber-600 dark:text-amber-500 rounded-2xl border border-amber-500/20 hover:bg-amber-500 hover:text-white transition-all text-[9px] font-black uppercase tracking-widest shadow-sm">
                                            Masuk
                                        </a>
                                    @endif
                                    
                                    <button wire:click="editUser({{ $user->id }})" class="p-3 mkt-surface-alt mkt-text rounded-2xl border mkt-border hover:border-blue-500 hover:text-blue-500 transition-all shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>

                                    @if(auth()->id() !== $user->id)
                                        <button onclick="confirmDelete({{ $user->id }})" class="p-3 mkt-surface-alt text-red-500 rounded-2xl border mkt-border hover:bg-red-500 hover:text-white transition-all shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    @else
                                        <div class="p-3 mkt-surface-alt mkt-text-muted rounded-2xl border mkt-border opacity-30 cursor-not-allowed">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        </div>
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
            <div class="fixed inset-0 bg-black/80 backdrop-blur-md transition-opacity"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-[2.5rem] mkt-surface border mkt-border text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-red-600 to-transparent"></div>

                    <div class="px-8 pt-8 pb-6">
                        <div class="mb-8">
                            <h3 class="text-3xl font-black italic uppercase tracking-tighter mkt-text" id="modal-title">
                                {{ $userId ? 'Update' : 'Register' }} <span class="text-red-600">Personel</span>
                            </h3>
                            <p class="text-[10px] font-bold mkt-text-muted uppercase tracking-[0.2em] mt-1">
                                Duniatex Group - Access Management System
                            </p>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-[10px] font-black mkt-text-muted uppercase tracking-widest mb-2 ml-1">Nama Lengkap</label>
                                <input type="text" wire:model="name" 
                                    class="w-full mkt-input border mkt-border rounded-2xl px-5 py-4 mkt-text font-bold focus:border-red-600 focus:ring-0 transition-all placeholder:mkt-text-muted" 
                                    placeholder="Contoh: Budi Santoso">
                                @error('name') <span class="text-red-500 text-[10px] font-bold uppercase mt-1 ml-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-black mkt-text-muted uppercase tracking-widest mb-2 ml-1">Email Corporate</label>
                                <input type="email" wire:model="email" 
                                    class="w-full mkt-input border mkt-border rounded-2xl px-5 py-4 mkt-text font-bold focus:border-red-600 focus:ring-0 transition-all placeholder:mkt-text-muted" 
                                    placeholder="name@duniatex.com">
                                @error('email') <span class="text-red-500 text-[10px] font-bold uppercase mt-1 ml-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black mkt-text-muted uppercase tracking-widest mb-2 ml-1">Otoritas / Role</label>
                                    <select wire:model="role" 
                                        class="w-full mkt-input border mkt-border rounded-2xl px-5 py-4 mkt-text font-bold focus:border-red-600 focus:ring-0 transition-all uppercase text-xs">
                                        <option value="">Pilih Otoritas</option>
                                        @foreach($divisions as $div)
                                            <option value="{{ strtolower($div->name) }}">{{ strtoupper($div->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black mkt-text-muted uppercase tracking-widest mb-2 ml-1">Password</label>
                                    <input type="password" wire:model="password" 
                                        class="w-full mkt-input border mkt-border rounded-2xl px-5 py-4 mkt-text font-bold focus:border-red-600 focus:ring-0 transition-all placeholder:mkt-text-muted" 
                                        placeholder="********">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mkt-bg px-8 py-6 flex flex-row-reverse gap-3 border-t mkt-border">
                        <button type="button" wire:click="save" 
                            class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-xl font-black italic uppercase tracking-widest text-xs transition-all shadow-[0_0_20px_rgba(220,38,38,0.2)]">
                            {{ $userId ? 'SIMPAN PERUBAHAN' : 'DAFTARKAN PERSONEL' }}
                        </button>
                        <button type="button" wire:click="closeModal" 
                            class="bg-transparent hover:bg-slate-200/50 mkt-text-muted hover:mkt-text px-8 py-3 rounded-xl font-black italic uppercase tracking-widest text-xs transition-all border mkt-border">
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


</script>
@endpush