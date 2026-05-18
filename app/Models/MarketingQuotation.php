<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingQuotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'customer_name',
        'article_name',
        'yarn_price',
        'chemical_price',
        'knitting_fee',
        'dyeing_fee',
        'overhead',
        'waste_knitting',
        'waste_dyeing',
        'margin',
        'ppn',
        'hpp',
        'selling_price',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
