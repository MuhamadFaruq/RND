<?php

namespace App\Livewire\Operator;

use Livewire\Component;
use App\Models\ProductionActivity;
use App\Models\MarketingOrder;
use App\Services\ProductionService;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KnittingForm extends Component
{
    // Deklarasikan semua properti
    public $operator_name,$sap_no, $tanggal, $no_mesin, $type_mesin, $gauge_inch, $jml_feeder, $jml_jarum, $konstruksi_greige;
    public $lebar, $gramasi, $kg, $roll;
    public $benang_1, $benang_2, $benang_3, $benang_4;
    public $yl_1, $yl_2, $yl_3, $yl_4;
    public $note, $produksi_per_day;

    public $order_detail = null;

    public function mount($orderId = null, $sap = null)
    {
        $this->tanggal = now()->format('Y-m-d');
        
        // LOGIKA EDIT: Jika ada orderId, ambil data dari production_activities
        if ($orderId) {
            $activity = ProductionActivity::where('marketing_order_id', $orderId)
                ->where('division_name', 'knitting')
                ->latest()
                ->first();

            if ($activity) {
                $this->sap_no = $activity->marketingOrder->sap_no ?? '';
                $this->kg     = $activity->kg;
                $this->roll   = $activity->roll;
                
                $tech = $activity->technical_data;
                
                // Mengisi properti dari technical_data JSON
                $this->operator_name    = $tech['nama_input'] ?? ''; 
                if (!$this->operator_name) {
                    $this->operator_name = $activity->operator_name ?? auth()->user()->name;
                }
                $this->no_mesin         = $tech['no_mesin'] ?? '';
                $this->type_mesin       = $tech['type_mesin'] ?? '';
                $this->gauge_inch       = $tech['gauge_inch'] ?? '';
                $this->jml_feeder       = $tech['jml_feeder'] ?? 0;
                $this->jml_jarum        = $tech['jml_jarum'] ?? 0;
                $this->lebar            = $tech['lebar'] ?? 0;
                $this->gramasi          = $tech['gramasi'] ?? 0;
                $this->note             = $tech['note'] ?? '';
                $this->produksi_per_day = $tech['produksi_per_day'] ?? 0;
                
                for ($i = 1; $i <= 4; $i++) {
                    $this->{'benang_' . $i} = $tech['benang_' . $i] ?? '';
                    $this->{'yl_' . $i}     = $tech['yl_' . $i] ?? 0;
                }

                // Panggil detail artikel (Pelanggan, Warna, dll)
                $this->updatedSapNo($this->sap_no, true); // Tambahkan parameter true untuk bypass status check
                return;
            }
        }

        // LOGIKA INPUT BARU: Ambil SAP dari Route atau Query String
        $targetSap = $sap ?? request()->query('sap');
        if ($targetSap) {
            $this->sap_no = $targetSap;
            $this->updatedSapNo($targetSap);
        }
    }

    public function updatedSapNo($value, $isEdit = false)
    {
        $query = MarketingOrder::where('sap_no', $value);
        
        // Jika bukan sedang edit, hanya boleh ambil yang statusnya masih 'knitting'
        if (!$isEdit) {
            $query->where('status', 'knitting');
        }

        $order = $query->first();

        if ($order) {
            $this->order_detail = [
                'id'        => $order->id,
                'art_no'    => $order->art_no,
                'pelanggan' => $order->pelanggan,
                'warna'     => $order->warna,
            ];

            // LOGIKA PENTING:
            // Jika sedang INPUT BARU (bukan edit), ambil lebar/gramasi dari target marketing.
            // Jika sedang EDIT, biarkan nilai lebar/gramasi tetap menggunakan hasil input operator yang sudah di-load di mount().
            if (!$isEdit) {
                $this->lebar = $order->target_lebar;
                $this->gramasi = $order->target_gramasi;
            }
            
        } else {
            $this->order_detail = null;
        }
    }

    public function save()
    {
        $this->validate([
            'sap_no'   => 'required|exists:marketing_orders,sap_no',
            'no_mesin' => 'required',
            'kg'       => 'required|numeric',
            'roll'     => 'required|numeric',
        ]);

        $marketingOrder = MarketingOrder::where('sap_no', $this->sap_no)->first();

        try {
            // Panggil ProductionService untuk memproses Knitting
            $productionService = app(ProductionService::class);
            $productionService->processKnitting(
                $marketingOrder->id,
                auth()->id(),
                $this->kg,
                $this->roll,
                [
                    'tanggal'          => $this->tanggal,
                    'no_mesin'         => $this->no_mesin,
                    'type_mesin'       => $this->type_mesin,
                    'gauge_inch'       => $this->gauge_inch,
                    'jml_feeder'       => $this->jml_feeder,
                    'jml_jarum'        => $this->jml_jarum,
                    'lebar'            => $this->lebar,
                    'gramasi'          => $this->gramasi,
                    'benang_1'         => $this->benang_1,
                    'benang_2'         => $this->benang_2,
                    'benang_3'         => $this->benang_3,
                    'benang_4'         => $this->benang_4,
                    'yl_1'             => $this->yl_1,
                    'yl_2'             => $this->yl_2,
                    'yl_3'             => $this->yl_3,
                    'yl_4'             => $this->yl_4,
                    'note'             => $this->note,
                    'produksi_per_day' => $this->produksi_per_day,
                    'nama_input'       => $this->operator_name,
                ]
            );

            session()->flash('message', 'Data berhasil diperbarui!');
            return redirect()->route('operator.logbook');
        } catch (\Exception $e) {
            $this->dispatch('show-error-toast', message: 'Gagal menyimpan data Knitting: ' . $e->getMessage());
        }
    }
    
    public function render()
    {
        return view('livewire.operator.knitting-form', [
            // Filter: Hanya ambil order yang masih 'knitting' (baru dari marketing)
            'orders' => \App\Models\MarketingOrder::where('status', OrderStatus::KNITTING->value)
                        ->latest()
                        ->paginate(10)
        ]);
    }
}