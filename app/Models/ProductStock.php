<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model
{
    protected $fillable = [
        'product_id',
        'location_id',
        'location_rack_id',
        'quantity',
        'damaged_quantity',
        'stock_min',
        'stock_max',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'damaged_quantity' => 'decimal:2',
        'stock_min' => 'decimal:2',
        'stock_max' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function rack()
    {
        return $this->belongsTo(LocationRack::class, 'location_rack_id');
    }
}
