<?php

namespace App\Http\Controllers;

use App\Models\ProductWaste;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Setting;
use Carbon\Carbon;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class InventoryLedgerController extends Controller
{
    public function index()
    {
        $stats = [
            'total_purchases' => Purchase::where('status', 'received')->count(),
            'total_sales' => Sale::where('status', 'completed')->count(),
            'purchase_amount' => Purchase::where('status', 'received')->sum('grand_total'),
            'sale_amount' => Sale::where('status', 'completed')->sum('total_amount'),
        ];
        return view('reports.inventory_ledger', compact('stats'));
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
        ]);

        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->endOfDay();
        $records = $this->getInventoryLedgerRecords($start, $end);

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
        ]);

        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->endOfDay();
        $records = $this->getInventoryLedgerRecords($start, $end);
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
            . 'Inventory Ledger' . "\n"
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

        $documentTitle = 'Inventory Ledger';

        return view('reports.inventory_ledger_print', compact('records', 'totals', 'start', 'end', 'settings', 'qrSvg', 'documentTitle'));
    }

    private function getInventoryLedgerRecords(Carbon $start, Carbon $end)
    {
        $purchases = Purchase::where('status', 'received')
            ->where('order_date', '>=', $start->toDateString())
            ->where('order_date', '<=', $end->toDateString())
            ->orderBy('order_date', 'asc')
            ->get();

        $sales = Sale::where('status', 'completed')
            ->where('sale_date', '>=', $start->toDateString())
            ->where('sale_date', '<=', $end->toDateString())
            ->orderBy('sale_date', 'asc')
            ->get();

        $records = collect();

        foreach ($purchases as $purchase) {
            $supplierName = optional($purchase->supplier)->first_name ? $purchase->supplier->first_name . ' ' . $purchase->supplier->last_name : 'Supplier';
            $records->push([
                'date' => Carbon::parse($purchase->order_date)->toDateString(),
                'voucher_no' => $purchase->ref_no,
                'type' => 'Purchase',
                'remark' => trim(($supplierName ? $supplierName . ' - ' : '') . ($purchase->notes ?? 'Purchase received')),
                'debit' => $purchase->grand_total,
                'credit' => 0,
            ]);
        }

        foreach ($sales as $sale) {
            $customerName = optional($sale->customer)->first_name ? $sale->customer->first_name . ' ' . $sale->customer->last_name : 'Customer';
            $records->push([
                'date' => Carbon::parse($sale->sale_date)->toDateString(),
                'voucher_no' => $sale->invoice_no,
                'type' => 'Sale',
                'remark' => trim(($customerName ? $customerName . ' - ' : '') . ($sale->notes ?? 'Sale completed')),
                'debit' => 0,
                'credit' => $sale->total_amount,
            ]);
        }

        $wastes = ProductWaste::whereBetween('waste_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('waste_date', 'asc')
            ->get();

        foreach ($wastes as $waste) {
            $productName = optional($waste->product)->name ?? 'Product';
            $records->push([
                'date' => Carbon::parse($waste->waste_date)->toDateString(),
                'voucher_no' => 'WST-' . $waste->id,
                'type' => 'Waste',
                'remark' => $productName . ' - ' . ($waste->reason ?? 'Waste/Expiry write-off'),
                'debit' => 0,
                'credit' => $waste->total_cost,
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
