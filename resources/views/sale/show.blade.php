@extends('layouts.app')

@section('title', 'Sale - ' . $sale->invoice_no)
@section('page-title', 'Sale Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('sale.index') }}" class="text-decoration-none text-muted">Sales</a></li>
    <li class="breadcrumb-item active">{{ $sale->invoice_no }}</li>
@endsection

@section('content')

<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $sale->invoice_no }}</h5>
            <div>
                @if($sale->status !== 'cancelled')
                    <a href="{{ route('sale.edit', $sale->id) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <button class="btn btn-danger btn-sm" onclick="deleteSale({{ $sale->id }})">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        {{-- Sale Information --}}
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Sale Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label class="text-muted small">Invoice Number</label>
                        <p class="fw-600">{{ $sale->invoice_no }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Sale Date</label>
                        <p class="fw-600">{{ $sale->sale_date->format('M d, Y') }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Status</label>
                        <p class="fw-600">
                            @if($sale->status === 'draft')
                                <span class="badge bg-secondary">Draft</span>
                            @elseif($sale->status === 'completed')
                                <span class="badge bg-success">Completed</span>
                            @elseif($sale->status === 'cancelled')
                                <span class="badge bg-danger">Cancelled</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Customer Information --}}
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Customer Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="text-muted small">Name</label>
                        <p class="fw-600">{{ $sale->customer->first_name . ' ' . $sale->customer->last_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Email</label>
                        <p class="fw-600">{{ $sale->customer->email }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Phone</label>
                        <p class="fw-600">{{ $sale->customer->phone }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Company</label>
                        <p class="fw-600">{{ $sale->customer->company ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sale Items --}}
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Items</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-center">Discount %</th>
                            <th class="text-end">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end">Rs. {{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-center">{{ number_format($item->discount_percent, 1) }}%</td>
                            <td class="text-end fw-600">Rs. {{ number_format($item->line_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Payment History --}}
        @if($sale->payments->count() > 0)
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Payment History</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Payment Type</th>
                            <th class="text-end">Amount</th>
                            <th>Payment Date</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->payments as $payment)
                        <tr>
                            <td>
                                <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</span>
                            </td>
                            <td class="text-end fw-600">Rs. {{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                            <td>{{ $payment->reference ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if($sale->notes)
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Notes</h6>
            </div>
            <div class="card-body">
                {{ $sale->notes }}
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        {{-- Sale Summary --}}
        <div class="card sticky-top">
            <div class="card-header">
                <h6 class="card-title mb-0">Sale Summary</h6>
            </div>
            <div class="card-body">
                <div class="summary-row mb-2">
                    <span>Subtotal</span>
                    <span class="fw-600">Rs. {{ number_format($sale->subtotal, 2) }}</span>
                </div>

                <div class="summary-row mb-2">
                    <span>Total Discount</span>
                    <span class="fw-600">Rs. {{ number_format($discountTotal, 2) }}</span>
                </div>

                <hr class="my-2">

                <div class="summary-row mb-3">
                    <span class="fw-600">Total Amount</span>
                    <span class="fw-700 fs-6">Rs. {{ number_format($sale->total_amount, 2) }}</span>
                </div>

                <hr class="my-2">

                <div class="summary-row mb-2">
                    <span>Paid Amount</span>
                    <span class="fw-600 text-success">Rs. {{ number_format($sale->paid_amount, 2) }}</span>
                </div>

                <div class="summary-row mb-3">
                    <span>Outstanding Balance</span>
                    <span class="fw-600 text-danger">Rs. {{ number_format($sale->balance, 2) }}</span>
                </div>

                <hr class="my-2">

                <div class="d-grid gap-2">
                    <button onclick="printInvoice()" class="btn btn-primary btn-sm">
                        <i class="bi bi-printer me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function deleteSale() {
    if (confirm('Are you sure you want to delete this sale?')) {
        $.ajax({
            url: '/sale/delete/{{ $sale->id }}',
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Sale deleted successfully',
                    timer: 2000
                });
                setTimeout(() => window.location.href = '{{ route('sale.index') }}', 1000);
            },
            error: function (response) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.responseJSON?.message ?? 'Error deleting sale'
                });
            }
        });
    }
}

function printInvoice() {
    var iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.top = '-10000px';
    iframe.style.left = '-10000px';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = 'none';
    document.body.appendChild(iframe);
    iframe.src = '{{ route('sale.print', $sale->id) }}';
    iframe.onload = function () {
        setTimeout(function () {
            iframe.contentWindow.print();
            setTimeout(function () {
                document.body.removeChild(iframe);
            }, 500);
        }, 500);
    };
}
</script>
@endpush
