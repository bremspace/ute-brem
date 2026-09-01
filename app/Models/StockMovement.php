<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'location_id',
        'location_rack_id',
        'movement_type',
        'quantity',
        'good_delta',
        'damaged_delta',
        'stock_before',
        'stock_after',
        'damaged_before',
        'damaged_after',
        'reference_type',
        'reference_code',
        'notes',
        'movement_at',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'good_delta' => 'decimal:2',
        'damaged_delta' => 'decimal:2',
        'stock_before' => 'decimal:2',
        'stock_after' => 'decimal:2',
        'damaged_before' => 'decimal:2',
        'damaged_after' => 'decimal:2',
        'movement_at' => 'datetime',
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
