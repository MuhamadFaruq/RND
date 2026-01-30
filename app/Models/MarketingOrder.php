<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'sap_no', 'art_no', 'tanggal', 'pelanggan', 'mkt', 'keperluan', 
        'konstruksi_greige', 'material', 'benang', 'kelompok_kain', 
        'target_lebar', 'belah_bulat', 'target_gramasi', 'warna', 
        'handfeel', 'treatment_khusus', 'roll_target', 'kg_target', 
        'keterangan_artikel', 'status'
    ];

    protected $guarded = [];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Related production activities logged for this marketing order.
     */
    public function productionActivities()
    {
        return $this->hasMany(ProductionActivity::class);
    }
}

