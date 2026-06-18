<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Models\SystemAccountMapping;
use App\Services\HandlesAccounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ExpenseController extends Controller
{
    use HandlesAccounting;
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $expenses = Expense::with(['chartOfAccount', 'bankAccount'])
                ->orderByDesc('expense_date')
                ->orderByDesc('id');

            return DataTables::of($expenses)
                ->addIndexColumn()
                ->addColumn('account_head', fn ($row) => $row->chartOfAccount
                    ? $row->chartOfAccount->code . ' - ' . $row->chartOfAccount->name
                    : '-')
                ->editColumn('expense_date', fn ($row) => $row->expense_date->format('Y-m-d'))
                ->addColumn('payment_method_label', function ($row) {
                    if ($row->payment_method === 'cash') {
                        return '<span class="badge bg-success">Cash</span>';
                    }

                    $bank = $row->bankAccount
                        ? $row->bankAccount->bank_name . ' - ' . $row->bankAccount->account_number
                        : 'Bank';

                    return '<span class="badge bg-info">Bank</span> <small class="text-muted">' . e($bank) . '</small>';
                })
                ->editColumn('amount', fn ($row) => number_format($row->amount, 2, '.', ','))
                ->addColumn('description', fn ($row) => $row->description ?: '-')
                ->addColumn('action', function ($row) {
                    $actions = '<a href="' . route('expenses.edit', $row->id) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>';
                    $actions .= '<button class="btn btn-sm btn-outline-danger delete-expense" data-id="' . $row->id . '"><i class="bi bi-trash"></i></button>';

                    return $actions;
                })
                ->rawColumns(['payment_method_label', 'action'])
                ->make(true);
        }

        return view('expenses.index');
    }

    public function create()
    {
        $accounts = ChartOfAccount::active()->where('type', 'expense')->orderBy('code')->get();
        $bankAccounts = BankAccount::orderBy('bank_name')->get();

        return view('expenses.create', compact('accounts', 'bankAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateExpense($request);

        if ($validated['payment_method'] === 'cash') {
            $validated['bank_account_id'] = null;
        }

        try {
            DB::beginTransaction();

            $expense = Expense::create(array_merge($validated, [
                'created_by' => auth()->id(),
            ]));

            $this->applyFinancialEffects($expense);

            $paymentMethod = $validated['payment_method'] === 'bank' ? 'bank' : 'cash';
            $this->postExpenseAccounting(
                $expense->id,
                $expense->amount,
                $expense->chart_of_account_id,
                $paymentMethod
            );

            DB::commit();

            return redirect()->route('expenses.index')
                ->with('success', 'Expense recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $expense = Expense::findOrFail($id);
        $accounts = ChartOfAccount::active()->where('type', 'expense')->orderBy('code')->get();
        $bankAccounts = BankAccount::orderBy('bank_name')->get();

        return view('expenses.edit', compact('expense', 'accounts', 'bankAccounts'));
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        $validated = $this->validateExpense($request, $expense->id);

        if ($validated['payment_method'] === 'cash') {
            $validated['bank_account_id'] = null;
        }

        try {
            DB::beginTransaction();

            $this->revertFinancialEffects($expense);
            $expense->update($validated);
            $expense->refresh();
            $this->applyFinancialEffects($expense);

            DB::commit();

            return redirect()->route('expenses.index')
                ->with('success', 'Expense updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $expense = Expense::findOrFail($id);

            DB::beginTransaction();
            $this->revertFinancialEffects($expense);
            $this->reverseAccounting('Expense', $expense->id, 'Expense deleted');
            $expense->delete();
            DB::commit();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Expense deleted successfully.',
                ]);
            }

            return back()->with('success', 'Expense deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ], 400);
            }

            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function generateVoucherNo()
    {
        $last = Expense::orderByDesc('id')->first();
        $nextNumber = $last ? intval(substr($last->voucher_no, -6)) + 1 : 1;

        return response()->json(['voucher_no' => 'EXP-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT)]);
    }

    private function validateExpense(Request $request, ?int $expenseId = null): array
    {
        $uniqueRule = 'required|string|unique:expenses,voucher_no';
        if ($expenseId) {
            $uniqueRule .= ',' . $expenseId;
        }

        return $request->validate([
            'voucher_no' => $uniqueRule,
            'expense_date' => 'required|date',
            'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank',
            'bank_account_id' => 'required_if:payment_method,bank|nullable|exists:bank_accounts,id',
            'reference' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
    }

    private function applyFinancialEffects(Expense $expense): void
    {
        $cashAccountId = SystemAccountMapping::getAccount('cash_account');
        $bankAccountId = SystemAccountMapping::getAccount('bank_account');

        $expenseAccount = ChartOfAccount::find($expense->chart_of_account_id);
        if ($expenseAccount) {
            $expenseAccount->current_balance = ($expenseAccount->current_balance ?? 0) + $expense->amount;
            $expenseAccount->save();
        }

        if ($expense->payment_method === 'cash') {
            if ($cashAccountId) {
                $cashAccount = ChartOfAccount::find($cashAccountId);
                if ($cashAccount) {
                    $cashAccount->current_balance = ($cashAccount->current_balance ?? 0) - $expense->amount;
                    $cashAccount->save();
                }
            }
            return;
        }

        if ($expense->bank_account_id) {
            $bankAccount = BankAccount::find($expense->bank_account_id);
            if ($bankAccount) {
                $bankAccount->current_balance = ($bankAccount->current_balance ?? 0) - $expense->amount;
                $bankAccount->save();
            }
        }

        if ($bankAccountId) {
            $bankChartAccount = ChartOfAccount::find($bankAccountId);
            if ($bankChartAccount) {
                $bankChartAccount->current_balance = ($bankChartAccount->current_balance ?? 0) - $expense->amount;
                $bankChartAccount->save();
            }
        }
    }

    private function revertFinancialEffects(Expense $expense): void
    {
        $cashAccountId = SystemAccountMapping::getAccount('cash_account');
        $bankAccountId = SystemAccountMapping::getAccount('bank_account');

        $expenseAccount = ChartOfAccount::find($expense->chart_of_account_id);
        if ($expenseAccount) {
            $expenseAccount->current_balance = ($expenseAccount->current_balance ?? 0) - $expense->amount;
            $expenseAccount->save();
        }

        if ($expense->payment_method === 'cash') {
            if ($cashAccountId) {
                $cashAccount = ChartOfAccount::find($cashAccountId);
                if ($cashAccount) {
                    $cashAccount->current_balance = ($cashAccount->current_balance ?? 0) + $expense->amount;
                    $cashAccount->save();
                }
            }
            return;
        }

        if ($expense->bank_account_id) {
            $bankAccount = BankAccount::find($expense->bank_account_id);
            if ($bankAccount) {
                $bankAccount->current_balance = ($bankAccount->current_balance ?? 0) + $expense->amount;
                $bankAccount->save();
            }
        }

        if ($bankAccountId) {
            $bankChartAccount = ChartOfAccount::find($bankAccountId);
            if ($bankChartAccount) {
                $bankChartAccount->current_balance = ($bankChartAccount->current_balance ?? 0) + $expense->amount;
                $bankChartAccount->save();
            }
        }
    }
}
