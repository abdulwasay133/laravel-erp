<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Encoding\Encoding;
use Yajra\DataTables\Facades\DataTables;

class CustomerLedgerController extends Controller
{
    public function index()
    {
        $customers = Customer::all()->sortBy('first_name');
        return view('customer.ledger', compact('customers'));
    }

    public function search(Request $request)
    {
        if (!$request->customer_id || !$request->start_date || !$request->end_date) {
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
            'customer_id' => ['required', 'integer'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);

        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->endOfDay();

        $qb = CustomerTransaction::where('customer_id', $request->customer_id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date');

        $totalDebit = (clone $qb)->sum('debit');
        $totalCredit = (clone $qb)->sum('credit');
        $lastBalance = (clone $qb)->orderByDesc('date')->value('balance') ?? 0;

        if ($request->ajax()) {
            return DataTables::of($qb)
                ->addIndexColumn()
                ->editColumn('date', function ($row) {
                    return Carbon::parse($row->date)->toDateString();
                })
                ->with(['totals' => [
                    'debit' => $totalDebit,
                    'credit' => $totalCredit,
                    'balance' => $lastBalance,
                ]])
                ->make(true);
        }

        return response()->json([
            'records' => [],
            'totals' => [
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'balance' => $lastBalance,
            ]
        ]);
    }

    public function print(Request $request)
    {
        $request->validate([
            'customer_id' => ['required', 'integer'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);

        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->endOfDay();

        $customer = Customer::findOrFail($request->customer_id);
        $records = CustomerTransaction::where('customer_id', $request->customer_id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();

        $lastRecord = $records->last();
        $totals = [
            'debit' => $records->sum('debit'),
            'credit' => $records->sum('credit'),
            'balance' => $lastRecord ? $lastRecord->balance : 0,
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
            . 'Customer Ledger: ' . $customer->first_name . ' ' . $customer->last_name . "\n"
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

        $documentTitle = 'Customer Ledger';

        return view('customer.ledger_print', compact('customer', 'records', 'totals', 'start', 'end', 'settings', 'qrSvg', 'documentTitle'));
    }

    public function creditCustomers(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(Customer::where('balance', '>', 0))
                ->addIndexColumn()
                ->addColumn('action', function ($c) {
                    return '<a href="' . route('customers.show', $c->id) . '" class="btn btn-sm btn-outline-info">View</a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        // initial load - view will call AJAX to populate table
        $customers = collect();
        return view('customer.credit_customers', compact('customers'));
    }
}
