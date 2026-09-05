<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineOrderItem extends Model
{
    protected $fillable = [
        'online_order_id',
        'product_id',
        'product_name',
        'product_code',
        'quantity',
        'unit',
        'unit_price',
        'discount_amount',
        'subtotal',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(OnlineOrder::class, 'online_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
