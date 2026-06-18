<?php

namespace App\Http\Controllers\Api\POS;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class POSCustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $customers = Customer::when($request->q, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('first_name')
            ->take(20)
            ->get(['id', 'first_name', 'last_name', 'phone', 'email', 'balance']);

        return response()->json(['customers' => $customers]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'email'      => 'nullable|email|max:255',
        ]);

        $customer = Customer::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'] ?? '',
            'phone'      => $data['phone'] ?? null,
            'email'      => $data['email'] ?? null,
            'type'       => 'individual',
            'status'     => 'active',
            'balance'    => 0,
        ]);

        return response()->json(['customer' => $customer], 201);
    }
}
