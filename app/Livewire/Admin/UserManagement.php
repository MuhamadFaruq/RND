<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Division;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagement extends Component
{
    use WithPagination;

    // Pastikan kedua fungsi ini bisa dipicu dari frontend
    protected $listeners = [
        'delete-confirmed' => 'delete',
        'delete-division-confirmed' => 'deleteDivision'
    ];

    public $name, $email, $role, $division_id, $password, $userId;
    public $search = '';
    public $isModalOpen = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'role' => 'required', 
        'division_id' => 'nullable|exists:divisions,id',
    ];

    public function render()
    {
        $users = User::with('division')
            ->where(function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('role', 'like', '%' . $this->search . '%');
            })
            // UBAH: Urutkan berdasarkan ID terkecil (biasanya admin) 
            // atau tetap latest() tapi cek halaman berikutnya.
            ->orderBy('id', 'asc') 
            ->paginate();

        return view('livewire.admin.user-management', [
            'users' => $users,
            'divisions' => Division::all()
        ])->layout('layouts.app');
    }

    public function openModal()
    {
        $this->resetFields();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    public function resetFields()
    {
        $this->name = '';
        $this->email = '';
        $this->role = '';
        $this->division_id = '';
        $this->password = '';
        $this->userId = null;
    }

    public function save()
    {
        $validationRules = $this->rules;
        $validationRules['email'] = ['required', 'email', Rule::unique('users', 'email')->ignore($this->userId)];
        
        if (!$this->userId) {
            $validationRules['password'] = 'required|min:8';
        }

        $this->validate($validationRules);

        $action = $this->userId ? 'UPDATE_USER' : 'CREATE_USER';

        // LOGIKA PENENTUAN ROLE & DIVISION_ID
        // Jika role yang dipilih berasal dari tabel divisi, kita isi juga division_id-nya
        $selectedDivision = Division::where('name', $this->role)->first();
        $finalDivisionId = $selectedDivision ? $selectedDivision->id : $this->division_id;

        $user = User::updateOrCreate(['id' => $this->userId], [
            'name' => $this->name,
            'email' => $this->email,
            'role' => strtolower($this->role), // Simpan dalam huruf kecil untuk konsistensi role
            'division_id' => $finalDivisionId ?: null, 
            'password' => $this->password ? Hash::make($this->password) : User::find($this->userId)->password,
        ]);

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'division' => 'ADMIN_SYSTEM',
            'sap_no' => '-',
            'details' => "{$action}: {$user->name} ({$user->role})",
        ]);

        session()->flash('message', $this->userId ? 'User berhasil diperbarui.' : 'User berhasil dibuat.');
        $this->closeModal();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->division_id = $user->division_id;
        $this->password = ''; // Kosongkan password saat edit
        $this->isModalOpen = true;
    }

    public function delete($id)
    {
        if (auth()->user()->role !== 'super-admin') {
            $this->dispatch('show-toast', message: 'Hanya Super-Admin yang boleh menghapus user.', type: 'error');
            return;
        }

        if ($id === auth()->id()) {
            $this->dispatch('show-toast', message: 'Gagal! Tidak boleh menghapus akun sendiri.', type: 'error');
            return;
        }

        $user = User::find($id);
        if ($user) {
            $userName = $user->name;
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'DELETE_USER',
                'division' => 'ADMIN_SYSTEM',
                'sap_no' => '-',
                'details' => "Menghapus User: {$userName}",
            ]);

            $user->delete();
            $this->dispatch('show-success-toast', message: 'User berhasil dihapus.');
        }
    }

    public function deleteDivision($id)
    {
        // Pastikan ID tidak null
        if (!$id) {
            $this->dispatch('show-toast', message: 'ID Divisi tidak ditemukan!', type: 'error');
            return;
        }

        if (auth()->user()->role !== 'super-admin') {
            $this->dispatch('show-toast', message: 'Akses Ditolak!', type: 'error');
            return;
        }

        $division = \App\Models\Division::find($id);
        
        if ($division) {
            $divName = $division->name;
            $userCount = \App\Models\User::where('division_id', $id)->count();

            // Eksekusi Cascade
            \App\Models\User::where('division_id', $id)->delete();
            $division->delete();

            // Activity Log
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'DELETE_DIVISION_CASCADE',
                'division' => 'ADMIN_SYSTEM',
                'details' => "MENGHAPUS UNIT: {$divName} & {$userCount} personil.",
            ]);

            $this->resetPage();
            $this->dispatch('show-success-toast', message: "Unit {$divName} dan {$userCount} personil dihapus.");
        }
    }
}