<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Enums\UserRole;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Relasi ke tabel ProductionActivity
     */
    public function productionActivities()
    {
        return $this->hasMany(\App\Models\ProductionActivity::class, 'operator_id');
    }

    /**
     * Masukkan semua kolom yang Anda miliki di database (DBeaver) 
     * agar bisa disimpan secara otomatis oleh Controller.
     */

    public function hasRole(UserRole|string|array $roles): bool
    {
        if ($roles instanceof UserRole) {
            return $this->role === $roles->value;
        }

        if (is_array($roles)) {
            $roleValues = array_map(fn($r) => $r instanceof UserRole ? $r->value : $r, $roles);
            return in_array($this->role, $roleValues);
        }

        return $this->role === $roles;
    }
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
     * Enum-based Role Helpers
     */
    public function isSuperAdmin(): bool {
        return $this->role === UserRole::SUPER_ADMIN->value;
    }

    public function isImpersonated(): bool {
        return session()->has('impersonator_id');
    }

    public function isAdmin(): bool {
        return $this->role === UserRole::ADMIN->value;
    }

    public function isOperator(): bool {
        return $this->role === UserRole::OPERATOR->value;
    }

    public function isMarketing(): bool {
        return $this->role === UserRole::MARKETING->value;
    }
    
    public function division()
    {
        // Pastikan foreign key di tabel users adalah 'division_id'
        return $this->belongsTo(Division::class, 'division_id');
    }

    
}