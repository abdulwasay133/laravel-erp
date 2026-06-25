<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $guarded = [];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'sale_amount' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function orderBooker()
    {
        return $this->belongsTo(Employee::class, 'order_booker_id');
    }

    public function paymentDetails()
    {
        return $this->hasMany(CommissionPaymentDetail::class);
    }
}
