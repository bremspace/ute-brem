<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class CustomerPointLedger extends Model
{
    protected $fillable = [
        'customer_id',
        'sale_id',
        'points',
        'balance_after',
        'source',
        'reference_code',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'points' => 'integer',
        'balance_after' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function files()
    {
        if (! Schema::hasTable('customer_point_ledger_files')) {
            return $this->hasMany(CustomerPointLedgerFile::class, 'customer_point_ledger_id')->whereRaw('1=0');
        }

        return $this->hasMany(CustomerPointLedgerFile::class, 'customer_point_ledger_id');
    }
}
