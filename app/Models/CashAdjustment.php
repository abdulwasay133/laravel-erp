<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashAdjustment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'adjustment_date' => 'date',
    ];

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2, '.', ',');
    }

    public function getAdjustmentTypeLabelAttribute()
    {
        return $this->adjustment_type === 'increase' ? 'Increase' : 'Decrease';
    }
}
