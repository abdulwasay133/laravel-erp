<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(Customer::query())
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return $row->first_name . ' ' . $row->last_name;
                })
                ->addColumn('status', function ($row) {
                    return ucfirst($row->status);
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="edit/' . $row->id . '" class="btn btn-primary btn-sm">'
                        . '<i class="bi bi-pencil"></i></a>';
                    $btn .= ' <a href="show/' . $row->id . '" class="btn btn-info btn-sm text-white">'
                        . '<i class="bi bi-eye"></i></a>';
                    $btn .= ' <button data-url="' . route('customers.destroy', $row->id) . '" '
                        . 'class="btn btn-danger btn-sm delete">'
                        . '<i class="bi bi-trash"></i></button>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('customer.index');
    }

    public function create()
    {
        return view('customer.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:customers,phone'],
            'type' => ['required', 'string', 'in:individual,business,wholesale'],
            'status' => ['required', 'string', 'in:active,inactive,blocked'],
            'company' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:255'],
        ]);

        Customer::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'type' => $request->type,
            'status' => $request->status,
            'company' => $request->company,
            'notes' => $request->notes,
            'address' => $request->address,
            'city' => $request->city,
            'province' => $request->province,
            'postal_code' => $request->postal_code,
            'opening_balance' => $request->opening_balance ?? 0,
            'currency' => $request->currency,
            'balance' => $request->opening_balance ?? 0,
        ]);

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function edit(int $id)
    {
        $customer = Customer::findOrFail($id);
        return view('customer.create', compact('customer'));
    }

    public function update(Request $request, int $id)
    {
        $customer = Customer::findOrFail($id);
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers,email,' . $customer->id],
            'phone' => ['required', 'string', 'max:20', 'unique:customers,phone,' . $customer->id],
            'type' => ['required', 'string', 'in:individual,business,wholesale'],
            'status' => ['required', 'string', 'in:active,inactive,blocked'],
            'company' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:255'],
        ]);

        $customer->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'type' => $request->type,
            'status' => $request->status,
            'company' => $request->company,
            'notes' => $request->notes,
            'address' => $request->address,
            'city' => $request->city,
            'province' => $request->province,
            'postal_code' => $request->postal_code,
            'opening_balance' => $request->opening_balance ?? 0,
            'currency' => $request->currency,
            'balance' => $request->opening_balance ?? 0,
        ]);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function show(int $id)
    {
        $customer = Customer::findOrFail($id);
        return view('customer.show', compact('customer'));
    }

    public function distroy(int $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return response()->json(['success' => 'Customer deleted successfully.']);
    }
}
