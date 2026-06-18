<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(User::query())
                ->addIndexColumn()
                ->addColumn('role_badge', function ($row) {
                    $class = match ($row->role) {
                        'admin' => 'bg-danger',
                        'manager' => 'bg-warning text-dark',
                        default => 'bg-secondary',
                    };
                    return '<span class="badge ' . $class . '">' . ucfirst($row->role) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    if ($row->id === auth()->id()) {
                        return '<span class="text-muted fst-italic">Current User</span>';
                    }
                    $btn = '<a href="' . route('users.edit', $row->id) . '" class="btn btn-primary btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>';
                    $btn .= ' <button data-url="' . route('users.destroy', $row->id) . '"
                                   class="btn btn-danger btn-sm delete">
                                <i class="bi bi-trash"></i>
                            </button>';
                    return $btn;
                })
                ->rawColumns(['role_badge', 'action'])
                ->make(true);
        }
        return view('users.index');
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|lowercase|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|in:admin,manager,staff',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users.create', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'lowercase', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => 'required|in:admin,manager,staff',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        if ($id == auth()->id()) {
            return response()->json(['error' => 'You cannot delete your own account.'], 403);
        }
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['success' => 'User deleted successfully.']);
    }
}
