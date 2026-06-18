<?php

namespace App\Services\POS;

use App\Models\POSSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class POSSessionService
{
    public function open(User $user, float $openingBalance = 0, ?string $notes = null): POSSession
    {
        $active = POSSession::open()->where('user_id', $user->id)->first();
        if ($active) {
            throw new \RuntimeException('You already have an open session (#' . $active->id . '). Close it first.');
        }

        return POSSession::create([
            'user_id'         => $user->id,
            'opened_at'       => now(),
            'opening_balance' => $openingBalance,
            'status'          => 'open',
            'notes'           => $notes,
        ]);
    }

    public function close(POSSession $session, float $closingBalance, ?string $notes = null): POSSession
    {
        if ($session->status !== 'open') {
            throw new \RuntimeException('Session is not open.');
        }

        $session->update([
            'status'          => 'closed',
            'closed_at'       => now(),
            'closing_balance' => $closingBalance,
            'cash_sales'      => $session->payments()->where('method', 'cash')->sum('amount'),
            'bank_sales'      => $session->payments()->where('method', 'bank')->sum('amount'),
            'refunds'         => $session->transactions()->where('status', 'refunded')->sum('grand_total'),
            'expected_balance'=> $session->opening_balance
                + $session->payments()->where('method', 'cash')->sum('amount')
                - ($session->transactions()->where('status', 'refunded')->sum('grand_total') ?? 0),
            'notes'           => $notes ? $session->notes . "\n" . $notes : $session->notes,
        ]);

        return $session->fresh();
    }

    public function reconcile(POSSession $session): POSSession
    {
        if ($session->status !== 'closed') {
            throw new \RuntimeException('Session must be closed before reconciliation.');
        }

        $session->update(['status' => 'reconciled']);
        return $session->fresh();
    }

    public function getActiveSession(User $user): ?POSSession
    {
        return POSSession::open()->where('user_id', $user->id)->first();
    }
}
