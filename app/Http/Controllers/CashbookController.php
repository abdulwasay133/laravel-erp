<?php

namespace App\Http\Controllers;

use App\Models\CashAdjustment;
use App\Models\CustomerPayment;
use App\Models\Expense;
use App\Models\Setting;
use App\Models\SupplierPayment;
use Carbon\Carbon;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CashbookController extends Controller
{
    public function index()
    {
        $stats = [
            'cash_adjustments' => CashAdjustment::count(),
            'customer_payments' => CustomerPayment::count(),
            'supplier_payments' => SupplierPayment::count(),
            'cash_expenses' => Expense::where('payment_method', 'cash')->count(),
        ];
        return view('reports.cashbook', compact('stats'));
    }

    public function search(Request $request)
    {
        if (!$request->start_date || !$request->end_date) {
            return DataTables::of(collect([]))
                ->setTotalRecords(0)
                ->setFilteredRecords(0)
                ->with(['totals' => [
                    'debit' => 0,
                    'credit' => 0,
                    'balance' => 0,
                ]])
                ->make(true);
        }

        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);

        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->endOfDay();

        $records = $this->getCashbookRecords($start, $end);

        $totals = [
            'debit' => collect($records)->sum('debit'),
            'credit' => collect($records)->sum('credit'),
            'balance' => collect($records)->last()['balance'] ?? 0,
        ];

        return DataTables::of($records)
            ->addIndexColumn()
            ->editColumn('date', function ($row) {
                return $row['date'];
            })
            ->editColumn('debit', function ($row) {
                return $row['debit'] ? number_format($row['debit'], 2, '.', ',') : '';
            })
            ->editColumn('credit', function ($row) {
                return $row['credit'] ? number_format($row['credit'], 2, '.', ',') : '';
            })
            ->editColumn('balance', function ($row) {
                return number_format($row['balance'], 2, '.', ',');
            })
            ->with(['totals' => $totals])
            ->make(true);
    }

    public function print(Request $request)
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);

        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->endOfDay();

        $records = $this->getCashbookRecords($start, $end);
        $totals = [
            'debit' => collect($records)->sum('debit'),
            'credit' => collect($records)->sum('credit'),
            'balance' => collect($records)->last()['balance'] ?? 0,
        ];

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
            . 'Cashbook' . "\n"
            . 'Period: ' . $start->format('d M, Y') . ' — ' . $end->format('d M, Y') . "\n"
            . 'Balance: Rs. ' . number_format($totals['balance'], 2);

        $qrCode = new QrCode(
            data: $qrData,
            encoding: new Encoding('UTF-8'),
            size: 200
        );

        $writer = new SvgWriter();
        $result = $writer->write($qrCode);
        $qrSvg = $result->getString();

        $documentTitle = 'Cashbook';

        return view('reports.cashbook_print', compact('records', 'totals', 'start', 'end', 'settings', 'qrSvg', 'documentTitle'));
    }

    private function getCashbookRecords(Carbon $start, Carbon $end)
    {
        $cashAdjustments = CashAdjustment::where('adjustment_date', '>=', $start->toDateString())
            ->where('adjustment_date', '<=', $end->toDateString())
            ->orderBy('adjustment_date', 'asc')
            ->get();

        $customerPayments = CustomerPayment::where('payment_date', '>=', $start->toDateString())
            ->where('payment_date', '<=', $end->toDateString())
            ->orderBy('payment_date', 'asc')
            ->get();

        $supplierPayments = SupplierPayment::where('payment_date', '>=', $start->toDateString())
            ->where('payment_date', '<=', $end->toDateString())
            ->orderBy('payment_date', 'asc')
            ->get();

        $expenses = Expense::where('payment_method', 'cash')
            ->where('expense_date', '>=', $start->toDateString())
            ->where('expense_date', '<=', $end->toDateString())
            ->orderBy('expense_date', 'asc')
            ->get();

        $records = collect();

        foreach ($cashAdjustments as $adjustment) {
            $records->push([
                'source' => 'cash_adjustment',
                'date' => Carbon::parse($adjustment->adjustment_date)->toDateString(),
                'voucher_no' => $adjustment->voucher_no,
                'voucher_type' => 'Cash Adjustment',
                'remark' => $adjustment->description ?: $adjustment->reference ?: 'Cash adjustment',
                'debit' => $adjustment->adjustment_type === 'decrease' ? $adjustment->amount : 0,
                'credit' => $adjustment->adjustment_type === 'increase' ? $adjustment->amount : 0,
            ]);
        }

        foreach ($customerPayments as $payment) {
            $records->push([
                'source' => 'customer_payment',
                'date' => $payment->payment_date->toDateString(),
                'voucher_no' => $payment->voucher_no,
                'voucher_type' => 'Customer Payment',
                'remark' => $payment->description ?: $payment->reference ?: ($payment->customer ? $payment->customer->first_name . ' ' . $payment->customer->last_name : 'Customer payment'),
                'debit' => $payment->payment_type === 'debit' ? $payment->amount : 0,
                'credit' => $payment->payment_type === 'credit' ? $payment->amount : 0,
            ]);
        }

        foreach ($supplierPayments as $payment) {
            $records->push([
                'source' => 'supplier_payment',
                'date' => $payment->payment_date->toDateString(),
                'voucher_no' => $payment->voucher_no,
                'voucher_type' => 'Supplier Payment',
                'remark' => $payment->description ?: $payment->reference ?: ($payment->supplier ? $payment->supplier->first_name . ' ' . $payment->supplier->last_name : 'Supplier payment'),
                'debit' => $payment->payment_type === 'debit' ? $payment->amount : 0,
                'credit' => $payment->payment_type === 'credit' ? $payment->amount : 0,
            ]);
        }

        foreach ($expenses as $expense) {
            $records->push([
                'source' => 'expense',
                'date' => $expense->expense_date->toDateString(),
                'voucher_no' => $expense->voucher_no,
                'voucher_type' => 'Expense',
                'remark' => $expense->title . ($expense->description ? ' - ' . $expense->description : ''),
                'debit' => $expense->amount,
                'credit' => 0,
            ]);
        }

        $records = $records->sortBy(function ($row) {
            return $row['date'];
        })->values();

        $runningBalance = 0;
        return $records->map(function ($row) use (&$runningBalance) {
            $runningBalance += $row['credit'] - $row['debit'];
            $row['balance'] = round($runningBalance, 2);
            return $row;
        });
    }
}
