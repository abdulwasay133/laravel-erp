<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\GeneralLedger;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\SystemAccountMapping;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    public function getAccount(string $key): ?int
    {
        return SystemAccountMapping::getAccount($key);
    }

    public function postJournalEntry(array $data): JournalEntry
    {
        return DB::transaction(function () use ($data) {
            $totalDebit = collect($data['lines'])->sum('debit');
            $totalCredit = collect($data['lines'])->sum('credit');

            foreach ($data['lines'] as $i => $line) {
                if (empty($line['chart_of_account_id'])) {
                    throw new \RuntimeException(
                        'Journal entry line ' . ($i + 1) . ' is missing chart_of_account_id. '
                        . 'Run php artisan db:seed --class=ChartOfAccountSeeder to set up system accounts.'
                    );
                }
            }

            $entry = JournalEntry::create([
                'voucher_no' => $data['voucher_no'] ?? $this->generateVoucherNo(),
                'date' => $data['date'],
                'description' => $data['description'] ?? null,
                'reference_type' => $data['reference_type'],
                'reference_id' => $data['reference_id'],
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
            ]);

            $runningBalances = [];

            foreach ($data['lines'] as $line) {
                $lineModel = $entry->lines()->create([
                    'chart_of_account_id' => $line['chart_of_account_id'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'description' => $line['description'] ?? null,
                ]);

                $accountId = $line['chart_of_account_id'];

                if (!isset($runningBalances[$accountId])) {
                    $lastBalance = GeneralLedger::where('chart_of_account_id', $accountId)
                        ->orderBy('id', 'desc')
                        ->value('balance') ?? 0;
                    $runningBalances[$accountId] = $lastBalance;
                }

                $account = ChartOfAccount::find($accountId);
                $normalDebit = $account && $account->normal_balance === 'debit';

                if ($normalDebit) {
                    $runningBalances[$accountId] += $line['debit'] - $line['credit'];
                } else {
                    $runningBalances[$accountId] += $line['credit'] - $line['debit'];
                }

                $entry->generalLedgers()->create([
                    'chart_of_account_id' => $accountId,
                    'journal_entry_line_id' => $lineModel->id,
                    'date' => $data['date'],
                    'reference_type' => $data['reference_type'],
                    'reference_id' => $data['reference_id'],
                    'voucher_no' => $entry->voucher_no,
                    'description' => $line['description'] ?? $data['description'] ?? null,
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'balance' => $runningBalances[$accountId],
                ]);

                $this->updateAccountBalance($accountId);
            }

            return $entry;
        });
    }

    public function reverseJournalEntry(string $referenceType, int $referenceId, string $reason = 'Reversed'): ?JournalEntry
    {
        $originalEntry = JournalEntry::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->first();

        if (!$originalEntry) {
            return null;
        }

        $lines = $originalEntry->lines()->get()->map(function ($line) {
            return [
                'chart_of_account_id' => $line->chart_of_account_id,
                'debit' => $line->credit,
                'credit' => $line->debit,
                'description' => $reason . ': ' . ($line->description ?? ''),
            ];
        })->toArray();

        return $this->postJournalEntry([
            'voucher_no' => $this->generateVoucherNo(),
            'date' => now()->toDateString(),
            'description' => $reason . ' - ' . $originalEntry->description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'lines' => $lines,
        ]);
    }

    public function generateVoucherNo(): string
    {
        $prefix = 'JV-' . now()->format('Ymd');
        $last = JournalEntry::where('voucher_no', 'like', $prefix . '-%')
            ->orderBy('id', 'desc')
            ->value('voucher_no');

        if ($last) {
            $num = (int) substr($last, strrpos($last, '-') + 1) + 1;
        } else {
            $num = 1;
        }

        return $prefix . '-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function updateAccountBalance(int $accountId): void
    {
        $account = ChartOfAccount::find($accountId);
        if ($account) {
            $account->updateBalance();
        }
    }

    public function postSaleInvoice(int $saleId, float $totalAmount, bool $hasInventory = true, ?float $cogs = null): JournalEntry
    {
        $arAccountId = $this->getAccount('accounts_receivable');
        $revenueAccountId = $this->getAccount('sales_revenue');
        $cogsAccountId = $this->getAccount('cogs');
        $inventoryAccountId = $this->getAccount('inventory_asset');

        $lines = [];

        $lines[] = [
            'chart_of_account_id' => $arAccountId,
            'debit' => $totalAmount,
            'credit' => 0,
            'description' => 'Sale invoice receivable',
        ];

        $lines[] = [
            'chart_of_account_id' => $revenueAccountId,
            'debit' => 0,
            'credit' => $totalAmount,
            'description' => 'Sale invoice revenue',
        ];

        if ($hasInventory && $cogsAccountId && $inventoryAccountId && $cogs) {
            $lines[] = [
                'chart_of_account_id' => $cogsAccountId,
                'debit' => $cogs,
                'credit' => 0,
                'description' => 'Cost of goods sold',
            ];

            $lines[] = [
                'chart_of_account_id' => $inventoryAccountId,
                'debit' => 0,
                'credit' => $cogs,
                'description' => 'Inventory reduction',
            ];
        }

        return $this->postJournalEntry([
            'voucher_no' => $this->generateVoucherNo(),
            'date' => now()->toDateString(),
            'description' => 'Sales Invoice #' . $saleId,
            'reference_type' => 'Sale',
            'reference_id' => $saleId,
            'lines' => $lines,
        ]);
    }

    public function postSalePayment(int $saleId, float $amount, string $paymentMethod = 'cash'): JournalEntry
    {
        $cashAccountId = $this->getAccount($paymentMethod === 'bank' ? 'bank_account' : 'cash_account');
        $arAccountId = $this->getAccount('accounts_receivable');

        return $this->postJournalEntry([
            'voucher_no' => $this->generateVoucherNo(),
            'date' => now()->toDateString(),
            'description' => 'Sale Payment for Invoice #' . $saleId,
            'reference_type' => 'SalePayment',
            'reference_id' => $saleId,
            'lines' => [
                [
                    'chart_of_account_id' => $cashAccountId,
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => 'Payment received',
                ],
                [
                    'chart_of_account_id' => $arAccountId,
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => 'Receivable settlement',
                ],
            ],
        ]);
    }

    public function postPurchaseInvoice(int $purchaseId, float $totalAmount, bool $isInventory = true): JournalEntry
    {
        $apAccountId = $this->getAccount('accounts_payable');
        $inventoryAccountId = $this->getAccount('inventory_asset');
        $expenseAccountId = $this->getAccount('expense_default');

        $lines = [];

        $lines[] = [
            'chart_of_account_id' => $isInventory ? $inventoryAccountId : $expenseAccountId,
            'debit' => $totalAmount,
            'credit' => 0,
            'description' => 'Purchase invoice',
        ];

        $lines[] = [
            'chart_of_account_id' => $apAccountId,
            'debit' => 0,
            'credit' => $totalAmount,
            'description' => 'Payable for purchase',
        ];

        return $this->postJournalEntry([
            'voucher_no' => $this->generateVoucherNo(),
            'date' => now()->toDateString(),
            'description' => 'Purchase Invoice #' . $purchaseId,
            'reference_type' => 'Purchase',
            'reference_id' => $purchaseId,
            'lines' => $lines,
        ]);
    }

    public function postSupplierPayment(int $supplierPaymentId, float $amount, string $paymentMethod = 'cash'): JournalEntry
    {
        $cashAccountId = $this->getAccount($paymentMethod === 'bank' ? 'bank_account' : 'cash_account');
        $apAccountId = $this->getAccount('accounts_payable');

        return $this->postJournalEntry([
            'voucher_no' => $this->generateVoucherNo(),
            'date' => now()->toDateString(),
            'description' => 'Supplier Payment #' . $supplierPaymentId,
            'reference_type' => 'SupplierPayment',
            'reference_id' => $supplierPaymentId,
            'lines' => [
                [
                    'chart_of_account_id' => $apAccountId,
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => 'Payable settlement',
                ],
                [
                    'chart_of_account_id' => $cashAccountId,
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => 'Payment made',
                ],
            ],
        ]);
    }

    public function postExpense(int $expenseId, float $amount, int $expenseAccountId, string $paymentMethod = 'cash'): JournalEntry
    {
        $cashAccountId = $this->getAccount($paymentMethod === 'bank' ? 'bank_account' : 'cash_account');

        return $this->postJournalEntry([
            'voucher_no' => $this->generateVoucherNo(),
            'date' => now()->toDateString(),
            'description' => 'Expense Voucher #' . $expenseId,
            'reference_type' => 'Expense',
            'reference_id' => $expenseId,
            'lines' => [
                [
                    'chart_of_account_id' => $expenseAccountId,
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => 'Expense incurred',
                ],
                [
                    'chart_of_account_id' => $cashAccountId,
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => 'Payment for expense',
                ],
            ],
        ]);
    }

    public function postSalaryExpense(int $salaryPaymentId, float $totalSalary): JournalEntry
    {
        $salaryExpenseId = $this->getAccount('salary_expense');
        $salaryPayableId = $this->getAccount('salary_payable');
        $cashAccountId = $this->getAccount('cash_account');

        return $this->postJournalEntry([
            'voucher_no' => $this->generateVoucherNo(),
            'date' => now()->toDateString(),
            'description' => 'Salary Payment #' . $salaryPaymentId,
            'reference_type' => 'SalaryPayment',
            'reference_id' => $salaryPaymentId,
            'lines' => [
                [
                    'chart_of_account_id' => $salaryExpenseId,
                    'debit' => $totalSalary,
                    'credit' => 0,
                    'description' => 'Salary expense accrued',
                ],
                [
                    'chart_of_account_id' => $cashAccountId,
                    'debit' => 0,
                    'credit' => $totalSalary,
                    'description' => 'Salary paid',
                ],
            ],
        ]);
    }

    public function postCustomerPayment(int $customerPaymentId, float $amount, string $paymentMethod = 'cash', string $paymentType = 'credit'): ?JournalEntry
    {
        if ($paymentType === 'debit') {
            return null;
        }

        $cashAccountId = $this->getAccount($paymentMethod === 'bank' ? 'bank_account' : 'cash_account');
        $arAccountId = $this->getAccount('accounts_receivable');

        return $this->postJournalEntry([
            'voucher_no' => $this->generateVoucherNo(),
            'date' => now()->toDateString(),
            'description' => 'Customer Payment #' . $customerPaymentId,
            'reference_type' => 'CustomerPayment',
            'reference_id' => $customerPaymentId,
            'lines' => [
                [
                    'chart_of_account_id' => $cashAccountId,
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => 'Customer payment received',
                ],
                [
                    'chart_of_account_id' => $arAccountId,
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => 'Receivable settlement',
                ],
            ],
        ]);
    }

    public function postSaleReturn(int $saleReturnId, float $amount): JournalEntry
    {
        $arAccountId = $this->getAccount('accounts_receivable');
        $salesReturnAccountId = $this->getAccount('sales_returns');
        $inventoryAccountId = $this->getAccount('inventory_asset');
        $cogsAccountId = $this->getAccount('cogs');

        $lines = [];

        $lines[] = [
            'chart_of_account_id' => $salesReturnAccountId,
            'debit' => $amount,
            'credit' => 0,
            'description' => 'Sales return',
        ];

        $lines[] = [
            'chart_of_account_id' => $arAccountId,
            'debit' => 0,
            'credit' => $amount,
            'description' => 'Receivable reduction',
        ];

        if ($inventoryAccountId && $cogsAccountId) {
            $lines[] = [
                'chart_of_account_id' => $inventoryAccountId,
                'debit' => $amount,
                'credit' => 0,
                'description' => 'Inventory restored',
            ];

            $lines[] = [
                'chart_of_account_id' => $cogsAccountId,
                'debit' => 0,
                'credit' => $amount,
                'description' => 'COGS reversal',
            ];
        }

        return $this->postJournalEntry([
            'voucher_no' => $this->generateVoucherNo(),
            'date' => now()->toDateString(),
            'description' => 'Sales Return #' . $saleReturnId,
            'reference_type' => 'SaleReturn',
            'reference_id' => $saleReturnId,
            'lines' => $lines,
        ]);
    }

    public function postWaste(int $wasteId, float $totalCost, string $wasteDate): JournalEntry
    {
        $wasteExpenseId = $this->getAccount('waste_expense');
        $inventoryAccountId = $this->getAccount('inventory_asset');

        return $this->postJournalEntry([
            'voucher_no' => $this->generateVoucherNo(),
            'date' => $wasteDate,
            'description' => 'Product Waste #' . $wasteId,
            'reference_type' => 'ProductWaste',
            'reference_id' => $wasteId,
            'lines' => [
                [
                    'chart_of_account_id' => $wasteExpenseId,
                    'debit' => $totalCost,
                    'credit' => 0,
                    'description' => 'Inventory waste/expiry write-off',
                ],
                [
                    'chart_of_account_id' => $inventoryAccountId,
                    'debit' => 0,
                    'credit' => $totalCost,
                    'description' => 'Inventory reduction due to waste',
                ],
            ],
        ]);
    }

    public function postCommissionPayment(int $commissionPaymentId, float $amount, string $paymentMethod = 'cash'): JournalEntry
    {
        $commissionExpenseId = $this->getAccount('commission_expense');
        $cashAccountId = $this->getAccount($paymentMethod === 'bank' ? 'bank_account' : 'cash_account');

        return $this->postJournalEntry([
            'voucher_no' => $this->generateVoucherNo(),
            'date' => now()->toDateString(),
            'description' => 'Commission Payment #' . $commissionPaymentId,
            'reference_type' => 'CommissionPayment',
            'reference_id' => $commissionPaymentId,
            'lines' => [
                [
                    'chart_of_account_id' => $commissionExpenseId,
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => 'Commission expense incurred',
                ],
                [
                    'chart_of_account_id' => $cashAccountId,
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => 'Commission paid',
                ],
            ],
        ]);
    }

    public function postPurchaseReturn(int $purchaseReturnId, float $amount): JournalEntry
    {
        $apAccountId = $this->getAccount('accounts_payable');
        $purchaseReturnAccountId = $this->getAccount('purchase_returns');
        $inventoryAccountId = $this->getAccount('inventory_asset');

        $lines = [];

        $lines[] = [
            'chart_of_account_id' => $apAccountId,
            'debit' => $amount,
            'credit' => 0,
            'description' => 'Payable reduction for return',
        ];

        $lines[] = [
            'chart_of_account_id' => $purchaseReturnAccountId,
            'debit' => 0,
            'credit' => $amount,
            'description' => 'Purchase return',
        ];

        if ($inventoryAccountId) {
            $lines[] = [
                'chart_of_account_id' => $inventoryAccountId,
                'debit' => 0,
                'credit' => $amount,
                'description' => 'Inventory reduction for return',
            ];
        }

        return $this->postJournalEntry([
            'voucher_no' => $this->generateVoucherNo(),
            'date' => now()->toDateString(),
            'description' => 'Purchase Return #' . $purchaseReturnId,
            'reference_type' => 'PurchaseReturn',
            'reference_id' => $purchaseReturnId,
            'lines' => $lines,
        ]);
    }
}
