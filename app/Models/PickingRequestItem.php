<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickingRequestItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'picking_request_id', 'product_id', 'location_rack_id',
        'qty_requested', 'qty_picked', 'status', 'note',
    ];

    protected $casts = [
        'qty_requested' => 'decimal:2',
        'qty_picked' => 'decimal:2',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(PickingRequest::class, 'picking_request_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function rack(): BelongsTo
    {
        return $this->belongsTo(LocationRack::class, 'location_rack_id');
    }
}
