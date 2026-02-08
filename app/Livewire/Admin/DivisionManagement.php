<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Division;
use Livewire\WithPagination;

class DivisionManagement extends Component
{
    use WithPagination;

    public $name, $description, $divisionId;
    public $search = '';
    public $isModalOpen = false;

    public function render()
    {
        $divisions = Division::where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.division-management', [
            'divisions' => $divisions
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
        $this->description = '';
        $this->divisionId = null;
    }

    public function edit($id)
    {
        $division = Division::findOrFail($id);
        $this->divisionId = $division->id;
        $this->name = $division->name;
        $this->description = $division->description;
        $this->isModalOpen = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:divisions,name,' . $this->divisionId,
        ]);

        Division::updateOrCreate(['id' => $this->divisionId], [
            'name' => $this->name,
            'description' => $this->description,
        ]);

        session()->flash('message', $this->divisionId ? 'Divisi berhasil diperbarui.' : 'Divisi baru berhasil ditambahkan.');
        $this->closeModal();
    }

    public function delete($id)
    {
        Division::find($id)->delete();
        session()->flash('message', 'Divisi berhasil dihapus.');
    }
}