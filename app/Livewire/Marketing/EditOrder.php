<?php
namespace App\Livewire\Marketing;

use Livewire\Component;
use App\Models\MarketingOrder;

class EditOrder extends Component
{
    public $orderId;
    
    // 1. TAMBAHKAN INI agar tidak error "Undefined variable"
    public $sapError = null;

    // Properti Form
    public $sap_no, $art_no, $tanggal, $pelanggan, $mkt, $keperluan, $material, $benang;
    public $konstruksi_greige, $kelompok_kain, $belah_bulat, $handfeel;
    public $target_lebar, $target_gramasi, $warna, $treatment_khusus;
    public $roll_target, $kg_target, $keterangan_artikel;

    public function mount($id)
    {
        $order = MarketingOrder::findOrFail($id);
        $this->orderId = $order->id;
        
        // Isi semua properti dari database
        $this->sap_no = $order->sap_no;
        $this->art_no = $order->art_no;
        // Pastikan format tanggal sesuai untuk input date
        $this->tanggal = $order->tanggal ? \Illuminate\Support\Carbon::parse($order->tanggal)->format('Y-m-d') : null;
        $this->pelanggan = $order->pelanggan;
        $this->mkt = $order->mkt;
        $this->keperluan = $order->keperluan;
        $this->material = $order->material;
        $this->benang = $order->benang;
        $this->konstruksi_greige = $order->konstruksi_greig; 
        $this->kelompok_kain = $order->kelompok_kain;
        $this->belah_bulat = $order->belah_bulat;
        $this->handfeel = $order->handfeel;
        $this->target_lebar = $order->target_lebar;
        $this->target_gramasi = $order->target_gramasi;
        $this->warna = $order->warna;
        $this->treatment_khusus = $order->treatment_khusus;
        $this->roll_target = $order->roll_target;
        $this->kg_target = $order->kg_target;
        $this->keterangan_artikel = $order->keterangan_artikel;
    }

    // 2. TAMBAHKAN fungsi ini agar fitur cek SAP tetap jalan saat edit
    public function updatedSapNo($value)
    {
        if (strlen($value) > 2) {
            $exists = MarketingOrder::where('sap_no', $value)
                ->where('id', '!=', $this->orderId) // Abaikan ID yang sedang diedit
                ->exists();
            $this->sapError = $exists ? 'Nomor SAP ini sudah terdaftar!' : null;
        } else {
            $this->sapError = null;
        }
    }

    // 3. UBAH nama fungsi dari update() menjadi submit() 
    // agar sesuai dengan wire:submit.prevent="submit" di Blade Anda
    public function submit()
    {
        $this->validate([
            'sap_no' => 'required|numeric|unique:marketing_orders,sap_no,' . $this->orderId,
            'art_no' => 'required',
            'pelanggan' => 'required',
            'warna' => 'required',
        ]);

        if ($this->sapError) return;

        $order = MarketingOrder::find($this->orderId);
        $order->update([
            'sap_no' => $this->sap_no,
            'art_no' => $this->art_no,
            'tanggal' => $this->tanggal,
            'pelanggan' => $this->pelanggan,
            'mkt' => $this->mkt,
            'keperluan' => $this->keperluan,
            'material' => $this->material,
            'benang' => $this->benang,
            'konstruksi_greig' => $this->konstruksi_greige,
            'kelompok_kain' => $this->kelompok_kain,
            'belah_bulat' => $this->belah_bulat,
            'handfeel' => $this->handfeel,
            'target_lebar' => $this->target_lebar,
            'target_gramasi' => $this->target_gramasi,
            'warna' => $this->warna,
            'treatment_khusus' => $this->treatment_khusus,
            'roll_target' => $this->roll_target,
            'kg_target' => $this->kg_target,
            'keterangan_artikel' => $this->keterangan_artikel,
        ]);

        session()->flash('message', 'Order SAP ' . $this->sap_no . ' Berhasil Diperbarui!');
        return redirect()->route('marketing.orders.index');
    }

    public function render()
    {
        return view('components.marketing.order-form')->layout('layouts.app');
    }
}