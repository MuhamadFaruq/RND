<?php

namespace App\Livewire\Operator;

use Livewire\Component;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use Illuminate\Support\Facades\Auth;

class QEForm extends Component
{
    public $sap_no, $order_id, $pelanggan, $art_no, $warna;
    
    // Data Quality Engineering
    public $grade, $defect_points, $final_gramasi, $final_lebar;
    public $keterangan;

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
            'grade' => 'required',
            'final_gramasi' => 'required|numeric',
            'final_lebar' => 'required|numeric',
        ]);

        ProductionActivity::create([
            'marketing_order_id' => $this->order_id,
            'user_id' => Auth::id(),
            'division_id' => Auth::user()->division_id,
            'gramasi_actual' => $this->final_gramasi,
            'lebar_actual' => $this->final_lebar,
            'keterangan' => "GRADE: {$this->grade} | Defect Pts: {$this->defect_points} | " . $this->keterangan,
            'type' => 'qe',
            'shift' => '1',
            'jumlah_roll' => 0,
            'berat_kg' => 0
        ]);

        session()->flash('message', 'Inspeksi QE untuk SAP '.$this->sap_no.' telah disimpan dengan Grade '.$this->grade);
        return redirect()->route('operator.logbook');
    }

    public function render()
    {
        return view('livewire.operator.qe-form')
        ->layout('layouts.app');
    }
}