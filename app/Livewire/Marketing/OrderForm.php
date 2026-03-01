<?php

namespace App\Livewire\Marketing;

use Livewire\Component;
use App\Models\MarketingOrder;

class OrderForm extends Component
{
    // Properti Identitas
    public $sap_no, $art_no, $tanggal, $pelanggan;
    // Properti Klasifikasi
    public $mkt, $keperluan, $material, $benang;
    // Properti Teknis
    public $konstruksi_greige, $kelompok_kain, $target_lebar, $belah_bulat;
    public $target_gramasi, $warna, $handfeel, $treatment_khusus; 
    // Properti Quantity
    public $roll_target, $kg_target, $keterangan_artikel;

    public $sapError = null;

    public function mount() {
        $menuFromUrl = request()->query('menu');
    
        if (in_array($menuFromUrl, ['dashboard', 'orders', 'history', 'notes'])) {
            $this->currentMenu = $menuFromUrl;
        } else {
            $this->currentMenu = 'dashboard';
        }
        // Inisialisasi default
        $this->tanggal = now()->format('Y-m-d');
        $this->mkt = ''; // Otomatis ambil nama marketing yang login
        $this->keperluan = 'New Order';
    }

    // Validasi Real-time untuk SAP
    public function updatedSapNo($value)
    {
        $exists = MarketingOrder::where('sap_no', $value)->exists();
        $this->sapError = $exists ? 'Nomor SAP ini sudah terdaftar!' : null;
    }

    public function submit()
{
    // Pengaman: Jika field ini null/kosong, isi dengan string kosong atau default agar SQL tidak error 1364
    $this->mkt = $this->mkt ?: auth()->user()->name;
    $this->material = $this->material ?: '-';
    $this->benang = $this->benang ?: '-';
    $this->roll_target = $this->roll_target ?: 0;
    $this->kg_target = $this->kg_target ?: 0;

    $this->validate([
        'sap_no'    => 'required|numeric|unique:marketing_orders,sap_no',
        'art_no'    => 'required|string', 
        'tanggal'   => 'required|date',    
        'pelanggan' => 'required|string', 
        'mkt'       => 'required',
        'warna'     => 'required|string',  
        'kg_target' => 'required|numeric', 
        'target_lebar' => 'required|string', 
        'target_gramasi' => 'required|string',
    ]);

    MarketingOrder::create([
        'sap_no'             => $this->sap_no,
        'art_no'             => $this->art_no,
        'tanggal'            => $this->tanggal,
        'pelanggan'          => $this->pelanggan,
        'mkt'                => $this->mkt,              
        'keperluan'          => $this->keperluan,
        'konstruksi_greige'  => $this->konstruksi_greige,
        'material'           => $this->material,
        'benang'             => $this->benang,             
        'kelompok_kain'      => $this->kelompok_kain,
        'target_lebar'       => $this->target_lebar,
        'belah_bulat'        => $this->belah_bulat,
        'target_gramasi'     => $this->target_gramasi,
        'warna'              => $this->warna,
        'handfeel'           => $this->handfeel,
        'treatment_khusus'   => $this->treatment_khusus,
        'roll_target'        => $this->roll_target,       
        'kg_target'          => $this->kg_target,          
        'keterangan_artikel' => $this->keterangan_artikel, 
        'status'             => 'knitting'
    ]);

    session()->flash('message', 'Order Berhasil Dikirim!');
    return redirect()->route('marketing.orders.index');
}

    public function render()
    {
        return view('livewire.marketing.order-form');
    }
}