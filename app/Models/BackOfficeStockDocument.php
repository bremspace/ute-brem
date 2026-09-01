<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackOfficeStockDocument extends Model
{
    protected $fillable = [
        'document_code',
        'document_date',
        'document_type',
        'product_id',
        'location_id',
        'location_rack_id',
        'movement_type',
        'quantity',
        'description',
        'stock_movement_id',
        'created_by',
    ];

    protected $casts = [
        'document_date' => 'date',
        'quantity' => 'decimal:2',
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

    public function stockMovement()
    {
        return $this->belongsTo(StockMovement::class);
    }
}
