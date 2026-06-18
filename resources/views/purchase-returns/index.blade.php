@extends('layouts.app')

@section('title', 'Purchase Returns')
@section('page-title', 'Purchase Returns')

@section('breadcrumb')
    <li class="breadcrumb-item active">Purchase Returns</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header justify-content-between">
        <div>
            <h6 class="card-title">Purchase Returns</h6>
            <p class="card-subtitle">Supplier return records</p>
        </div>
        <a href="{{ route('purchase-returns.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> New Purchase Return
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="purchaseReturnTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Return No</th>
                        <th>Ref No</th>
                        <th>Supplier</th>
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
    $('#purchaseReturnTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('purchase-returns.index') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'return_no' },
            { data: 'ref_no' },
            { data: 'supplier_name' },
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
