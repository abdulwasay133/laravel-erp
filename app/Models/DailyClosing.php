<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyClosing extends Model
{
    protected $guarded = [];

    protected $casts = [
        'closing_date' => 'date',
        'last_day_closing' => 'decimal:2',
        'receive' => 'decimal:2',
        'payment' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
