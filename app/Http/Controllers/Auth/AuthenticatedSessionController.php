<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BackupController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\CashAdjustment;
use App\Models\CustomerPayment;
use App\Models\DailyClosing;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\SalePayment;
use App\Models\SupplierPayment;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $this->ensurePreviousDayClosed();

        $request->session()->regenerate();

        BackupController::autoBackup();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function ensurePreviousDayClosed(): void
    {
        $yesterday = now()->subDay()->startOfDay();
        $dateString = $yesterday->toDateString();

        if (DailyClosing::where('closing_date', $dateString)->exists()) {
            return;
        }

        $lastDayClosing = $this->getLastDayClosing($yesterday);
        $receive = $this->getDayReceive($yesterday);
        $payment = $this->getDayPayment($yesterday);
        $balance = round($lastDayClosing + $receive - $payment, 2);

        DailyClosing::create([
            'closing_date' => $dateString,
            'last_day_closing' => round($lastDayClosing, 2),
            'receive' => round($receive, 2),
            'payment' => round($payment, 2),
            'balance' => $balance,
            'closed_by' => auth()->id(),
        ]);
    }

    private function getLastDayClosing(Carbon $date): float
    {
        $previousClosing = DailyClosing::where('closing_date', '<', $date->toDateString())
            ->orderByDesc('closing_date')
            ->first();

        if ($previousClosing) {
            return (float) $previousClosing->balance;
        }

        $before = $date->copy()->subDay()->toDateString();

        $inflow = (float) SalePayment::where('payment_type', 'cash')
            ->where('payment_date', '<=', $before)->sum('amount');

        $inflow += (float) CustomerPayment::where('payment_method', 'cash')
            ->where('payment_type', 'credit')
            ->where('payment_date', '<=', $before)->sum('amount');

        $inflow += (float) CashAdjustment::where('adjustment_type', 'increase')
            ->where('adjustment_date', '<=', $before)->sum('amount');

        $outflow = (float) Purchase::where('payment_method', 'cash')
            ->where('order_date', '<=', $before)->sum('paid_amount');

        $outflow += (float) SupplierPayment::where('payment_method', 'cash')
            ->where('payment_type', 'debit')
            ->where('payment_date', '<=', $before)->sum('amount');

        $outflow += (float) CashAdjustment::where('adjustment_type', 'decrease')
            ->where('adjustment_date', '<=', $before)->sum('amount');

        $outflow += (float) Expense::where('payment_method', 'cash')
            ->where('expense_date', '<=', $before)->sum('amount');

        return $inflow - $outflow;
    }

    private function getDayReceive(Carbon $date): float
    {
        $dateString = $date->toDateString();

        $saleCash = (float) SalePayment::where('payment_type', 'cash')
            ->where('payment_date', $dateString)->sum('amount');

        $customerCash = (float) CustomerPayment::where('payment_method', 'cash')
            ->where('payment_type', 'credit')
            ->where('payment_date', $dateString)->sum('amount');

        $cashIncrease = (float) CashAdjustment::where('adjustment_type', 'increase')
            ->where('adjustment_date', $dateString)->sum('amount');

        return $saleCash + $customerCash + $cashIncrease;
    }

    private function getDayPayment(Carbon $date): float
    {
        $dateString = $date->toDateString();

        $purchaseCash = (float) Purchase::where('payment_method', 'cash')
            ->where('paid_amount', '>', 0)
            ->where('order_date', $dateString)->sum('paid_amount');

        $supplierCash = (float) SupplierPayment::where('payment_method', 'cash')
            ->where('payment_type', 'debit')
            ->where('payment_date', $dateString)->sum('amount');

        $cashDecrease = (float) CashAdjustment::where('adjustment_type', 'decrease')
            ->where('adjustment_date', $dateString)->sum('amount');

        $cashExpenses = (float) Expense::where('payment_method', 'cash')
            ->where('expense_date', $dateString)->sum('amount');

        return $purchaseCash + $supplierCash + $cashDecrease + $cashExpenses;
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
