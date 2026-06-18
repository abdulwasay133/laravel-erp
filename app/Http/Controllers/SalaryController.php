<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\Employee;
use App\Models\SalaryPayment;
use App\Models\SystemAccountMapping;
use App\Services\HandlesAccounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SalaryController extends Controller
{
    use HandlesAccounting;
    public function index()
    {
        if (request()->ajax()) {
            $payments = SalaryPayment::with('employee');

            return DataTables::of($payments)
                ->addIndexColumn()
                ->addColumn('employee', function ($row) {
                    return $row->employee->first_name . ' ' . $row->employee->last_name;
                })
                ->addColumn('amount', function ($row) {
                    return number_format($row->amount, 2);
                })
                ->addColumn('payment_method', function ($row) {
                    $badge = $row->payment_method === 'cash' ? 'bg-success' : 'bg-info';
                    return '<span class="badge ' . $badge . '">' . ucfirst($row->payment_method) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-outline-secondary" disabled>Paid</button>';
                })
                ->rawColumns(['payment_method', 'action'])
                ->make(true);
        }

        return view('salary.index');
    }

    public function pending()
    {
        $currentMonth = now()->format('Y-m');
        $previousMonth = now()->subMonth()->format('Y-m');

        $paidEmployeeIds = SalaryPayment::whereIn('salary_month', [$currentMonth, $previousMonth])
            ->pluck('employee_id');

        $pendingEmployees = Employee::where('status', true)
            ->whereNotIn('id', $paidEmployeeIds)
            ->get();

        return response()->json(['pending' => $pendingEmployees]);
    }

    public function create()
    {
        $bankAccounts = BankAccount::orderBy('bank_name')->get();
        $employees = Employee::where('status', true)->orderBy('first_name')->get();
        return view('salary.create', compact('bankAccounts', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'salary_month' => 'required|date_format:Y-m',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank',
            'bank_account_id' => 'required_if:payment_method,bank|nullable|exists:bank_accounts,id',
            'reference' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $exists = SalaryPayment::where('employee_id', $request->employee_id)
            ->where('salary_month', $request->salary_month)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Salary already paid for this month.'], 422);
        }

        DB::beginTransaction();
        try {
            $salariesAccountId = SystemAccountMapping::getAccount('salary_expense');
            if (!$salariesAccountId) {
                DB::rollBack();
                return response()->json(['error' => 'Salaries expense account not found. Please run the seeder.'], 500);
            }
            $salariesAccount = ChartOfAccount::find($salariesAccountId);

            $voucherNo = 'EXP-' . str_pad(DB::table('expenses')->count() + 1, 6, '0', STR_PAD_LEFT);

            $expenseId = DB::table('expenses')->insertGetId([
                'voucher_no' => $voucherNo,
                'expense_date' => $request->payment_date,
                'chart_of_account_id' => $salariesAccount->id,
                'title' => 'Salary for ' . $request->salary_month . ' - Employee #' . $request->employee_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'bank_account_id' => $request->bank_account_id,
                'reference' => $request->reference,
                'description' => $request->description,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $expense = DB::table('expenses')->where('id', $expenseId)->first();

            $salariesAccount->increment('current_balance', $expense->amount);

            if ($request->payment_method === 'cash') {
                $cashAccountId = SystemAccountMapping::getAccount('cash_account');
                if ($cashAccountId) {
                    ChartOfAccount::find($cashAccountId)?->decrement('current_balance', $expense->amount);
                }
            } elseif ($request->payment_method === 'bank' && $request->bank_account_id) {
                $bankAccount = BankAccount::findOrFail($request->bank_account_id);
                $bankAccount->decrement('current_balance', $expense->amount);

                $bankCoaId = SystemAccountMapping::getAccount('bank_account');
                if ($bankCoaId) {
                    ChartOfAccount::find($bankCoaId)?->decrement('current_balance', $expense->amount);
                }
            }

            $salaryPayment = SalaryPayment::create([
                'employee_id' => $request->employee_id,
                'salary_month' => $request->salary_month,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'bank_account_id' => $request->bank_account_id,
                'expense_id' => $expenseId,
                'reference' => $request->reference,
                'description' => $request->description,
            ]);

            $this->postSalaryAccounting($salaryPayment->id, $request->amount);

            DB::commit();

            return redirect()->route('salary.index')->with('success', 'Salary paid successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
