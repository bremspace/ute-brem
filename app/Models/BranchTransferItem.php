<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchTransferItem extends Model
{
    protected $fillable = [
        'branch_transfer_id',
        'product_id',
        'source_location_rack_id',
        'target_location_rack_id',
        'quantity_sent',
        'quantity_received',
        'notes',
    ];

    protected $casts = [
        'quantity_sent' => 'decimal:2',
        'quantity_received' => 'decimal:2',
    ];

    public function branchTransfer()
    {
        return $this->belongsTo(BranchTransfer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sourceRack()
    {
        return $this->belongsTo(LocationRack::class, 'source_location_rack_id');
    }

    public function targetRack()
    {
        return $this->belongsTo(LocationRack::class, 'target_location_rack_id');
    }
}
