<?php

namespace App\Livewire\Operator;

use Livewire\Component;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use Illuminate\Support\Facades\Auth;

class KnittingForm extends Component
{
    // Data Identitas (Auto-fill berdasarkan SAP)
    public $sap_no, $order_id, $pelanggan, $art_no, $warna;
    
    // Data Produksi Knitting
    public $no_mesin, $shift, $gramasi_actual, $lebar_actual;
    public $jumlah_roll, $berat_kg, $keterangan;

    public function updatedSapNo($value)
    {
        // Cari data order marketing secara otomatis saat SAP diisi
        $order = MarketingOrder::where('sap_no', $value)->first();
        
        if ($order) {
            $this->order_id = $order->id;
            $this->pelanggan = $order->pelanggan;
            $this->art_no = $order->art_no;
            $this->warna = $order->warna;
        } else {
            $this->reset(['order_id', 'pelanggan', 'art_no', 'warna']);
        }
    }

    public function submit()
    {
        $this->validate([
            'sap_no' => 'required',
            'no_mesin' => 'required',
            'shift' => 'required',
            'jumlah_roll' => 'required|numeric',
            'berat_kg' => 'required|numeric',
        ]);

        ProductionActivity::create([
            'marketing_order_id' => $this->order_id,
            'user_id' => Auth::id(),
            'division_id' => Auth::user()->division_id, // Divisi Rajut/Knitting
            'no_mesin' => $this->no_mesin,
            'shift' => $this->shift,
            'gramasi_actual' => $this->gramasi_actual,
            'lebar_actual' => $this->lebar_actual,
            'jumlah_roll' => $this->jumlah_roll,
            'berat_kg' => $this->berat_kg,
            'keterangan' => $this->keterangan,
            'type' => 'knitting'
        ]);

        session()->flash('message', 'Data produksi rajut berhasil disimpan!');
        return redirect()->route('operator.logbook');
    }

    public function render()
    {
        return view('livewire.operator.knitting-form');
    }
}