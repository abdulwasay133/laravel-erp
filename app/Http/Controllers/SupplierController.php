<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    
    public function index(Request $request)
    {
        if($request->ajax()){
            return DataTables::of(Supplier::query())
            ->addIndexColumn()
            ->addColumn('name', function($row){
                return $row->first_name.' '.$row->last_name;
            })
            ->addColumn('status', function($row){
                return $row->active == 1 ? 'Active' : 'Inactive';
            })
            ->addColumn('action', function($row){
                $btn = '<div class="d-flex flex-nowrap gap-1">';
                $btn .= '<a href="edit/'.$row->id.'" data-id="'.$row->id.'" class="btn btn-primary btn-sm edit"><i class="bi bi-pencil"></i></a>';
                $btn .= '<button data-id="'.$row->id.'" class="btn btn-secondary btn-sm edit viewSupplier"><i class="bi bi-eye"></i></button>';
                $btn .= '<button data-url="'.route('supplier.destroy', $row->id).'" class="btn btn-danger btn-sm delete"><i class="bi bi-trash"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status', 'action','name'])
            ->make(true);
        }
        $stats = [
            'total' => Supplier::count(),
            'active' => Supplier::where('active', 1)->count(),
            'total_balance' => Supplier::sum('balance'),
        ];

        return view('supplier.index', compact('stats'));
    }

    public function view(int $id){
        $supplier = Supplier::findOrFail($id);
        return response()->json($supplier);
    }

    public function create()
    {
        return view('supplier.create');
    }

    public function store(Request $request){
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:suppliers'],
            'phone' => ['required', 'string', 'min:11' ,'max:13', 'unique:suppliers'],
            'type'=>['required', 'string', 'max:255'],
            'status'=>['required', 'string', 'max:255'],
            'company_name'=>['string', 'max:255','min:3'],
            'notes'=>['string', 'max:255','min:3'],
            'address'=>['string', 'max:255','min:3'],
            'city'=>['string', 'max:255','min:3'],
            'province'=>['string', 'max:255','min:3'],
            'postal_code'=>['string', 'max:255','min:3'],
            'opening_balance'=>['required', 'numeric', 'min:1000'],
            'currency'=>['string', 'max:255','min:3'],  
        ]);

        Supplier::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'type' => $request->type,
            'active' => $request->status,
            'company_name' => $request->company_name,
            'notes' => $request->notes,
            'address' => $request->address,
            'city' => $request->city,
            'province' => $request->province,
            'postal_code' => $request->postal_code,
            'opening_balance' => $request->opening_balance,
            'currency' => $request->currency,
            'balance'=>$request->opening_balance
        ]);
        return redirect()->route('supplier.index')->with('success','Supplier created successfully.');
    }
    public function edit(int $id){
        $supplier = Supplier::findOrFail($id);
        return view('supplier.create', compact('supplier'));
    }
    public function update(Request $request,int $id){
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'min:11' ,'max:13'],
            'type'=>['required', 'string', 'max:255'],
            'status'=>['required', 'string', 'max:255'],
            'company_name'=>['string', 'max:255','min:3'],
            'notes'=>['string', 'max:255','min:3'],
            'address'=>['string', 'max:255','min:3'],
            'city'=>['string', 'max:255','min:3'],
            'province'=>['string', 'max:255','min:3'],
            'postal_code'=>['string', 'max:255','min:3'],
            'opening_balance'=>['required', 'numeric', 'min:1000'],
            'currency'=>['string', 'max:255','min:3'],  
        ]);

        $supplier = Supplier::findOrFail($id);
        $supplier->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'type' => $request->type,
            'active' => $request->status,
            'company_name' => $request->company_name,
            'notes' => $request->notes,
            'address' => $request->address,
            'city' => $request->city,
            'province' => $request->province,
            'postal_code' => $request->postal_code,
            'opening_balance' => $request->opening_balance,
            'currency' => $request->currency,
            'balance'=>$request->opening_balance
        ]);
        return redirect()->route('supplier.index')->with('success','Supplier updated successfully.');
    }
    public function distroy(int $id){
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
        return response()->json(['success' => 'Supplier deleted successfully.']);
    }
}
