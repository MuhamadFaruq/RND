<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Division;
use App\Models\MarketingOrder; // Tambahkan import ini agar lebih rapi
use Livewire\WithPagination;
use Illuminate\Support\Str;

class DivisionManagement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $name, $description, $divisionId;
    public $search = '';
    public $isModalOpen = false;

    // Properti Modal Hapus - SEKARANG AMAN
    public $showDeleteModal = false;
    public $selectedDivisionId;
    public $selectedDivisionName;

    protected $rules = [
        'name' => 'required|min:2',
        'description' => 'nullable|string',
    ];

    public function render()
    {
        $divisions = Division::where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.division-management', [
            'divisions' => $divisions
        ])->layout('layouts.app');
    }

    public function confirmDelete($id, $name)
    {
        $this->selectedDivisionId = $id;
        $this->selectedDivisionName = $name;
        $this->showDeleteModal = true;
    }

    public function delete($id)
    {
        $div = Division::find($id);
        if ($div) {
            $name = $div->name;
            
            // Proteksi jika masih ada order aktif
            $isUsed = MarketingOrder::where('status', strtolower($name))->exists();
            if ($isUsed) {
                $this->dispatch('show-error-toast', message: "Gagal! Unit {$name} masih digunakan.");
                $this->showDeleteModal = false;
                return;
            }

            $div->delete();
            $this->showDeleteModal = false;
            session()->flash('message', "Unit {$name} berhasil dihapus.");
        }
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
        $this->resetValidation(); // Tambahkan ini agar pesan error merah hilang saat buka modal baru
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
        $this->validate();
        
        Division::updateOrCreate(
            ['id' => $this->divisionId], 
            [
                'name' => strtoupper($this->name),
                'description' => $this->description,
                'slug' => Str::slug($this->name), 
            ]
        );

        session()->flash('message', $this->divisionId ? 'Divisi diperbarui!' : 'Divisi ditambahkan!');
        $this->closeModal();
    }
}