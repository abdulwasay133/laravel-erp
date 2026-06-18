<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NearToExpiryController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $nearExpiry = DB::table('product_batches')
                ->join('products', 'product_batches.product_id', '=', 'products.id')
                ->where('product_batches.quantity', '>', 0)
                ->where('products.expiry_alert_days', '>', 0)
                ->whereRaw('DATEDIFF(product_batches.expiry_date, CURDATE()) BETWEEN 0 AND products.expiry_alert_days')
                ->select(
                    'product_batches.id',
                    'products.name as product_name',
                    'product_batches.batch_number',
                    'product_batches.quantity',
                    'product_batches.expiry_date',
                    'products.expiry_alert_days'
                )
                ->orderBy('product_batches.expiry_date')
                ->get();

            return \Yajra\DataTables\Facades\DataTables::of($nearExpiry)
                ->addIndexColumn()
                ->addColumn('expiry_status', function ($row) {
                    $daysLeft = now()->diffInDays(\Carbon\Carbon::parse($row->expiry_date), false);
                    if ($daysLeft < 0) {
                        return '<span class="badge bg-danger">Expired</span>';
                    } elseif ($daysLeft <= 7) {
                        return '<span class="badge bg-warning text-dark">' . $daysLeft . ' days left</span>';
                    }
                    return '<span class="badge bg-info">' . $daysLeft . ' days left</span>';
                })
                ->rawColumns(['expiry_status'])
                ->make(true);
        }

        return view('near-to-expiry.index');
    }

    public function print()
    {
        $batches = DB::table('product_batches')
            ->join('products', 'product_batches.product_id', '=', 'products.id')
            ->where('product_batches.quantity', '>', 0)
            ->where('products.expiry_alert_days', '>', 0)
            ->whereRaw('DATEDIFF(product_batches.expiry_date, CURDATE()) BETWEEN 0 AND products.expiry_alert_days')
            ->select(
                'product_batches.id',
                'products.name as product_name',
                'product_batches.batch_number',
                'product_batches.quantity',
                'product_batches.expiry_date'
            )
            ->orderBy('product_batches.expiry_date')
            ->get();

        return view('near-to-expiry.print', compact('batches'));
    }
}
