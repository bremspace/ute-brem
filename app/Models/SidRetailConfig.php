<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SidRetailConfig extends Model
{
    protected $table = 'sid_retail_config';

    protected $fillable = [
        'name',
        'host',
        'port',
        'database',
        'username',
        'password',
        'prefix',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function importLogs()
    {
        return $this->hasMany(SidRetailImportLog::class);
    }
}