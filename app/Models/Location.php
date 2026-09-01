<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name',
        'code',
        'branch_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function productStocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function racks()
    {
        return $this->hasMany(LocationRack::class)->orderBy('name');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function outgoingTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'source_location_id');
    }

    public function incomingTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'target_location_id');
    }
}
