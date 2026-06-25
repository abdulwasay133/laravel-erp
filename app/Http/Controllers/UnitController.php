<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $units = Unit::query();

            return DataTables::of($units)
                ->addIndexColumn()

                ->addColumn('status', function ($row) {
                    return $row->active == 1 ? 'Active' : 'Inactive';
                })

                ->addColumn('action', function ($row) {
                    $btn = '<a href="editunit/'.$row->id.'" data-id="'.$row->id.'" class="btn btn-primary btn-sm edit">
                        <i class="bi bi-pencil"></i> 
                    </a>';
                    $btn .= ' <button 
            data-url="'.route('unit.destroy', $row->id).'" 
            class="btn btn-danger btn-sm delete">
                    <i class="bi bi-trash"></i>
                    </button>';
                    return $btn;
                })

                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        $stats = [
            'total' => Unit::count(),
            'active' => Unit::where('active', 1)->count(),
        ];

        return view('product.unitlist', compact('stats'));
    }

    public function create()
    {
        return view('product.addunit');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string','min:3', 'max:255'],
            'symbol' => ['required', 'string', 'max:255', 'unique:units'],
            'type' => ['required', 'string', 'max:255'],
            'status'=>['required', 'string', 'max:255'],
        ]);
        
        Unit::create([
            'name' => $request->name,
            'symbol' => $request->symbol,
            'type' => $request->type,
            'active' => $request->status
        ]);

        return redirect()->route('unit.unitlist')->with('success','Unit created successfully.');
    }

    public function edit(int $id)
    {
         $unit = Unit::findOrFail($id);
         return view('product.addunit', compact('unit'));
    }

    public function update(Request $request,int $id)
    {
        $request->validate([
            'name' => ['required', 'string','min:3', 'max:255'],
            'symbol' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'status'=>['required', 'string', 'max:255'],
        ]);

        $unit = Unit::findOrFail($id);
        $unit->update([
            'name' => $request->name,
            'symbol' => $request->symbol,
            'type' => $request->type,
            'active' => $request->status
        ]);

        return redirect()->route('unit.unitlist')->with('success','Unit updated successfully.');
    }
public function distroy(int $id)
{
    
    Unit::findOrFail($id)->delete();

    return response()->json(['success' => 'Unit deleted successfully.']);
}
}
