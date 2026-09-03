<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntry extends Model
{
    protected $fillable = [
        'journal_code', 'journal_date', 'source_type', 'source_id',
        'source_reference', 'description', 'debit_total', 'credit_total', 'created_by',
    ];

    protected $casts = [
        'journal_date' => 'date',
        'debit_total' => 'decimal:2',
        'credit_total' => 'decimal:2',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
