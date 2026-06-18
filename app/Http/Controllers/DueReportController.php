<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Setting;
use Carbon\Carbon;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DueReportController extends Controller
{
    public function index()
    {
        return view('reports.due_report');
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

        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);

        $query = $this->buildQuery($request);
        $totals = $this->calculateTotals(clone $query);

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('customer_name', function ($row) {
                return $row->customer
                    ? trim($row->customer->first_name . ' ' . $row->customer->last_name)
                    : '-';
            })
            ->editColumn('sale_date', fn ($row) => $row->sale_date->format('d M Y'))
            ->editColumn('total_amount', fn ($row) => number_format($row->total_amount, 2, '.', ','))
            ->editColumn('paid_amount', fn ($row) => number_format($row->paid_amount, 2, '.', ','))
            ->addColumn('due_amount', fn ($row) => number_format($row->balance, 2, '.', ','))
            ->filterColumn('customer_name', function ($query, $keyword) {
                $query->whereHas('customer', function ($customerQuery) use ($keyword) {
                    $customerQuery->where('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhere('company', 'like', "%{$keyword}%");
                });
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

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $sales = $this->buildQuery($request)->get();
        $totals = $this->calculateTotalsFromCollection($sales);

        $settings = [
            'company_name'     => Setting::getValue('company_name'),
            'company_address'  => Setting::getValue('company_address'),
            'company_phone'    => Setting::getValue('company_phone'),
            'company_email'    => Setting::getValue('company_email'),
            'company_website'  => Setting::getValue('company_website'),
            'company_logo'     => Setting::getValue('company_logo'),
            'terms_conditions' => Setting::getValue('terms_conditions', 'Thank you for your business!'),
        ];

        $qrData = $settings['company_name'] . "\n"
            . 'Due Report: ' . $start->format('d M, Y') . ' — ' . $end->format('d M, Y') . "\n"
            . 'Total Amount: Rs. ' . number_format($totals['total_amount'], 2) . "\n"
            . 'Total Paid: Rs. ' . number_format($totals['paid_amount'], 2) . "\n"
            . 'Total Due: Rs. ' . number_format($totals['due_amount'], 2);

        $qrCode = new QrCode(
            data: $qrData,
            encoding: new Encoding('UTF-8'),
            size: 200,
        );

        $writer = new SvgWriter();
        $result = $writer->write($qrCode);
        $qrSvg = $result->getString();

        $documentTitle = 'Due Report';

        return view('reports.due_report_print', compact(
            'sales', 'totals', 'start', 'end', 'settings', 'qrSvg', 'documentTitle'
        ));
    }

    private function buildQuery(Request $request)
    {
        $start = Carbon::parse($request->start_date)->toDateString();
        $end = Carbon::parse($request->end_date)->toDateString();

        return Sale::with('customer')
            ->where('balance', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('sale_date', [$start, $end])
            ->orderByDesc('sale_date')
            ->orderByDesc('id');
    }

    private function calculateTotals($query): array
    {
        return [
            'count' => (int) (clone $query)->count(),
            'total_amount' => (float) (clone $query)->sum('total_amount'),
            'paid_amount' => (float) (clone $query)->sum('paid_amount'),
            'due_amount' => (float) (clone $query)->sum('balance'),
        ];
    }

    private function calculateTotalsFromCollection($sales): array
    {
        return [
            'count' => $sales->count(),
            'total_amount' => (float) $sales->sum('total_amount'),
            'paid_amount' => (float) $sales->sum('paid_amount'),
            'due_amount' => (float) $sales->sum('balance'),
        ];
    }

    private function emptyTotals(): array
    {
        return [
            'count' => 0,
            'total_amount' => 0,
            'paid_amount' => 0,
            'due_amount' => 0,
        ];
    }
}
