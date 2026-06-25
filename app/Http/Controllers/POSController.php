<?php

namespace App\Http\Controllers;

use App\Models\POSTransaction;
use App\Models\POSSession;
use Illuminate\Http\Request;

class POSController extends Controller
{
    public function index()
    {
        return view('pos.index');
    }

    public function refund(POSTransaction $transaction)
    {
        $transaction->load(['items', 'payments', 'customer', 'session']);
        return view('pos.refund', compact('transaction'));
    }

    public function list(Request $request)
    {
        $query = POSTransaction::with(['items', 'payments', 'customer', 'session'])
            ->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_at', '<=', $request->date_to);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('payment_method')) {
            $query->whereHas('payments', fn ($q) => $q->where('method', $request->payment_method));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('receipt_no')) {
            $query->where('receipt_no', 'like', '%' . $request->receipt_no . '%');
        }

        $transactions = $query->paginate(20)->withQueryString();

        $sessions = POSSession::select('id')->where('status', 'open')->get();
        $customers = \App\Models\Customer::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'phone']);

        $stats = [
            'total' => POSTransaction::count(),
            'completed' => POSTransaction::where('status', 'completed')->count(),
            'todays_sales' => POSTransaction::where('status', 'completed')
                ->whereDate('transaction_at', today())
                ->sum('grand_total'),
            'total_revenue' => POSTransaction::where('status', 'completed')->sum('grand_total'),
        ];

        return view('pos.list', compact('transactions', 'sessions', 'customers', 'stats'));
    }
}
