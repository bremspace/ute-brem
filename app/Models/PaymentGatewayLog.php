<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGatewayLog extends Model
{
    protected $fillable = [
        'online_order_id',
        'gateway',
        'gateway_order_id',
        'gateway_payment_id',
        'payment_type',
        'amount',
        'status',
        'raw_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'raw_response' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(OnlineOrder::class, 'online_order_id');
    }
}
