<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\CommissionPayment;
use App\Models\CommissionPaymentDetail;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    public function __construct(
        protected AccountingService $accountingService
    ) {}

    public function postCommissionToAccounting(CommissionPayment $payment): void
    {
        $this->accountingService->postCommissionPayment(
            $payment->id,
            $payment->amount,
            $payment->payment_method
        );
    }
    public function generateCommission(Sale $sale): ?Commission
    {
        if (!$sale->order_booker_id || $sale->status !== 'completed') {
            return null;
        }

        $employee = $sale->orderBooker;
        if (!$employee || !$employee->is_order_booker) {
            return null;
        }

        $exists = Commission::where('sale_id', $sale->id)->exists();
        if ($exists) {
            return null;
        }

        $rate = $employee->commission_rate;
        $commissionAmount = ($sale->total_amount * $rate) / 100;

        return Commission::create([
            'sale_id' => $sale->id,
            'order_booker_id' => $employee->id,
            'sale_amount' => $sale->total_amount,
            'commission_rate' => $rate,
            'commission_amount' => $commissionAmount,
            'status' => 'pending',
        ]);
    }

    public function approveCommission(Commission $commission): void
    {
        if ($commission->status !== 'pending') {
            throw new \RuntimeException('Only pending commissions can be approved.');
        }
        $commission->update(['status' => 'approved']);
    }

    public function cancelCommission(Commission $commission): void
    {
        if ($commission->status === 'paid') {
            throw new \RuntimeException('Paid commissions cannot be cancelled.');
        }
        $commission->update(['status' => 'cancelled']);
    }

    public function payCommissions(array $commissionIds, int $orderBookerId, string $paymentMethod, string $paymentDate, ?string $referenceNo = null, ?string $remarks = null): CommissionPayment
    {
        return DB::transaction(function () use ($commissionIds, $orderBookerId, $paymentMethod, $paymentDate, $referenceNo, $remarks) {
            $commissions = Commission::whereIn('id', $commissionIds)
                ->where('order_booker_id', $orderBookerId)
                ->whereIn('status', ['pending', 'approved'])
                ->get();

            if ($commissions->isEmpty()) {
                throw new \RuntimeException('No payable commissions found.');
            }

            $totalAmount = $commissions->sum('commission_amount');

            $lastPayment = CommissionPayment::latest()->first();
            $nextNumber = $lastPayment ? intval(substr($lastPayment->payment_no, -6)) + 1 : 1;
            $paymentNo = 'CP-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            $payment = CommissionPayment::create([
                'payment_no' => $paymentNo,
                'order_booker_id' => $orderBookerId,
                'payment_date' => $paymentDate,
                'amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'reference_no' => $referenceNo,
                'remarks' => $remarks,
                'created_by' => auth()->id(),
            ]);

            foreach ($commissions as $commission) {
                CommissionPaymentDetail::create([
                    'commission_payment_id' => $payment->id,
                    'commission_id' => $commission->id,
                    'paid_amount' => $commission->commission_amount,
                ]);

                $commission->update(['status' => 'paid']);
            }

            $this->postCommissionToAccounting($payment);

            return $payment;
        });
    }
}
