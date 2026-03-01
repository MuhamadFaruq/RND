<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Tambahkan import ini

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 
        'action', 
        'division',
        'sap_no',
        'details', 
        'model', 
        'description', 
        'payload', 
        'ip_address', 
        'user_agent'
    ];

    /**
     * Relasi ke model User
     * Ini akan memperbaiki error "undefined relationship [user]"
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}