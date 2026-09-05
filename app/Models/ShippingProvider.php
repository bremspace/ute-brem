<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingProvider extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'is_active',
        'api_config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'api_config' => 'array',
    ];

    public function rates()
    {
        return $this->hasMany(ShippingRate::class);
    }
}
