<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Setting;
use App\Models\SystemAccountMapping;
use Illuminate\Database\Seeder;

class POSSeeder extends Seeder
{
    public function run(): void
    {
        // 1. POS Settings
        $settings = [
            'pos_receipt_label'       => 'SALE INVOICE',
            'pos_receipt_footer'      => 'Thank you for your purchase!',
            'pos_thermal_width'       => '48',
            'pos_default_customer_id' => '',
            'pos_auto_print'          => '1',
            'pos_currency_symbol'     => 'Rs. ',
            'pos_tax_label'           => 'GST',
            'pos_tax_rate'            => '0',
        ];

        foreach ($settings as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        // 2. Ensure POS-related system account mappings exist
        $posMappings = [
            'sales_returns' => 'Sales Returns',
            'purchase_returns' => 'Purchase Returns',
        ];

        foreach ($posMappings as $key => $description) {
            if (!SystemAccountMapping::where('key', $key)->exists()) {
                $account = ChartOfAccount::where('type', 'income')
                    ->where('name', 'like', '%return%')
                    ->first();

                if (!$account) {
                    $account = ChartOfAccount::create([
                        'code' => '4100',
                        'name' => $description,
                        'type' => 'income',
                        'subtype' => 'operating_income',
                        'is_active' => true,
                        'is_posting' => true,
                        'is_system' => true,
                        'normal_balance' => 'credit',
                    ]);
                }

                SystemAccountMapping::create([
                    'key' => $key,
                    'chart_of_account_id' => $account->id,
                    'is_system' => true,
                ]);
            }
        }
    }
}
