<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'product_id',
        'supplier_id',
        'location_id',
        'quantity',
        'unit_price',
        'total_price',
        'payment_method',
        'credit_term_days',
        'credit_due_at',
        'credit_status',
        'paid_amount',
        'status',
        'notes',
        'ordered_at',
        'received_at',
        'stock_movement_id',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'ordered_at' => 'datetime',
        'received_at' => 'datetime',
        'credit_due_at' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function stockMovement()
    {
        return $this->belongsTo(StockMovement::class);
    }

    public function files()
    {
        return $this->hasMany(PurchaseOrderFile::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchaseOrderPayment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
