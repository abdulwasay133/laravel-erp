<?php

namespace App\Http\Controllers\Api\POS;

use App\Http\Controllers\Controller;
use App\Models\POSSession;
use App\Services\POS\POSSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class POSSessionController extends Controller
{
    public function __construct(protected POSSessionService $sessionService) {}

    public function current(Request $request): JsonResponse
    {
        $session = $this->sessionService->getActiveSession($request->user());
        if (!$session) {
            return response()->json(['session' => null, 'message' => 'No active session.']);
        }
        return response()->json([
            'session' => $session->load(['transactions' => fn ($q) => $q->latest()->take(50)]),
        ]);
    }

    public function open(Request $request): JsonResponse
    {
        $data = $request->validate([
            'opening_balance' => 'required|numeric|min:0',
            'notes'           => 'nullable|string|max:500',
        ]);

        try {
            $session = $this->sessionService->open(
                $request->user(),
                $data['opening_balance'],
                $data['notes'] ?? null
            );
            return response()->json(['session' => $session, 'message' => 'Session opened.'], 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function close(Request $request, POSSession $session): JsonResponse
    {
        if ($session->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $data = $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'notes'           => 'nullable|string|max:500',
        ]);

        try {
            $session = $this->sessionService->close(
                $session,
                $data['closing_balance'],
                $data['notes'] ?? null
            );
            return response()->json(['session' => $session, 'message' => 'Session closed.']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(POSSession $session): JsonResponse
    {
        return response()->json([
            'session' => $session->load(['transactions.items', 'transactions.payments', 'payments']),
        ]);
    }
}
