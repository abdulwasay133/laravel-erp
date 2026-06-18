<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $guarded = [];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'is_posting' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopePosting($query)
    {
        return $query->where('is_posting', true);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeNonSystem($query)
    {
        return $query->where('is_system', false);
    }

    public function isParent(): bool
    {
        return !$this->is_posting;
    }

    public function journalEntryLines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function generalLedgers()
    {
        return $this->hasMany(GeneralLedger::class);
    }

    public function updateBalance(): void
    {
        $balance = $this->generalLedgers()
            ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as net')
            ->value('net');

        if ($this->normal_balance === 'credit') {
            $balance = -$balance;
        }

        $this->update(['current_balance' => abs($balance)]);
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            'asset' => 'Asset',
            'liability' => 'Liability',
            'equity' => 'Equity',
            'income' => 'Income',
            'expense' => 'Expense',
        ];

        return $labels[$this->type] ?? ucfirst($this->type);
    }

    public function getSubtypeLabelAttribute()
    {
        $labels = [
            'current' => 'Current',
            'non_current' => 'Non-Current',
            'current_liability' => 'Current Liability',
            'non_current_liability' => 'Non-Current Liability',
            'current_asset' => 'Current Asset',
            'non_current_asset' => 'Non-Current Asset',
            'operating' => 'Operating',
            'non_operating' => 'Non-Operating',
            'revenue' => 'Revenue',
            'capital' => 'Capital',
            'retained_earnings' => 'Retained Earnings',
        ];

        return $labels[$this->subtype] ?? ucfirst($this->subtype);
    }
}
