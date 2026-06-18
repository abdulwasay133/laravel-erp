<?php

namespace App\Services\POS;

use App\Models\BankAccount;
use App\Models\POSPayment;
use App\Models\POSTransaction;
use Illuminate\Support\Collection;

class POSPaymentService
{
    public function process(POSTransaction $transaction, array $payments): Collection
    {
        $totalPaid = 0;
        $records = [];

        foreach ($payments as $payment) {
            $records[] = POSPayment::create([
                'pos_transaction_id' => $transaction->id,
                'pos_session_id'     => $transaction->pos_session_id,
                'method'             => $payment['method'],
                'bank_account_id'    => $payment['bank_account_id'] ?? null,
                'amount'             => $payment['amount'],
                'reference'          => $payment['reference'] ?? null,
            ]);

            $totalPaid += $payment['amount'];

            if (($payment['method'] ?? '') === 'bank' && !empty($payment['bank_account_id'])) {
                BankAccount::where('id', $payment['bank_account_id'])
                    ->increment('current_balance', $payment['amount']);
            }
        }

        return collect($records);
    }

    public function refund(POSTransaction $transaction): void
    {
        foreach ($transaction->payments as $payment) {
            if ($payment->method === 'bank' && $payment->bank_account_id) {
                BankAccount::where('id', $payment->bank_account_id)
                    ->decrement('current_balance', $payment->amount);
            }
        }
    }
}
