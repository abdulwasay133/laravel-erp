<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Setting;
use Carbon\Carbon;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;

class TodayReportController extends Controller
{
    public function index(Request $request)
    {
        $date = Carbon::parse($request->get('date', today()->toDateString()));
        $data = $this->getReportData($date);

        if ($request->ajax() || $request->has('ajax')) {
            return response()->json($data);
        }

        return view('reports.today_report', compact('date', 'data'));
    }

    public function print(Request $request)
    {
        $date = Carbon::parse($request->get('date', today()->toDateString()));
        $data = $this->getReportData($date);

        $settings = [
            'company_name'    => Setting::getValue('company_name'),
            'company_address' => Setting::getValue('company_address'),
            'company_phone'   => Setting::getValue('company_phone'),
            'company_email'   => Setting::getValue('company_email'),
            'company_website' => Setting::getValue('company_website'),
            'company_logo'    => Setting::getValue('company_logo'),
            'terms_conditions' => Setting::getValue('terms_conditions', 'Thank you for your business!'),
        ];

        $qrData = $settings['company_name'] . "\n"
            . 'Today Report: ' . $date->format('d M, Y') . "\n"
            . 'Sales: Rs. ' . number_format($data['sales']['total'], 2) . "\n"
            . 'Purchases: Rs. ' . number_format($data['purchases']['total'], 2) . "\n"
            . 'Expenses: Rs. ' . number_format($data['expenses']['total'], 2);

        $qrCode = new QrCode(
            data: $qrData,
            encoding: new Encoding('UTF-8'),
            size: 200
        );

        $writer = new SvgWriter();
        $result = $writer->write($qrCode);
        $qrSvg = $result->getString();

        $documentTitle = 'Today Report';

        return view('reports.today_report_print', compact('date', 'data', 'settings', 'qrSvg', 'documentTitle'));
    }

    private function getReportData(Carbon $date): array
    {
        $dateString = $date->toDateString();

        $sales = Sale::with('customer')
            ->where('sale_date', $dateString)
            ->orderByDesc('created_at')
            ->get();

        $purchases = Purchase::with('supplier')
            ->where('order_date', $dateString)
            ->orderByDesc('created_at')
            ->get();

        $expenses = Expense::with('chartOfAccount')
            ->where('expense_date', $dateString)
            ->orderByDesc('created_at')
            ->get();

        return [
            'date' => $date->format('d M Y'),
            'sales' => [
                'count' => $sales->count(),
                'total' => (float) $sales->sum('total_amount'),
                'records' => $sales->map(fn ($sale) => [
                    'invoice_no' => $sale->invoice_no,
                    'customer' => $sale->customer
                        ? trim($sale->customer->first_name . ' ' . $sale->customer->last_name)
                        : '-',
                    'amount' => (float) $sale->total_amount,
                    'status' => $sale->status,
                ]),
            ],
            'purchases' => [
                'count' => $purchases->count(),
                'total' => (float) $purchases->sum('grand_total'),
                'records' => $purchases->map(fn ($purchase) => [
                    'ref_no' => $purchase->ref_no,
                    'supplier' => $purchase->supplier
                        ? trim($purchase->supplier->first_name . ' ' . $purchase->supplier->last_name)
                        : '-',
                    'amount' => (float) $purchase->grand_total,
                    'status' => $purchase->status,
                ]),
            ],
            'expenses' => [
                'count' => $expenses->count(),
                'total' => (float) $expenses->sum('amount'),
                'records' => $expenses->map(fn ($expense) => [
                    'voucher_no' => $expense->voucher_no,
                    'account' => $expense->chartOfAccount
                        ? $expense->chartOfAccount->code . ' - ' . $expense->chartOfAccount->name
                        : '-',
                    'description' => $expense->title . ($expense->description ? ' - ' . $expense->description : ''),
                    'amount' => (float) $expense->amount,
                ]),
            ],
        ];
    }
}
