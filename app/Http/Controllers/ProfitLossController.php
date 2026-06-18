<?php

namespace App\Http\Controllers;

use App\Models\CashAdjustment;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Setting;
use Carbon\Carbon;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProfitLossController extends Controller
{
    public function index()
    {
        return view('reports.profit_loss');
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
        $records = $this->getProfitLossRecords($start, $end);
        $totals = $this->calculateTotals($records);

        return DataTables::of($records)
            ->addIndexColumn()
            ->editColumn('amount', fn ($row) => number_format($row['amount'], 2, '.', ','))
            ->editColumn('effect', function ($row) {
                return $row['effect'] === 'income'
                    ? '<span class="badge bg-success">Income</span>'
                    : '<span class="badge bg-danger">Deduction</span>';
            })
            ->with(['totals' => $totals])
            ->rawColumns(['effect'])
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
        $records = $this->getProfitLossRecords($start, $end);
        $totals = $this->calculateTotals($records);

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
            . 'Profit & Loss' . "\n"
            . 'Period: ' . $start->format('d M, Y') . ' — ' . $end->format('d M, Y') . "\n"
            . 'Net: Rs. ' . number_format(abs($totals['profit_loss']), 2) . ' (' . $totals['status'] . ')';

        $qrCode = new QrCode(
            data: $qrData,
            encoding: new Encoding('UTF-8'),
            size: 200
        );

        $writer = new SvgWriter();
        $result = $writer->write($qrCode);
        $qrSvg = $result->getString();

        $documentTitle = 'Profit & Loss';

        return view('reports.profit_loss_print', compact('records', 'totals', 'start', 'end', 'settings', 'qrSvg', 'documentTitle'));
    }

    private function getProfitLossRecords(Carbon $start, Carbon $end)
    {
        $sales = Sale::whereBetween('sale_date', [$start->toDateString(), $end->toDateString()]);
        $purchases = Purchase::whereBetween('order_date', [$start->toDateString(), $end->toDateString()]);
        $expenseAdjustments = CashAdjustment::whereHas('chartOfAccount', fn ($query) => $query->where('type', 'expense'))
            ->whereBetween('adjustment_date', [$start->toDateString(), $end->toDateString()]);
        $expenseRecords = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()]);

        return collect([
            [
                'particular' => 'Sale',
                'description' => 'Total sales invoices in selected date range',
                'records_count' => $sales->count(),
                'effect' => 'income',
                'amount' => (float) $sales->sum('total_amount'),
            ],
            [
                'particular' => 'Purchase',
                'description' => 'Total purchase invoices in selected date range',
                'records_count' => $purchases->count(),
                'effect' => 'deduction',
                'amount' => (float) $purchases->sum('grand_total'),
            ],
            [
                'particular' => 'Expense',
                'description' => 'Recorded business expenses and expense adjustments',
                'records_count' => $expenseRecords->count() + $expenseAdjustments->count(),
                'effect' => 'deduction',
                'amount' => (float) $expenseRecords->sum('amount') + (float) $expenseAdjustments->sum('amount'),
            ],
        ]);
    }

    private function calculateTotals($records)
    {
        $income = $records->where('effect', 'income')->sum('amount');
        $deduction = $records->where('effect', 'deduction')->sum('amount');
        $profitLoss = $income - $deduction;

        return [
            'income' => $income,
            'deduction' => $deduction,
            'profit_loss' => $profitLoss,
            'status' => $profitLoss >= 0 ? 'Profit' : 'Loss',
        ];
    }

    private function emptyTotals()
    {
        return [
            'income' => 0,
            'deduction' => 0,
            'profit_loss' => 0,
            'status' => 'Profit',
        ];
    }
}
