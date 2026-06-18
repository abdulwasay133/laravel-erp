<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class POSHold extends Model
{
    protected $table = 'pos_holds';

    protected $guarded = [];

    protected $casts = [
        'cart_data' => 'json',
        'held_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(POSSession::class, 'pos_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
