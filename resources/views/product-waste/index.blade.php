@extends('layouts.app')

@section('title', 'Product Waste')
@section('page-title', 'Product Waste')

@section('breadcrumb')
    <li class="breadcrumb-item active">Product Waste</li>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Waste Records</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['total_wastes'] }}</h5>
                    </div>
                    <i class="bi bi bi-clipboard-data fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Wasted Quantity</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['total_quantity'] }}</h5>
                    </div>
                    <i class="bi bi-box fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Waste Value</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['total_cost'], 2) }}</h5>
                    </div>
                    <i class="bi bi-currency-dollar fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="card-title">Waste Records</h6>
            <p class="card-subtitle">All recorded product waste</p>
        </div>
        <a href="{{ route('product-waste.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> New Waste
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="wasteTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Product</th>
                        <th>Batch No</th>
                        <th>Qty</th>
                        <th>Unit Cost</th>
                        <th>Total Cost</th>
                        <th>Date</th>
                        <th>Reason</th>
                        <th>Created By</th>
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
    $('#wasteTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('product-waste.index') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'product_name' },
            { data: 'batch_number' },
            { data: 'quantity', className: 'text-center' },
            { data: 'unit_cost', className: 'text-end' },
            { data: 'total_cost', className: 'text-end' },
            { data: 'waste_date' },
            { data: 'reason' },
            { data: 'created_by_name' },
            { data: 'action', orderable: false, searchable: false, className: 'text-center' },
        ],
        order: [[6, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
    });
});
</script>
@endpush
