<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    protected $fillable = [
        'product_id',
        'level',
        'unit_name',
        'conversion_qty',
        'price_toko',
        'margin_toko',
        'price_partai',
        'margin_partai',
        'price_cabang',
        'margin_cabang',
        'price_lain',
        'margin_lain',
        'barcode',
        'barcode_label',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
