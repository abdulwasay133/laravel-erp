<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierTransaction;
use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\SystemAccountMapping;
use App\Services\HandlesAccounting;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SupplierPaymentController extends Controller
{
    use HandlesAccounting;
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $payments = SupplierPayment::with('supplier', 'bankAccount')
                ->orderBy('payment_date', 'desc')
                ->get();

            return DataTables::of($payments)
                ->addIndexColumn()
                ->addColumn('voucher_no', function ($row) {
                    return $row->voucher_no;
                })
                ->addColumn('supplier_name', function ($row) {
                    return $row->supplier ? $row->supplier->first_name . ' ' . $row->supplier->last_name : '-';
                })
                ->addColumn('payment_date', function ($row) {
                    return $row->payment_date->format('Y-m-d');
                })
                ->addColumn('payment_type', function ($row) {
                    $badgeColor = $row->payment_type === 'credit' ? 'bg-primary' : 'bg-warning';
                    $label = $row->payment_type === 'credit' ? 'Credit' : 'Debit';
                    return '<span class="badge ' . $badgeColor . '">' . $label . '</span>';
                })
                ->addColumn('payment_method', function ($row) {
                    $badgeColor = $row->payment_method === 'cash' ? 'bg-success' : 'bg-info';
                    $label = $row->payment_method === 'cash' ? 'Cash' : 'Account';
                    return '<span class="badge ' . $badgeColor . '">' . $label . '</span>';
                })
                ->addColumn('amount', function ($row) {
                    return number_format($row->amount, 2, '.', ',');
                })
                ->addColumn('reference', function ($row) {
                    return $row->reference ?: '-';
                })
                ->addColumn('action', function ($row) {
                    $actions = '<a href="' . route('supplier-payments.edit', $row->id) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>';
                    $actions .= '<button class="btn btn-sm btn-outline-danger delete-payment" data-id="' . $row->id . '"><i class="bi bi-trash"></i></button>';
                    return $actions;
                })
                ->rawColumns(['payment_type', 'payment_method', 'action'])
                ->make(true);
        }

        $stats = [
            'total_payments' => SupplierPayment::count(),
            'total_credit' => SupplierPayment::where('payment_type', 'credit')->sum('amount'),
            'total_debit' => SupplierPayment::where('payment_type', 'debit')->sum('amount'),
            'net_amount' => SupplierPayment::where('payment_type', 'credit')->sum('amount') - SupplierPayment::where('payment_type', 'debit')->sum('amount'),
        ];

        return view('supplier-payments.index', compact('stats'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('first_name')->get();
        $bankAccounts = BankAccount::orderBy('account_number')->get();
        return view('supplier-payments.create', compact('suppliers', 'bankAccounts'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'voucher_no' => 'required|string|unique:supplier_payments,voucher_no',
                'supplier_id' => 'required|exists:suppliers,id',
                'payment_date' => 'required|date',
                'payment_type' => 'required|in:credit,debit',
                'payment_method' => 'required|in:cash,account',
                'bank_account_id' => 'required_if:payment_method,account|nullable|exists:bank_accounts,id',
                'amount' => 'required|numeric|min:0.01',
                'reference' => 'nullable|string',
                'description' => 'nullable|string',
            ]);

            // Check sufficient balance for debit payments
            if ($validated['payment_type'] === 'debit') {
                if ($validated['payment_method'] === 'account' && $validated['bank_account_id']) {
                    $bankAccount = BankAccount::findOrFail($validated['bank_account_id']);
                    if ($bankAccount->current_balance < $validated['amount']) {
                        throw new \Exception('Insufficient bank balance. Available: ' . number_format($bankAccount->current_balance, 2));
                    }
                } elseif ($validated['payment_method'] === 'cash') {
                    $cashAccountId = SystemAccountMapping::getAccount('cash_account');
                    if ($cashAccountId) {
                        $cashAccount = ChartOfAccount::findOrFail($cashAccountId);
                        if ($cashAccount->current_balance < $validated['amount']) {
                            throw new \Exception('Insufficient cash balance. Available: ' . number_format($cashAccount->current_balance, 2));
                        }
                    }
                }
            }

            $payment = SupplierPayment::create($validated);

            // Update supplier balance and ledger
            $this->updateSupplierBalance($validated, 'add');

            // Update Chart of Accounts
            $this->updateAccountBalance($validated, 'add');

            // Update Bank Account if payment method is account
            if ($validated['payment_method'] === 'account' && $validated['bank_account_id']) {
                $bankAccount = BankAccount::find($validated['bank_account_id']);
                if ($bankAccount) {
                    if ($validated['payment_type'] === 'credit') {
                        $bankAccount->current_balance += $validated['amount'];
                    } else {
                        $bankAccount->current_balance -= $validated['amount'];
                    }
                    $bankAccount->save();
                }
            }

            if ($validated['payment_type'] === 'debit') {
                $paymentMethod = $validated['payment_method'] === 'account' ? 'bank' : 'cash';
                $this->postSupplierPaymentAccounting($payment->id, $payment->amount, $paymentMethod);
            }

            return redirect()->route('supplier-payments.index')
                ->with('success', 'Supplier payment recorded successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $payment = SupplierPayment::findOrFail($id);
        $suppliers = Supplier::orderBy('first_name')->get();
        $bankAccounts = BankAccount::orderBy('account_number')->get();
        return view('supplier-payments.edit', compact('payment', 'suppliers', 'bankAccounts'));
    }

    public function update(Request $request, $id)
    {
        try {
            $payment = SupplierPayment::findOrFail($id);

            $validated = $request->validate([
                'voucher_no' => 'required|string|unique:supplier_payments,voucher_no,' . $id,
                'supplier_id' => 'required|exists:suppliers,id',
                'payment_date' => 'required|date',
                'payment_type' => 'required|in:credit,debit',
                'payment_method' => 'required|in:cash,account',
                'bank_account_id' => 'required_if:payment_method,account|nullable|exists:bank_accounts,id',
                'amount' => 'required|numeric|min:0.01',
                'reference' => 'nullable|string',
                'description' => 'nullable|string',
            ]);

            // Check sufficient balance for debit payments
            if ($validated['payment_type'] === 'debit') {
                if ($validated['payment_method'] === 'account' && $validated['bank_account_id']) {
                    $bankAccount = BankAccount::findOrFail($validated['bank_account_id']);
                    $availableBalance = $bankAccount->current_balance;

                    // Account for old payment's effect being reverted from same bank
                    if ($payment->payment_method === 'account' && $payment->bank_account_id == $validated['bank_account_id']) {
                        if ($payment->payment_type === 'debit') {
                            $availableBalance += $payment->amount;
                        } else {
                            $availableBalance -= $payment->amount;
                        }
                    }

                    if ($availableBalance < $validated['amount']) {
                        throw new \Exception('Insufficient bank balance. Available: ' . number_format($availableBalance, 2));
                    }
                } elseif ($validated['payment_method'] === 'cash') {
                    $cashAccountId = SystemAccountMapping::getAccount('cash_account');
                    if ($cashAccountId) {
                        $cashAccount = ChartOfAccount::findOrFail($cashAccountId);
                        $availableBalance = $cashAccount->current_balance;

                        if ($payment->payment_method === 'cash') {
                            if ($payment->payment_type === 'debit') {
                                $availableBalance += $payment->amount;
                            } else {
                                $availableBalance -= $payment->amount;
                            }
                        }

                        if ($availableBalance < $validated['amount']) {
                            throw new \Exception('Insufficient cash balance. Available: ' . number_format($availableBalance, 2));
                        }
                    }
                }
            }

            // Revert old supplier balance
            $oldData = [
                'supplier_id' => $payment->supplier_id,
                'payment_type' => $payment->payment_type,
                'payment_method' => $payment->payment_method,
                'bank_account_id' => $payment->bank_account_id,
                'amount' => $payment->amount,
                'payment_date' => $payment->payment_date->format('Y-m-d'),
                'voucher_no' => $payment->voucher_no,
                'description' => $payment->description,
            ];
            $this->updateSupplierBalance($oldData, 'subtract');

            // Revert old account balance
            $oldAcctData = [
                'payment_type' => $payment->payment_type,
                'payment_method' => $payment->payment_method,
                'bank_account_id' => $payment->bank_account_id,
                'amount' => $payment->amount,
            ];
            $this->updateAccountBalance($oldAcctData, 'subtract');

            // Update payment
            $payment->update($validated);

            // Apply new supplier balance
            $this->updateSupplierBalance($validated, 'add');

            // Add new account balance
            $this->updateAccountBalance($validated, 'add');

            return redirect()->route('supplier-payments.index')
                ->with('success', 'Supplier payment updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $payment = SupplierPayment::findOrFail($id);

            // Revert supplier balance
            $this->updateSupplierBalance([
                'supplier_id' => $payment->supplier_id,
                'payment_type' => $payment->payment_type,
                'amount' => $payment->amount,
                'payment_date' => $payment->payment_date->format('Y-m-d'),
                'voucher_no' => $payment->voucher_no,
                'description' => $payment->description,
            ], 'subtract');

            // Revert account balance
            $data = [
                'payment_type' => $payment->payment_type,
                'payment_method' => $payment->payment_method,
                'bank_account_id' => $payment->bank_account_id,
                'amount' => $payment->amount,
            ];
            $this->updateAccountBalance($data, 'subtract');

            $payment->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Supplier payment deleted successfully!'
                ]);
            }

            return back()->with('success', 'Supplier payment deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 400);
            }

            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function generateVoucherNo()
    {
        $lastVoucher = SupplierPayment::orderBy('id', 'desc')->first();
        if ($lastVoucher) {
            $lastNo = intval(substr($lastVoucher->voucher_no, -6));
            $newNo = str_pad($lastNo + 1, 6, '0', STR_PAD_LEFT);
            return response()->json(['voucher_no' => 'SP-' . $newNo]);
        }
        return response()->json(['voucher_no' => 'SP-000001']);
    }

    /**
     * Update supplier balance and create ledger transaction
     */
    private function updateSupplierBalance($data, $operation = 'add')
    {
        $supplier = Supplier::findOrFail($data['supplier_id']);
        $lastBalance = SupplierTransaction::where('supplier_id', $data['supplier_id'])
            ->orderByDesc('id')
            ->value('balance') ?? $supplier->balance ?? 0;

        $paymentType = $data['payment_type'];
        $amount = $data['amount'];

        if ($operation === 'add') {
            if ($paymentType === 'credit') {
                $debit = $amount;
                $credit = 0;
            } else {
                $debit = 0;
                $credit = $amount;
            }
        } else {
            if ($paymentType === 'credit') {
                $debit = 0;
                $credit = $amount;
            } else {
                $debit = $amount;
                $credit = 0;
            }
        }

        $newBalance = $lastBalance + $debit - $credit;

        SupplierTransaction::create([
            'supplier_id' => $data['supplier_id'],
            'date' => $data['payment_date'],
            'type' => $operation === 'add' ? 'payment' : 'payment_reverted',
            'reference' => 'SP: ' . ($data['voucher_no'] ?? ''),
            'description' => $data['description'] ?? ($paymentType === 'credit' ? 'Credit payment' : 'Debit payment'),
            'debit' => $debit,
            'credit' => $credit,
            'balance' => $newBalance,
        ]);

        $supplier->update(['balance' => $newBalance]);
    }

    /**
     * Update Chart of Accounts balance based on payment
     */
    private function updateAccountBalance($data, $operation = 'add')
    {
        try {
            $paymentType = $data['payment_type'];
            $paymentMethod = $data['payment_method'];
            $amount = $data['amount'];

            $accountId = null;
            if ($paymentMethod === 'cash') {
                $accountId = SystemAccountMapping::getAccount('cash_account');
            } elseif ($paymentMethod === 'account' && !empty($data['bank_account_id'])) {
                $bankAccount = BankAccount::find($data['bank_account_id']);
                if ($bankAccount) {
                    $accountId = SystemAccountMapping::getAccount('bank_account');
                }
            }

            if ($accountId) {
                $account = ChartOfAccount::find($accountId);
                if ($account) {
                    $changeAmount = $amount;

                    if ($operation === 'add') {
                        if ($paymentType === 'credit') {
                            // Credit increases balance (money coming in)
                            $account->current_balance += $changeAmount;
                        } else {
                            // Debit decreases balance (money going out)
                            $account->current_balance -= $changeAmount;
                        }
                    } else {
                        if ($paymentType === 'credit') {
                            // Subtract credit
                            $account->current_balance -= $changeAmount;
                        } else {
                            // Add back debit
                            $account->current_balance += $changeAmount;
                        }
                    }

                    $account->save();
                }
            }

            // Also update bank account if payment method is account
            if ($paymentMethod === 'account' && !empty($data['bank_account_id'])) {
                $bankAccount = BankAccount::find($data['bank_account_id']);
                if ($bankAccount) {
                    if ($operation === 'add') {
                        if ($paymentType === 'credit') {
                            $bankAccount->current_balance += $amount;
                        } else {
                            $bankAccount->current_balance -= $amount;
                        }
                    } else {
                        if ($paymentType === 'credit') {
                            $bankAccount->current_balance -= $amount;
                        } else {
                            $bankAccount->current_balance += $amount;
                        }
                    }
                    $bankAccount->save();
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error updating account balance: ' . $e->getMessage());
        }
    }
}
