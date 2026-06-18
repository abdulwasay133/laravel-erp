<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'is_balanced' => 'boolean',
    ];

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function generalLedgers()
    {
        return $this->hasMany(GeneralLedger::class);
    }

    public function scopeForReference($query, $type, $id)
    {
        return $query->where('reference_type', $type)->where('reference_id', $id);
    }
}
