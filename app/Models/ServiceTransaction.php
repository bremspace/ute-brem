<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceTransaction extends Model
{
    protected $fillable = [
        'service_code',
        'service_at',
        'return_date',
        'customer_id',
        'customer_code',
        'customer_name',
        'customer_phone',
        'customer_address',
        'device_brand',
        'device_type',
        'serial_number',
        'device_lock_type',
        'device_lock_value',
        'technician_id',
        'technician_commission',
        'check_notes',
        'complaint',
        'accessories',
        'subtotal',
        'discount_total',
        'tax_total',
        'grand_total',
        'paid_amount',
        'change_amount',
        'location_id',
        'cashier_id',
        'cash_session_id',
        'payment_method',
        'payment_reference',
        'credit_term_days',
        'credit_due_at',
        'credit_status',
        'status',
        'is_checked',
        'print_detail_price',
        'invoice_format',
    ];

    protected $casts = [
        'service_at' => 'datetime',
        'return_date' => 'date',
        'technician_commission' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'credit_due_at' => 'date',
        'is_checked' => 'boolean',
        'print_detail_price' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(ServiceTransactionItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function cashSession()
    {
        return $this->belongsTo(CashSession::class);
    }

    public function payments()
    {
        return $this->hasMany(TransactionPayment::class);
    }

    public function outstandingAmount(): float
    {
        return max(0, (float) $this->grand_total - (float) $this->paid_amount);
    }
}
