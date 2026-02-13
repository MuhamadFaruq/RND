<?php

namespace App\Livewire\Operator;

use Livewire\Component;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use Illuminate\Support\Facades\Auth;

class PengujianForm extends Component
{
    public $sap_no, $order_id, $pelanggan, $art_no, $warna;
    
    // Data Hasil Pengujian
    public $gramasi_actual, $lebar_actual, $shrinkage_lebar, $shrinkage_panjang;
    public $washing_test, $keterangan;

    public function updatedSapNo($value)
    {
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
            'gramasi_actual' => 'required|numeric',
            'lebar_actual' => 'required|numeric',
        ]);

        ProductionActivity::create([
            'marketing_order_id' => $this->order_id,
            'user_id' => Auth::id(),
            'division_id' => Auth::user()->division_id,
            'gramasi_actual' => $this->gramasi_actual,
            'lebar_actual' => $this->lebar_actual,
            'keterangan' => "Shrinkage: L({$this->shrinkage_lebar}%) P({$this->shrinkage_panjang}%) | Wash: {$this->washing_test} | " . $this->keterangan,
            'type' => 'pengujian',
            'shift' => '1', // Default atau sesuaikan
            'jumlah_roll' => 0, // Pengujian biasanya tidak menambah stok roll
            'berat_kg' => 0
        ]);

        session()->flash('message', 'Hasil pengujian laboratorium berhasil disimpan!');
        return redirect()->route('operator.logbook');
    }

    public function render()
    {
        return view('livewire.operator.pengujian-form')
        ->layout('layouts.app');
    }
}