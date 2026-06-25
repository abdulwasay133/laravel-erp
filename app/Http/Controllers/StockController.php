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
            $products = Product::with('category', 'unit', 'purchaseItems')->get();

            $totalInQty = 0;
            $totalOutQty = 0;
            $totalStock = 0;
            $totalStockSaleValue = 0;
            $totalStockPurchaseValue = 0;

            return DataTables::of($products)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return $row->name;
                })
                ->addColumn('sale_price', function ($row) {
                    return 'Rs. ' . number_format($row->sale_price ?? 0, 2, '.', ',');
                })
                ->addColumn('purchase_price', function ($row) {
                    $purchaseItems = $row->purchaseItems;
                    if ($purchaseItems->isEmpty()) {
                        return 'Rs. 0.00';
                    }
                    $totalCost = $purchaseItems->sum(function ($item) {
                        return $item->unit_cost * $item->quantity;
                    });
                    $totalQty = $purchaseItems->sum('quantity');
                    $avgCost = $totalQty > 0 ? $totalCost / $totalQty : 0;
                    return 'Rs. ' . number_format($avgCost, 2, '.', ',');
                })
                ->addColumn('in_qty', function ($row) {
                    $inQty = PurchaseItem::where('product_id', $row->id)->sum('quantity');
                    return $inQty;
                })
                ->addColumn('out_qty', function ($row) {
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
                    $purchaseItems = $row->purchaseItems;
                    if ($purchaseItems->isEmpty()) {
                        return 'Rs. 0.00';
                    }
                    $totalCost = $purchaseItems->sum(function ($item) {
                        return $item->unit_cost * $item->quantity;
                    });
                    $totalQty = $purchaseItems->sum('quantity');
                    $avgCost = $totalQty > 0 ? $totalCost / $totalQty : 0;
                    $totalValue = $stock * $avgCost;
                    return 'Rs. ' . number_format($totalValue, 2, '.', ',');
                })
                ->with('totals', function () use ($products, &$totalInQty, &$totalOutQty, &$totalStock, &$totalStockSaleValue, &$totalStockPurchaseValue) {
                    foreach ($products as $row) {
                        $inQty = PurchaseItem::where('product_id', $row->id)->sum('quantity');
                        $outQty = SaleItem::where('product_id', $row->id)
                            ->whereHas('sale', fn($q) => $q->where('status', 'completed'))
                            ->sum('quantity');
                        $stock = $row->quantity ?? 0;
                        $saleValue = $stock * ($row->sale_price ?? 0);

                        $purchaseItems = $row->purchaseItems;
                        $purchaseValue = 0;
                        if ($purchaseItems->isNotEmpty()) {
                            $totalCost = $purchaseItems->sum(fn($i) => $i->unit_cost * $i->quantity);
                            $totalQty = $purchaseItems->sum('quantity');
                            $avgCost = $totalQty > 0 ? $totalCost / $totalQty : 0;
                            $purchaseValue = $stock * $avgCost;
                        }

                        $totalInQty += $inQty;
                        $totalOutQty += $outQty;
                        $totalStock += $stock;
                        $totalStockSaleValue += $saleValue;
                        $totalStockPurchaseValue += $purchaseValue;
                    }

                    return [
                        'total_in_qty' => $totalInQty,
                        'total_out_qty' => $totalOutQty,
                        'total_stock' => $totalStock,
                        'total_stock_sale_price' => 'Rs. ' . number_format($totalStockSaleValue, 2, '.', ','),
                        'total_stock_purchase_price' => 'Rs. ' . number_format($totalStockPurchaseValue, 2, '.', ','),
                    ];
                })
                ->make(true);
        }

        $stats = [
            'total_products' => Product::where('quantity', '>', 0)->count(),
            'total_stock_qty' => Product::sum('quantity'),
            'total_stock_sale_value' => Product::selectRaw('SUM(quantity * COALESCE(sale_price, 0)) as total')->value('total'),
            'total_stock_purchase_value' => Product::selectRaw('SUM(quantity * COALESCE(purchase_price, 0)) as total')->value('total'),
        ];

        return view('stock.index', compact('stats'));
    }
}
