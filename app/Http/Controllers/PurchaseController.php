<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function create()
    {
        $suppliers = Supplier::all();
        $warehouses = Supplier::all();
        $products = Product::all();
        return view('purchase.create',compact('suppliers','warehouses','products'));
    }

    public function getSupplierProducts(int $id)
    {
        // dd($id);
            $products = Product::whereHas('suppliers', function ($q) use ($id) {
        $q->where('suppliers.id', $id);
    })->select('id', 'name', 'sku')->get();
        
        // dd($products);
        return response()->json($products);
    }

    
}
