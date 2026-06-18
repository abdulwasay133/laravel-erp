<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $products = Product::with('category', 'unit')->get();

            return DataTables::of($products)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return $row->name;
                })
                ->addColumn('sale_price', function ($row) {
                    return 'Rs. ' . number_format($row->sale_price ?? 0, 2, '.', ',');
                })
                ->addColumn('purchase_price', function ($row) {
                    // Get average purchase price from purchase items
                    $purchaseItems = PurchaseItem::where('product_id', $row->id)->get();
                    if ($purchaseItems->isEmpty()) {
                        return 'Rs. 0.00';
                    }
                    $totalCost = $purchaseItems->sum('unit_cost');
                    $avgCost = $totalCost / $purchaseItems->count();
                    return 'Rs. ' . number_format($avgCost, 2, '.', ',');
                })
                ->addColumn('in_qty', function ($row) {
                    // Sum of quantities from purchase items
                    $inQty = PurchaseItem::where('product_id', $row->id)->sum('quantity');
                    return $inQty;
                })
                ->addColumn('out_qty', function ($row) {
                    // Sum of quantities from sale items (only completed sales)
                    $outQty = SaleItem::where('product_id', $row->id)
                        ->whereHas('sale', function ($query) {
                            $query->where('status', 'completed');
                        })
                        ->sum('quantity');
                    return $outQty;
                })
                ->addColumn('stock', function ($row) {
                    return $row->quantity ?? 0;
                })
                ->addColumn('stock_sale_price', function ($row) {
                    $stock = $row->quantity ?? 0;
                    $salePrice = $row->sale_price ?? 0;
                    $totalValue = $stock * $salePrice;
                    return 'Rs. ' . number_format($totalValue, 2, '.', ',');
                })
                ->addColumn('stock_purchase_price', function ($row) {
                    $stock = $row->quantity ?? 0;
                    // Get average purchase price
                    $purchaseItems = PurchaseItem::where('product_id', $row->id)->get();
                    if ($purchaseItems->isEmpty()) {
                        return 'Rs. 0.00';
                    }
                    $totalCost = $purchaseItems->sum('unit_cost');
                    $avgCost = $totalCost / $purchaseItems->count();
                    $totalValue = $stock * $avgCost;
                    return 'Rs. ' . number_format($totalValue, 2, '.', ',');
                })
                ->make(true);
        }

        return view('stock.index');
    }
}
