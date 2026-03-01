<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    // Mengizinkan pengisian data massal
    protected $fillable = ['key', 'value', 'group'];
}