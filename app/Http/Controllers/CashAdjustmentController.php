<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\CashAdjustment;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CashAdjustmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $cashAdjustments = CashAdjustment::with('chartOfAccount')->orderBy('adjustment_date', 'desc')->get();

            return DataTables::of($cashAdjustments)
                ->addIndexColumn()
                ->addColumn('voucher_no', function ($row) {
                    return $row->voucher_no;
                })
                ->addColumn('adjustment_date', function ($row) {
                    return $row->adjustment_date->format('Y-m-d');
                })
                ->addColumn('adjustment_type', function ($row) {
                    $badgeColor = $row->adjustment_type === 'increase' ? 'bg-success' : 'bg-danger';
                    $label = $row->adjustment_type === 'increase' ? 'Increase' : 'Decrease';
                    return '<span class="badge ' . $badgeColor . '">' . $label . '</span>';
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
                ->addColumn('reference', function ($row) {
                    return $row->reference ?: '-';
                })
                ->addColumn('action', function ($row) {
                    $actions = '<a href="' . route('cash-adjustments.edit', $row->id) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>';
                    $actions .= '<button class="btn btn-sm btn-outline-danger delete-cash-adjustment" data-id="' . $row->id . '"><i class="bi bi-trash"></i></button>';
                    return $actions;
                })
                ->rawColumns(['adjustment_type', 'action'])
                ->make(true);
        }

        $stats = [
            'total_adjustments' => CashAdjustment::count(),
            'total_increase' => CashAdjustment::where('adjustment_type', 'increase')->sum('amount'),
            'total_decrease' => CashAdjustment::where('adjustment_type', 'decrease')->sum('amount'),
            'net_adjustment' => CashAdjustment::where('adjustment_type', 'increase')->sum('amount') - CashAdjustment::where('adjustment_type', 'decrease')->sum('amount'),
        ];

        return view('cash-adjustments.index', compact('stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accounts = ChartOfAccount::active()->orderBy('code')->get();
        return view('cash-adjustments.create', compact('accounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'voucher_no' => 'required|string|unique:cash_adjustments,voucher_no',
                'adjustment_date' => 'required|date',
                'adjustment_type' => 'required|in:increase,decrease',
                'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
                'amount' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'reference' => 'nullable|string',
            ]);

            $cashAdjustment = CashAdjustment::create([
                'voucher_no' => $validated['voucher_no'],
                'adjustment_date' => $validated['adjustment_date'],
                'adjustment_type' => $validated['adjustment_type'],
                'chart_of_account_id' => $validated['chart_of_account_id'],
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? null,
                'reference' => $validated['reference'] ?? null,
            ]);

            // Update chart of account current balance
            $account = ChartOfAccount::find($validated['chart_of_account_id']);
            if ($account) {
                if ($validated['adjustment_type'] === 'increase') {
                    $account->current_balance += $validated['amount'];
                } else {
                    $account->current_balance -= $validated['amount'];
                }
                $account->save();
            }

            return redirect()->route('cash-adjustments.index')
                ->with('success', 'Cash adjustment created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating cash adjustment: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $cashAdjustment = CashAdjustment::findOrFail($id);
        $accounts = ChartOfAccount::active()->orderBy('code')->get();
        return view('cash-adjustments.edit', compact('cashAdjustment', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $cashAdjustment = CashAdjustment::findOrFail($id);

            $validated = $request->validate([
                'voucher_no' => 'required|string|unique:cash_adjustments,voucher_no,' . $id,
                'adjustment_date' => 'required|date',
                'adjustment_type' => 'required|in:increase,decrease',
                'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
                'amount' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'reference' => 'nullable|string',
            ]);

            // Revert old amount from chart of account
            $oldAccount = ChartOfAccount::find($cashAdjustment->chart_of_account_id);
            if ($oldAccount) {
                if ($cashAdjustment->adjustment_type === 'increase') {
                    $oldAccount->current_balance -= $cashAdjustment->amount;
                } else {
                    $oldAccount->current_balance += $cashAdjustment->amount;
                }
                $oldAccount->save();
            }

            // Update cash adjustment
            $cashAdjustment->voucher_no = $validated['voucher_no'];
            $cashAdjustment->adjustment_date = $validated['adjustment_date'];
            $cashAdjustment->adjustment_type = $validated['adjustment_type'];
            $cashAdjustment->chart_of_account_id = $validated['chart_of_account_id'];
            $cashAdjustment->amount = $validated['amount'];
            $cashAdjustment->description = $validated['description'] ?? null;
            $cashAdjustment->reference = $validated['reference'] ?? null;
            $cashAdjustment->save();

            // Add new amount to chart of account
            $newAccount = ChartOfAccount::find($validated['chart_of_account_id']);
            if ($newAccount) {
                if ($validated['adjustment_type'] === 'increase') {
                    $newAccount->current_balance += $validated['amount'];
                } else {
                    $newAccount->current_balance -= $validated['amount'];
                }
                $newAccount->save();
            }

            return redirect()->route('cash-adjustments.index')
                ->with('success', 'Cash adjustment updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating cash adjustment: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $cashAdjustment = CashAdjustment::findOrFail($id);

            // Revert amount from chart of account
            $account = ChartOfAccount::find($cashAdjustment->chart_of_account_id);
            if ($account) {
                if ($cashAdjustment->adjustment_type === 'increase') {
                    $account->current_balance -= $cashAdjustment->amount;
                } else {
                    $account->current_balance += $cashAdjustment->amount;
                }
                $account->save();
            }

            $cashAdjustment->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cash adjustment deleted successfully!'
                ]);
            }

            return back()->with('success', 'Cash adjustment deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting cash adjustment: ' . $e->getMessage()
                ], 400);
            }

            return back()->with('error', 'Error deleting cash adjustment: ' . $e->getMessage());
        }
    }

    /**
     * Generate next voucher number
     */
    public function generateVoucherNo()
    {
        $lastVoucher = CashAdjustment::orderBy('id', 'desc')->first();
        if ($lastVoucher) {
            $lastNo = intval(substr($lastVoucher->voucher_no, -6));
            $newNo = str_pad($lastNo + 1, 6, '0', STR_PAD_LEFT);
            return response()->json(['voucher_no' => 'CA-' . $newNo]);
        }
        return response()->json(['voucher_no' => 'CA-000001']);
    }
}
