<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
    public $timestamps = false;

    protected $fillable = ['stock_opname_id', 'product_id', 'system_qty', 'actual_qty', 'difference', 'note'];

    protected $casts = [
        'system_qty' => 'decimal:2',
        'actual_qty' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    public function opname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
