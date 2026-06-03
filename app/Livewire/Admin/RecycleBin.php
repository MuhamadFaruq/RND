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

    public function restoreOrder($id)
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            $this->dispatch('show-error-toast', message: 'Hanya Super Admin yang bisa mengembalikan data.');
            return;
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
                $archivedOrder = ArchivedOrder::findOrFail($id);
                
                $originalData = $archivedOrder->original_data;
                $productionLogs = $archivedOrder->production_logs;

                // Cek apakah ID aslinya sudah dipakai (walau jarang terjadi)
                // Jika tidak dipakai, kita gunakan ID aslinya agar relasi lain tetap terjaga (jika ada).
                $order = new \App\Models\MarketingOrder();
                $order->fill($originalData);
                $order->id = $originalData['id'];
                $order->save();

                if (!empty($productionLogs)) {
                    foreach ($productionLogs as $logData) {
                        $log = new \App\Models\ProductionActivity();
                        $log->fill($logData);
                        $log->id = $logData['id'];
                        $log->marketing_order_id = $order->id;
                        $log->save();
                    }
                }

                $archivedOrder->delete();

                // Catat ke log
                \App\Models\ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'RESTORE_ORDER',
                    'division' => 'ADMIN_SYSTEM',
                    'art_no' => $order->art_no,
                    'description' => "Super Admin memulihkan order {$order->art_no} dari Cold Storage.",
                ]);
            });

            $this->dispatch('show-toast', message: 'Data pesanan berhasil dipulihkan (Restore).', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('show-error-toast', message: 'Gagal memulihkan data: ' . $e->getMessage());
        }
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
