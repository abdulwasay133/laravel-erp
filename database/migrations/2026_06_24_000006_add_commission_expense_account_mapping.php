<?php

use App\Models\ChartOfAccount;
use App\Models\SystemAccountMapping;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $expenseAccount = ChartOfAccount::where('code', '51')->first();
        if ($expenseAccount) {
            SystemAccountMapping::updateOrCreate(
                ['key' => 'commission_expense'],
                [
                    'chart_of_account_id' => $expenseAccount->id,
                    'description' => 'Commission expense for order bookers',
                ]
            );
        }
    }

    public function down(): void
    {
        SystemAccountMapping::where('key', 'commission_expense')->delete();
    }
};
