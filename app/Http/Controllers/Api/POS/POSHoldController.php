<?php

namespace App\Http\Controllers\Api\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\HoldCartRequest;
use App\Models\POSHold;
use App\Models\POSSession;
use Illuminate\Http\JsonResponse;

class POSHoldController extends Controller
{
    public function index(POSSession $session): JsonResponse
    {
        return response()->json([
            'holds' => $session->holds()->with('user')->latest()->get(),
        ]);
    }

    public function store(HoldCartRequest $request): JsonResponse
    {
        $hold = POSHold::create([
            'pos_session_id' => $request->session_id,
            'user_id'        => $request->user()->id,
            'cart_data'      => json_decode($request->cart_data, true),
            'note'           => $request->note,
        ]);

        return response()->json(['hold' => $hold, 'message' => 'Cart held.'], 201);
    }

    public function resume(POSHold $hold): JsonResponse
    {
        return response()->json(['hold' => $hold->load('user')]);
    }

    public function destroy(POSHold $hold): JsonResponse
    {
        $hold->delete();
        return response()->json(['message' => 'Hold discarded.']);
    }
}
