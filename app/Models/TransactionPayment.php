<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionPayment extends Model
{
    protected $fillable = [
        'sale_id',
        'service_transaction_id',
        'customer_id',
        'payment_at',
        'amount',
        'payment_method',
        'reference',
        'notes',
        'received_by',
    ];

    protected $casts = [
        'payment_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function serviceTransaction()
    {
        return $this->belongsTo(ServiceTransaction::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
