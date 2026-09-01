<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'product_code',
        'product_name',
        'barcode',
        'unit_level',
        'unit_name',
        'conversion_qty',
        'quantity',
        'base_quantity',
        'unit_price',
        'purchase_price',
        'discount_value',
        'subtotal',
        'serial_numbers',
    ];

    protected $casts = [
        'conversion_qty' => 'decimal:2',
        'quantity' => 'decimal:2',
        'base_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'serial_numbers' => 'array',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

