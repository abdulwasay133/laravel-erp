@extends('layouts.app')

@section('title', 'Sale Return - ' . $saleReturn->return_no)
@section('page-title', 'Sale Return Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('sale-returns.index') }}" class="text-decoration-none text-muted">Sale Returns</a></li>
    <li class="breadcrumb-item active">{{ $saleReturn->return_no }}</li>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $saleReturn->return_no }}</h5>
        <a href="{{ route('sale.show', $saleReturn->sale_id) }}" class="btn btn-outline-primary btn-sm">View Original Invoice</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><h6 class="card-title mb-0">Return Information</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><small class="text-muted">Return No</small><div class="fw-semibold">{{ $saleReturn->return_no }}</div></div>
                    <div class="col-md-4"><small class="text-muted">Return Date</small><div class="fw-semibold">{{ $saleReturn->return_date->format('d M Y') }}</div></div>
                    <div class="col-md-4"><small class="text-muted">Invoice No</small><div class="fw-semibold">{{ $saleReturn->sale->invoice_no }}</div></div>
                    <div class="col-md-4"><small class="text-muted">Customer</small><div class="fw-semibold">{{ $saleReturn->customer->first_name }} {{ $saleReturn->customer->last_name }}</div></div>
                    <div class="col-md-4"><small class="text-muted">Return Amount</small><div class="fw-semibold text-danger">{{ number_format($saleReturn->total_amount, 2) }}</div></div>
                    <div class="col-md-4"><small class="text-muted">Refund Amount</small><div class="fw-semibold">{{ number_format($saleReturn->refund_amount, 2) }}</div></div>
                    <div class="col-md-4"><small class="text-muted">Refund Method</small><div class="fw-semibold">{{ ucwords(str_replace('_', ' ', $saleReturn->refund_method)) }}</div></div>
                    <div class="col-md-8"><small class="text-muted">Reason</small><div class="fw-semibold">{{ $saleReturn->reason }}</div></div>
                    @if($saleReturn->notes)
                        <div class="col-12"><small class="text-muted">Notes</small><div>{{ $saleReturn->notes }}</div></div>
                    @endif
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
                            <th class="text-end">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($saleReturn->items as $item)
                            <tr>
                                <td>{{ $item->product->name ?? '-' }}</td>
                                <td class="text-end">{{ $item->quantity }}</td>
                                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">{{ number_format($item->discount_amount, 2) }}</td>
                                <td class="text-end">{{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Total</th>
                            <th class="text-end">{{ number_format($saleReturn->total_amount, 2) }}</th>
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
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Product stock restored</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Customer ledger credit note posted</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Sale totals updated</li>
                    @if($saleReturn->refund_amount > 0)
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Refund processed ({{ ucwords(str_replace('_', ' ', $saleReturn->refund_method)) }})</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
