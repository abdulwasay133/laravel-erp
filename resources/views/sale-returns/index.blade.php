@extends('layouts.app')

@section('title', 'Sale Returns')
@section('page-title', 'Sale Returns')

@section('breadcrumb')
    <li class="breadcrumb-item active">Sale Returns</li>
@endsection

@section('content')
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
