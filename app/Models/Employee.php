<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_order_booker' => 'boolean',
        'commission_rate' => 'decimal:2',
    ];

    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'order_booker_id');
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class, 'order_booker_id');
    }

    public function commissionPayments()
    {
        return $this->hasMany(CommissionPayment::class, 'order_booker_id');
    }
}
