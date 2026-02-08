<?php

namespace App\Livewire\Operator;

use Livewire\Component;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use Illuminate\Support\Facades\Auth;

class DyeingForm extends Component
{
    // State Identitas
    public $sap_no, $order_id, $pelanggan, $art_no, $warna_target;
    
    // State Produksi Dyeing
    public $no_mesin, $shift, $lot_no, $suhu_actual;
    public $berat_bahan, $jumlah_chemical, $keterangan;

    public function updatedSapNo($value)
    {
        $order = MarketingOrder::where('sap_no', $value)->first();
        if ($order) {
            $this->order_id = $order->id;
            $this->pelanggan = $order->pelanggan;
            $this->art_no = $order->art_no;
            $this->warna_target = $order->warna;
        } else {
            $this->reset(['order_id', 'pelanggan', 'art_no', 'warna_target']);
        }
    }

    public function submit()
    {
        $this->validate([
            'sap_no' => 'required',
            'no_mesin' => 'required',
            'lot_no' => 'required',
            'berat_bahan' => 'required|numeric',
        ]);

        ProductionActivity::create([
            'marketing_order_id' => $this->order_id,
            'user_id' => Auth::id(),
            'division_id' => Auth::user()->division_id,
            'no_mesin' => $this->no_mesin,
            'shift' => $this->shift,
            'lot_no' => $this->lot_no,
            'suhu_actual' => $this->suhu_actual,
            'berat_kg' => $this->berat_bahan,
            'keterangan' => "Chemical: {$this->jumlah_chemical} | " . $this->keterangan,
            'type' => 'dyeing'
        ]);

        session()->flash('message', 'Data Dyeing (Lot: '.$this->lot_no.') berhasil disimpan!');
        return redirect()->route('operator.logbook');
    }

    public function render()
    {
        return view('livewire.operator.dyeing-form');
    }
}