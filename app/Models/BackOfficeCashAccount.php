<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackOfficeCashAccount extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'opening_balance',
        'current_balance',
        'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
