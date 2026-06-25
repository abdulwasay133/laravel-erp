<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function index(){
        if(request()->ajax()){
            return DataTables::of(Category::query())
            ->addIndexColumn()
            ->addColumn('status', function ($row) {
                return $row->active == 1 ? 'Active' : 'Inactive';
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="edit/'.$row->id.'" data-id="'.$row->id.'" class="btn btn-primary btn-sm edit">
                    <i class="bi bi-pencil"></i> 
                </a>';
                $btn .= ' <button 
                data-url="'.route('category.destroy', $row->id).'" 
                class="btn btn-danger btn-sm delete">
                <i class="bi bi-trash"></i>
                </button>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
        }
        $stats = [
            'total' => Category::count(),
            'active' => Category::where('active', 1)->count(),
        ];

        return view('category.index', compact('stats'));
    }

    public function create(){
        return view('category.create');
    }
    
    public function store(Request $request){
        $request->validate([
            'name'=> 'required||min:3||max:255',
            'slug'=>'required||unique:categories',
            'status'=>'required',
        ]);
        // dd('ok');

        Category::create([
            'name'=>$request->name,
            'slug'=>$request->slug,
            'description'=>$request->description,
            'active'=>$request->status
        ]);

        return redirect()->route('category.index')->with('success','Category created successfully.');
    }

    public function edit(int $id){
        $category = Category::findOrFail($id);
        return view('category.create', compact('category'));
    }

    public function update(Request $request,int $id){
        $request->validate([
            'name'=> 'required||min:3||max:255',
            'slug'=>'required||unique:categories',
            'status'=>'required',
        ]);

        $category = Category::findOrFail($id);
        $category->update([
            'name'=>$request->name,
            'slug'=>$request->slug,
            'description'=>$request->description,
            'active'=>$request->status
        ]);

        return redirect()->route('category.index')->with('success','Category updated successfully.');
    }

    public function distroy(int $id){
        Category::findOrFail($id)->delete();
        return response()->json(['success' => 'Category deleted successfully.']);
    }
}
