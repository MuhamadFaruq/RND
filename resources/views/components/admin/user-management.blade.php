<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-black text-slate-800 uppercase italic">User Management</h2>
                <button wire:click="openModal" class="bg-[#ED1C24] text-white px-6 py-2 rounded-xl font-bold shadow-lg hover:bg-red-700 transition">
                    + TAMBAH USER
                </button>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm mb-6">
                <input wire:model.live="search" type="text" placeholder="Cari nama atau email..." class="w-full border-slate-200 rounded-xl">
            </div>

            @if (session()->has('message'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-xl">
                    {{ session('message') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="p-4 text-[10px] font-black uppercase text-slate-400">Nama</th>
                            <th class="p-4 text-[10px] font-black uppercase text-slate-400">Email</th>
                            <th class="p-4 text-[10px] font-black uppercase text-slate-400 text-center">Role</th>
                            <th class="p-4 text-[10px] font-black uppercase text-slate-400 text-center">Divisi</th>
                            <th class="p-4 text-[10px] font-black uppercase text-slate-400 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($users as $user)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-4 font-bold text-slate-800">{{ $user->name }}</td>
                            <td class="p-4 text-slate-600">{{ $user->email }}</td>
                            <td class="p-4 text-center">
                                <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase bg-blue-100 text-blue-600">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="p-4 text-center text-xs font-bold uppercase text-slate-500">
                                {{ $user->division->name ?? 'N/A' }}
                            </td>
                            <td class="p-4 text-right">
                                <button wire:click="edit({{ $user->id }})" class="text-blue-600 hover:underline mr-3">Edit</button>
                                <button wire:click="delete({{ $user->id }})" onclick="confirm('Hapus user ini?') || event.stopImmediatePropagation()" class="text-red-600 hover:underline">Hapus</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4 border-t">{{ $users->links() }}</div>
            </div>

            @if($isModalOpen)
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50">
                <div class="bg-white w-full max-w-md p-8 rounded-[2rem] shadow-2xl">
                    <h3 class="text-xl font-black uppercase italic mb-6">{{ $userId ? 'Edit User' : 'User Baru' }}</h3>
                    <form wire:submit.prevent="save" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Nama Lengkap</label>
                            <input type="text" wire:model="name" class="w-full rounded-xl border-slate-200">
                            @error('name') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Email</label>
                            <input type="email" wire:model="email" class="w-full rounded-xl border-slate-200">
                            @error('email') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Role</label>
                                <select wire:model="role" class="w-full rounded-xl border-slate-200">
                                    <option value="">Pilih Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="marketing">Marketing</option>
                                    <option value="operator">Operator</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Divisi</label>
                                <select wire:model="division_id" class="w-full rounded-xl border-slate-200">
                                    <option value="">Pilih Divisi</option>
                                    @foreach($divisions as $div)
                                        <option value="{{ $div->id }}">{{ $div->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Password {{ $userId ? '(Kosongkan jika tidak ganti)' : '' }}</label>
                            <input type="password" wire:model="password" class="w-full rounded-xl border-slate-200">
                            @error('password') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" wire:click="closeModal" class="px-6 py-2 text-slate-400 font-bold">Batal</button>
                            <button type="submit" class="bg-slate-900 text-white px-8 py-2 rounded-xl font-bold">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>