<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // Hubungkan model ke nama tabel yang benar di database Anda
    protected $table = 'marketing_orders';

    // Izinkan semua kolom untuk diakses
    protected $guarded = [];
}