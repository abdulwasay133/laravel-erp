<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class ProcessSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_id'          => 'required|exists:pos_sessions,id',
            'customer_id'         => 'nullable|exists:customers,id',
            'customer_name'       => 'nullable|string|max:255',
            'customer_phone'      => 'nullable|string|max:20',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'nullable|exists:products,id',
            'items.*.batch_id'    => 'nullable|exists:product_batches,id',
            'items.*.product_name'=> 'required|string|max:255',
            'items.*.barcode'     => 'nullable|string|max:100',
            'items.*.sku'         => 'nullable|string|max:100',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'payments'            => 'required|array|min:1',
            'payments.*.method'   => 'required|in:cash,bank,credit',
            'payments.*.amount'   => 'required|numeric|min:0',
            'payments.*.bank_account_id' => 'nullable|exists:bank_accounts,id',
            'payments.*.reference' => 'nullable|string|max:255',
            'discount_amount'     => 'nullable|numeric|min:0',
            'tendered_amount'     => 'required|numeric|min:0',
            'notes'               => 'nullable|string|max:1000',
        ];
    }
}
