@extends('layouts.app')

@section('title', 'Credit Customers')
@section('page-title', 'Credit Customers')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}" class="text-decoration-none text-muted">Customers</a></li>
    <li class="breadcrumb-item active">Credit Customers</li>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Credit Customers</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['total'] }}</h5>
                    </div>
                    <i class="bi bi-people fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Outstanding</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['total_balance'], 0) }}</h5>
                    </div>
                    <i class="bi bi-credit-card fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Avg Balance</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['avg_balance'], 0) }}</h5>
                    </div>
                    <i class="bi bi-bar-chart fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Highest Balance</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['max_balance'], 0) }}</h5>
                    </div>
                    <i class="bi bi-trophy fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="card-title">Customers with Outstanding Balance</h6>
        <p class="card-subtitle">List of customers who owe money</p>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="creditTable">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th class="text-end">Balance</th>
                        <th width="120" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

@push('scripts')
<script>
$(function () {
    $('#creditTable').DataTable({
        processing : true,
        serverSide : true,
        ajax: {
            url  : '{{ route('customers.credit') }}',
            type : 'GET',
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'first_name', name: 'first_name', render: function(data, type, row){ return row.first_name + ' ' + row.last_name; } },
            { data: 'email', name: 'email' },
            { data: 'balance', name: 'balance', className: 'text-end', render: $.fn.dataTable.render.number(',', '.', 2, 'Rs. ') },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        pageLength : 10,
        lengthMenu : [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        order : [[3, 'desc']],
        dom: '<"d-flex justify-content-between align-items-center mb-3"lBf>rtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="bi bi-clipboard me-1"></i> Copy',
                className: 'btn buttons-copy',
                exportOptions: { columns: [0,1,2,3] }
            },
            {
                extend: 'csv',
                text: '<i class="bi bi-filetype-csv me-1"></i> CSV',
                className: 'btn buttons-csv',
                title: 'Credit Customers',
                exportOptions: { columns: [0,1,2,3] }
            },
            {
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
                className: 'btn buttons-excel',
                title: 'Credit Customers',
                exportOptions: { columns: [0,1,2,3] }
            },
            {
                extend: 'pdf',
                text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF',
                className: 'btn buttons-pdf',
                title: 'Credit Customers',
                orientation: 'portrait',
                pageSize: 'A4',
                exportOptions: { columns: [0,1,2,3] }
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer me-1"></i> Print',
                className: 'btn buttons-print',
                title: 'Credit Customers',
                exportOptions: { columns: [0,1,2,3] }
            },
        ],
        language: {
            processing: '<span class="spinner-border spinner-border-sm text-primary me-2"></span> Loading...',
            search: '',
            searchPlaceholder: 'Search ...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            paginate: {
                previous: '<i class="bi bi-chevron-left"></i>',
                next: '<i class="bi bi-chevron-right"></i>'
            },
            zeroRecords: '<div class="text-center py-3 text-muted"><i class="bi bi-inbox d-block fs-4 mb-1"></i>No records found</div>'
        }
    });
});
</script>
@endpush
</div>
@endsection
