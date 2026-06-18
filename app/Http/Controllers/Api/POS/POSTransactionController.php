<?php

namespace App\Http\Controllers\Api\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\ProcessSaleRequest;
use App\Models\POSSession;
use App\Models\POSTransaction;
use App\Services\POS\POSTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class POSTransactionController extends Controller
{
    public function __construct(protected POSTransactionService $transactionService) {}

    public function process(ProcessSaleRequest $request): JsonResponse
    {
        $session = POSSession::findOrFail($request->session_id);

        if ($session->status !== 'open') {
            return response()->json(['message' => 'Session is not open.'], 422);
        }

        try {
            $transaction = $this->transactionService->processSale(
                session: $session,
                cartData: $request->items,
                payments: $request->payments,
                tendered: $request->tendered_amount,
                customerId: $request->customer_id,
                customerName: $request->customer_name,
                customerPhone: $request->customer_phone,
                notes: $request->notes,
                discountAmount: $request->discount_amount,
            );

            return response()->json([
                'transaction' => $transaction->load(['items', 'payments', 'customer', 'session.user']),
                'message'     => 'Sale completed.',
            ], 201);

        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(POSTransaction $transaction): JsonResponse
    {
        return response()->json([
            'transaction' => $transaction->load(['items', 'payments', 'customer', 'session.user']),
        ]);
    }

    public function void(POSTransaction $transaction): JsonResponse
    {
        try {
            $transaction = $this->transactionService->void($transaction);
            return response()->json(['transaction' => $transaction, 'message' => 'Transaction voided.']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function refund(POSTransaction $transaction): JsonResponse
    {
        try {
            $transaction = $this->transactionService->refund($transaction);
            return response()->json(['transaction' => $transaction, 'message' => 'Transaction refunded.']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function refundItems(Request $request, POSTransaction $transaction): JsonResponse
    {
        $request->validate([
            'items'   => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:pos_transaction_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'reason'  => 'required|string|max:1000',
        ]);

        try {
            $transaction = $this->transactionService->refundItems($transaction, $request->items, $request->reason);
            return response()->json(['transaction' => $transaction, 'message' => 'Refund processed successfully.']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function history(POSSession $session): JsonResponse
    {
        return response()->json([
            'transactions' => $session->transactions()
                ->with(['items', 'payments', 'customer'])
                ->latest()
                ->take(100)
                ->get(),
        ]);
    }
}
