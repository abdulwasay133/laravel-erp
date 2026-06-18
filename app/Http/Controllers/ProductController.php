<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index(){
        if(request()->ajax()){
            $products = DB::table('products')
            ->leftJoin('categories','products.category_id','=','categories.id')
            ->leftJoin('units','products.unit_id','=','units.id')

            //join with Batch table
            ->leftJoin('product_batches','products.id','=','product_batches.product_id')

            //join with Supplier table
            ->leftJoin('product_suppliers','products.id','=','product_suppliers.product_id')
            ->leftJoin('suppliers','product_suppliers.supplier_id','=','suppliers.id')

            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'categories.name as category',
                'units.name as unit',
                'products.active',
                'products.price',
                'products.purchase_price',
                'products.sale_price',

                //Multiple Suppliers
                DB::raw('GROUP_CONCAT(suppliers.first_name," ",suppliers.last_name) as suppliers'),
            )
            ->whereNull('products.deleted_at')
            ->groupBy('products.id')->get();
            // dd($products);

            return DataTables::of($products)
            ->addIndexColumn()

            
            ->addColumn('status', function ($row) {
                return $row->active == 1 ? 'Active' : 'Inactive';
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="edit/'.$row->id.'" data-id="'.$row->id.'" class="btn btn-primary btn-sm edit">
                    <i class="bi bi-pencil"></i>
                </a>';

                $btn .= ' <button data-id="'.$row->id.'" class="btn btn-secondary btn-sm edit viewProduct">
                <i class="bi bi-eye"></i> 
                </button>';
                
                $btn .= ' <button 
                data-url="'.route('product.destroy', $row->id).'" 
                class="btn btn-danger btn-sm delete">
                <i class="bi bi-trash"></i>
                </button>';

                return $btn;
            })
            ->rawColumns([
                'action','status'
            ])
            ->make(true);
        }
        return view('product.index');
    }

public function show(int $id)
{
    $product = DB::table('products')
    ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
    ->leftJoin('units', 'products.unit_id', '=', 'units.id')
    ->select('products.*', 'categories.name as category', 'units.name as unit')
    ->where('products.id', $id)
    ->whereNull('products.deleted_at')
    ->first();

    $batches = DB::table('product_batches')
        ->where('product_id', $id)
        ->where('quantity', '>', 0)
        ->get();

    $suppliers = DB::table('product_suppliers')
        ->join('suppliers', 'product_suppliers.supplier_id', '=', 'suppliers.id')
        ->where('product_suppliers.product_id', $id)
        ->select(
            'suppliers.id',
            DB::raw('CONCAT(suppliers.first_name," ",suppliers.last_name) as name'),
            'product_suppliers.cost'
        )
        ->get();

    return response()->json([
        'product'   => $product,
        'batches'   => $batches,
        'suppliers' => $suppliers,
    ]);
}
    public function create(){
        
        $categories = Category::all();
        $units = Unit::all();
        $suppliers = Supplier::all();
        $product = null;
        return view('product.create',compact('product','categories','units','suppliers'));
    }


    public function store(Request $request){
        
            $request->validate([
                'product_name'  => 'required|string|min:3|max:255',
                'sku'           => 'required|unique:products,sku',
                'category'      => 'required',
                'unit'          => 'required',
                'sale_price'    => 'required|numeric',
                'opening_stock' => 'nullable|numeric|min:0',
                'minimum_quantity' => 'required|numeric|min:0',
                'has_expiry'          => 'nullable',
                'expiry_alert_days'    => 'required_if:has_expiry,1|integer|min:0',
                'notes'               => 'nullable|string',

                // batch fields
                'batch_number'      => 'nullable|string',
                'expiry_date'       => 'nullable|date',
                'purchase_price'    => 'nullable|numeric|min:0',

                // supplier fields
                'suppliers'                => 'required|array|min:1',
                'suppliers.*.supplier_id'  => 'required|exists:suppliers,id',
                'suppliers.*.unit_price'   => 'required|numeric|min:0',
            ]);
            

            DB::beginTransaction();

            try{
                $product = DB::table('products')->insertGetId([
                    'name'            => $request->product_name,
                    'sku'             => $request->sku,
                    'category_id'     => $request->category,
                    'unit_id'         => $request->unit,
                    'price'           => $request->sale_price,
                    'purchase_price'  => $request->purchase_price,
                    'sale_price'      => $request->sale_price,
                    'alert_quantity'  => $request->minimum_quantity,
                    'is_expiry'       => $request->has_expiry ? 1 : 0,
                    'expiry_alert_days' => $request->expiry_alert_days ?? 0,
                    'quantity'        => $request->opening_stock ?? 0,
                    'description'     => $request->notes,
                    'active'          => $request->is_active ? 1 : 0,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
                

                DB::table('product_batches')->insert([
                    'product_id'   => $product,
                    'batch_number' => $request->batch_number ?? 'BATCH-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                    'expiry_date'  => $request->has_expiry ? $request->expiry_date : null,
                    'quantity'     => $request->opening_stock ?? 0,
                    'cost'         => $request->purchase_price ?? 0,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                
                if($request->suppliers){
                    foreach($request->suppliers as $i => $supplier){
                        DB::table('product_suppliers')->insert([
                            'product_id'   => $product,
                            'supplier_id'  => $supplier['supplier_id'],
                            'cost'         => $supplier['unit_price'] ?? 0,
                            'is_preferred' => $request->preferred_supplier == $i ? 1 : 0,
                        ]);
                    }
                    
                }

            
                DB::commit();

                return redirect()->route('product.index')->with('success','Product created successfully');
            }catch(\Exception $e){
                DB::rollBack();
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }
    }

    public function edit(int $id){
        

        $product = Product::with(['batches','suppliers','unit','category'])->where('id',$id)->first();
        $units = Unit::all();
        $categories = Category::all();
        $suppliers = Supplier::all();
        
        return view('product.create',compact('product','categories','units','suppliers'));
    }

    public function update(Request $request,int $id){
        $request->validate([
                'product_name'  => 'required|string|min:3|max:255',
                'sku'           => 'required|unique:products,sku,' . $id,
                'category'      => 'required',
                'unit'          => 'required',
                'sale_price'    => 'required|numeric',
                'opening_stock' => 'nullable|numeric|min:0',
                'minimum_quantity' => 'required|numeric|min:0',
                'has_expiry'          => 'nullable',
                'expiry_alert_days'    => 'required_if:has_expiry,1|integer|min:0',
                'notes'               => 'nullable|string',

                // batch fields
                'batch_number'      => 'nullable|string',
                'expiry_date'       => 'nullable|date',
                'purchase_price'    => 'nullable|numeric|min:0',

                // supplier fields
                'suppliers'                => 'required|array|min:1',
                'suppliers.*.supplier_id'  => 'required|exists:suppliers,id',
                'suppliers.*.unit_price'   => 'required|numeric|min:0',
            ]);

            DB::beginTransaction();

 try{
        $product = Product::findOrFail($id);
        $product->update([
            'name'            => $request->product_name,
            'sku'             => $request->sku,
            'category_id'     => $request->category,
            'unit_id'         => $request->unit,
            'price'           => $request->sale_price,
            'purchase_price'  => $request->purchase_price,
            'sale_price'      => $request->sale_price,
            'alert_quantity'  => $request->minimum_quantity,
            'is_expiry'       => $request->has_expiry ? 1 : 0,
            'expiry_alert_days' => $request->expiry_alert_days ?? 0,
            'quantity'        => $request->opening_stock ?? 0,
            'description'     => $request->notes,
            'active'          => $request->is_active ? 1 : 0,
            'updated_at'      => now(),
        ]);

        $openingStock = $request->opening_stock ?? 0;
        $batch = $product->batches()->first();
        if ($batch) {
            $batch->update([
                'batch_number' => $request->batch_number ?? $batch->batch_number,
                'expiry_date'  => $request->has_expiry ? $request->expiry_date : null,
                'quantity'     => $openingStock,
                'cost'         => $request->purchase_price ?? 0,
            ]);
        } elseif ($openingStock > 0) {
            $product->batches()->create([
                'batch_number' => $request->batch_number ?? 'BATCH-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'expiry_date'  => $request->has_expiry ? $request->expiry_date : null,
                'quantity'     => $openingStock,
                'cost'         => $request->purchase_price ?? 0,
            ]);
        }

        $product->suppliers()->detach();
        if($request->suppliers){
            foreach($request->suppliers as $i => $supplier){
                DB::table('product_suppliers')->insert([
                    'product_id'   => $product->id,
                    'supplier_id'  => $supplier['supplier_id'],
                    'cost'         => $supplier['unit_price'] ?? 0,
                    'is_preferred' => $request->preferred_supplier == $i ? 1 : 0,
                ]);
            }
        }
       DB::commit();

                return redirect()->route('product.index')->with('success','Product Updated successfully');
            }catch(\Exception $e){
                DB::rollBack();
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }

    }

    public function distroy(int $id){
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            return response()->json([
                'success' => 'Product deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
