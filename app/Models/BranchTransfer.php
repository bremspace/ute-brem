<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchTransfer extends Model
{
    protected $fillable = [
        'transfer_code',
        'source_branch_id',
        'target_branch_id',
        'source_location_id',
        'target_location_id',
        'status', // draft, in_transit, completed, cancelled
        'notes',
        'sent_at',
        'received_at',
        'created_by',
        'received_by',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function sourceBranch()
    {
        return $this->belongsTo(Branch::class, 'source_branch_id');
    }

    public function targetBranch()
    {
        return $this->belongsTo(Branch::class, 'target_branch_id');
    }

    public function sourceLocation()
    {
        return $this->belongsTo(Location::class, 'source_location_id');
    }

    public function targetLocation()
    {
        return $this->belongsTo(Location::class, 'target_location_id');
    }

    public function items()
    {
        return $this->hasMany(BranchTransferItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
