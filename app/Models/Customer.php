<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'customer_group_id',
        'member_code',
        'name',
        'phone',
        'email',
        'password',
        'type',
        'is_active',
        'points_balance',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'points_balance' => 'integer',
        ];
    }

    public function group()
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }

    public function isMember(): bool
    {
        return $this->type === 'member' && $this->is_active;
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function serviceTransactions()
    {
        return $this->hasMany(ServiceTransaction::class);
    }

    public function transactionPayments()
    {
        return $this->hasMany(TransactionPayment::class);
    }
}
