<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2, '.', ',');
    }

    public function getPaymentTypeLabel()
    {
        return $this->payment_type === 'credit' ? 'Credit' : 'Debit';
    }

    public function getPaymentMethodLabel()
    {
        return $this->payment_method === 'cash' ? 'Cash' : 'Account';
    }
}
