<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionActivity extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'technical_data' => 'array',
    ];

    /**
     * The marketing order this activity belongs to.
     */
    public function marketingOrder()
    {
        return $this->belongsTo(MarketingOrder::class);
    }

    /**
     * The operator (user) responsible for this activity.
     */
    public function user()
    {
        // Ubah 'user_id' menjadi 'operator_id' agar sesuai dengan database Anda
        return $this->belongsTo(User::class, 'operator_id'); 
    }

    /**
     * Alias for user relation to match UI usage.
     */
    public function operator()
    {
        return $this->user();
    }

    /**
     * Relasi balik ke Division berdasarkan nama.
     */
    public function division()
    {
        return $this->belongsTo(Division::class, 'division_name', 'name');
    }
}

