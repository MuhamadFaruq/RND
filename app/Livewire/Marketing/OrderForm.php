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


    // Properti Workflow
    public $req_compactor = false, $req_heat_setting = false, $req_stenter = false, $req_tumbler = false, $req_fleece = false;
    public $req_pengujian = false, $req_qe = false;

    public $artError = null;
    public $recommendations = [];
    public $orderId = null;
    public $exists = false;

    public function mount($id = null) {
        if (!auth()->check() || (auth()->user()->role !== 'marketing' && !auth()->user()->isSuperAdmin())) {
            abort(403, 'Akses Ditolak.');
        }

        // Inisialisasi default
        $this->tanggal = now()->format('Y-m-d');
        $this->mkt = ''; 
        $this->keperluan = '';

        if ($id) {
            $order = MarketingOrder::findOrFail($id);
            $this->orderId = $order->id;
            $this->sap_no = $order->sap_no;
            $this->art_no = $order->art_no;
            $this->tanggal = $order->tanggal;
            $this->pelanggan = $order->pelanggan;
            $this->mkt = $order->mkt;
            $this->keperluan = $order->keperluan;
            $this->material = $order->material;
            $this->benang = $order->benang;
            $this->konstruksi_greige = $order->konstruksi_greige;
            $this->kelompok_kain = $order->kelompok_kain;
            $this->target_lebar = $order->target_lebar;
            $this->belah_bulat = $order->belah_bulat;
            $this->target_gramasi = $order->target_gramasi;
            $this->warna = $order->warna;
            $this->handfeel = $order->handfeel;
            $this->treatment_khusus = $order->treatment_khusus;
            $this->roll_target = $order->roll_target;
            $this->kg_target = $order->kg_target;
            $this->keterangan_artikel = $order->keterangan_artikel;
            $this->req_compactor = (bool)$order->req_compactor;
            $this->req_heat_setting = (bool)$order->req_heat_setting;
            $this->req_stenter = (bool)$order->req_stenter;
            $this->req_tumbler = (bool)$order->req_tumbler;
            $this->req_fleece = (bool)$order->req_fleece;
            $this->req_pengujian = (bool)$order->req_pengujian;
            $this->req_qe = (bool)$order->req_qe;
        }
    }

    /**
     * Tampilkan saran riwayat artikel ketika fokus (menampilkan 5 artikel terakhir)
     */
    public function showHistorySuggestions()
    {
        $this->recommendations = MarketingOrder::select('id', 'art_no', 'warna', 'kelompok_kain', 'pelanggan')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('art_no')
            ->take(5)
            ->toArray();
    }

    /**
     * Logic Rekomendasi & Auto-fill dari Masa Lalu
     */
    public function updatedArtNo($value)
    {
        if (strlen($value) < 1) {
            $this->recommendations = [];
            $this->exists = false;
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

        $this->exists = MarketingOrder::where('art_no', $value)->exists();
        $this->artError = null; // Hapus pesan error yang membingungkan agar repeat order berjalan lancar
    }

    /**
     * Muat data dari artikel masa lalu
     */
    public function loadArticleTemplate($id)
    {
        $pastOrder = MarketingOrder::find($id);
        if (!$pastOrder) return;

        // Populate Form (Kecuali sap_no dan tanggal yang biasanya baru)
        $this->art_no = $pastOrder->art_no; 
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


        // Workflow
        $this->req_compactor = (bool)$pastOrder->req_compactor;
        $this->req_heat_setting = (bool)$pastOrder->req_heat_setting;
        $this->req_stenter = (bool)$pastOrder->req_stenter;
        $this->req_tumbler = (bool)$pastOrder->req_tumbler;
        $this->req_fleece = (bool)$pastOrder->req_fleece;
        $this->req_pengujian = (bool)$pastOrder->req_pengujian;
        $this->req_qe = (bool)$pastOrder->req_qe;

        $this->recommendations = [];
        $this->exists = true;
        $this->dispatch('show-toast', message: "Data Artikel {$pastOrder->art_no} berhasil dimuat!", type: 'success');
    }

    public function submit()
    {
        // Pengaman: Jika field ini null/kosong, isi dengan string default agar SQL tidak error 1364
        $this->material = $this->material ?: '-';
        $this->benang = $this->benang ?: '-';
        $this->roll_target = $this->roll_target ?: 0;
        $this->treatment_khusus = $this->treatment_khusus ?: '-';
        $this->kelompok_kain = $this->kelompok_kain ?: '-';

        $this->validate([
            'art_no'    => 'required|string', 
            'sap_no'    => 'nullable|numeric|unique:marketing_orders,sap_no,' . ($this->orderId ?: 'NULL'), 
            'tanggal'   => 'required|date',    
            'pelanggan' => 'required|string', 
            'mkt'       => 'required',
            'warna'     => 'required|string',  
            'kg_target' => 'required|numeric|min:1', 
            'target_lebar' => 'required|string', 
            'target_gramasi' => 'required|string',
            'handfeel' => 'required',
        ], [
            'art_no.required'    => 'Nomor Artikel wajib diisi sebagai identitas utama.',
            'sap_no.unique'      => 'Nomor SAP ini sudah digunakan oleh pesanan lain.',
            'pelanggan.required' => 'Nama Pelanggan harus dicantumkan.',
            'mkt.required'       => 'Nama Marketing wajib diisi untuk koordinasi sales.',
            'warna.required'     => 'Target warna kain wajib ditentukan.',
            'kg_target.required' => 'Target berat (KG) tidak boleh kosong.',
            'kg_target.min'      => 'Target berat minimal harus 1 KG.',
            'target_lebar.required' => 'Target lebar kain wajib diisi.',
            'target_gramasi.required' => 'Target gramasi kain wajib diisi.',
            'handfeel.required'  => 'Jenis Handfeel wajib dipilih.',
        ]);

        try {
            $data = [
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
            ];

            if ($this->orderId) {
                $order = MarketingOrder::findOrFail($this->orderId);
                $order->update($data);

                // LOGGING AUDIT TRAIL
                ActivityLog::create([
                    'user_id'     => auth()->id(),
                    'action'      => 'UPDATE_ORDER',
                    'division'    => 'MARKETING',
                    'art_no'      => $this->art_no,
                    'sap_no'      => $this->sap_no,
                    'description' => "Memperbarui Order Artikel: {$this->art_no}",
                ]);

                session()->flash('message', 'Order Berhasil Diperbarui!');
            } else {
                $data['status'] = 'knitting';
                MarketingOrder::create($data);

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
            }

            return redirect()->route('marketing.orders.index');
        } catch (\Exception $e) {
            $this->dispatch('show-error-toast', message: 'Gagal memproses order: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.marketing.order-form');
    }
}