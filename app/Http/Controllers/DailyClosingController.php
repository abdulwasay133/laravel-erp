<?php

namespace App\Http\Controllers;

use App\Models\CashAdjustment;
use App\Models\CustomerPayment;
use App\Models\DailyClosing;
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

class DailyClosingController extends Controller
{
    public function index(Request $request)
    {
        $date = Carbon::parse($request->get('date', today()->toDateString()));
        $figures = $this->calculateDailyFigures($date);
        $existingClosing = DailyClosing::where('closing_date', $date->toDateString())->first();

        if ($request->ajax() || $request->has('ajax')) {
            return response()->json([
                'figures' => $figures,
                'is_closed' => (bool) $existingClosing,
                'closed_at' => $existingClosing?->created_at?->format('Y-m-d H:i'),
                'closed_by' => $existingClosing?->closedByUser?->name,
            ]);
        }

        return view('reports.daily_closing', compact('date', 'figures', 'existingClosing'));
    }

    public function closeDay(Request $request)
    {
        $validated = $request->validate([
            'closing_date' => ['required', 'date'],
        ]);

        $date = Carbon::parse($validated['closing_date']);
        $figures = $this->calculateDailyFigures($date);

        DailyClosing::updateOrCreate(
            ['closing_date' => $date->toDateString()],
            [
                'last_day_closing' => $figures['last_day_closing'],
                'receive' => $figures['receive'],
                'payment' => $figures['payment'],
                'balance' => $figures['balance'],
                'closed_by' => auth()->id(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Day closed successfully for ' . $date->format('d M Y') . '.',
            'figures' => $figures,
        ]);
    }

    public function report()
    {
        return view('reports.closing_report');
    }

    public function reportSearch(Request $request)
    {
        $query = DailyClosing::with('closedByUser')->orderByDesc('closing_date');

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('closing_date', [
                Carbon::parse($request->start_date)->toDateString(),
                Carbon::parse($request->end_date)->toDateString(),
            ]);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('closing_date', fn ($row) => $row->closing_date->format('d M Y'))
            ->editColumn('last_day_closing', fn ($row) => number_format($row->last_day_closing, 2, '.', ','))
            ->editColumn('receive', fn ($row) => number_format($row->receive, 2, '.', ','))
            ->editColumn('payment', fn ($row) => number_format($row->payment, 2, '.', ','))
            ->editColumn('balance', fn ($row) => number_format($row->balance, 2, '.', ','))
            ->addColumn('closed_by_name', fn ($row) => $row->closedByUser?->name ?? '-')
            ->editColumn('created_at', fn ($row) => $row->created_at->format('d M Y H:i'))
            ->make(true);
    }

    public function printClosing(Request $request)
    {
        $date = Carbon::parse($request->get('date', today()->toDateString()));
        $figures = $this->calculateDailyFigures($date);
        $existingClosing = DailyClosing::where('closing_date', $date->toDateString())->first();

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
            . 'Daily Closing: ' . $date->format('d M, Y') . "\n"
            . 'Balance: Rs. ' . number_format($figures['balance'], 2);

        $qrCode = new QrCode(
            data: $qrData,
            encoding: new Encoding('UTF-8'),
            size: 200
        );

        $writer = new SvgWriter();
        $result = $writer->write($qrCode);
        $qrSvg = $result->getString();

        $documentTitle = 'Daily Closing';

        return view('reports.daily_closing_print', compact('date', 'figures', 'existingClosing', 'settings', 'qrSvg', 'documentTitle'));
    }

    public function reportPrint(Request $request)
    {
        $query = DailyClosing::with('closedByUser')->orderByDesc('closing_date');

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('closing_date', [
                Carbon::parse($request->start_date)->toDateString(),
                Carbon::parse($request->end_date)->toDateString(),
            ]);
        }

        $records = $query->get();

        $totals = [
            'last_day_closing' => $records->isNotEmpty() ? $records->first()->last_day_closing : 0,
            'receive' => $records->sum('receive'),
            'payment' => $records->sum('payment'),
            'balance' => $records->isNotEmpty() ? $records->last()->balance : 0,
        ];

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : null;

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
            . 'Closing Report' . "\n"
            . ($startDate && $endDate ? $startDate->format('d M, Y') . ' — ' . $endDate->format('d M, Y') : 'All time') . "\n"
            . 'Balance: Rs. ' . number_format($totals['balance'], 2);

        $qrCode = new QrCode(
            data: $qrData,
            encoding: new Encoding('UTF-8'),
            size: 200
        );

        $writer = new SvgWriter();
        $result = $writer->write($qrCode);
        $qrSvg = $result->getString();

        $documentTitle = 'Closing Report';

        return view('reports.closing_report_print', compact('records', 'totals', 'startDate', 'endDate', 'settings', 'qrSvg', 'documentTitle'));
    }

    private function calculateDailyFigures(Carbon $date): array
    {
        $lastDayClosing = $this->getLastDayClosing($date);
        $receive = $this->getDayReceive($date);
        $payment = $this->getDayPayment($date);
        $balance = round($lastDayClosing + $receive - $payment, 2);

        return [
            'last_day_closing' => round($lastDayClosing, 2),
            'receive' => round($receive, 2),
            'payment' => round($payment, 2),
            'balance' => $balance,
        ];
    }

    private function getLastDayClosing(Carbon $date): float
    {
        $previousClosing = DailyClosing::where('closing_date', '<', $date->toDateString())
            ->orderByDesc('closing_date')
            ->first();

        if ($previousClosing) {
            return (float) $previousClosing->balance;
        }

        return $this->cashBalanceBefore($date);
    }

    private function getDayReceive(Carbon $date): float
    {
        $dateString = $date->toDateString();

        $saleCash = (float) SalePayment::where('payment_type', 'cash')
            ->where('payment_date', $dateString)
            ->sum('amount');

        $customerCash = (float) CustomerPayment::where('payment_method', 'cash')
            ->where('payment_type', 'credit')
            ->where('payment_date', $dateString)
            ->sum('amount');

        $cashIncrease = (float) CashAdjustment::where('adjustment_type', 'increase')
            ->where('adjustment_date', $dateString)
            ->sum('amount');

        return $saleCash + $customerCash + $cashIncrease;
    }

    private function getDayPayment(Carbon $date): float
    {
        $dateString = $date->toDateString();

        $purchaseCash = (float) Purchase::where('payment_method', 'cash')
            ->where('paid_amount', '>', 0)
            ->where('order_date', $dateString)
            ->sum('paid_amount');

        $supplierCash = (float) SupplierPayment::where('payment_method', 'cash')
            ->where('payment_type', 'debit')
            ->where('payment_date', $dateString)
            ->sum('amount');

        $cashDecrease = (float) CashAdjustment::where('adjustment_type', 'decrease')
            ->where('adjustment_date', $dateString)
            ->sum('amount');

        $cashExpenses = (float) Expense::where('payment_method', 'cash')
            ->where('expense_date', $dateString)
            ->sum('amount');

        return $purchaseCash + $supplierCash + $cashDecrease + $cashExpenses;
    }

    private function cashBalanceBefore(Carbon $date): float
    {
        $before = $date->copy()->subDay()->toDateString();

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
}
