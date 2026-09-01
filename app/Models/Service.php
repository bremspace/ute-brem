<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'service_code',
        'name',
        'slug',
        'category',
        'group',
        'price_toko',
        'price_partai',
        'price_cabang',
        'image_path',
        'image_note',
        'is_taxable',
        'is_open_price',
        'allow_discount_override',
        'is_published',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price_toko' => 'decimal:2',
        'price_partai' => 'decimal:2',
        'price_cabang' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_open_price' => 'boolean',
        'allow_discount_override' => 'boolean',
        'is_published' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function transactionItems()
    {
        return $this->hasMany(ServiceTransactionItem::class);
    }
}
