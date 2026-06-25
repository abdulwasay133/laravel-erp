<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\CommissionPayment;
use App\Models\Employee;
use App\Models\Setting;
use App\Services\CommissionService;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CommissionPaymentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $payments = CommissionPayment::with('orderBooker', 'createdBy');

            return DataTables::of($payments)
                ->addIndexColumn()
                ->addColumn('booker_name', function ($row) {
                    return $row->orderBooker?->first_name . ' ' . $row->orderBooker?->last_name;
                })
                ->editColumn('amount', function ($row) {
                    return 'Rs. ' . number_format($row->amount, 2);
                })
                ->editColumn('payment_date', function ($row) {
                    return $row->payment_date->format('d M, Y');
                })
                ->addColumn('payment_method_badge', function ($row) {
                    $badges = [
                        'cash' => '<span class="badge bg-success">Cash</span>',
                        'bank' => '<span class="badge bg-primary">Bank</span>',
                    ];
                    return $badges[$row->payment_method] ?? '<span class="badge bg-secondary">' . $row->payment_method . '</span>';
                })
                ->addColumn('created_by_name', function ($row) {
                    return $row->createdBy?->name ?? '-';
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('commission-payments.show', $row->id) . '" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>';
                })
                ->rawColumns(['payment_method_badge', 'action'])
                ->make(true);
        }

        return view('commission-payments.index');
    }

    public function create()
    {
        $orderBookers = Employee::where('is_order_booker', true)
            ->whereHas('commissions', function ($q) {
                $q->whereIn('status', ['pending', 'approved']);
            })
            ->orderBy('first_name')
            ->get();

        return view('commission-payments.create', compact('orderBookers'));
    }

    public function getCommissions($orderBookerId)
    {
        $commissions = Commission::where('order_booker_id', $orderBookerId)
            ->whereIn('status', ['pending', 'approved'])
            ->with('sale')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'invoice_no' => $c->sale?->invoice_no ?? '-',
                    'sale_date' => $c->sale?->sale_date?->format('d M, Y') ?? '-',
                    'sale_amount' => $c->sale_amount,
                    'commission_rate' => $c->commission_rate,
                    'commission_amount' => $c->commission_amount,
                ];
            });

        return response()->json($commissions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_booker_id' => 'required|exists:employees,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank',
            'commission_ids' => 'required|array|min:1',
            'commission_ids.*' => 'exists:commissions,id',
            'reference_no' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        try {
            $payment = app(CommissionService::class)->payCommissions(
                $request->commission_ids,
                $request->order_booker_id,
                $request->payment_method,
                $request->payment_date,
                $request->reference_no,
                $request->remarks
            );

            return redirect()->route('commission-payments.show', $payment->id)
                ->with('success', 'Commission payment of Rs. ' . number_format($payment->amount, 2) . ' recorded successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $payment = CommissionPayment::with(['orderBooker', 'createdBy', 'details.commission.sale'])->findOrFail($id);
        return view('commission-payments.show', compact('payment'));
    }

    public function print($id)
    {
        $payment = CommissionPayment::with(['orderBooker', 'createdBy', 'details.commission.sale'])->findOrFail($id);

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
            . 'Voucher: ' . $payment->payment_no . "\n"
            . 'Date: ' . $payment->payment_date->format('d M, Y') . "\n"
            . 'Amount: Rs. ' . number_format($payment->amount, 2) . "\n"
            . 'Booker: ' . ($payment->orderBooker?->first_name ?? '') . ' ' . ($payment->orderBooker?->last_name ?? '');

        $qrCode = new QrCode(
            data: $qrData,
            encoding: new Encoding('UTF-8'),
            size: 200
        );

        $writer = new SvgWriter();
        $result = $writer->write($qrCode);
        $qrSvg = $result->getString();

        $documentTitle = 'Commission Payment Voucher';

        return view('commission-payments.print', compact('payment', 'settings', 'qrSvg', 'documentTitle'));
    }
}
