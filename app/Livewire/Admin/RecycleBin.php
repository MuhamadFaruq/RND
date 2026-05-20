<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ArchivedOrder;

class RecycleBin extends Component
{
    use WithPagination;

    public $search = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function destroyPermanently($id)
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            $this->dispatch('show-error-toast', message: 'Hanya Super Admin yang bisa menghapus permanen.');
            return;
        }

        $archivedOrder = ArchivedOrder::findOrFail($id);
        $archivedOrder->delete(); // This actually hard deletes since ArchivedOrder doesn't use SoftDeletes

        $this->dispatch('show-toast', message: 'Data berhasil dihapus permanen dari sistem.', type: 'success');
    }

    public function render()
    {
        $query = ArchivedOrder::with('deleter')->latest();

        if ($this->search) {
            $query->where('sap_no', 'like', "%{$this->search}%")
                  ->orWhere('art_no', 'like', "%{$this->search}%")
                  ->orWhere('pelanggan', 'like', "%{$this->search}%");
        }

        return view('livewire.admin.recycle-bin', [
            'archivedOrders' => $query->paginate(15)
        ])->layout('layouts.app');
    }
}
