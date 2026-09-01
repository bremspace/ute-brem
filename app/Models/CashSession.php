<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashSession extends Model
{
    protected $fillable = [
        'session_date',
        'location_id',
        'cashier_id',
        'opening_cash',
        'closing_cash',
        'opened_at',
        'closed_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'session_date' => 'date',
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}

