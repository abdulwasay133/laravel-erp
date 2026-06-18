<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class POSSession extends Model
{
    protected $table = 'pos_sessions';

    protected $guarded = [];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'expected_balance' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'bank_sales' => 'decimal:2',
        'refunds' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(POSTransaction::class, 'pos_session_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(POSPayment::class, 'pos_session_id');
    }

    public function holds(): HasMany
    {
        return $this->hasMany(POSHold::class, 'pos_session_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
