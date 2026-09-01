<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderPayment extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'supplier_id',
        'payment_at',
        'amount',
        'payment_method',
        'reference',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'payment_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
