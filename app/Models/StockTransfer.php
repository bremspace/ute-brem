<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $fillable = [
        'product_id',
        'transfer_code',
        'source_location_id',
        'source_location_rack_id',
        'target_location_id',
        'target_location_rack_id',
        'quantity',
        'notes',
        'transferred_at',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'transferred_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sourceLocation()
    {
        return $this->belongsTo(Location::class, 'source_location_id');
    }

    public function sourceRack()
    {
        return $this->belongsTo(LocationRack::class, 'source_location_rack_id');
    }

    public function targetLocation()
    {
        return $this->belongsTo(Location::class, 'target_location_id');
    }

    public function targetRack()
    {
        return $this->belongsTo(LocationRack::class, 'target_location_rack_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
