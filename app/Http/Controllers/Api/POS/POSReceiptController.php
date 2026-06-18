<?php

namespace App\Http\Controllers\Api\POS;

use App\Http\Controllers\Controller;
use App\Models\POSTransaction;
use App\Services\POS\POSReceiptService;
use Illuminate\Http\JsonResponse;

class POSReceiptController extends Controller
{
    public function __construct(protected POSReceiptService $receiptService) {}

    public function show(POSTransaction $transaction): JsonResponse
    {
        $transaction->load(['items', 'payments', 'customer', 'session.user']);

        return response()->json([
            'transaction' => $transaction,
            'thermal'     => $this->receiptService->getThermalData($transaction),
        ]);
    }

    public function print(POSTransaction $transaction)
    {
        $transaction->load(['items', 'payments', 'customer', 'session.user']);
        return view('pos.receipt-thermal', compact('transaction'));
    }

    public function json(POSTransaction $transaction): JsonResponse
    {
        $data = $this->receiptService->getThermalData($transaction);

        return response()->json([
            'raw'   => $data['raw'],
            'lines' => $data['lines'],
        ]);
    }

    public function pdf(POSTransaction $transaction)
    {
        $transaction->load(['items', 'payments', 'customer', 'session.user']);
        $settings = [
            'company_name'    => \App\Models\Setting::getValue('company_name', 'Store'),
            'company_address' => \App\Models\Setting::getValue('company_address', ''),
            'company_phone'   => \App\Models\Setting::getValue('company_phone', ''),
            'company_email'   => \App\Models\Setting::getValue('company_email', ''),
        ];

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('pos.receipt-pdf', compact('transaction', 'settings'))
            ->stream("receipt-{$transaction->receipt_no}.pdf");
    }
}
