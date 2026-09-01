<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackOfficeCashTransaction extends Model
{
    protected $fillable = [
        'transaction_code',
        'transaction_date',
        'transaction_type',
        'cash_account_id',
        'target_cash_account_id',
        'cost_category_id',
        'customer_id',
        'supplier_id',
        'employee_id',
        'amount',
        'description',
        'reference',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function cashAccount()
    {
        return $this->belongsTo(BackOfficeCashAccount::class, 'cash_account_id');
    }

    public function targetCashAccount()
    {
        return $this->belongsTo(BackOfficeCashAccount::class, 'target_cash_account_id');
    }

    public function costCategory()
    {
        return $this->belongsTo(BackOfficeCostCategory::class, 'cost_category_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
