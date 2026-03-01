<?php
namespace App\Livewire\Operator;

use Livewire\Component;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DyeingForm extends Component
{
    public $sap_no;
    public $order;

    // 1. Definisikan semua variabel agar bisa dibaca oleh wire:model
    public $gramasi;
    public $tanggal_proses;
    public $jenis_mesin;
    public $no_mesin;
    public $warna;
    public $kode_warna;
    public $dye_system;
    public $treatment;

    public function mount($sap)
    {
        $this->sap = $sap;
        $this->order = MarketingOrder::where('sap_no', $sap)->first();

        if (!$this->order) {
            abort(404, 'Data Order Tidak Ditemukan');
        }

        // Default value
        $this->tanggal_proses = now()->format('Y-m-d');
        $this->warna = $this->order->warna; // Otomatis ambil warna dari Marketing
    }

    // 2. Tambahkan Fungsi saveDyeing yang tadinya "Hilang"
    public function saveDyeing()
    {
        // Validasi data
        $this->validate([
            'gramasi' => 'required|string',
            'tanggal_proses' => 'required|date',
            'jenis_mesin' => 'required',
            'no_mesin' => 'required',
            'dye_system' => 'required',
        ]);

        DB::transaction(function () {
            // Simpan ke Tabel ProductionActivity
            ProductionActivity::create([
                'operator_id' => Auth::id(),
                'marketing_order_id' => $this->order->id,
                'division_name' => 'dyeing',
                'kg' => $this->order->qty_kg ?? 0, // Mengambil Qty dari order asli
                'technical_data' => [
                    'gramasi' => $this->gramasi,
                    'jenis_mesin' => $this->jenis_mesin,
                    'no_mesin' => $this->no_mesin,
                    'kode_warna' => $this->kode_warna,
                    'dye_system' => $this->dye_system,
                    'treatment' => $this->treatment,
                ]
            ]);

            // UPDATE STATUS: Melempar ke divisi selanjutnya (misal: STENTER)
            $this->order->update([
                'status' => 'relax-dryer' 
            ]);
        });

        session()->flash('success', 'Data DYEING Berhasil Disimpan!');

        return redirect()->route('operator.logbook');
    }

    public function render()
    {
        return view('livewire.operator.dyeing-form');
    }
}