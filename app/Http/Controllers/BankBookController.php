<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\CustomerPayment;
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

class BankBookController extends Controller
{
    public function index()
    {
        $bankAccounts = BankAccount::query()->orderBy('bank_name', 'asc')->get();
        return view('reports.bank_book', compact('bankAccounts'));
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

        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
        ]);

        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->endOfDay();
        $records = $this->getBankBookRecords($start, $end, $request->bank_account_id);

        $totals = [
            'debit' => collect($records)->sum('debit'),
            'credit' => collect($records)->sum('credit'),
            'balance' => collect($records)->last()['balance'] ?? 0,
        ];

        return DataTables::of($records)
            ->addIndexColumn()
            ->editColumn('date', fn ($row) => $row['date'])
            ->editColumn('debit', fn ($row) => $row['debit'] ? number_format($row['debit'], 2, '.', ',') : '')
            ->editColumn('credit', fn ($row) => $row['credit'] ? number_format($row['credit'], 2, '.', ',') : '')
            ->editColumn('balance', fn ($row) => number_format($row['balance'], 2, '.', ','))
            ->with(['totals' => $totals])
            ->make(true);
    }

    public function print(Request $request)
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
        ]);

        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->endOfDay();
        $bankAccount = null;

        if ($request->bank_account_id) {
            $bankAccount = BankAccount::query()->find($request->bank_account_id);
        }

        $records = $this->getBankBookRecords($start, $end, $request->bank_account_id);
        $totals = [
            'debit' => collect($records)->sum('debit'),
            'credit' => collect($records)->sum('credit'),
            'balance' => collect($records)->last()['balance'] ?? 0,
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
            . 'Bank Book' . "\n"
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

        $documentTitle = 'Bank Book';

        return view('reports.bank_book_print', compact('records', 'totals', 'start', 'end', 'bankAccount', 'settings', 'qrSvg', 'documentTitle'));
    }

    private function getBankBookRecords(Carbon $start, Carbon $end, $bankAccountId = null)
    {
        $customerPayments = CustomerPayment::with('bankAccount')
            ->where('payment_method', 'account')
            ->whereBetween('payment_date', [$start->toDateString(), $end->toDateString()]);

        $supplierPayments = SupplierPayment::with('bankAccount')
            ->where('payment_method', 'account')
            ->whereBetween('payment_date', [$start->toDateString(), $end->toDateString()]);

        $purchases = Purchase::with('bankAccount')
            ->where('payment_method', 'bank')
            ->where('paid_amount', '>', 0)
            ->whereBetween('order_date', [$start->toDateString(), $end->toDateString()]);

        $salePayments = SalePayment::with(['sale', 'bankAccount'])
            ->where('payment_type', 'bank_transfer')
            ->whereBetween('payment_date', [$start->toDateString(), $end->toDateString()]);

        $expenses = Expense::with('bankAccount')
            ->where('payment_method', 'bank')
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()]);

        if ($bankAccountId) {
            $customerPayments->where('bank_account_id', $bankAccountId);
            $supplierPayments->where('bank_account_id', $bankAccountId);
            $purchases->where('bank_account_id', $bankAccountId);
            $salePayments->where('bank_account_id', $bankAccountId);
            $expenses->where('bank_account_id', $bankAccountId);
        }

        $records = collect();

        foreach ($customerPayments->orderBy('payment_date', 'asc')->get() as $payment) {
            $bankLabel = $payment->bankAccount ? trim($payment->bankAccount->bank_name . ' - ' . $payment->bankAccount->account_title) : 'Customer Payment';
            $customerName = optional($payment->customer)->first_name ? trim($payment->customer->first_name . ' ' . $payment->customer->last_name) : 'Customer';

            $records->push([
                'date' => $payment->payment_date->toDateString(),
                'voucher_no' => $payment->voucher_no,
                'type' => $bankLabel,
                'remark' => $payment->description ?: $payment->reference ?: $customerName,
                'debit' => $payment->payment_type === 'credit' ? $payment->amount : 0,
                'credit' => $payment->payment_type === 'debit' ? $payment->amount : 0,
            ]);
        }

        foreach ($supplierPayments->orderBy('payment_date', 'asc')->get() as $payment) {
            $bankLabel = $payment->bankAccount ? trim($payment->bankAccount->bank_name . ' - ' . $payment->bankAccount->account_title) : 'Supplier Payment';
            $supplierName = optional($payment->supplier)->first_name ? trim($payment->supplier->first_name . ' ' . $payment->supplier->last_name) : 'Supplier';

            $records->push([
                'date' => $payment->payment_date->toDateString(),
                'voucher_no' => $payment->voucher_no,
                'type' => $bankLabel,
                'remark' => $payment->description ?: $payment->reference ?: $supplierName,
                'debit' => $payment->payment_type === 'credit' ? $payment->amount : 0,
                'credit' => $payment->payment_type === 'debit' ? $payment->amount : 0,
            ]);
        }

        foreach ($purchases->orderBy('order_date', 'asc')->get() as $purchase) {
            $bankLabel = $purchase->bankAccount ? trim($purchase->bankAccount->bank_name . ' - ' . $purchase->bankAccount->account_title) : 'Purchase Bank Payment';
            $supplierName = optional($purchase->supplier)->first_name ? trim($purchase->supplier->first_name . ' ' . $purchase->supplier->last_name) : 'Supplier';

            $records->push([
                'date' => Carbon::parse($purchase->order_date)->toDateString(),
                'voucher_no' => $purchase->ref_no,
                'type' => $bankLabel,
                'remark' => trim(($supplierName ? $supplierName . ' - ' : '') . 'Purchase payment'),
                'debit' => 0,
                'credit' => $purchase->paid_amount,
            ]);
        }

        foreach ($salePayments->orderBy('payment_date', 'asc')->get() as $payment) {
            $bankLabel = $payment->bankAccount ? trim($payment->bankAccount->bank_name . ' - ' . $payment->bankAccount->account_title) : 'Sale Bank Transfer';
            $invoiceNo = optional($payment->sale)->invoice_no ?: 'Sale Payment';

            $records->push([
                'date' => $payment->payment_date->toDateString(),
                'voucher_no' => $invoiceNo,
                'type' => $bankLabel,
                'remark' => $payment->sale ? 'Sale receipt for ' . $invoiceNo : 'Sale bank transfer',
                'debit' => $payment->amount,
                'credit' => 0,
            ]);
        }

        foreach ($expenses->orderBy('expense_date', 'asc')->get() as $expense) {
            $bankLabel = $expense->bankAccount ? trim($expense->bankAccount->bank_name . ' - ' . $expense->bankAccount->account_title) : 'Expense';

            $records->push([
                'date' => $expense->expense_date->toDateString(),
                'voucher_no' => $expense->voucher_no,
                'type' => $bankLabel,
                'remark' => $expense->title . ($expense->description ? ' - ' . $expense->description : ''),
                'debit' => 0,
                'credit' => $expense->amount,
            ]);
        }

        $records = $records->sortBy(function ($row) {
            return $row['date'];
        })->values();

        $runningBalance = 0;
        return $records->map(function ($row) use (&$runningBalance) {
            $runningBalance += $row['debit'] - $row['credit'];
            $row['balance'] = round($runningBalance, 2);
            return $row;
        });
    }
}
