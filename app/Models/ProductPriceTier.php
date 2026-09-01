<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPriceTier extends Model
{
    protected $fillable = [
        'product_id',
        'unit_level',
        'min_qty',
        'price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
