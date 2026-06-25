<?php

use App\Models\ChartOfAccount;
use App\Models\SystemAccountMapping;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $expenseRoot = ChartOfAccount::where('code', '5')->first();

        if (!$expenseRoot) {
            return;
        }

        $wasteExpense = ChartOfAccount::updateOrCreate(
            ['code' => '54'],
            [
                'name' => 'Waste/Expiry',
                'type' => 'expense',
                'parent_id' => $expenseRoot->id,
                'level' => 1,
                'normal_balance' => 'debit',
                'is_posting' => true,
                'is_system' => true,
                'is_active' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
                'description' => 'Inventory waste and expiry write-offs',
            ]
        );

        SystemAccountMapping::updateOrCreate(
            ['key' => 'waste_expense'],
            [
                'chart_of_account_id' => $wasteExpense->id,
                'description' => 'Waste/Expiry expense account',
            ]
        );
    }

    public function down(): void
    {
        SystemAccountMapping::where('key', 'waste_expense')->delete();
        ChartOfAccount::where('code', '54')->delete();
    }
};
