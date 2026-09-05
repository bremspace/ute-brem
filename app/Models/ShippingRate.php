<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    protected $fillable = [
        'shipping_provider_id',
        'origin_city',
        'destination_city',
        'min_weight',
        'max_weight',
        'base_rate',
        'per_kg_rate',
        'estimated_days',
        'is_active',
    ];

    protected $casts = [
        'min_weight' => 'decimal:2',
        'max_weight' => 'decimal:2',
        'base_rate' => 'decimal:2',
        'per_kg_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function provider()
    {
        return $this->belongsTo(ShippingProvider::class, 'shipping_provider_id');
    }
}
