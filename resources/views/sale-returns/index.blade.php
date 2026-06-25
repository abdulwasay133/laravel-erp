@extends('layouts.app')

@section('title', 'Sale Returns')
@section('page-title', 'Sale Returns')

@section('breadcrumb')
    <li class="breadcrumb-item active">Sale Returns</li>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Returns</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['total'] }}</h5>
                    </div>
                    <i class="bi bi-arrow-return-left fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Completed</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['completed'] }}</h5>
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
                        <h6 class="card-title mb-1 text-white-50">Total Return Amount</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['total_return_amount'], 0) }}</h5>
                    </div>
                    <i class="bi bi-cash fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Refund</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['total_refund'], 0) }}</h5>
                    </div>
                    <i class="bi bi-currency-dollar fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header justify-content-between">
        <div>
            <h6 class="card-title">Sale Returns</h6>
            <p class="card-subtitle">Customer return records</p>
        </div>
        <a href="{{ route('sale-returns.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> New Sale Return
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="saleReturnTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Return No</th>
                        <th>Invoice No</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Return Amount</th>
                        <th>Refund</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
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
    $('#saleReturnTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('sale-returns.index') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'return_no' },
            { data: 'invoice_no' },
            { data: 'customer_name' },
            { data: 'return_date' },
            { data: 'total_amount' },
            { data: 'refund_amount' },
            { data: 'status_badge', orderable: false },
            { data: 'action', orderable: false, searchable: false, className: 'text-center' },
        ],
        order: [[4, 'desc']],
    });
});
</script>
@endpush
