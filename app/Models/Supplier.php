<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'code',
        'slug',
        'phone',
        'email',
        'address',
        'contact_person',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function productSuppliers()
    {
        return $this->hasMany(ProductSupplier::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_suppliers')
            ->withPivot(['supplier_product_code', 'last_purchase_price', 'is_primary', 'notes'])
            ->withTimestamps();
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
