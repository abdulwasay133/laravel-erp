<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $employees = Employee::query();

            return DataTables::of($employees)
                ->addIndexColumn()
                ->addColumn('full_name', function ($row) {
                    return $row->first_name . ' ' . $row->last_name;
                })
                ->addColumn('status', function ($row) {
                    return $row->status
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('employees.edit', $row->id) . '" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil"></i>
                    </a>';
                    $btn .= ' <button data-url="' . route('employees.destroy', $row->id) . '" class="btn btn-danger btn-sm delete">
                        <i class="bi bi-trash"></i>
                    </button>';
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('employees.index');
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_code' => 'required|string|max:50|unique:employees,employee_code',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'salary_amount' => 'required|numeric|min:0',
            'joining_date' => 'required|date',
            'address' => 'nullable|string',
            'status' => 'nullable',
            'is_order_booker' => 'nullable',
            'commission_type' => 'nullable|string|max:50',
            'commission_rate' => 'nullable|numeric|min:0|max:999999.99',
            'territory' => 'nullable|string|max:255',
        ]);

        Employee::create([
            'employee_code' => $request->employee_code,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'department' => $request->department,
            'designation' => $request->designation,
            'salary_amount' => $request->salary_amount,
            'joining_date' => $request->joining_date,
            'address' => $request->address,
            'status' => $request->status ?? true,
            'is_order_booker' => $request->boolean('is_order_booker'),
            'commission_type' => $request->commission_type ?? 'fixed_percent',
            'commission_rate' => $request->commission_rate ?? 0,
            'territory' => $request->territory,
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'employee_code' => 'required|string|max:50|unique:employees,employee_code,' . $employee->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'salary_amount' => 'required|numeric|min:0',
            'joining_date' => 'required|date',
            'address' => 'nullable|string',
            'status' => 'nullable',
            'is_order_booker' => 'nullable',
            'commission_type' => 'nullable|string|max:50',
            'commission_rate' => 'nullable|numeric|min:0|max:999999.99',
            'territory' => 'nullable|string|max:255',
        ]);

        $employee->update([
            'employee_code' => $request->employee_code,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'department' => $request->department,
            'designation' => $request->designation,
            'salary_amount' => $request->salary_amount,
            'joining_date' => $request->joining_date,
            'address' => $request->address,
            'status' => $request->status ?? true,
            'is_order_booker' => $request->boolean('is_order_booker'),
            'commission_type' => $request->commission_type ?? 'fixed_percent',
            'commission_rate' => $request->commission_rate ?? 0,
            'territory' => $request->territory,
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        DB::beginTransaction();
        try {
            $employee->salaryPayments()->delete();
            $employee->delete();
            DB::commit();
            return response()->json(['success' => 'Employee deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
