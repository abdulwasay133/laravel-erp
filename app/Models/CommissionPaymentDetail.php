<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionPaymentDetail extends Model
{
    protected $table = 'commission_payment_details';

    protected $guarded = [];

    public function commissionPayment()
    {
        return $this->belongsTo(CommissionPayment::class, 'commission_payment_id');
    }

    public function commission()
    {
        return $this->belongsTo(Commission::class);
    }
}
