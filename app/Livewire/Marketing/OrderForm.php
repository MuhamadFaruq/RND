<?php

namespace App\Livewire\Marketing;

use Livewire\Component;
use App\Models\MarketingOrder;
use Illuminate\Support\Facades\DB;

class OrderForm extends Component
{
    // Properti Form (Identitas Order)
    public $sap_no, $art_no, $tanggal, $pelanggan;
    
    // Klasifikasi & Material
    public $mkt, $keperluan, $material, $benang;
    
    // Spesifikasi Teknis
    public $konstruksi_greige, $kelompok_kain, $belah_bulat, $handfeel;
    public $target_lebar, $target_gramasi, $warna, $treatment_khusus;
    
    // Quantity & Keterangan
    public $roll_target, $kg_target, $keterangan_artikel;

    public $sapError = null;

    // Validasi Real-time untuk SAP (Mencegah Duplikat)
    public function updatedSapNo($value)
    {
        if (strlen($value) > 2) {
            $exists = MarketingOrder::where('sap_no', $value)->exists();
            $this->sapError = $exists ? 'Nomor SAP ini sudah terdaftar!' : null;
        }
    }

    public function submit()
    {
        $this->validate([
            'sap_no' => 'required|numeric|unique:marketing_orders,sap_no',
            'art_no' => 'required',
            'pelanggan' => 'required',
            'tanggal' => 'required|date',
            'warna' => 'required',
        ]);

        if ($this->sapError) return;

        MarketingOrder::create([
            'sap_no' => $this->sap_no,
            'art_no' => $this->art_no,
            'tanggal' => $this->tanggal,
            'pelanggan' => $this->pelanggan,
            'mkt' => $this->mkt,
            'keperluan' => $this->keperluan,
            'material' => $this->material,
            'benang' => $this->benang,
            'konstruksi_greige' => $this->konstruksi_greige, 
            'kelompok_kain' => $this->kelompok_kain,
            'target_lebar' => $this->target_lebar,
            'belah_bulat' => $this->belah_bulat,
            'target_gramasi' => $this->target_gramasi,
            'warna' => $this->warna,
            'handfeel' => $this->handfeel,
            'treatment_khusus' => $this->treatment_khusus,
            'roll_target' => $this->roll_target,
            'kg_target' => $this->kg_target,
            'keterangan_artikel' => $this->keterangan_artikel,
            'status' => 'pending'
        ]);

        session()->flash('message', 'Order SAP ' . $this->sap_no . ' Berhasil Dipublikasikan!');
        return redirect()->route('marketing.orders.index');
    }

    public function render()
    {
        return view('components.marketing.order-form');
    }
}