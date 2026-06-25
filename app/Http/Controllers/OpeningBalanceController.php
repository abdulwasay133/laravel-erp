<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\OpeningBalance;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OpeningBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $openingBalances = OpeningBalance::with('chartOfAccount')->orderBy('voucher_date', 'desc')->get();

            return DataTables::of($openingBalances)
                ->addIndexColumn()
                ->addColumn('voucher_no', function ($row) {
                    return $row->voucher_no;
                })
                ->addColumn('voucher_date', function ($row) {
                    return $row->voucher_date->format('Y-m-d');
                })
                ->addColumn('account_head', function ($row) {
                    return $row->chartOfAccount ? $row->chartOfAccount->code . ' - ' . $row->chartOfAccount->name : '-';
                })
                ->addColumn('amount', function ($row) {
                    return number_format($row->amount, 2, '.', ',');
                })
                ->addColumn('description', function ($row) {
                    return $row->description ?: '-';
                })
                ->addColumn('action', function ($row) {
                    $actions = '<a href="' . route('opening-balances.edit', $row->id) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>';
                    $actions .= '<button class="btn btn-sm btn-outline-danger delete-opening-balance" data-id="' . $row->id . '"><i class="bi bi-trash"></i></button>';
                    return $actions;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $stats = [
            'total_entries' => OpeningBalance::count(),
            'total_amount' => OpeningBalance::sum('amount'),
            'total_accounts' => OpeningBalance::distinct('chart_of_account_id')->count('chart_of_account_id'),
            'max_amount' => OpeningBalance::max('amount') ?? 0,
        ];

        return view('opening-balances.index', compact('stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accounts = ChartOfAccount::active()->orderBy('code')->get();
        return view('opening-balances.create', compact('accounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'voucher_no' => 'required|string|unique:opening_balances,voucher_no',
            'voucher_date' => 'required|date',
            'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $openingBalance = OpeningBalance::create([
            'voucher_no' => $validated['voucher_no'],
            'voucher_date' => $validated['voucher_date'],
            'chart_of_account_id' => $validated['chart_of_account_id'],
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
        ]);

        // Update chart of account opening balance
        $account = ChartOfAccount::find($validated['chart_of_account_id']);
        if ($account) {
            $account->opening_balance += $validated['amount'];
            $account->current_balance += $validated['amount'];
            $account->save();
        }

        return redirect()->route('opening-balances.index')
            ->with('success', 'Opening balance created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $openingBalance = OpeningBalance::findOrFail($id);
        $accounts = ChartOfAccount::active()->orderBy('code')->get();
        return view('opening-balances.edit', compact('openingBalance', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $openingBalance = OpeningBalance::findOrFail($id);

        $validated = $request->validate([
            'voucher_no' => 'required|string|unique:opening_balances,voucher_no,' . $id,
            'voucher_date' => 'required|date',
            'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        // Revert old amount from chart of account
        $oldAccount = ChartOfAccount::find($openingBalance->chart_of_account_id);
        if ($oldAccount) {
            $oldAccount->opening_balance -= $openingBalance->amount;
            $oldAccount->current_balance -= $openingBalance->amount;
            $oldAccount->save();
        }

        // Update opening balance
        $openingBalance->voucher_no = $validated['voucher_no'];
        $openingBalance->voucher_date = $validated['voucher_date'];
        $openingBalance->chart_of_account_id = $validated['chart_of_account_id'];
        $openingBalance->amount = $validated['amount'];
        $openingBalance->description = $validated['description'] ?? null;
        $openingBalance->save();

        // Add new amount to chart of account
        $newAccount = ChartOfAccount::find($validated['chart_of_account_id']);
        if ($newAccount) {
            $newAccount->opening_balance += $validated['amount'];
            $newAccount->current_balance += $validated['amount'];
            $newAccount->save();
        }

        return redirect()->route('opening-balances.index')
            ->with('success', 'Opening balance updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $openingBalance = OpeningBalance::findOrFail($id);

        // Revert amount from chart of account
        $account = ChartOfAccount::find($openingBalance->chart_of_account_id);
        if ($account) {
            $account->opening_balance -= $openingBalance->amount;
            $account->current_balance -= $openingBalance->amount;
            $account->save();
        }

        $openingBalance->delete();

        return back()->with('success', 'Opening balance deleted successfully!');
    }

    /**
     * Generate next voucher number
     */
    public function generateVoucherNo()
    {
        $lastVoucher = OpeningBalance::orderBy('id', 'desc')->first();
        if ($lastVoucher) {
            $lastNo = intval(substr($lastVoucher->voucher_no, -6));
            $newNo = str_pad($lastNo + 1, 6, '0', STR_PAD_LEFT);
            return response()->json(['voucher_no' => 'OB-' . $newNo]);
        }
        return response()->json(['voucher_no' => 'OB-000001']);
    }
}
