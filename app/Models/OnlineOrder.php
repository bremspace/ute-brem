<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnlineOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_code',
        'customer_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'order_type',
        'status',
        'subtotal',
        'discount_total',
        'shipping_cost',
        'grand_total',
        'payment_method',
        'payment_gateway',
        'payment_reference',
        'paid_at',
        'shipping_provider',
        'shipping_service',
        'shipping_tracking_number',
        'shipping_address',
        'shipping_city',
        'shipping_province',
        'shipping_postal_code',
        'delivery_lat',
        'delivery_lng',
        'delivery_notes',
        'pickup_location_id',
        'pickup_time',
        'points_earned',
        'points_used',
        'notes',
        'internal_notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_at' => 'datetime',
        'delivery_lat' => 'decimal:8',
        'delivery_lng' => 'decimal:8',
        'pickup_time' => 'datetime',
        'points_earned' => 'integer',
        'points_used' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(OnlineOrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function pickupLocation()
    {
        return $this->belongsTo(Location::class, 'pickup_location_id');
    }

    public function payments()
    {
        return $this->hasMany(PaymentGatewayLog::class);
    }

    public function deliveryTrackings()
    {
        return $this->hasMany(DeliveryTracking::class);
    }
}
