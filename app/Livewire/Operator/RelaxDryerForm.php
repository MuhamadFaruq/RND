<?php

namespace App\Livewire\Operator;

use Livewire\Component;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RelaxDryerForm extends Component
{
    // State Control
    public $isProcessing = false;
    public $selectedJob;

    // Data Identitas (Display Only)
    public $order_id, $sap_no, $pelanggan, $art_no;
    
    // Data Form Relax Dryer (Input)
    public $operator;
    public $tanggal;
    public $chemical;
    public $handfeel;
    public $no_mesin;
    public $overfeed;
    public $temperatur;
    public $speed;
    public $hasil_lebar;
    public $hasil_gramasi;
    public $shrinkage;

    public function mount()
    {
        $this->tanggal = date('Y-m-d');
        $this->operator = Auth::user()->name; // Default nama operator login
    }

    /**
     * Dipanggil saat tombol "PROSES RELAX DRYER" di list diklik
     */
    public function startProcess($id)
    {
        $order = MarketingOrder::find($id);
        if ($order) {
            $this->selectedJob = $order;
            $this->order_id = $order->id;
            $this->sap_no = $order->sap_no;
            $this->art_no = $order->art_no;
            $this->pelanggan = $order->pelanggan;
            
            $this->isProcessing = true;
        }
    }

    /**
     * Kembali ke daftar list
     */
    public function backToList()
    {
        $this->isProcessing = false;
        $this->reset(['order_id', 'sap_no', 'art_no', 'pelanggan', 'chemical', 'handfeel']);
    }

    public function submit()
    {
        $this->validate([
            'operator' => 'required',
            'tanggal' => 'required|date',
            'no_mesin' => 'required',
            'temperatur' => 'required|numeric',
            'speed' => 'required|numeric',
            'hasil_lebar' => 'required',
            'hasil_gramasi' => 'required',
        ]);

        DB::transaction(function () {
            ProductionActivity::create([
                'marketing_order_id' => $this->order_id,
                'user_id' => Auth::id(),
                'division_id' => Auth::user()->division_id, // Pastikan user punya division_id
                'no_mesin' => $this->no_mesin,
                'tanggal_proses' => $this->tanggal,
                'suhu_actual' => $this->temperatur,
                'speed' => $this->speed,
                'type' => 'relaxdryer',
                // Simpan data tambahan ke kolom keterangan atau kolom teknis khusus
                'keterangan' => json_encode([
                    'operator' => $this->operator,
                    'chemical' => $this->chemical,
                    'handfeel' => $this->handfeel,
                    'overfeed' => $this->overfeed,
                    'hasil_lebar' => $this->hasil_lebar,
                    'hasil_gramasi' => $this->hasil_gramasi,
                    'shrinkage' => $this->shrinkage,
                ]),
            ]);

            // Update status MO agar hilang dari list permintaan dan lanjut ke Compactor
            MarketingOrder::where('id', $this->order_id)->update(['status' => 'COMPACTOR']);
        });

        session()->flash('message', 'Data Relax Dryer berhasil disimpan!');
        return redirect()->route('operator.logbook');
    }

    public function render()
    {
        // Ambil data permintaan yang statusnya memang untuk Relax Dryer
        $jobs = MarketingOrder::where('status', 'RELAX-DRYER')->get();

        return view('livewire.operator.relax-dryer-form', [
            'jobs' => $jobs
        ])->layout('layouts.app');
    }
}