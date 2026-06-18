<?php

namespace App\Services\POS;

use App\Models\POSTransaction;
use App\Services\AccountingService;

class POSAccountingService
{
    public function __construct(protected AccountingService $accounting) {}

    public function postSale(POSTransaction $transaction): void
    {
        $cashAmount = (float) $transaction->payments->where('method', 'cash')->sum('amount');
        $bankAmount = (float) $transaction->payments->where('method', 'bank')->sum('amount');
        $creditAmount = (float) $transaction->payments->where('method', 'credit')->sum('amount');

        $lines = [];

        if ($cashAmount > 0) {
            $lines[] = [
                'chart_of_account_id' => $this->accounting->getAccount('cash_account'),
                'debit' => $cashAmount,
                'credit' => 0,
                'description' => 'POS cash payment',
            ];
        }

        if ($bankAmount > 0) {
            $lines[] = [
                'chart_of_account_id' => $this->accounting->getAccount('bank_account'),
                'debit' => $bankAmount,
                'credit' => 0,
                'description' => 'POS bank payment',
            ];
        }

        if ($creditAmount > 0) {
            $lines[] = [
                'chart_of_account_id' => $this->accounting->getAccount('accounts_receivable'),
                'debit' => $creditAmount,
                'credit' => 0,
                'description' => 'POS credit sale',
            ];
        }

        $salesAmount = $transaction->subtotal - $transaction->discount_amount;
        $lines[] = [
            'chart_of_account_id' => $this->accounting->getAccount('sales_revenue'),
            'debit' => 0,
            'credit' => $salesAmount,
            'description' => 'POS sales revenue',
        ];

        $this->accounting->postJournalEntry([
            'date' => $transaction->transaction_at->toDateString(),
            'description' => "POS Sale {$transaction->receipt_no}",
            'reference_type' => 'POS',
            'reference_id' => $transaction->id,
            'lines' => $lines,
        ]);

        // COGS entry
        $totalCost = (float) $transaction->items->sum(fn ($i) => (float) $i->cost * (float) $i->quantity);

        if ($totalCost > 0) {
            $this->accounting->postJournalEntry([
                'date' => $transaction->transaction_at->toDateString(),
                'description' => "POS COGS {$transaction->receipt_no}",
                'reference_type' => 'POS_COGS',
                'reference_id' => $transaction->id,
                'lines' => [
                    [
                        'chart_of_account_id' => $this->accounting->getAccount('cogs'),
                        'debit' => $totalCost,
                        'credit' => 0,
                        'description' => 'Cost of goods sold',
                    ],
                    [
                        'chart_of_account_id' => $this->accounting->getAccount('inventory_asset'),
                        'debit' => 0,
                        'credit' => $totalCost,
                        'description' => 'Inventory reduction',
                    ],
                ],
            ]);
        }
    }

    public function postVoid(POSTransaction $transaction): void
    {
        $this->accounting->reverseJournalEntry('POS', $transaction->id);

        if ($transaction->items->sum(fn ($i) => (float) $i->cost * (float) $i->quantity) > 0) {
            $this->accounting->reverseJournalEntry('POS_COGS', $transaction->id);
        }
    }

    public function postRefund(POSTransaction $transaction, ?float $amount = null): void
    {
        if ($amount === null) {
            $this->postVoid($transaction);
            $cashAmount = (float) $transaction->payments->where('method', 'cash')->sum('amount');
            $bankAmount = (float) $transaction->payments->where('method', 'bank')->sum('amount');
        } else {
            $cashAmount = min($amount, (float) $transaction->payments->where('method', 'cash')->sum('amount'));
            $bankAmount = $amount - $cashAmount;
        }

        $lines = [];

        if ($cashAmount > 0) {
            $lines[] = [
                'chart_of_account_id' => $this->accounting->getAccount('sales_returns'),
                'debit' => $cashAmount,
                'credit' => 0,
                'description' => 'POS partial refund',
            ];
            $lines[] = [
                'chart_of_account_id' => $this->accounting->getAccount('cash_account'),
                'debit' => 0,
                'credit' => $cashAmount,
                'description' => 'Cash refunded',
            ];
        }

        if ($bankAmount > 0) {
            if (empty($lines)) {
                $lines[] = [
                    'chart_of_account_id' => $this->accounting->getAccount('sales_returns'),
                    'debit' => $bankAmount,
                    'credit' => 0,
                    'description' => 'POS partial refund',
                ];
            }
            $lines[] = [
                'chart_of_account_id' => $this->accounting->getAccount('bank_account'),
                'debit' => 0,
                'credit' => $bankAmount,
                'description' => 'Bank refunded',
            ];
        }

        if (!empty($lines)) {
            $this->accounting->postJournalEntry([
                'date' => now()->toDateString(),
                'description' => "POS Refund {$transaction->receipt_no}" . ($amount !== null ? ' (partial)' : ''),
                'reference_type' => 'POS_REFUND',
                'reference_id' => $transaction->id,
                'lines' => $lines,
            ]);
        }
    }
}
