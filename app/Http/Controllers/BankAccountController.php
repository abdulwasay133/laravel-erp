<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BankAccountController extends Controller
{
    
    public function index()
    {
        if(request()->ajax()){
            if(request()->ajax()){
            return DataTables::of(BankAccount::query())
            ->addIndexColumn()
            
            ->addColumn('action', function ($row) {
                $btn = '<a href="edit/'.$row->id.'" data-id="'.$row->id.'" class="btn btn-primary btn-sm edit">
                    <i class="bi bi-pencil"></i> 
                </a>';
                $btn .= ' <button 
                data-url="'.route('bank.destroy', $row->id).'" 
                class="btn btn-danger btn-sm delete">
                <i class="bi bi-trash"></i>
                </button>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        }
        $stats = [
            'total_accounts' => BankAccount::count(),
            'total_balance' => BankAccount::sum('current_balance'),
            'total_opening' => BankAccount::sum('opening_balance'),
            'active_accounts' => BankAccount::where('current_balance', '>', 0)->count(),
        ];

        return view('bank.index', compact('stats'));
    }

    public function create()
    {
        return view('bank.create');
    }

    public function store(Request $request){
        // Validation and storage logic for creating a new bank account
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_title' => 'required|string|max:255',
            'branch_code' => 'nullable|string|max:50',
            'account_number' => 'required|string|max:50',
            'opening_balance' => 'nullable|numeric',
        ]);

        // Assuming BankAccount is a model representing the bank accounts in the database
        BankAccount::create([
            'bank_name' => $request->bank_name,
            'account_title' => $request->account_title,
            'branch_code' => $request->branch_code,
            'account_number' => $request->account_number,
            'opening_balance' => $request->opening_balance,
            'current_balance'=> $request->opening_balance
        ]);

        return redirect()->route('bank.index')->with('success', 'Bank account created successfully.');
    }

    public function edit(int $id){
        $bank = BankAccount::findOrFail($id);
        return view('bank.create',compact('bank'));
    }

    public function update(Request $request,int $id){
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_title' => 'required|string|max:255',
            'branch_code' => 'nullable|string|max:50',
            'account_number' => 'required|string|max:50',
            'opening_balance' => 'nullable|numeric',
        ]);

        $bank = BankAccount::findOrFail($id);
        $bank->update([
            'bank_name' => $request->bank_name,
            'account_title' => $request->account_title,
            'branch_code' => $request->branch_code,
            'account_number' => $request->account_number,
            'opening_balance' => $request->opening_balance,
        ]);

        return redirect()->route('bank.index')->with('success', 'Bank account updated successfully.');
    }

    public function destroy(int $id){
        BankAccount::findOrFail($id)->delete();
        return response()->json(['success' => 'Bank account deleted successfully.']);
    }
}
