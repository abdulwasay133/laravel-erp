<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Employee;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $commissions = Commission::with('sale', 'orderBooker');

            return DataTables::of($commissions)
                ->addIndexColumn()
                ->addColumn('booker_name', function ($row) {
                    return $row->orderBooker?->first_name . ' ' . $row->orderBooker?->last_name;
                })
                ->addColumn('invoice_no', function ($row) {
                    return $row->sale?->invoice_no ?? '-';
                })
                ->editColumn('sale_amount', function ($row) {
                    return 'Rs. ' . number_format($row->sale_amount, 2);
                })
                ->editColumn('commission_rate', function ($row) {
                    return $row->commission_rate . '%';
                })
                ->editColumn('commission_amount', function ($row) {
                    return 'Rs. ' . number_format($row->commission_amount, 2);
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('d M, Y');
                })
                ->addColumn('status_badge', function ($row) {
                    $badges = [
                        'pending' => '<span class="badge bg-warning text-dark">Pending</span>',
                        'approved' => '<span class="badge bg-info">Approved</span>',
                        'paid' => '<span class="badge bg-success">Paid</span>',
                        'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
                    ];
                    return $badges[$row->status] ?? '<span class="badge bg-secondary">' . $row->status . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('sale.show', $row->sale_id) . '" class="btn btn-sm btn-outline-info me-1" title="View Invoice"><i class="bi bi-eye"></i></a>';
                    if ($row->status === 'pending') {
                        $btn .= '<button data-url="' . route('commissions.approve', $row->id) . '" class="btn btn-sm btn-success approve-commission me-1" title="Approve"><i class="bi bi-check-lg"></i></button>';
                        $btn .= '<button data-url="' . route('commissions.cancel', $row->id) . '" class="btn btn-sm btn-danger cancel-commission" title="Cancel"><i class="bi bi-x-lg"></i></button>';
                    }
                    if ($row->status === 'approved') {
                        $btn .= '<button data-url="' . route('commissions.approve', $row->id) . '" class="btn btn-sm btn-success approve-commission me-1" title="Approve" disabled><i class="bi bi-check-lg"></i></button>';
                    }
                    return $btn;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        $stats = [
            'total_pending' => Commission::where('status', 'pending')->sum('commission_amount'),
            'total_approved' => Commission::where('status', 'approved')->sum('commission_amount'),
            'total_paid' => Commission::where('status', 'paid')->sum('commission_amount'),
            'pending_count' => Commission::where('status', 'pending')->count(),
        ];

        $orderBookers = Employee::where('is_order_booker', true)->orderBy('first_name')->get();

        return view('commissions.index', compact('stats', 'orderBookers'));
    }

    public function approve($id)
    {
        try {
            $commission = Commission::findOrFail($id);
            app(CommissionService::class)->approveCommission($commission);
            return response()->json(['success' => 'Commission approved successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function cancel($id)
    {
        try {
            $commission = Commission::findOrFail($id);
            app(CommissionService::class)->cancelCommission($commission);
            return response()->json(['success' => 'Commission cancelled successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
