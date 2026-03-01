<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    /**
     * Izinkan pengisian massal untuk kolom name
     */
    protected $fillable = ['name', 'slug', 'description'];

    /**
     * Mendefinisikan relasi ke tabel ProductionActivity.
     * Kita menggunakan 'division_name' sebagai foreign key karena 
     * di tabel production_activities Anda menyimpan nama divisi (knitting, dyeing, dll).
     */
    public function productionActivities(): HasMany
    {
        // Parameter: (ModelTujuan, ForeignKeyDiTabelTujuan, LocalKeyDiTabelIni)
        return $this->hasMany(ProductionActivity::class, 'division_name', 'name');
    }
}