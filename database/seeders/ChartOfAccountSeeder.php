<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\SystemAccountMapping;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        // ====================================================================
        // ASSETS
        // ====================================================================
        $assets = ChartOfAccount::create([
            'code' => '1', 'name' => 'Assets', 'type' => 'asset',
            'parent_id' => null, 'level' => 0, 'normal_balance' => 'debit',
            'is_posting' => false, 'is_system' => false, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => 'Total assets',
        ]);

        $cashInHand = ChartOfAccount::create([
            'code' => '11', 'name' => 'Cash in Hand', 'type' => 'asset',
            'parent_id' => $assets->id, 'level' => 1, 'normal_balance' => 'debit',
            'is_posting' => true, 'is_system' => true, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => 'Physical cash on hand',
        ]);

        $bankAccounts = ChartOfAccount::create([
            'code' => '12', 'name' => 'Bank accounts', 'type' => 'asset',
            'parent_id' => $assets->id, 'level' => 1, 'normal_balance' => 'debit',
            'is_posting' => true, 'is_system' => true, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => 'Bank and cash equivalents',
        ]);

        $customerReceivables = ChartOfAccount::create([
            'code' => '13', 'name' => 'Customer receivables', 'type' => 'asset',
            'parent_id' => $assets->id, 'level' => 1, 'normal_balance' => 'debit',
            'is_posting' => true, 'is_system' => true, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => 'Amounts receivable from customers',
        ]);

        $creditSalesControl = ChartOfAccount::create([
            'code' => '14', 'name' => 'Credit sales control', 'type' => 'asset',
            'parent_id' => $assets->id, 'level' => 1, 'normal_balance' => 'debit',
            'is_posting' => true, 'is_system' => false, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => 'Control account for credit sales',
        ]);

        $rawMaterial = ChartOfAccount::create([
            'code' => '15', 'name' => 'Raw material', 'type' => 'asset',
            'parent_id' => $assets->id, 'level' => 1, 'normal_balance' => 'debit',
            'is_posting' => true, 'is_system' => false, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => 'Raw materials inventory',
        ]);

        $tradingMaterial = ChartOfAccount::create([
            'code' => '16', 'name' => 'Trading material', 'type' => 'asset',
            'parent_id' => $assets->id, 'level' => 1, 'normal_balance' => 'debit',
            'is_posting' => true, 'is_system' => true, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => 'Trading / finished goods inventory',
        ]);

        // ====================================================================
        // LIABILITIES
        // ====================================================================
        $liabilities = ChartOfAccount::create([
            'code' => '2', 'name' => 'Liabilities', 'type' => 'liability',
            'parent_id' => null, 'level' => 0, 'normal_balance' => 'credit',
            'is_posting' => false, 'is_system' => false, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => 'Total liabilities',
        ]);

        $suppliersPayables = ChartOfAccount::create([
            'code' => '21', 'name' => 'Suppliers payables', 'type' => 'liability',
            'parent_id' => $liabilities->id, 'level' => 1, 'normal_balance' => 'credit',
            'is_posting' => true, 'is_system' => true, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => 'Amounts payable to suppliers',
        ]);

        // Internal system accounts under Liabilities (hidden from main listing)
        $salaryPayable = ChartOfAccount::create([
            'code' => '22', 'name' => 'Salary payable', 'type' => 'liability',
            'parent_id' => $liabilities->id, 'level' => 1, 'normal_balance' => 'credit',
            'is_posting' => true, 'is_system' => true, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => 'Unpaid salaries and wages',
        ]);

        // ====================================================================
        // EQUITY
        // ====================================================================
        $equity = ChartOfAccount::create([
            'code' => '3', 'name' => 'Equity', 'type' => 'equity',
            'parent_id' => null, 'level' => 0, 'normal_balance' => 'credit',
            'is_posting' => false, 'is_system' => false, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => "Owner's equity",
        ]);

        $capital = ChartOfAccount::create([
            'code' => '31', 'name' => 'Capital', 'type' => 'equity',
            'parent_id' => $equity->id, 'level' => 1, 'normal_balance' => 'credit',
            'is_posting' => true, 'is_system' => true, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => "Owner's capital",
        ]);

        $ownerInvestment = ChartOfAccount::create([
            'code' => '32', 'name' => 'Owner investment', 'type' => 'equity',
            'parent_id' => $equity->id, 'level' => 1, 'normal_balance' => 'credit',
            'is_posting' => true, 'is_system' => false, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => 'Additional owner investments',
        ]);

        $accumulatedProfit = ChartOfAccount::create([
            'code' => '33', 'name' => 'Accumulated profit', 'type' => 'equity',
            'parent_id' => $equity->id, 'level' => 1, 'normal_balance' => 'credit',
            'is_posting' => true, 'is_system' => true, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => 'Retained earnings',
        ]);

        $currentYearProfit = ChartOfAccount::create([
            'code' => '34', 'name' => 'Current year profit', 'type' => 'equity',
            'parent_id' => $equity->id, 'level' => 1, 'normal_balance' => 'credit',
            'is_posting' => true, 'is_system' => false, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => 'Current fiscal year profit / loss',
        ]);

        // ====================================================================
        // INCOME
        // ====================================================================
        $income = ChartOfAccount::create([
            'code' => '4', 'name' => 'Income', 'type' => 'income',
            'parent_id' => null, 'level' => 0, 'normal_balance' => 'credit',
            'is_posting' => false, 'is_system' => false, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => 'Total income',
        ]);

        $productSales = ChartOfAccount::create([
            'code' => '41', 'name' => 'Product sales', 'type' => 'income',
            'parent_id' => $income->id, 'level' => 1, 'normal_balance' => 'credit',
            'is_posting' => true, 'is_system' => true, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => 'Revenue from product sales',
        ]);

        // ====================================================================
        // EXPENSES
        // ====================================================================
        $expenseRoot = ChartOfAccount::create([
            'code' => '5', 'name' => 'Expenses', 'type' => 'expense',
            'parent_id' => null, 'level' => 0, 'normal_balance' => 'debit',
            'is_posting' => false, 'is_system' => false, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => 'Total expenses',
        ]);

        $generalExpense = ChartOfAccount::create([
            'code' => '51', 'name' => 'Expense', 'type' => 'expense',
            'parent_id' => $expenseRoot->id, 'level' => 1, 'normal_balance' => 'debit',
            'is_posting' => true, 'is_system' => true, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => 'General business expenses',
        ]);

        $salaries = ChartOfAccount::create([
            'code' => '52', 'name' => 'Salaries', 'type' => 'expense',
            'parent_id' => $expenseRoot->id, 'level' => 1, 'normal_balance' => 'debit',
            'is_posting' => true, 'is_system' => true, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => 'Employee salaries and wages',
        ]);

        // Internal system account for COGS (hidden)
        $cogs = ChartOfAccount::create([
            'code' => '53', 'name' => 'Cost of Goods Sold', 'type' => 'expense',
            'parent_id' => $expenseRoot->id, 'level' => 1, 'normal_balance' => 'debit',
            'is_posting' => true, 'is_system' => true, 'is_active' => true,
            'opening_balance' => 0, 'current_balance' => 0,
            'description' => 'Cost of goods sold',
        ]);

        // ====================================================================
        // System Account Mappings
        // ====================================================================
        $mappings = [
            'cash_account'       => [$cashInHand->id,         'Cash in hand account'],
            'bank_account'       => [$bankAccounts->id,       'Bank accounts'],
            'accounts_receivable'=> [$customerReceivables->id,'Customer receivables'],
            'inventory_asset'    => [$tradingMaterial->id,    'Trading material inventory'],
            'accounts_payable'   => [$suppliersPayables->id,  'Supplier payables'],
            'salary_payable'     => [$salaryPayable->id,      'Salary payable liability'],
            'owner_capital'      => [$capital->id,            "Owner's capital"],
            'retained_earnings'  => [$accumulatedProfit->id,  'Accumulated profit'],
            'sales_revenue'      => [$productSales->id,       'Product sales revenue'],
            'expense_default'    => [$generalExpense->id,     'General expenses'],
            'salary_expense'     => [$salaries->id,           'Salaries expense'],
            'cogs'               => [$cogs->id,               'Cost of goods sold'],
        ];

        foreach ($mappings as $key => [$accountId, $description]) {
            SystemAccountMapping::updateOrCreate(
                ['key' => $key],
                ['chart_of_account_id' => $accountId, 'description' => $description]
            );
        }
    }
}
