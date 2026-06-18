<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class POSTransaction extends Model
{
    protected $table = 'pos_transactions';

    protected $guarded = [];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'round_off' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'tendered_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'transaction_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(POSSession::class, 'pos_session_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Sale::class, 'pos_transaction_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(POSTransactionItem::class, 'pos_transaction_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(POSPayment::class, 'pos_transaction_id');
    }
}
