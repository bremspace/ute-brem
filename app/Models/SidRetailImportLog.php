<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SidRetailImportLog extends Model
{
    protected $table = 'sid_retail_import_logs';

    protected $fillable = [
        'sid_retail_config_id',
        'table_name',
        'status',
        'records_total',
        'records_imported',
        'records_failed',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'records_total' => 'integer',
        'records_imported' => 'integer',
        'records_failed' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function config()
    {
        return $this->belongsTo(SidRetailConfig::class, 'sid_retail_config_id');
    }
}