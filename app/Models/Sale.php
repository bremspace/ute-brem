<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'sale_code',
        'sale_channel',
        'sale_at',
        'location_id',
        'customer_id',
        'cashier_id',
        'cash_session_id',
        'subtotal',
        'discount_total',
        'grand_total',
        'paid_amount',
        'change_amount',
        'payment_method',
        'payment_reference',
        'credit_term_days',
        'credit_due_at',
        'credit_status',
        'items_count',
        'points_earned',
        'status',
        'notes',
    ];

    protected $casts = [
        'sale_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'credit_due_at' => 'date',
        'items_count' => 'integer',
        'points_earned' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function cashSession()
    {
        return $this->belongsTo(CashSession::class);
    }

    public function payments()
    {
        return $this->hasMany(TransactionPayment::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function outstandingAmount(): float
    {
        return max(0, (float) $this->grand_total - (float) $this->paid_amount);
    }
}
