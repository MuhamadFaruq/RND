<?php

namespace App\Livewire\Marketing;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MarketingOrder;

class OrderList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    // Reset halaman ke nomor 1 jika user mencari sesuatu
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $orders = MarketingOrder::query()
            ->when($this->search, function($query) {
                $query->where('sap_no', 'like', '%' . $this->search . '%')
                      ->orWhere('pelanggan', 'like', '%' . $this->search . '%')
                      ->orWhere('art_no', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.marketing.order-list', [
            'orders' => $orders
        ]);
    }

    public function deleteOrder($id)
    {
        MarketingOrder::findOrFail($id)->delete();
        session()->flash('message', 'Order berhasil dihapus.');
    }
}