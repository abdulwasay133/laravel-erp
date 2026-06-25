<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\CommissionPayment;
use App\Models\Employee;
use App\Models\Setting;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CommissionReportController extends Controller
{
    public function orderBookerPerformance(Request $request)
    {
        $orderBookers = Employee::where('is_order_booker', true)->orderBy('first_name')->get();
        $selectedBooker = null;
        $reportData = [];

        if ($request->order_booker_id) {
            $selectedBooker = Employee::findOrFail($request->order_booker_id);
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->startOfMonth();
            $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::now()->endOfMonth();

            $commissions = Commission::where('order_booker_id', $selectedBooker->id)
                ->whereHas('sale', function ($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('sale_date', [$dateFrom, $dateTo]);
                })
                ->with('sale')
                ->get();

            $totalSales = $commissions->sum('sale_amount');
            $totalCommission = $commissions->sum('commission_amount');
            $totalPaid = $commissions->where('status', 'paid')->sum('commission_amount');
            $pendingAmount = $commissions->whereIn('status', ['pending', 'approved'])->sum('commission_amount');

            $reportData = [
                'total_sales' => $totalSales,
                'total_commission' => $totalCommission,
                'total_paid' => $totalPaid,
                'pending_amount' => $pendingAmount,
                'commissions' => $commissions,
            ];
        }

        return view('commissions.reports.performance', compact('orderBookers', 'selectedBooker', 'reportData'));
    }

    public function dueReport(Request $request)
    {
        $orderBookers = Employee::where('is_order_booker', true)->orderBy('first_name')->get();
        $selectedBookerId = $request->order_booker_id;

        $query = Commission::whereIn('status', ['pending', 'approved'])
            ->with('orderBooker', 'sale');

        if ($selectedBookerId) {
            $query->where('order_booker_id', $selectedBookerId);
        }

        $commissions = $query->orderBy('created_at')->get()->groupBy('order_booker_id');

        return view('commissions.reports.due', compact('orderBookers', 'selectedBookerId', 'commissions'));
    }

    public function monthlyReport(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;
        $orderBookerId = $request->order_booker_id;
        $orderBookers = Employee::where('is_order_booker', true)->orderBy('first_name')->get();

        $query = Commission::query()
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, order_booker_id')
            ->selectRaw('SUM(sale_amount) as total_sales')
            ->selectRaw('SUM(commission_amount) as total_commission')
            ->selectRaw('SUM(CASE WHEN status = "paid" THEN commission_amount ELSE 0 END) as paid_commission')
            ->whereYear('created_at', $year)
            ->with('orderBooker');

        if ($orderBookerId) {
            $query->where('order_booker_id', $orderBookerId);
        }

        $monthlyData = $query->groupBy('year', 'month', 'order_booker_id')
            ->orderBy('month')
            ->get()
            ->groupBy('order_booker_id');

        return view('commissions.reports.monthly', compact('monthlyData', 'orderBookers', 'year', 'orderBookerId'));
    }

    protected function loadSettings(): array
    {
        return [
            'company_name'     => Setting::getValue('company_name'),
            'company_address'  => Setting::getValue('company_address'),
            'company_phone'    => Setting::getValue('company_phone'),
            'company_email'    => Setting::getValue('company_email'),
            'company_website'  => Setting::getValue('company_website'),
            'company_logo'     => Setting::getValue('company_logo'),
            'terms_conditions' => Setting::getValue('terms_conditions', 'Thank you for your business!'),
        ];
    }

    protected function generateQrCode(string $data): string
    {
        $qrCode = new QrCode(data: $data, encoding: new Encoding('UTF-8'), size: 200);
        return (new SvgWriter())->write($qrCode)->getString();
    }

    public function printPerformance(Request $request)
    {
        $selectedBooker = Employee::findOrFail($request->order_booker_id);
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->startOfMonth();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::now()->endOfMonth();

        $commissions = Commission::where('order_booker_id', $selectedBooker->id)
            ->whereHas('sale', fn($q) => $q->whereBetween('sale_date', [$dateFrom, $dateTo]))
            ->with('sale')
            ->get();

        $totalSales = $commissions->sum('sale_amount');
        $totalCommission = $commissions->sum('commission_amount');
        $totalPaid = $commissions->where('status', 'paid')->sum('commission_amount');
        $pendingAmount = $commissions->whereIn('status', ['pending', 'approved'])->sum('commission_amount');

        $settings = $this->loadSettings();
        $qrSvg = $this->generateQrCode(
            $settings['company_name'] . "\n"
            . 'Performance Report: ' . $selectedBooker->first_name . ' ' . $selectedBooker->last_name . "\n"
            . 'Period: ' . $dateFrom->format('d M, Y') . ' — ' . $dateTo->format('d M, Y') . "\n"
            . 'Total Commission: Rs. ' . number_format($totalCommission, 2)
        );
        $documentTitle = 'Order Booker Performance Report';

        return view('commissions.reports.performance_print', compact(
            'commissions', 'totalSales', 'totalCommission', 'totalPaid', 'pendingAmount',
            'selectedBooker', 'dateFrom', 'dateTo', 'settings', 'qrSvg', 'documentTitle'
        ));
    }

    public function printDue(Request $request)
    {
        $selectedBookerId = $request->order_booker_id;

        $query = Commission::whereIn('status', ['pending', 'approved'])->with('orderBooker', 'sale');
        if ($selectedBookerId) {
            $query->where('order_booker_id', $selectedBookerId);
        }
        $commissions = $query->orderBy('created_at')->get()->groupBy('order_booker_id');

        $settings = $this->loadSettings();
        $grandTotal = $query->get()->sum('commission_amount');
        $qrSvg = $this->generateQrCode(
            $settings['company_name'] . "\n"
            . 'Due Commission Report' . "\n"
            . 'Total Due: Rs. ' . number_format($grandTotal, 2)
        );
        $documentTitle = 'Commission Due Report';

        return view('commissions.reports.due_print', compact(
            'commissions', 'grandTotal', 'selectedBookerId', 'settings', 'qrSvg', 'documentTitle'
        ));
    }

    public function printMonthly(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;
        $orderBookerId = $request->order_booker_id;

        $query = Commission::query()
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, order_booker_id')
            ->selectRaw('SUM(sale_amount) as total_sales')
            ->selectRaw('SUM(commission_amount) as total_commission')
            ->selectRaw('SUM(CASE WHEN status = "paid" THEN commission_amount ELSE 0 END) as paid_commission')
            ->whereYear('created_at', $year)
            ->with('orderBooker');

        if ($orderBookerId) {
            $query->where('order_booker_id', $orderBookerId);
        }

        $monthlyData = $query->groupBy('year', 'month', 'order_booker_id')
            ->orderBy('month')
            ->get()
            ->groupBy('order_booker_id');

        $settings = $this->loadSettings();
        $qrSvg = $this->generateQrCode(
            $settings['company_name'] . "\n"
            . 'Monthly Commission Report — ' . $year
        );
        $documentTitle = 'Monthly Commission Report';

        return view('commissions.reports.monthly_print', compact(
            'monthlyData', 'year', 'orderBookerId', 'settings', 'qrSvg', 'documentTitle'
        ));
    }
}
