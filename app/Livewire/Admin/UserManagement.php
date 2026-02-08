<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Division;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagement extends Component
{
    use WithPagination;

    // Properti Form
    public $name, $email, $role, $division_id, $password, $userId;
    public $search = '';
    public $isModalOpen = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'role' => 'required|in:admin,marketing,operator',
        'division_id' => 'nullable|exists:divisions,id',
    ];

    public function render()
    {
        $users = User::with('division')
            ->where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

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

        User::updateOrCreate(['id' => $this->userId], [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'division_id' => $this->division_id ?: null,
            'password' => $this->password ? Hash::make($this->password) : User::find($this->userId)->password,
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
        User::find($id)->delete();
        session()->flash('message', 'User berhasil dihapus.');
    }
}