@extends('layouts.app')

@section('title', 'Purchase Orders')
@section('page-title', 'Purchase Orders')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Purchases</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['total'] }}</h5>
                    </div>
                    <i class="bi bi-cart-check fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Received</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['received'] }}</h5>
                    </div>
                    <i class="bi bi-check-circle fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Amount</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['total_amount'], 0) }}</h5>
                    </div>
                    <i class="bi bi-cash-stack fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Due</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['total_due'], 0) }}</h5>
                    </div>
                    <i class="bi bi-credit-card-2-front fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="card-title">Purchase Orders</h6>
            <p class="card-subtitle">List of purchase orders and status</p>
        </div>
        <a href="{{ route('purchase.create') }}" class="btn btn-primary">Create Purchase</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped" id="purchaseTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Supplier</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#purchaseTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.location.href,
            type: 'GET',
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'order_date', name: 'order_date' },
            { data: 'ref_no', name: 'ref_no' },
            { data: 'supplier_name', name: 'supplier_name' },
            { data: 'grand_total', name: 'grand_total', className: 'text-end' },
            { data: 'paid_amount', name: 'paid_amount', className: 'text-end' },
            { data: 'due_amount', name: 'due_amount', className: 'text-end' },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' },
        ],
        order: [[1, 'desc']],
        pageLength: 10,
        responsive: true,
    });
});

</script>
@endpush

