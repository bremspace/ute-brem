<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryTracking extends Model
{
    protected $fillable = [
        'online_order_id',
        'provider',
        'tracking_number',
        'status',
        'location',
        'lat',
        'lng',
        'notes',
        'raw_data',
    ];

    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'raw_data' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(OnlineOrder::class, 'online_order_id');
    }
}
