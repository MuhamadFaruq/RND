<?php

namespace App\Livewire\Operator;

use Livewire\Component;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use Illuminate\Support\Facades\Auth;

class CompactorForm extends Component
{
    // Data Identitas
    public $sap_no, $order_id, $pelanggan, $art_no, $warna;
    
    // Data Produksi Compactor
    public $no_mesin, $shift, $speed, $steam_pressure;
    public $temp_felt, $lebar_actual, $gramasi_actual;
    public $jumlah_roll, $berat_kg, $keterangan;

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
            'no_mesin' => 'required',
            'temp_felt' => 'required|numeric',
            'jumlah_roll' => 'required|numeric',
            'berat_kg' => 'required|numeric',
        ]);

        ProductionActivity::create([
            'marketing_order_id' => $this->order_id,
            'user_id' => Auth::id(),
            'division_id' => Auth::user()->division_id,
            'no_mesin' => $this->no_mesin,
            'shift' => $this->shift,
            'suhu_actual' => $this->temp_felt,
            'lebar_actual' => $this->lebar_actual,
            'gramasi_actual' => $this->gramasi_actual,
            'jumlah_roll' => $this->jumlah_roll,
            'berat_kg' => $this->berat_kg,
            'keterangan' => "Speed: {$this->speed} | Steam: {$this->steam_pressure} bar | " . $this->keterangan,
            'type' => 'compactor'
        ]);

        session()->flash('message', 'Data Compactor berhasil disimpan!');
        return redirect()->route('operator.logbook');
    }

    public function render()
    {
        return view('livewire.operator.compactor-form');
    }
}