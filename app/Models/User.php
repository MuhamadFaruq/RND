<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Masukkan semua kolom yang Anda miliki di database (DBeaver) 
     * agar bisa disimpan secara otomatis oleh Controller.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',      // Tambahkan ini
        'division',  // Tambahkan ini
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Helper untuk mengecek Role
     */
    public function isSuperAdmin() {
        return $this->role === 'superadmin';
    }

    public function isAdmin() {
        return $this->role === 'admin';
    }

    public function isOperator() {
        return $this->role === 'operator';
    }

    public function isMarketing() {
        return $this->role === 'marketing';
    }
    
    public function division()
    {
        // Pastikan foreign key di tabel users adalah 'division_id'
        return $this->belongsTo(Division::class, 'division_id');
    }

    
}