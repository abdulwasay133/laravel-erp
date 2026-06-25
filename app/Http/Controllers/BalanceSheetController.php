<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Setting;
use Carbon\Carbon;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BalanceSheetController extends Controller
{
    public function index()
    {
        $stats = [
            'asset_accounts' => ChartOfAccount::where('type', 'asset')->count(),
            'liability_accounts' => ChartOfAccount::where('type', 'liability')->count(),
            'equity_accounts' => ChartOfAccount::where('type', 'equity')->count(),
            'total_accounts' => ChartOfAccount::count(),
        ];
        return view('reports.balance_sheet', compact('stats'));
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
        $records = $this->getBalanceSheetRecords();
        $totals = $this->calculateTotals($records);

        return DataTables::of($records)
            ->addIndexColumn()
            ->editColumn('amount', fn ($row) => number_format($row['amount'], 2, '.', ','))
            ->editColumn('section', function ($row) {
                $classes = [
                    'Assets' => 'bg-success',
                    'Liabilities' => 'bg-danger',
                    'Equity' => 'bg-primary',
                ];

                return '<span class="badge ' . ($classes[$row['section']] ?? 'bg-secondary') . '">' . $row['section'] . '</span>';
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
        $records = $this->getBalanceSheetRecords();
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

        $qrLabel = abs($totals['difference']) < 0.01 ? 'Balanced' : 'Difference: Rs. ' . number_format(abs($totals['difference']), 2);

        $qrData = $settings['company_name'] . "\n"
            . 'Balance Sheet' . "\n"
            . 'Period: ' . $start->format('d M, Y') . ' — ' . $end->format('d M, Y') . "\n"
            . $qrLabel;

        $qrCode = new QrCode(
            data: $qrData,
            encoding: new Encoding('UTF-8'),
            size: 200
        );

        $writer = new SvgWriter();
        $result = $writer->write($qrCode);
        $qrSvg = $result->getString();

        $documentTitle = 'Balance Sheet';

        return view('reports.balance_sheet_print', compact('records', 'totals', 'start', 'end', 'settings', 'qrSvg', 'documentTitle'));
    }

    private function getBalanceSheetRecords()
    {
        $sections = [
            'asset' => ['label' => 'Assets', 'description' => 'Total active asset account balances'],
            'liability' => ['label' => 'Liabilities', 'description' => 'Total active liability account balances'],
            'equity' => ['label' => 'Equity', 'description' => 'Total active equity account balances'],
        ];

        return collect($sections)->map(function ($section, $type) {
            $accounts = ChartOfAccount::active()->where('type', $type);

            return [
                'section' => $section['label'],
                'description' => $section['description'],
                'accounts_count' => $accounts->count(),
                'amount' => (float) $accounts->sum('current_balance'),
            ];
        })->values();
    }

    private function calculateTotals($records)
    {
        $assets = $records->where('section', 'Assets')->sum('amount');
        $liabilities = $records->where('section', 'Liabilities')->sum('amount');
        $equity = $records->where('section', 'Equity')->sum('amount');
        $liabilitiesAndEquity = $liabilities + $equity;
        $difference = $assets - $liabilitiesAndEquity;

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'liabilities_and_equity' => $liabilitiesAndEquity,
            'difference' => $difference,
            'status' => abs($difference) < 0.01 ? 'Balanced' : 'Unbalanced',
        ];
    }

    private function emptyTotals()
    {
        return [
            'assets' => 0,
            'liabilities' => 0,
            'equity' => 0,
            'liabilities_and_equity' => 0,
            'difference' => 0,
            'status' => 'Balanced',
        ];
    }
}
