<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemSerial extends Model
{
    protected $fillable = [
        'product_id',
        'product_stock_id',
        'location_rack_id',
        'serial_number',
        'status',
        'reference_type',
        'reference_code',
        'created_by',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productStock(): BelongsTo
    {
        return $this->belongsTo(ProductStock::class, 'product_stock_id');
    }

    public function rack(): BelongsTo
    {
        return $this->belongsTo(LocationRack::class, 'location_rack_id');
    }
}
