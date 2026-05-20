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
    public $operator_name,$artikelNo, $sap_no, $tanggal, $no_mesin, $type_mesin, $gauge_inch, $jml_feeder, $jml_jarum, $konstruksi_greige;
    public $lebar, $gramasi, $kg, $roll;
    public $benang_1, $benang_2, $benang_3, $benang_4;
    public $benang_1_lot, $benang_2_lot, $benang_3_lot, $benang_4_lot;
    public $benang_1_percent, $benang_2_percent, $benang_3_percent, $benang_4_percent;
    public $yl_1, $yl_2, $yl_3, $yl_4;
    public $note, $produksi_per_day;
    public $kgDeviation = false;
    public $rollDeviation = false;
    public $rnd_gramasi_greige, $rnd_mesin_rajut, $rnd_jenis_mesin_rajut;

    protected $listeners = ['submitForm'];

    public $order_detail = null;
    public $productionHistory = [];
    public $activeDetailTab = 'marketing';

    public function mount($artikel = null, $orderId = null)
    {
        $this->tanggal = now()->format('Y-m-d');
        
        // LOGIKA EDIT: Jika ada orderId, ambil data dari production_activities
        if (is_numeric($orderId)) {
            $activity = ProductionActivity::where('marketing_order_id', $orderId)
                ->where('division_name', 'knitting')
                ->latest()
                ->first();

            if ($activity) {
                $this->artikelNo = $activity->marketingOrder->art_no ?? $activity->marketingOrder->sap_no ?? '';
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
                $this->jml_feeder       = $tech['jml_feeder'] ?? '';
                $this->jml_jarum        = $tech['jml_jarum'] ?? '';
                $this->lebar            = $tech['lebar'] ?? '';
                $this->gramasi          = $tech['gramasi'] ?? '';
                $this->note             = $tech['note'] ?? '';
                $this->produksi_per_day = $tech['produksi_per_day'] ?? '';
                $this->rnd_gramasi_greige = $tech['rnd_gramasi_greige'] ?? $activity->marketingOrder->rnd_gramasi_greige ?? '';
                $this->rnd_mesin_rajut    = $tech['rnd_mesin_rajut'] ?? $activity->marketingOrder->rnd_mesin_rajut ?? '';
                $this->rnd_jenis_mesin_rajut = $tech['rnd_jenis_mesin_rajut'] ?? $activity->marketingOrder->rnd_jenis_mesin_rajut ?? '';
                
                for ($i = 1; $i <= 4; $i++) {
                    $this->{'benang_' . $i} = $tech['benang_' . $i] ?? '';
                    $this->{'benang_' . $i . '_lot'} = $tech['benang_' . $i . '_lot'] ?? '';
                    $this->{'benang_' . $i . '_percent'} = $tech['benang_' . $i . '_percent'] ?? '';
                    $this->{'yl_' . $i}     = $tech['yl_' . $i] ?? '';
                }

                // Panggil detail artikel (Pelanggan, Warna, dll)
                $this->updatedArtikelNo($this->artikelNo, true); 
                return;
            }
        }

        // LOGIKA INPUT BARU: Ambil Artikel dari Route atau Query String (mendukung 'artikel' atau legacy 'sap')
        $targetIdentifier = $artikel ?? request()->query('artikel') ?? request()->query('sap');
        if ($targetIdentifier) {
            $this->artikelNo = $targetIdentifier;
            $this->updatedArtikelNo($targetIdentifier);
        }
    }

    public function updatedArtikelNo($value, $isEdit = false)
    {
        $repo = app(\App\Repositories\OrderRepository::class);
        $order = $repo->findByIdentifier($value);

        // Jika bukan sedang edit, cek apakah statusnya masih 'knitting'
        if ($order && !$isEdit && $order->status !== 'knitting') {
            $order = null;
        }

        if ($order) {
            $this->order_detail = [
                'id'          => $order->id,
                'art_no'      => $order->art_no,
                'sap_no'      => $order->sap_no,
                'pelanggan'   => $order->pelanggan,
                'warna'       => $order->warna,
                'material'    => $order->material,
                'kg_target'   => $order->kg_target,
                'roll_target' => $order->roll_target,
                'benang'      => $order->benang,
                // Tambahan Field Lengkap
                'keterangan'  => $order->keterangan_artikel,
                'keperluan'   => $order->keperluan,
                'konstruksi'  => $order->konstruksi_greige,
                'kelompok'    => $order->kelompok_kain,
                'target_lebar' => $order->target_lebar,
                'target_gramasi' => $order->target_gramasi,
                'handfeel'    => $order->handfeel,
                'treatment'   => $order->treatment_khusus,
                'belah_bulat' => $order->belah_bulat,
                'urgent'      => $order->is_urgent,
                // RND Info
                'rnd_gramasi' => $order->rnd_gramasi_greige,
                'rnd_mesin'   => $order->rnd_mesin_rajut,
                'rnd_jenis'   => $order->rnd_jenis_mesin_rajut,
            ];

            $this->sap_no = $order->sap_no;
            $this->rnd_gramasi_greige = $order->rnd_gramasi_greige;
            $this->rnd_mesin_rajut = $order->rnd_mesin_rajut;
            $this->rnd_jenis_mesin_rajut = $order->rnd_jenis_mesin_rajut;

            // Load Production History for Traceability in Modal
            $this->productionHistory = ProductionActivity::with('operator')
                ->where('marketing_order_id', $order->id)
                ->orderBy('created_at', 'asc')
                ->get()->toArray();
            
            // LOGIKA PENTING:
            // Jika sedang INPUT BARU (bukan edit), ambil lebar/gramasi dari target marketing.
            // Jika sedang EDIT, biarkan nilai lebar/gramasi tetap menggunakan hasil input operator yang sudah di-load di mount().
            if (!$isEdit) {
                $this->lebar = '';
                $this->gramasi = '';
            }
            
        } else {
            $this->order_detail = null;
        }
    }

    public function updatedKg($value)
    {
        $this->checkDeviation();
    }

    public function updatedRoll($value)
    {
        $this->checkDeviation();
    }

    protected function checkDeviation()
    {
        if ($this->order_detail) {
            $kgTarget = $this->order_detail['kg_target'];
            $rollTarget = $this->order_detail['roll_target'];
            
            if ($kgTarget > 0 && is_numeric($this->kg)) {
                $this->kgDeviation = abs($this->kg - $kgTarget) / $kgTarget > 0.1;
            }
            
            if ($rollTarget > 0 && is_numeric($this->roll)) {
                $this->rollDeviation = abs($this->roll - $rollTarget) / $rollTarget > 0.1;
            }
        }
    }

    public function save()
    {
        $this->validate([
            'artikelNo'        => 'required',
            'operator_name'    => 'required|min:3',
            'tanggal'          => 'required|date',
            'no_mesin'         => 'required',
            'type_mesin'       => 'required',
            'gauge_inch'       => 'required',
            'jml_feeder'       => 'required|numeric',
            'jml_jarum'        => 'required|numeric',
            'lebar'            => 'required|numeric',
            'gramasi'          => 'required|numeric',
            'kg'               => 'required|numeric|min:0.1',
            'roll'             => 'required|numeric|min:1',
            'rnd_gramasi_greige' => 'required',
            'rnd_mesin_rajut'    => 'required',
            'rnd_jenis_mesin_rajut' => 'required',
        ]);

        if ($this->kgDeviation || $this->rollDeviation) {
            $this->dispatch('show-alert', [
                'type' => 'warning',
                'title' => 'Deviasi Terdeteksi!',
                'text' => 'Input KG atau Roll menyimpang lebih dari 10% dari target. Apakah Anda yakin ingin melanjutkan?',
                'showCancelButton' => true,
                'confirmButtonText' => 'Ya, Lanjutkan',
                'cancelButtonText' => 'Periksa Kembali',
                'callback' => 'submitForm'
            ]);
            return;
        }

        $this->submitForm();
    }

    public function submitForm()
    {
        $repo = app(\App\Repositories\OrderRepository::class);
        $marketingOrder = $repo->findByIdentifier($this->artikelNo);

        try {
            $service = app(\App\Services\ProductionService::class);
            
            // PENTING: Update data R&D pada MarketingOrder
            $marketingOrder->update([
                'rnd_gramasi_greige' => $this->rnd_gramasi_greige,
                'rnd_mesin_rajut'    => $this->rnd_mesin_rajut,
                'rnd_jenis_mesin_rajut' => $this->rnd_jenis_mesin_rajut,
            ]);

            $service->processKnitting(
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
                    'benang_1_lot'     => $this->benang_1_lot,
                    'benang_1_percent' => $this->benang_1_percent,
                    'benang_2'         => $this->benang_2,
                    'benang_2_lot'     => $this->benang_2_lot,
                    'benang_2_percent' => $this->benang_2_percent,
                    'benang_3'         => $this->benang_3,
                    'benang_3_lot'     => $this->benang_3_lot,
                    'benang_3_percent' => $this->benang_3_percent,
                    'benang_4'         => $this->benang_4,
                    'benang_4_lot'     => $this->benang_4_lot,
                    'benang_4_percent' => $this->benang_4_percent,
                    'yl_1'             => $this->yl_1,
                    'yl_2'             => $this->yl_2,
                    'yl_3'             => $this->yl_3,
                    'yl_4'             => $this->yl_4,
                    'note'             => $this->note,
                    'produksi_per_day' => $this->produksi_per_day,
                    'nama_input'       => $this->operator_name,
                    'rnd_gramasi_greige' => $this->rnd_gramasi_greige,
                    'rnd_mesin_rajut'    => $this->rnd_mesin_rajut,
                    'rnd_jenis_mesin_rajut' => $this->rnd_jenis_mesin_rajut,
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