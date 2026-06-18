@extends('layouts.app')

@section('title', 'Purchase - ' . $purchase->ref_no)
@section('page-title', 'Purchase Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('purchase.index') }}" class="text-decoration-none text-muted">Purchases</a></li>
    <li class="breadcrumb-item active">{{ $purchase->ref_no }}</li>
@endsection

@section('content')

<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $purchase->ref_no }}</h5>
            <div>
                @if($purchase->status !== 'cancelled')
                    <a href="{{ route('purchase.edit', $purchase->id) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <button class="btn btn-danger btn-sm" onclick="deletePurchase({{ $purchase->id }})">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        {{-- Purchase Information --}}
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Purchase Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label class="text-muted small">Reference No</label>
                        <p class="fw-600">{{ $purchase->ref_no }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Order Date</label>
                        <p class="fw-600">{{ $purchase->order_date->format('M d, Y') }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Status</label>
                        <p class="fw-600">
                            @if($purchase->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($purchase->status === 'received')
                                <span class="badge bg-success">Received</span>
                            @elseif($purchase->status === 'cancelled')
                                <span class="badge bg-danger">Cancelled</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Supplier Information --}}
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Supplier Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="text-muted small">Name</label>
                        <p class="fw-600">{{ $purchase->supplier->first_name . ' ' . $purchase->supplier->last_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Email</label>
                        <p class="fw-600">{{ $purchase->supplier->email ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Phone</label>
                        <p class="fw-600">{{ $purchase->supplier->phone ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Company</label>
                        <p class="fw-600">{{ $purchase->supplier->company_name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Purchase Items --}}
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
                            <th class="text-end">Unit Cost</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-center">Batch</th>
                            <th class="text-center">Expiry</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end">Rs. {{ number_format($item->unit_cost, 2) }}</td>
                            <td class="text-end fw-600">Rs. {{ number_format($item->subtotal, 2) }}</td>
                            <td class="text-center">{{ $item->batch_number ?? '-' }}</td>
                            <td class="text-center">{{ optional($item->expiry_date)->format('d M, Y') ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Payment Info --}}
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Payment Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="text-muted small">Payment Method</label>
                        <p class="fw-600">{{ ucfirst($purchase->payment_method ?? 'N/A') }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Bank Account</label>
                        <p class="fw-600">
                            @if($purchase->bankAccount)
                                {{ $purchase->bankAccount->bank_name }} - {{ $purchase->bankAccount->account_number }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if($purchase->notes)
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Notes</h6>
            </div>
            <div class="card-body">
                {{ $purchase->notes }}
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        {{-- Purchase Summary --}}
        <div class="card sticky-top">
            <div class="card-header">
                <h6 class="card-title mb-0">Purchase Summary</h6>
            </div>
            <div class="card-body">
                <div class="summary-row mb-2">
                    <span>Subtotal</span>
                    <span class="fw-600">Rs. {{ number_format($purchase->subtotal, 2) }}</span>
                </div>

                <div class="summary-row mb-2">
                    <span>Discount</span>
                    <span class="fw-600">- Rs. {{ number_format($purchase->discount, 2) }}</span>
                </div>

                <div class="summary-row mb-2">
                    <span>Tax</span>
                    <span class="fw-600">Rs. {{ number_format($purchase->tax_amount, 2) }}</span>
                </div>

                <hr class="my-2">

                <div class="summary-row mb-3">
                    <span class="fw-600">Grand Total</span>
                    <span class="fw-700 fs-6">Rs. {{ number_format($purchase->grand_total, 2) }}</span>
                </div>

                <hr class="my-2">

                <div class="summary-row mb-2">
                    <span>Paid Amount</span>
                    <span class="fw-600 text-success">Rs. {{ number_format($purchase->paid_amount, 2) }}</span>
                </div>

                <div class="summary-row mb-3">
                    <span>Due Amount</span>
                    <span class="fw-600 text-danger">Rs. {{ number_format($purchase->due_amount, 2) }}</span>
                </div>

                <hr class="my-2">

                <div class="d-grid gap-2">
                    <button onclick="printPurchase()" class="btn btn-primary btn-sm">
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
function deletePurchase() {
    if (confirm('Are you sure you want to delete this purchase?')) {
        $.ajax({
            url: '/purchase/delete/{{ $purchase->id }}',
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Purchase deleted successfully',
                    timer: 2000
                });
                setTimeout(() => window.location.href = '{{ route('purchase.index') }}', 1000);
            },
            error: function (response) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.responseJSON?.message ?? 'Error deleting purchase'
                });
            }
        });
    }
}

function printPurchase() {
    var iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.top = '-10000px';
    iframe.style.left = '-10000px';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = 'none';
    document.body.appendChild(iframe);
    iframe.src = '{{ route('purchase.print', $purchase->id) }}';
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
