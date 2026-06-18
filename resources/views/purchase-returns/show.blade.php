@extends('layouts.app')

@section('title', 'Purchase Return - ' . $purchaseReturn->return_no)
@section('page-title', 'Purchase Return Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('purchase-returns.index') }}" class="text-decoration-none text-muted">Purchase Returns</a></li>
    <li class="breadcrumb-item active">{{ $purchaseReturn->return_no }}</li>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $purchaseReturn->return_no }}</h5>
        <a href="{{ route('purchase.show', $purchaseReturn->purchase_id) }}" class="btn btn-outline-primary btn-sm">View Original Purchase</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><h6 class="card-title mb-0">Return Information</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><small class="text-muted">Return No</small><div class="fw-semibold">{{ $purchaseReturn->return_no }}</div></div>
                    <div class="col-md-4"><small class="text-muted">Return Date</small><div class="fw-semibold">{{ $purchaseReturn->return_date->format('d M Y') }}</div></div>
                    <div class="col-md-4"><small class="text-muted">Ref No</small><div class="fw-semibold">{{ $purchaseReturn->purchase->ref_no }}</div></div>
                    <div class="col-md-4"><small class="text-muted">Supplier</small><div class="fw-semibold">{{ $purchaseReturn->supplier->first_name }} {{ $purchaseReturn->supplier->last_name }}</div></div>
                    <div class="col-md-4"><small class="text-muted">Return Amount</small><div class="fw-semibold text-danger">{{ number_format($purchaseReturn->total_amount, 2) }}</div></div>
                    <div class="col-md-4"><small class="text-muted">Refund Received</small><div class="fw-semibold">{{ number_format($purchaseReturn->refund_amount, 2) }}</div></div>
                    <div class="col-md-4"><small class="text-muted">Refund Method</small><div class="fw-semibold">{{ ucwords(str_replace('_', ' ', $purchaseReturn->refund_method)) }}</div></div>
                    <div class="col-md-8"><small class="text-muted">Reason</small><div class="fw-semibold">{{ $purchaseReturn->reason }}</div></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0">Returned Items</h6></div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Batch</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Unit Cost</th>
                            <th class="text-end">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseReturn->items as $item)
                            <tr>
                                <td>{{ $item->product->name ?? '-' }}</td>
                                <td>{{ $item->batch_number ?: '-' }}</td>
                                <td class="text-end">{{ $item->quantity }}</td>
                                <td class="text-end">{{ number_format($item->unit_cost, 2) }}</td>
                                <td class="text-end">{{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Total</th>
                            <th class="text-end">{{ number_format($purchaseReturn->total_amount, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0">Effects Applied</h6></div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Product stock reduced</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Supplier ledger updated</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Purchase totals updated</li>
                    @if($purchaseReturn->refund_amount > 0)
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Refund received ({{ ucwords(str_replace('_', ' ', $purchaseReturn->refund_method)) }})</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
