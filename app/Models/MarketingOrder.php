<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingOrder extends Model
{
    use HasFactory, SoftDeletes;

    // Tambahkan 'mkt', 'material', 'benang', 'roll_target', 'kg_target', 'keterangan_artikel'
    // Dan hapus yang tidak dipakai (seperti sales_name, qty_order, dll)
    protected $fillable = [
        'sap_no', 'art_no', 'tanggal', 'pelanggan', 'mkt',
        'warna', 'kg_target', 'roll_target', 'benang', 'benang_percent', 'material',
        'keterangan_artikel', 'status', 'keperluan', 'konstruksi_greige',
        'kelompok_kain', 'target_lebar', 'target_gramasi',
        'handfeel', 'treatment_khusus', 'belah_bulat', 'is_urgent',
        // Flag alur kerja dinamis
        'req_stenter', 'req_compactor', 'req_heat_setting',
        'req_tumbler', 'req_fleece', 'req_pengujian', 'req_qe',
        'processing_by', 'processing_at',
        'rnd_gramasi_greige', 'rnd_mesin_rajut', 'rnd_jenis_mesin_rajut',
    ];

    // Cukup gunakan ini saja jika ingin praktis (opsional):
    // protected $guarded = [];

    protected $casts = [
        'tanggal'          => 'date',
        'is_urgent'        => 'boolean',
        // Workflow flags
        'req_stenter'      => 'boolean',
        'req_compactor'    => 'boolean',
        'req_heat_setting' => 'boolean',
        'req_tumbler'      => 'boolean',
        'req_fleece'       => 'boolean',
        'req_pengujian'    => 'boolean',
        'req_qe'           => 'boolean',
    ];

    public function productionActivities()
    {
        return $this->hasMany(ProductionActivity::class);
    }

    public function processingBy()
    {
        return $this->belongsTo(User::class, 'processing_by');
    }
}