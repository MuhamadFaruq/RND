<?php

namespace App\Livewire\Marketing;

use Livewire\Component;
use App\Models\MarketingOrder;
use App\Models\ActivityLog;

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
    // Properti R&D
    public $rnd_gramasi_greige, $rnd_mesin_rajut, $rnd_jenis_mesin_rajut;

    // Properti Workflow
    public $req_compactor = false, $req_heat_setting = false, $req_stenter = false, $req_tumbler = false, $req_fleece = false;
    public $req_pengujian = true, $req_qe = true;

    public $artError = null;
    public $recommendations = [];

    public function mount() {
        // Inisialisasi default
        $this->tanggal = now()->format('Y-m-d');
        $this->mkt = auth()->user()->name; 
        $this->keperluan = '';
    }

    /**
     * Logic Rekomendasi & Auto-fill dari Masa Lalu
     */
    public function updatedArtNo($value)
    {
        if (strlen($value) < 2) {
            $this->recommendations = [];
            return;
        }

        // Cari artikel serupa dari masa lalu (unique by art_no)
        $this->recommendations = MarketingOrder::where('art_no', 'like', "%{$value}%")
            ->select('id', 'art_no', 'warna', 'kelompok_kain', 'pelanggan')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('art_no')
            ->take(5)
            ->toArray();

        $exists = MarketingOrder::where('art_no', $value)->exists();
        $this->artError = $exists ? 'Nomor Artikel ini sudah terdaftar sebagai order aktif!' : null;
    }

    /**
     * Muat data dari artikel masa lalu
     */
    public function loadArticleTemplate($id)
    {
        $pastOrder = MarketingOrder::find($id);
        if (!$pastOrder) return;

        // Populate Form (Kecuali sap_no dan tanggal yang biasanya baru)
        $this->art_no = $pastOrder->art_no; // Keep the same or let user modify
        $this->pelanggan = $pastOrder->pelanggan;
        $this->mkt = $pastOrder->mkt;
        $this->keperluan = 'Repeat Order';
        $this->material = $pastOrder->material;
        $this->benang = $pastOrder->benang;
        $this->konstruksi_greige = $pastOrder->konstruksi_greige;
        $this->kelompok_kain = $pastOrder->kelompok_kain;
        $this->target_lebar = $pastOrder->target_lebar;
        $this->belah_bulat = $pastOrder->belah_bulat;
        $this->target_gramasi = $pastOrder->target_gramasi;
        $this->warna = $pastOrder->warna;
        $this->handfeel = $pastOrder->handfeel;
        $this->treatment_khusus = $pastOrder->treatment_khusus;
        
        // Data R&D
        $this->rnd_gramasi_greige = $pastOrder->rnd_gramasi_greige;
        $this->rnd_mesin_rajut = $pastOrder->rnd_mesin_rajut;
        $this->rnd_jenis_mesin_rajut = $pastOrder->rnd_jenis_mesin_rajut;

        // Workflow
        $this->req_compactor = (bool)$pastOrder->req_compactor;
        $this->req_heat_setting = (bool)$pastOrder->req_heat_setting;
        $this->req_stenter = (bool)$pastOrder->req_stenter;
        $this->req_tumbler = (bool)$pastOrder->req_tumbler;
        $this->req_fleece = (bool)$pastOrder->req_fleece;
        $this->req_pengujian = (bool)$pastOrder->req_pengujian;
        $this->req_qe = (bool)$pastOrder->req_qe;

        $this->recommendations = [];
        $this->dispatch('show-toast', message: "Data Artikel {$pastOrder->art_no} berhasil dimuat!", type: 'success');
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
            'art_no'    => 'required|string', // Tidak lagi unik agar bisa repeat order artikel yang sama
            'sap_no'    => 'nullable|numeric|unique:marketing_orders,sap_no', // SAP tetap unik sebagai ID transaksi
            'tanggal'   => 'required|date',    
            'pelanggan' => 'required|string', 
            'mkt'       => 'required',
            'warna'     => 'required|string',  
            'kg_target' => 'required|numeric', 
            'target_lebar' => 'required|string', 
            'target_gramasi' => 'required|string',
        ]);

        try {
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
                'req_compactor'      => $this->req_compactor,
                'req_heat_setting'   => $this->req_heat_setting,
                'req_stenter'        => $this->req_stenter,
                'req_tumbler'        => $this->req_tumbler,
                'req_fleece'         => $this->req_fleece,
                'req_pengujian'      => $this->req_pengujian,
                'req_qe'             => $this->req_qe,
                'rnd_gramasi_greige' => $this->rnd_gramasi_greige,
                'rnd_mesin_rajut'    => $this->rnd_mesin_rajut,
                'rnd_jenis_mesin_rajut' => $this->rnd_jenis_mesin_rajut,
                'status'             => 'knitting'
            ]);
            
            // LOGGING AUDIT TRAIL
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'CREATE_ORDER',
                'division'    => 'MARKETING',
                'art_no'      => $this->art_no,
                'sap_no'      => $this->sap_no,
                'description' => "Membuat Order Artikel Baru: {$this->art_no}",
            ]);

            session()->flash('message', 'Order Berhasil Dikirim!');
            return redirect()->route('marketing.orders.index');
        } catch (\Exception $e) {
            $this->dispatch('show-error-toast', message: 'Gagal membuat order: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.marketing.order-form');
    }
}