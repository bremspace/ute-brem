<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCustomerGroupPrice extends Model
{
    protected $fillable = [
        'product_id',
        'customer_group_id',
        'channel',
        'price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class);
    }
}
