<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingOrder extends Model
{
    use HasFactory;

    // Tambahkan 'mkt', 'material', 'benang', 'roll_target', 'kg_target', 'keterangan_artikel'
    // Dan hapus yang tidak dipakai (seperti sales_name, qty_order, dll)
    protected $fillable = [
        'sap_no', 'art_no', 'tanggal', 'pelanggan', 'mkt', 
        'warna', 'kg_target', 'roll_target', 'benang', 'material',
        'keterangan_artikel', 'status', 'keperluan', 'konstruksi_greige', 
        'kelompok_kain', 'target_lebar', 'target_gramasi', 
        'handfeel', 'treatment_khusus', 'belah_bulat', 'is_urgent'
    ];

    // Cukup gunakan ini saja jika ingin praktis (opsional):
    // protected $guarded = [];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function productionActivities()
    {
        return $this->hasMany(ProductionActivity::class);
    }
}