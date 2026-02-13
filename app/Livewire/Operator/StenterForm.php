<?php

namespace App\Livewire\Operator;

use Livewire\Component;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use Illuminate\Support\Facades\Auth;

class StenterForm extends Component
{
    // Data Identitas
    public $sap_no, $order_id, $pelanggan, $art_no, $warna;
    
    // Data Produksi Stenter
    public $no_mesin, $shift, $suhu_set, $speed;
    public $overfeed, $lebar_set, $gramasi_set;
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
            'suhu_set' => 'required|numeric',
            'lebar_set' => 'required|numeric',
            'jumlah_roll' => 'required|numeric',
        ]);

        ProductionActivity::create([
            'marketing_order_id' => $this->order_id,
            'user_id' => Auth::id(),
            'division_id' => Auth::user()->division_id,
            'no_mesin' => $this->no_mesin,
            'shift' => $this->shift,
            'suhu_actual' => $this->suhu_set, // Kita petakan ke kolom suhu_actual
            'lebar_actual' => $this->lebar_set,
            'gramasi_actual' => $this->gramasi_set,
            'jumlah_roll' => $this->jumlah_roll,
            'berat_kg' => $this->berat_kg,
            'keterangan' => "Speed: {$this->speed} | Overfeed: {$this->overfeed}% | " . $this->keterangan,
            'type' => 'stenter'
        ]);

        session()->flash('message', 'Data Stenter berhasil disimpan!');
        return redirect()->route('operator.logbook');
    }

    public function render()
    {
        return view('livewire.operator.stenter-form')
        ->layout('layouts.app');
    }
}