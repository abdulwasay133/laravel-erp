<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductWaste;
use App\Services\HandlesAccounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProductWasteController extends Controller
{
    use HandlesAccounting;
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $wastes = ProductWaste::with(['product', 'batch', 'createdBy'])->orderByDesc('id');

            return DataTables::of($wastes)
                ->addIndexColumn()
                ->addColumn('product_name', fn($row) => $row->product?->name ?? '-')
                ->editColumn('waste_date', fn($row) => $row->waste_date->format('d M Y'))
                ->editColumn('unit_cost', fn($row) => 'Rs. ' . number_format($row->unit_cost, 2))
                ->editColumn('total_cost', fn($row) => 'Rs. ' . number_format($row->total_cost, 2))
                ->addColumn('created_by_name', fn($row) => $row->createdBy?->name ?? '-')
                ->addColumn('action', fn($row) =>
                    '<a href="' . route('product-waste.show', $row->id) . '" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>'
                )
                ->rawColumns(['action'])
                ->make(true);
        }

        $stats = [
            'total_wastes' => ProductWaste::count(),
            'total_quantity' => ProductWaste::sum('quantity'),
            'total_cost' => ProductWaste::sum('total_cost'),
        ];

        return view('product-waste.index', compact('stats'));
    }

    public function create()
    {
        $products = Product::where('quantity', '>', 0)
            ->orWhereHas('batches', fn($q) => $q->where('quantity', '>', 0))
            ->orderBy('name')
            ->get(['id', 'name', 'quantity']);

        return view('product-waste.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_batch_id' => 'required|exists:product_batches,id',
            'quantity' => 'required|integer|min:1',
            'waste_date' => 'required|date',
            'reason' => 'nullable|string|max:1000',
            'notes' => 'nullable|string',
        ]);

        $batch = ProductBatch::findOrFail($validated['product_batch_id']);
        $product = Product::findOrFail($validated['product_id']);

        if ($validated['quantity'] > $batch->quantity) {
            return back()->withInput()->with('error',
                'Waste quantity (' . $validated['quantity'] . ') exceeds available batch quantity (' . $batch->quantity . ').');
        }

        try {
            DB::beginTransaction();

            $unitCost = (float) $batch->cost;
            $totalCost = round($unitCost * $validated['quantity'], 2);

            $waste = ProductWaste::create([
                'product_id' => $product->id,
                'product_batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'quantity' => $validated['quantity'],
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'waste_date' => $validated['waste_date'],
                'reason' => $validated['reason'],
                'created_by' => auth()->id(),
                'notes' => $validated['notes'],
            ]);

            $this->postWasteAccounting($waste->id, $totalCost, $validated['waste_date']);

            $batch->quantity = max(0, $batch->quantity - $validated['quantity']);
            $batch->save();

            $product->quantity = max(0, ($product->quantity ?? 0) - $validated['quantity']);
            $product->save();

            DB::commit();

            return redirect()->route('product-waste.index')
                ->with('success', 'Product waste recorded successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to record waste. Please try again.');
        }
    }

    public function show($id)
    {
        $waste = ProductWaste::with(['product', 'batch', 'createdBy'])->findOrFail($id);
        return view('product-waste.show', compact('waste'));
    }

    public function getBatches(Request $request)
    {
        $batches = ProductBatch::where('product_id', $request->product_id)
            ->where('quantity', '>', 0)
            ->orderBy('batch_number')
            ->get(['id', 'batch_number', 'quantity', 'cost', 'expiry_date']);

        return response()->json($batches);
    }
}
