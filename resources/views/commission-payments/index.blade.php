@extends('layouts.app')

@section('title', 'Commission Payments')
@section('page-title', 'Commission Payments')

@section('breadcrumb')
    <li class="breadcrumb-item active">Commission Payments</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="card-title">All Payments</h6>
            <p class="card-subtitle">Commission settlement records</p>
        </div>
        <a href="{{ route('commission-payments.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> New Payment
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="paymentTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Payment No</th>
                        <th>Order Booker</th>
                        <th>Amount</th>
                        <th>Payment Date</th>
                        <th>Method</th>
                        <th>Created By</th>
                        <th width="100">Action</th>
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
    $('#paymentTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('commission-payments.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'payment_no', name: 'payment_no' },
            { data: 'booker_name', name: 'orderBooker.first_name' },
            { data: 'amount', name: 'amount' },
            { data: 'payment_date', name: 'payment_date' },
            { data: 'payment_method_badge', name: 'payment_method' },
            { data: 'created_by_name', name: 'createdBy.name' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        pageLength: 10,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        order: [[1, 'desc']],
        dom: '<"d-flex justify-content-between align-items-center mb-3"lBf>rtip',
        buttons: [
            { extend: 'csv', text: '<i class="bi bi-filetype-csv me-1"></i> CSV', className: 'btn buttons-csv', title: 'Commission Payments', exportOptions: { columns: [0,1,2,3,4,5,6] } },
            { extend: 'excel', text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel', className: 'btn buttons-excel', title: 'Commission Payments', exportOptions: { columns: [0,1,2,3,4,5,6] } },
            { extend: 'print', text: '<i class="bi bi-printer me-1"></i> Print', className: 'btn buttons-print', title: 'Commission Payments', exportOptions: { columns: [0,1,2,3,4,5,6] } },
        ],
        language: {
            processing: '<span class="spinner-border spinner-border-sm text-primary me-2"></span> Loading...',
            search: '', searchPlaceholder: 'Search ...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            paginate: { previous: '<i class="bi bi-chevron-left"></i>', next: '<i class="bi bi-chevron-right"></i>' },
            zeroRecords: '<div class="text-center py-3 text-muted"><i class="bi bi-inbox d-block fs-4 mb-1"></i>No payments found</div>'
        },
    });
});
</script>
@endpush
