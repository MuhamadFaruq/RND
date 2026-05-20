<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivedOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_order_id',
        'sap_no',
        'art_no',
        'tanggal',
        'pelanggan',
        'mkt',
        'original_data',
        'production_logs',
        'deleted_by',
        'reason'
    ];

    protected $casts = [
        'original_data' => 'array',
        'production_logs' => 'array',
        'tanggal' => 'date',
    ];

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
