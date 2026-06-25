<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductWaste;
use App\Services\HandlesAccounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NearToExpiryController extends Controller
{
    use HandlesAccounting;
    public function index()
    {
        if (request()->ajax()) {
            $nearExpiry = DB::table('product_batches')
                ->join('products', 'product_batches.product_id', '=', 'products.id')
                ->where('product_batches.quantity', '>', 0)
                ->where('products.expiry_alert_days', '>', 0)
                ->whereRaw('DATEDIFF(product_batches.expiry_date, CURDATE()) BETWEEN 0 AND products.expiry_alert_days')
                ->select(
                    'product_batches.id',
                    'products.name as product_name',
                    'product_batches.batch_number',
                    'product_batches.quantity',
                    'product_batches.expiry_date',
                    'products.expiry_alert_days',
                    DB::raw('(SELECT MIN(purchases.ref_no) FROM purchases INNER JOIN purchase_items ON purchase_items.purchase_id = purchases.id WHERE purchase_items.product_id = product_batches.product_id AND purchase_items.batch_number = product_batches.batch_number) as purchase_ref')
                )
                ->orderBy('product_batches.expiry_date')
                ->get();

            return \Yajra\DataTables\Facades\DataTables::of($nearExpiry)
                ->addIndexColumn()
                ->addColumn('expiry_status', function ($row) {
                    $daysLeft = (int) now()->diffInDays(\Carbon\Carbon::parse($row->expiry_date), false);
                    if ($daysLeft < 0) {
                        return '<span class="badge bg-danger">Expired</span>';
                    } elseif ($daysLeft <= 7) {
                        return '<span class="badge bg-warning text-dark">' . $daysLeft . ' days left</span>';
                    }
                    return '<span class="badge bg-info">' . $daysLeft . ' days left</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex flex-nowrap gap-1">';
                    $btn .= '<button class="btn btn-warning btn-sm waste-batch" data-id="' . $row->id . '" title="Waste"><i class="bi bi-trash3"></i> Waste</button>';
                    $btn .= '<a href="' . route('purchase-returns.create', ['ref_no' => $row->purchase_ref]) . '" class="btn btn-info btn-sm text-white" title="Return"><i class="bi bi-arrow-return-left"></i> Return</a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['expiry_status', 'action'])
                ->make(true);
        }

        $stats = [
            'total_batches' => DB::table('product_batches')
                ->join('products', 'product_batches.product_id', '=', 'products.id')
                ->where('product_batches.quantity', '>', 0)
                ->where('products.expiry_alert_days', '>', 0)
                ->whereRaw('DATEDIFF(product_batches.expiry_date, CURDATE()) BETWEEN 0 AND products.expiry_alert_days')
                ->count(),
            'total_quantity' => DB::table('product_batches')
                ->join('products', 'product_batches.product_id', '=', 'products.id')
                ->where('product_batches.quantity', '>', 0)
                ->where('products.expiry_alert_days', '>', 0)
                ->whereRaw('DATEDIFF(product_batches.expiry_date, CURDATE()) BETWEEN 0 AND products.expiry_alert_days')
                ->sum('product_batches.quantity'),
            'expired' => DB::table('product_batches')
                ->join('products', 'product_batches.product_id', '=', 'products.id')
                ->where('product_batches.quantity', '>', 0)
                ->whereRaw('DATEDIFF(product_batches.expiry_date, CURDATE()) < 0')
                ->count(),
            'critical' => DB::table('product_batches')
                ->join('products', 'product_batches.product_id', '=', 'products.id')
                ->where('product_batches.quantity', '>', 0)
                ->where('products.expiry_alert_days', '>', 0)
                ->whereRaw('DATEDIFF(product_batches.expiry_date, CURDATE()) BETWEEN 0 AND 7')
                ->count(),
        ];

        return view('near-to-expiry.index', compact('stats'));
    }

    public function print()
    {
        $batches = DB::table('product_batches')
            ->join('products', 'product_batches.product_id', '=', 'products.id')
            ->where('product_batches.quantity', '>', 0)
            ->where('products.expiry_alert_days', '>', 0)
            ->whereRaw('DATEDIFF(product_batches.expiry_date, CURDATE()) BETWEEN 0 AND products.expiry_alert_days')
            ->select(
                'product_batches.id',
                'products.name as product_name',
                'product_batches.batch_number',
                'product_batches.quantity',
                'product_batches.expiry_date',
                DB::raw('(SELECT MIN(purchases.ref_no) FROM purchases INNER JOIN purchase_items ON purchase_items.purchase_id = purchases.id WHERE purchase_items.product_id = product_batches.product_id AND purchase_items.batch_number = product_batches.batch_number) as purchase_ref')
            )
            ->orderBy('product_batches.expiry_date')
            ->get();

        return view('near-to-expiry.print', compact('batches'));
    }

    public function waste(Request $request)
    {
        try {
            $batch = ProductBatch::findOrFail($request->id);
            $product = Product::findOrFail($batch->product_id);

            DB::transaction(function () use ($batch, $product) {
                $unitCost = (float) $batch->cost;
                $totalCost = round($unitCost * $batch->quantity, 2);
                $wasteDate = now()->toDateString();

                $waste = ProductWaste::create([
                    'product_id' => $product->id,
                    'product_batch_id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'quantity' => $batch->quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'waste_date' => $wasteDate,
                    'reason' => 'Near to expiry waste',
                    'created_by' => auth()->id(),
                ]);

                $this->postWasteAccounting($waste->id, $totalCost, $wasteDate);

                $product->quantity = max(0, ($product->quantity ?? 0) - $batch->quantity);
                $product->save();

                $batch->quantity = 0;
                $batch->save();
            });

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Batch wasted successfully!']);
            }
            return back()->with('success', 'Batch wasted successfully!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 400);
            }
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function returnBatch(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:product_batches,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $batch = ProductBatch::findOrFail($request->id);
            $product = Product::findOrFail($batch->product_id);

            if ($request->quantity > $batch->quantity) {
                throw new \Exception('Return quantity cannot exceed available batch quantity (' . $batch->quantity . ').');
            }

            DB::transaction(function () use ($batch, $product, $request) {
                $product->quantity = max(0, ($product->quantity ?? 0) - $request->quantity);
                $product->save();

                $batch->quantity = max(0, $batch->quantity - $request->quantity);
                $batch->save();
            });

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Batch returned successfully!']);
            }
            return back()->with('success', 'Batch returned successfully!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 400);
            }
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
