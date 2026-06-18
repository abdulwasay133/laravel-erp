<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SaleReportController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'company']);
        $users = User::orderBy('name')->get(['id', 'name']);
        $categories = Category::where('active', true)->orderBy('name')->get(['id', 'name']);

        return view('reports.sale_report', compact('customers', 'users', 'categories'));
    }

    public function search(Request $request)
    {
        if (!$request->start_date || !$request->end_date) {
            return DataTables::of(collect([]))
                ->setTotalRecords(0)
                ->setFilteredRecords(0)
                ->with(['totals' => $this->emptyTotals()])
                ->make(true);
        }

        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'created_by' => ['nullable', 'exists:users,id'],
            'status' => ['nullable', 'in:draft,completed,cancelled'],
            'payment_status' => ['nullable', 'in:paid,partial,unpaid'],
            'payment_type' => ['nullable', 'in:cash,check,bank_transfer,credit_card,credit'],
            'invoice_no' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ]);

        $query = $this->buildQuery($request);
        $totals = $this->calculateTotals(clone $query);

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('category_names', fn ($row) => $this->categoryNames($row))
            ->addColumn('customer_name', function ($row) {
                return $row->customer
                    ? trim($row->customer->first_name . ' ' . $row->customer->last_name)
                    : '-';
            })
            ->addColumn('user_name', fn ($row) => $row->createdBy?->name ?? '-')
            ->editColumn('sale_date', fn ($row) => $row->sale_date->format('d M Y'))
            ->editColumn('total_amount', fn ($row) => number_format($row->total_amount, 2, '.', ','))
            ->editColumn('paid_amount', fn ($row) => number_format($row->paid_amount, 2, '.', ','))
            ->editColumn('balance', fn ($row) => number_format($row->balance, 2, '.', ','))
            ->addColumn('payment_status_label', fn ($row) => $this->paymentStatusBadge($row))
            ->addColumn('payment_types', function ($row) {
                $types = $row->payments->pluck('payment_type')->unique()->map(function ($type) {
                    return ucwords(str_replace('_', ' ', $type));
                });

                return $types->isNotEmpty() ? $types->implode(', ') : '-';
            })
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    'draft' => '<span class="badge bg-secondary">Draft</span>',
                    'completed' => '<span class="badge bg-success">Completed</span>',
                    'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
                ];

                return $badges[$row->status] ?? '<span class="badge bg-light">Unknown</span>';
            })
            ->filterColumn('customer_name', function ($query, $keyword) {
                $query->whereHas('customer', function ($customerQuery) use ($keyword) {
                    $customerQuery->where('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhere('company', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('user_name', function ($query, $keyword) {
                $query->whereHas('createdBy', fn ($userQuery) => $userQuery->where('name', 'like', "%{$keyword}%"));
            })
            ->with(['totals' => $totals])
            ->rawColumns(['status_badge', 'payment_status_label'])
            ->make(true);
    }

    public function print(Request $request)
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'created_by' => ['nullable', 'exists:users,id'],
            'status' => ['nullable', 'in:draft,completed,cancelled'],
            'payment_status' => ['nullable', 'in:paid,partial,unpaid'],
            'payment_type' => ['nullable', 'in:cash,check,bank_transfer,credit_card,credit'],
            'invoice_no' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $sales = $this->buildQuery($request)->get();
        $totals = $this->calculateTotalsFromCollection($sales);
        $filters = $this->filterSummary($request);

        $settings = [
            'company_name'     => Setting::getValue('company_name'),
            'company_address'  => Setting::getValue('company_address'),
            'company_phone'    => Setting::getValue('company_phone'),
            'company_email'    => Setting::getValue('company_email'),
            'company_website'  => Setting::getValue('company_website'),
            'company_logo'     => Setting::getValue('company_logo'),
            'terms_conditions' => Setting::getValue('terms_conditions', 'Thank you for your business!'),
        ];

        $qrData = $settings['company_name'] . "\n"
            . 'Sale Report: ' . $start->format('d M, Y') . ' — ' . $end->format('d M, Y') . "\n"
            . 'Total Amount: Rs. ' . number_format($totals['total_amount'], 2) . "\n"
            . 'Total Paid: Rs. ' . number_format($totals['paid_amount'], 2) . "\n"
            . 'Total Balance: Rs. ' . number_format($totals['balance'], 2);

        $qrCode = new QrCode(
            data: $qrData,
            encoding: new Encoding('UTF-8'),
            size: 200,
        );

        $writer = new SvgWriter();
        $result = $writer->write($qrCode);
        $qrSvg = $result->getString();

        $documentTitle = 'Sale Report';

        return view('reports.sale_report_print', compact(
            'sales', 'totals', 'start', 'end', 'filters', 'settings', 'qrSvg', 'documentTitle'
        ));
    }

    private function buildQuery(Request $request)
    {
        $start = Carbon::parse($request->start_date)->toDateString();
        $end = Carbon::parse($request->end_date)->toDateString();

        $query = Sale::with(['customer', 'createdBy', 'payments', 'items.product.category'])
            ->whereBetween('sale_date', [$start, $end])
            ->orderByDesc('sale_date')
            ->orderByDesc('id');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('invoice_no')) {
            $query->where('invoice_no', 'like', '%' . $request->invoice_no . '%');
        }

        if ($request->filled('payment_status')) {
            match ($request->payment_status) {
                'paid' => $query->where('balance', '<=', 0)->where('total_amount', '>', 0),
                'unpaid' => $query->where('paid_amount', '<=', 0)->where('total_amount', '>', 0),
                'partial' => $query->where('balance', '>', 0)->where('paid_amount', '>', 0),
                default => null,
            };
        }

        if ($request->filled('payment_type')) {
            $query->whereHas('payments', fn ($paymentQuery) => $paymentQuery->where('payment_type', $request->payment_type));
        }

        if ($request->filled('category_id')) {
            $query->whereHas('items.product', fn ($productQuery) => $productQuery->where('category_id', $request->category_id));
        }

        return $query;
    }

    private function categoryNames(Sale $sale): string
    {
        $names = $sale->items
            ->pluck('product.category.name')
            ->filter()
            ->unique()
            ->values();

        return $names->isNotEmpty() ? $names->implode(', ') : '-';
    }

    private function calculateTotals($query): array
    {
        return [
            'count' => (int) (clone $query)->count(),
            'total_amount' => (float) (clone $query)->sum('total_amount'),
            'paid_amount' => (float) (clone $query)->sum('paid_amount'),
            'balance' => (float) (clone $query)->sum('balance'),
        ];
    }

    private function calculateTotalsFromCollection($sales): array
    {
        return [
            'count' => $sales->count(),
            'total_amount' => (float) $sales->sum('total_amount'),
            'paid_amount' => (float) $sales->sum('paid_amount'),
            'balance' => (float) $sales->sum('balance'),
        ];
    }

    private function paymentStatusBadge(Sale $sale): string
    {
        if ($sale->total_amount <= 0) {
            return '<span class="badge bg-secondary">N/A</span>';
        }

        if ($sale->balance <= 0) {
            return '<span class="badge bg-success">Paid</span>';
        }

        if ($sale->paid_amount <= 0) {
            return '<span class="badge bg-danger">Unpaid</span>';
        }

        return '<span class="badge bg-warning text-dark">Partial</span>';
    }

    private function filterSummary(Request $request): array
    {
        $summary = [];

        if ($request->filled('customer_id')) {
            $customer = Customer::find($request->customer_id);
            $summary['Customer'] = $customer
                ? trim($customer->first_name . ' ' . $customer->last_name)
                : '-';
        }

        if ($request->filled('created_by')) {
            $user = User::find($request->created_by);
            $summary['User'] = $user?->name ?? '-';
        }

        if ($request->filled('status')) {
            $summary['Status'] = ucfirst($request->status);
        }

        if ($request->filled('payment_status')) {
            $summary['Payment Status'] = ucfirst($request->payment_status);
        }

        if ($request->filled('payment_type')) {
            $summary['Payment Type'] = ucwords(str_replace('_', ' ', $request->payment_type));
        }

        if ($request->filled('invoice_no')) {
            $summary['Invoice No'] = $request->invoice_no;
        }

        if ($request->filled('category_id')) {
            $category = Category::find($request->category_id);
            $summary['Category'] = $category?->name ?? '-';
        }

        return $summary;
    }

    private function emptyTotals(): array
    {
        return [
            'count' => 0,
            'total_amount' => 0,
            'paid_amount' => 0,
            'balance' => 0,
        ];
    }
}
