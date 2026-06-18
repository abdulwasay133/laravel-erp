<?php

namespace App\Services\POS;

use App\Models\POSTransaction;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class POSTransactionService
{
    public function __construct(
        protected POSInventoryService  $inventory,
        protected POSPaymentService    $payment,
        protected POSAccountingService $accounting,
        protected POSReceiptService    $receipt,
    ) {}

    public function processSale(
        \App\Models\POSSession $session,
        array $cartData,
        array $payments,
        float $tendered,
        ?int $customerId = null,
        ?string $customerName = null,
        ?string $customerPhone = null,
        ?string $notes = null,
        ?float $discountAmount = null,
    ): POSTransaction {
        DB::beginTransaction();
        try {
            $items = collect($cartData);

            $this->inventory->validateStock($items);

            $subtotal = (float) $items->sum(fn ($i) => $i['unit_price'] * $i['quantity']);
            $discountAmount ??= (float) $items->sum(fn ($i) => $i['discount_amount'] ?? 0);
            $grandTotal = $subtotal - $discountAmount;
            $change = $tendered - $grandTotal;

            $paymentTotal = (float) collect($payments)->sum('amount');
            if (abs($paymentTotal - $grandTotal) > 0.01 && abs($tendered - $grandTotal) > 0.01) {
                throw new \RuntimeException("Payment total ($paymentTotal) does not match grand total ($grandTotal).");
            }

            $transaction = POSTransaction::create([
                'pos_session_id'   => $session->id,
                'receipt_no'       => $this->generateReceiptNo($session),
                'customer_id'      => $customerId,
                'customer_name'    => $customerName,
                'customer_phone'   => $customerPhone,
                'subtotal'         => $subtotal,
                'discount_amount'  => $discountAmount,
                'grand_total'      => $grandTotal,
                'tendered_amount'  => $tendered,
                'change_amount'    => max(0, $change),
                'transaction_at'   => now(),
                'status'           => 'completed',
                'notes'            => $notes,
            ]);

            foreach ($items as $item) {
                $cost = $this->inventory->getItemCost($item);
                $lineTotal = ($item['unit_price'] * $item['quantity']) - ($item['discount_amount'] ?? 0);

                $transaction->items()->create([
                    'product_id'       => $item['product_id'] ?? null,
                    'product_batch_id' => $item['batch_id'] ?? null,
                    'product_name'     => $item['product_name'] ?? 'Unknown',
                    'sku'              => $item['sku'] ?? null,
                    'barcode'          => $item['barcode'] ?? null,
                    'quantity'         => $item['quantity'],
                    'unit_price'       => $item['unit_price'],
                    'discount_amount'  => $item['discount_amount'] ?? 0,
                    'line_total'       => $lineTotal,
                    'cost'             => $cost,
                ]);
            }

            $this->inventory->deductStock($items);

            $this->payment->process($transaction, $payments);

            $this->accounting->postSale($transaction);

            $this->linkToSale($transaction);

            DB::commit();

            return $transaction->fresh(['items', 'payments', 'customer', 'session.user']);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function void(POSTransaction $transaction): POSTransaction
    {
        DB::beginTransaction();
        try {
            if ($transaction->status !== 'completed') {
                throw new \RuntimeException('Only completed transactions can be voided.');
            }

            $this->accounting->postVoid($transaction);

            $this->inventory->restoreStock(collect($transaction->items->map(fn ($i) => [
                'product_id' => $i->product_id,
                'batch_id'   => $i->product_batch_id,
                'quantity'   => $i->quantity,
            ])->toArray()));

            $transaction->update(['status' => 'voided']);

            if ($transaction->relationLoaded('sale') && $transaction->sale) {
                $transaction->sale->update(['status' => 'cancelled']);
            } else {
                Sale::where('pos_transaction_id', $transaction->id)->update(['status' => 'cancelled']);
            }

            DB::commit();

            return $transaction->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function refundItems(POSTransaction $transaction, array $refundItems, string $reason): POSTransaction
    {
        DB::beginTransaction();
        try {
            if ($transaction->status !== 'completed') {
                throw new \RuntimeException('Only completed transactions can be refunded.');
            }

            $totalRefund = 0;
            $restoreItems = [];

            foreach ($refundItems as $ri) {
                $item = $transaction->items()->findOrFail($ri['item_id']);
                $available = $item->quantity - ($item->refunded_quantity ?? 0);

                if ($ri['quantity'] > $available) {
                    throw new \RuntimeException("Cannot refund {$ri['quantity']} of \"{$item->product_name}\". Only {$available} available.");
                }

                $lineRefund = $item->unit_price * $ri['quantity'];
                $totalRefund += $lineRefund;

                $item->increment('refunded_quantity', $ri['quantity']);

                $restoreItems[] = [
                    'product_id' => $item->product_id,
                    'batch_id'   => $item->product_batch_id,
                    'quantity'   => $ri['quantity'],
                ];
            }

            $this->inventory->restoreStock(collect($restoreItems));

            $this->accounting->postRefund($transaction, $totalRefund);

            $transaction->update([
                'refund_reason' => $reason,
            ]);

            DB::commit();

            return $transaction->fresh(['items', 'payments', 'customer']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function refund(POSTransaction $transaction): POSTransaction
    {
        DB::beginTransaction();
        try {
            if ($transaction->status !== 'completed') {
                throw new \RuntimeException('Only completed transactions can be refunded.');
            }

            $this->accounting->postRefund($transaction);

            $this->inventory->restoreStock(collect($transaction->items->map(fn ($i) => [
                'product_id' => $i->product_id,
                'batch_id'   => $i->product_batch_id,
                'quantity'   => $i->quantity,
            ])->toArray()));

            $this->payment->refund($transaction);

            $transaction->update(['status' => 'refunded']);

            if ($transaction->relationLoaded('sale') && $transaction->sale) {
                $transaction->sale->update(['status' => 'cancelled']);
            } else {
                Sale::where('pos_transaction_id', $transaction->id)->update(['status' => 'cancelled']);
            }

            DB::commit();

            return $transaction->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function generateReceiptNo(\App\Models\POSSession $session): string
    {
        $prefix = 'POS-' . str_pad((string) $session->id, 4, '0', STR_PAD_LEFT) . '-';
        $last = POSTransaction::where('receipt_no', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('receipt_no');

        if ($last) {
            $num = (int) substr($last, strrpos($last, '-') + 1) + 1;
        } else {
            $num = 1;
        }

        return $prefix . str_pad((string) $num, 6, '0', STR_PAD_LEFT);
    }

    protected function linkToSale(POSTransaction $transaction): void
    {
        $customerId = $transaction->customer_id;
        if (!$customerId && $transaction->customer_name) {
            $customer = Customer::firstOrCreate(
                [
                    'first_name' => $transaction->customer_name ?: 'Walk-in',
                    'last_name'  => '',
                ],
                [
                    'phone'   => $transaction->customer_phone,
                    'email'   => null,
                    'type'    => 'individual',
                    'status'  => 'active',
                    'balance' => 0,
                ]
            );
            $customerId = $customer->id;
            $transaction->update(['customer_id' => $customerId]);
        }

        $sale = Sale::create([
            'invoice_no'    => 'POS-' . $transaction->receipt_no,
            'customer_id'   => $customerId,
            'sale_date'     => $transaction->transaction_at->toDateString(),
            'subtotal'      => $transaction->subtotal,
            'total_amount'  => $transaction->grand_total,
            'paid_amount'   => $transaction->grand_total,
            'balance'       => 0,
            'status'        => 'completed',
            'is_pos'        => true,
            'pos_transaction_id' => $transaction->id,
            'pos_session_id'     => $transaction->pos_session_id,
        ]);

        foreach ($transaction->items as $item) {
            SaleItem::create([
                'sale_id'    => $sale->id,
                'product_id' => $item->product_id,
                'batch_id'   => $item->product_batch_id,
                'quantity'   => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal'   => $item->unit_price * $item->quantity,
                'line_total' => $item->line_total,
            ]);
        }
    }
}
