<?php

namespace App\Http\Controllers;

use App\Models\CashAdjustment;
use App\Models\CustomerPayment;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\SalePayment;
use App\Models\Setting;
use App\Models\SupplierPayment;
use Carbon\Carbon;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CashFlowController extends Controller
{
    public function index()
    {
        $stats = [
            'cash_adjustments' => CashAdjustment::count(),
            'customer_payments' => CustomerPayment::count(),
            'supplier_payments' => SupplierPayment::count(),
            'cash_expenses' => Expense::where('payment_method', 'cash')->count(),
        ];
        return view('reports.cash_flow', compact('stats'));
    }

    public function search(Request $request)
    {
        if (!$request->start_date || !$request->end_date) {
            return DataTables::of(collect([]))
                ->setTotalRecords(0)
                ->setFilteredRecords(0)
                ->with(['totals' => $this->emptyTotals()])
                ->make(true);
        }

        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);

        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $end = Carbon::parse($validated['end_date'])->endOfDay();
        $records = $this->getCashFlowRecords($start, $end);
        $totals = $this->calculateTotals($records, $start);

        return DataTables::of($records)
            ->addIndexColumn()
            ->editColumn('inflow', fn ($row) => $row['inflow'] ? number_format($row['inflow'], 2, '.', ',') : '')
            ->editColumn('outflow', fn ($row) => $row['outflow'] ? number_format($row['outflow'], 2, '.', ',') : '')
            ->editColumn('net_amount', fn ($row) => number_format($row['net_amount'], 2, '.', ','))
            ->editColumn('section', function ($row) {
                return $row['net_amount'] >= 0
                    ? '<span class="badge bg-success">' . $row['section'] . '</span>'
                    : '<span class="badge bg-danger">' . $row['section'] . '</span>';
            })
            ->with(['totals' => $totals])
            ->rawColumns(['section'])
            ->make(true);
    }

    public function print(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);

        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $end = Carbon::parse($validated['end_date'])->endOfDay();
        $records = $this->getCashFlowRecords($start, $end);
        $totals = $this->calculateTotals($records, $start);

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
            . 'Cash Flow' . "\n"
            . 'Period: ' . $start->format('d M, Y') . ' — ' . $end->format('d M, Y') . "\n"
            . 'Closing: Rs. ' . number_format($totals['closing_cash'], 2);

        $qrCode = new QrCode(
            data: $qrData,
            encoding: new Encoding('UTF-8'),
            size: 200
        );

        $writer = new SvgWriter();
        $result = $writer->write($qrCode);
        $qrSvg = $result->getString();

        $documentTitle = 'Cash Flow';

        return view('reports.cash_flow_print', compact('records', 'totals', 'start', 'end', 'settings', 'qrSvg', 'documentTitle'));
    }

    private function getCashFlowRecords(Carbon $start, Carbon $end)
    {
        $saleCashReceipts = SalePayment::where('payment_type', 'cash')
            ->whereBetween('payment_date', [$start->toDateString(), $end->toDateString()]);

        $customerCashReceipts = CustomerPayment::where('payment_method', 'cash')
            ->where('payment_type', 'credit')
            ->whereBetween('payment_date', [$start->toDateString(), $end->toDateString()]);

        $cashIncreases = CashAdjustment::where('adjustment_type', 'increase')
            ->whereBetween('adjustment_date', [$start->toDateString(), $end->toDateString()]);

        $purchaseCashPayments = Purchase::where('payment_method', 'cash')
            ->where('paid_amount', '>', 0)
            ->whereBetween('order_date', [$start->toDateString(), $end->toDateString()]);

        $supplierCashPayments = SupplierPayment::where('payment_method', 'cash')
            ->where('payment_type', 'debit')
            ->whereBetween('payment_date', [$start->toDateString(), $end->toDateString()]);

        $cashDecreases = CashAdjustment::where('adjustment_type', 'decrease')
            ->whereBetween('adjustment_date', [$start->toDateString(), $end->toDateString()]);

        $cashExpenses = Expense::where('payment_method', 'cash')
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()]);

        $saleCashAmount = (float) $saleCashReceipts->sum('amount');
        $customerReceiptAmount = (float) $customerCashReceipts->sum('amount');
        $cashIncreaseAmount = (float) $cashIncreases->sum('amount');
        $purchaseCashAmount = (float) $purchaseCashPayments->sum('paid_amount');
        $supplierPaymentAmount = (float) $supplierCashPayments->sum('amount');
        $cashDecreaseAmount = (float) $cashDecreases->sum('amount');
        $cashExpenseAmount = (float) $cashExpenses->sum('amount');

        return collect([
            [
                'section' => 'Cash Inflow',
                'particular' => 'Sales Cash Receipts',
                'description' => 'Cash received from sales',
                'records_count' => $saleCashReceipts->count(),
                'inflow' => $saleCashAmount,
                'outflow' => 0,
                'net_amount' => $saleCashAmount,
            ],
            [
                'section' => 'Cash Inflow',
                'particular' => 'Customer Cash Receipts',
                'description' => 'Cash received through customer payments',
                'records_count' => $customerCashReceipts->count(),
                'inflow' => $customerReceiptAmount,
                'outflow' => 0,
                'net_amount' => $customerReceiptAmount,
            ],
            [
                'section' => 'Cash Inflow',
                'particular' => 'Cash Increase Adjustments',
                'description' => 'Cash adjustments that increase cash balance',
                'records_count' => $cashIncreases->count(),
                'inflow' => $cashIncreaseAmount,
                'outflow' => 0,
                'net_amount' => $cashIncreaseAmount,
            ],
            [
                'section' => 'Cash Outflow',
                'particular' => 'Purchase Cash Payments',
                'description' => 'Cash paid against purchases',
                'records_count' => $purchaseCashPayments->count(),
                'inflow' => 0,
                'outflow' => $purchaseCashAmount,
                'net_amount' => -$purchaseCashAmount,
            ],
            [
                'section' => 'Cash Outflow',
                'particular' => 'Supplier Cash Payments',
                'description' => 'Cash paid through supplier payments',
                'records_count' => $supplierCashPayments->count(),
                'inflow' => 0,
                'outflow' => $supplierPaymentAmount,
                'net_amount' => -$supplierPaymentAmount,
            ],
            [
                'section' => 'Cash Outflow',
                'particular' => 'Cash Expenses',
                'description' => 'Business expenses paid in cash',
                'records_count' => $cashExpenses->count(),
                'inflow' => 0,
                'outflow' => $cashExpenseAmount,
                'net_amount' => -$cashExpenseAmount,
            ],
            [
                'section' => 'Cash Outflow',
                'particular' => 'Cash Decrease Adjustments',
                'description' => 'Cash adjustments that decrease cash balance',
                'records_count' => $cashDecreases->count(),
                'inflow' => 0,
                'outflow' => $cashDecreaseAmount,
                'net_amount' => -$cashDecreaseAmount,
            ],
        ]);
    }

    private function calculateTotals($records, Carbon $start)
    {
        $openingCash = $this->cashBalanceBefore($start);
        $inflow = $records->sum('inflow');
        $outflow = $records->sum('outflow');
        $netCashFlow = $inflow - $outflow;
        $closingCash = $openingCash + $netCashFlow;

        return [
            'opening_cash' => $openingCash,
            'inflow' => $inflow,
            'outflow' => $outflow,
            'net_cash_flow' => $netCashFlow,
            'closing_cash' => $closingCash,
            'status' => $netCashFlow >= 0 ? 'Net Increase' : 'Net Decrease',
        ];
    }

    private function cashBalanceBefore(Carbon $start)
    {
        $before = $start->copy()->subDay()->toDateString();

        $inflow = (float) SalePayment::where('payment_type', 'cash')
            ->where('payment_date', '<=', $before)
            ->sum('amount');

        $inflow += (float) CustomerPayment::where('payment_method', 'cash')
            ->where('payment_type', 'credit')
            ->where('payment_date', '<=', $before)
            ->sum('amount');

        $inflow += (float) CashAdjustment::where('adjustment_type', 'increase')
            ->where('adjustment_date', '<=', $before)
            ->sum('amount');

        $outflow = (float) Purchase::where('payment_method', 'cash')
            ->where('order_date', '<=', $before)
            ->sum('paid_amount');

        $outflow += (float) SupplierPayment::where('payment_method', 'cash')
            ->where('payment_type', 'debit')
            ->where('payment_date', '<=', $before)
            ->sum('amount');

        $outflow += (float) CashAdjustment::where('adjustment_type', 'decrease')
            ->where('adjustment_date', '<=', $before)
            ->sum('amount');

        $outflow += (float) Expense::where('payment_method', 'cash')
            ->where('expense_date', '<=', $before)
            ->sum('amount');

        return $inflow - $outflow;
    }

    private function emptyTotals()
    {
        return [
            'opening_cash' => 0,
            'inflow' => 0,
            'outflow' => 0,
            'net_cash_flow' => 0,
            'closing_cash' => 0,
            'status' => 'Net Increase',
        ];
    }
}
