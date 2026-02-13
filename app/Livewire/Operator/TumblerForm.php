<?php

namespace App\Livewire\Operator;

use Livewire\Component;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use Illuminate\Support\Facades\Auth;

class TumblerForm extends Component
{
    // Data Identitas
    public $sap_no, $order_id, $pelanggan, $art_no, $warna;
    
    // Data Produksi Tumbler
    public $no_mesin, $shift, $suhu_set, $waktu_proses;
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
            'waktu_proses' => 'required|numeric',
            'jumlah_roll' => 'required|numeric',
            'berat_kg' => 'required|numeric',
        ]);

        ProductionActivity::create([
            'marketing_order_id' => $this->order_id,
            'user_id' => Auth::id(),
            'division_id' => Auth::user()->division_id,
            'no_mesin' => $this->no_mesin,
            'shift' => $this->shift,
            'suhu_actual' => $this->suhu_set,
            'jumlah_roll' => $this->jumlah_roll,
            'berat_kg' => $this->berat_kg,
            'keterangan' => "Waktu: {$this->waktu_proses} Menit | " . $this->keterangan,
            'type' => 'tumbler'
        ]);

        session()->flash('message', 'Data Tumbler berhasil disimpan!');
        return redirect()->route('operator.logbook');
    }

    public function render()
    {
        return view('livewire.operator.tumbler-form')
        ->layout('layouts.app');
    }
}