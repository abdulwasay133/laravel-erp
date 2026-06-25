<?php

namespace App\Http\Controllers;

use App\Models\CashAdjustment;
use App\Models\ChartOfAccount;
use App\Models\OpeningBalance;
use App\Models\Setting;
use Carbon\Carbon;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class GeneralLedgerController extends Controller
{
    public function index()
    {
        $generalHeads = ChartOfAccount::active()
            ->roots()
            ->orderBy('code')
            ->get();

        $transactionHeads = ChartOfAccount::active()
            ->whereNotNull('parent_id')
            ->orderBy('code')
            ->get();

        $stats = [
            'total_accounts' => ChartOfAccount::count(),
            'general_heads' => ChartOfAccount::active()->roots()->count(),
            'transaction_heads' => ChartOfAccount::active()->whereNotNull('parent_id')->count(),
            'opening_balances' => OpeningBalance::count(),
        ];

        return view('reports.general_ledger', compact('generalHeads', 'transactionHeads', 'stats'));
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

        $validated = $request->validate([
            'general_head_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'transaction_head_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'with_details' => ['nullable', 'boolean'],
        ]);

        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $end = Carbon::parse($validated['end_date'])->endOfDay();
        $records = $this->getLedgerRecords(
            $start,
            $end,
            $validated['general_head_id'] ?? null,
            $validated['transaction_head_id'] ?? null
        );

        $totals = [
            'debit' => $records->sum('debit'),
            'credit' => $records->sum('credit'),
            'balance' => $records->last()['balance'] ?? 0,
        ];

        return DataTables::of($records)
            ->addIndexColumn()
            ->editColumn('debit', fn ($row) => $row['debit'] ? number_format($row['debit'], 2, '.', ',') : '')
            ->editColumn('credit', fn ($row) => $row['credit'] ? number_format($row['credit'], 2, '.', ',') : '')
            ->editColumn('balance', fn ($row) => number_format($row['balance'], 2, '.', ','))
            ->with(['totals' => $totals])
            ->make(true);
    }

    public function print(Request $request)
    {
        $validated = $request->validate([
            'general_head_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'transaction_head_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'with_details' => ['nullable', 'boolean'],
        ]);

        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $end = Carbon::parse($validated['end_date'])->endOfDay();
        $withDetails = $request->boolean('with_details');
        $generalHead = !empty($validated['general_head_id']) ? ChartOfAccount::find($validated['general_head_id']) : null;
        $transactionHead = !empty($validated['transaction_head_id']) ? ChartOfAccount::find($validated['transaction_head_id']) : null;

        $records = $this->getLedgerRecords(
            $start,
            $end,
            $validated['general_head_id'] ?? null,
            $validated['transaction_head_id'] ?? null
        );

        $totals = [
            'debit' => $records->sum('debit'),
            'credit' => $records->sum('credit'),
            'balance' => $records->last()['balance'] ?? 0,
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
            . 'General Ledger' . "\n"
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

        $documentTitle = 'General Ledger';

        return view('reports.general_ledger_print', compact(
            'records',
            'totals',
            'start',
            'end',
            'withDetails',
            'generalHead',
            'transactionHead',
            'settings',
            'qrSvg',
            'documentTitle'
        ));
    }

    private function getLedgerRecords(Carbon $start, Carbon $end, $generalHeadId = null, $transactionHeadId = null)
    {
        $accountIds = $this->filteredAccountIds($generalHeadId, $transactionHeadId);

        $openingBalances = OpeningBalance::with('chartOfAccount.parent')
            ->whereBetween('voucher_date', [$start->toDateString(), $end->toDateString()])
            ->when($accountIds !== null, fn ($query) => $query->whereIn('chart_of_account_id', $accountIds))
            ->orderBy('voucher_date')
            ->get();

        $cashAdjustments = CashAdjustment::with('chartOfAccount.parent')
            ->whereBetween('adjustment_date', [$start->toDateString(), $end->toDateString()])
            ->when($accountIds !== null, fn ($query) => $query->whereIn('chart_of_account_id', $accountIds))
            ->orderBy('adjustment_date')
            ->get();

        $records = collect();

        foreach ($openingBalances as $balance) {
            $account = $balance->chartOfAccount;

            $records->push([
                'date' => $balance->voucher_date->toDateString(),
                'voucher_no' => $balance->voucher_no,
                'general_head' => optional($account->parent)->name ?: $account->name,
                'transaction_head' => $account->name,
                'type' => 'Opening Balance',
                'remark' => $balance->description ?: 'Opening balance',
                'debit' => $balance->amount,
                'credit' => 0,
            ]);
        }

        foreach ($cashAdjustments as $adjustment) {
            $account = $adjustment->chartOfAccount;

            $records->push([
                'date' => $adjustment->adjustment_date->toDateString(),
                'voucher_no' => $adjustment->voucher_no,
                'general_head' => optional($account->parent)->name ?: $account->name,
                'transaction_head' => $account->name,
                'type' => 'Cash Adjustment',
                'remark' => $adjustment->description ?: $adjustment->reference ?: 'Cash adjustment',
                'debit' => $adjustment->adjustment_type === 'increase' ? $adjustment->amount : 0,
                'credit' => $adjustment->adjustment_type === 'decrease' ? $adjustment->amount : 0,
            ]);
        }

        $runningBalance = 0;

        return $records
            ->sortBy(fn ($row) => $row['date'] . '-' . $row['voucher_no'])
            ->values()
            ->map(function ($row) use (&$runningBalance) {
                $runningBalance += $row['debit'] - $row['credit'];
                $row['balance'] = round($runningBalance, 2);

                return $row;
            });
    }

    private function filteredAccountIds($generalHeadId = null, $transactionHeadId = null)
    {
        if ($transactionHeadId) {
            return [(int) $transactionHeadId];
        }

        if (!$generalHeadId) {
            return null;
        }

        $ids = [(int) $generalHeadId];
        $children = ChartOfAccount::where('parent_id', $generalHeadId)->pluck('id');

        foreach ($children as $childId) {
            $ids = array_merge($ids, $this->filteredAccountIds($childId) ?? []);
        }

        return array_values(array_unique($ids));
    }
}
