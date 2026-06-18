<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Purchase;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PurchaseReportController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'company_name']);
        $categories = Category::where('active', true)->orderBy('name')->get(['id', 'name']);

        return view('reports.purchase_report', compact('suppliers', 'categories'));
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
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'status' => ['nullable', 'in:pending,received,cancelled'],
            'payment_status' => ['nullable', 'in:paid,partial,unpaid'],
            'ref_no' => ['nullable', 'string'],
        ]);

        $query = $this->buildQuery($request);
        $totals = $this->calculateTotals(clone $query);

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('category_names', fn ($row) => $this->categoryNames($row))
            ->addColumn('supplier_name', function ($row) {
                return $row->supplier
                    ? trim($row->supplier->first_name . ' ' . $row->supplier->last_name)
                    : '-';
            })
            ->editColumn('order_date', fn ($row) => Carbon::parse($row->order_date)->format('d M Y'))
            ->editColumn('grand_total', fn ($row) => number_format($row->grand_total, 2, '.', ','))
            ->editColumn('paid_amount', fn ($row) => number_format($row->paid_amount, 2, '.', ','))
            ->editColumn('due_amount', fn ($row) => number_format($row->due_amount, 2, '.', ','))
            ->addColumn('payment_status_label', fn ($row) => $this->paymentStatusBadge($row))
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    'pending' => '<span class="badge bg-warning text-dark">Pending</span>',
                    'received' => '<span class="badge bg-success">Received</span>',
                    'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
                ];

                return $badges[$row->status] ?? '<span class="badge bg-light">Unknown</span>';
            })
            ->filterColumn('supplier_name', function ($query, $keyword) {
                $query->whereHas('supplier', function ($supplierQuery) use ($keyword) {
                    $supplierQuery->where('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhere('company_name', 'like', "%{$keyword}%");
                });
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
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'status' => ['nullable', 'in:pending,received,cancelled'],
            'payment_status' => ['nullable', 'in:paid,partial,unpaid'],
            'ref_no' => ['nullable', 'string'],
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $purchases = $this->buildQuery($request)->get();
        $totals = $this->calculateTotalsFromCollection($purchases);
        $filters = $this->filterSummary($request);

        return view('reports.purchase_report_print', compact('purchases', 'totals', 'start', 'end', 'filters'));
    }

    private function buildQuery(Request $request)
    {
        $start = Carbon::parse($request->start_date)->toDateString();
        $end = Carbon::parse($request->end_date)->toDateString();

        $query = Purchase::with(['supplier', 'items.product.category'])
            ->whereBetween('order_date', [$start, $end])
            ->orderByDesc('order_date')
            ->orderByDesc('id');

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('ref_no')) {
            $query->where('ref_no', 'like', '%' . $request->ref_no . '%');
        }

        if ($request->filled('payment_status')) {
            match ($request->payment_status) {
                'paid' => $query->where('due_amount', '<=', 0)->where('grand_total', '>', 0),
                'unpaid' => $query->where('paid_amount', '<=', 0)->where('grand_total', '>', 0),
                'partial' => $query->where('due_amount', '>', 0)->where('paid_amount', '>', 0),
                default => null,
            };
        }

        if ($request->filled('category_id')) {
            $query->whereHas('items.product', fn ($productQuery) => $productQuery->where('category_id', $request->category_id));
        }

        return $query;
    }

    private function categoryNames(Purchase $purchase): string
    {
        $names = $purchase->items
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
            'grand_total' => (float) (clone $query)->sum('grand_total'),
            'paid_amount' => (float) (clone $query)->sum('paid_amount'),
            'due_amount' => (float) (clone $query)->sum('due_amount'),
        ];
    }

    private function calculateTotalsFromCollection($purchases): array
    {
        return [
            'count' => $purchases->count(),
            'grand_total' => (float) $purchases->sum('grand_total'),
            'paid_amount' => (float) $purchases->sum('paid_amount'),
            'due_amount' => (float) $purchases->sum('due_amount'),
        ];
    }

    private function paymentStatusBadge(Purchase $purchase): string
    {
        if ($purchase->grand_total <= 0) {
            return '<span class="badge bg-secondary">N/A</span>';
        }

        if ($purchase->due_amount <= 0) {
            return '<span class="badge bg-success">Paid</span>';
        }

        if ($purchase->paid_amount <= 0) {
            return '<span class="badge bg-danger">Unpaid</span>';
        }

        return '<span class="badge bg-warning text-dark">Partial</span>';
    }

    private function filterSummary(Request $request): array
    {
        $summary = [];

        if ($request->filled('supplier_id')) {
            $supplier = Supplier::find($request->supplier_id);
            $summary['Supplier'] = $supplier
                ? trim($supplier->first_name . ' ' . $supplier->last_name)
                : '-';
        }

        if ($request->filled('category_id')) {
            $category = Category::find($request->category_id);
            $summary['Category'] = $category?->name ?? '-';
        }

        if ($request->filled('status')) {
            $summary['Status'] = ucfirst($request->status);
        }

        if ($request->filled('payment_status')) {
            $summary['Payment Status'] = ucfirst($request->payment_status);
        }

        if ($request->filled('ref_no')) {
            $summary['Ref No'] = $request->ref_no;
        }

        return $summary;
    }

    private function emptyTotals(): array
    {
        return [
            'count' => 0,
            'grand_total' => 0,
            'paid_amount' => 0,
            'due_amount' => 0,
        ];
    }
}
