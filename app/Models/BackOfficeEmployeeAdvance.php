<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackOfficeEmployeeAdvance extends Model
{
    protected $fillable = [
        'advance_code',
        'advance_date',
        'employee_id',
        'cash_account_id',
        'amount',
        'paid_amount',
        'status',
        'description',
        'cash_transaction_id',
        'created_by',
    ];

    protected $casts = [
        'advance_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function cashAccount()
    {
        return $this->belongsTo(BackOfficeCashAccount::class, 'cash_account_id');
    }
}
