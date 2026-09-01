<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceTransactionItem extends Model
{
    protected $fillable = [
        'service_transaction_id',
        'item_type',
        'service_id',
        'product_id',
        'code',
        'name',
        'quantity',
        'unit_price',
        'purchase_price',
        'discount_value',
        'subtotal',
        'is_open_price',
        'allow_discount_override',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'is_open_price' => 'boolean',
        'allow_discount_override' => 'boolean',
    ];

    public function transaction()
    {
        return $this->belongsTo(ServiceTransaction::class, 'service_transaction_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
