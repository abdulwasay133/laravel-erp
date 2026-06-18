<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class POSPayment extends Model
{
    protected $table = 'pos_payments';

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(POSTransaction::class, 'pos_transaction_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(POSSession::class, 'pos_session_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }
}
