@extends('layouts.app')

@section('title', 'Purchase Report Print')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4>Purchase Report</h4>
            <div>{{ $start->format('d M Y') }} — {{ $end->format('d M Y') }}</div>
            @if(!empty($filters))
                <div class="mt-2 small text-muted">
                    @foreach($filters as $label => $value)
                        <span class="me-3"><strong>{{ $label }}:</strong> {{ $value }}</span>
                    @endforeach
                </div>
            @endif
        </div>
        <div>
            <button class="btn btn-outline-secondary" onclick="window.print()">Print</button>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><strong>Total Purchases:</strong> {{ $totals['count'] }}</div>
        <div class="col-md-3"><strong>Grand Total:</strong> {{ number_format($totals['grand_total'], 2) }}</div>
        <div class="col-md-3"><strong>Total Paid:</strong> {{ number_format($totals['paid_amount'], 2) }}</div>
        <div class="col-md-3"><strong>Total Due:</strong> {{ number_format($totals['due_amount'], 2) }}</div>
    </div>

    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>S.no</th>
                <th>Ref No</th>
                <th>Order Date</th>
                <th>Supplier</th>
                <th>Category</th>
                <th>Status</th>
                <th>Payment Status</th>
                <th class="text-end">Grand Total</th>
                <th class="text-end">Paid</th>
                <th class="text-end">Due</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchases as $index => $purchase)
                @php
                    $paymentStatus = 'N/A';
                    if ($purchase->grand_total > 0) {
                        if ($purchase->due_amount <= 0) {
                            $paymentStatus = 'Paid';
                        } elseif ($purchase->paid_amount <= 0) {
                            $paymentStatus = 'Unpaid';
                        } else {
                            $paymentStatus = 'Partial';
                        }
                    }

                    $categoryNames = $purchase->items
                        ->pluck('product.category.name')
                        ->filter()
                        ->unique()
                        ->implode(', ');
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $purchase->ref_no }}</td>
                    <td>{{ \Carbon\Carbon::parse($purchase->order_date)->format('d M Y') }}</td>
                    <td>{{ $purchase->supplier ? trim($purchase->supplier->first_name . ' ' . $purchase->supplier->last_name) : '-' }}</td>
                    <td>{{ $categoryNames ?: '-' }}</td>
                    <td>{{ ucfirst($purchase->status) }}</td>
                    <td>{{ $paymentStatus }}</td>
                    <td class="text-end">{{ number_format($purchase->grand_total, 2) }}</td>
                    <td class="text-end">{{ number_format($purchase->paid_amount, 2) }}</td>
                    <td class="text-end">{{ number_format($purchase->due_amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center text-muted">No purchases found for selected filters.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="7" class="text-end">Totals</th>
                <th class="text-end">{{ number_format($totals['grand_total'], 2) }}</th>
                <th class="text-end">{{ number_format($totals['paid_amount'], 2) }}</th>
                <th class="text-end">{{ number_format($totals['due_amount'], 2) }}</th>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
