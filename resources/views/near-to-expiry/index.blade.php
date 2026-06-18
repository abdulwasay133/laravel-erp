@extends('layouts.app')

@section('title', 'Near to Expiry')
@section('page-title', 'Near to Expiry Products')

@section('breadcrumb')
    <li class="breadcrumb-item active">Near to Expiry</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="card-title">Near to Expiry Products</h6>
            <p class="card-subtitle">Products that are close to their expiration date</p>
        </div>
        <div>
            <a href="{{ route('near-to-expiry.print') }}" target="_blank" class="btn btn-primary">
                <i class="bi bi-printer me-1"></i> Print
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="nearExpiryTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Product Name</th>
                        <th>Batch No</th>
                        <th>Qty Left</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
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
    $('#nearExpiryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('near-to-expiry.index') }}",
            type: 'GET',
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'product_name', name: 'product_name' },
            { data: 'batch_number', name: 'batch_number' },
            { data: 'quantity', name: 'quantity', className: 'text-center' },
            { data: 'expiry_date', name: 'expiry_date' },
            { data: 'expiry_status', name: 'expiry_status', orderable: false, searchable: false },
        ],
        pageLength: 10,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        order: [[4, 'asc']],
        dom: '<"d-flex justify-content-between align-items-center mb-3"lBf>rtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="bi bi-clipboard me-1"></i> Copy',
                className: 'btn buttons-copy',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            },
            {
                extend: 'csv',
                text: '<i class="bi bi-filetype-csv me-1"></i> CSV',
                className: 'btn buttons-csv',
                title: 'Near to Expiry Products',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            },
            {
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
                className: 'btn buttons-excel',
                title: 'Near to Expiry Products',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            },
            {
                extend: 'pdf',
                text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF',
                className: 'btn buttons-pdf',
                title: 'Near to Expiry Products',
                orientation: 'portrait',
                pageSize: 'A4',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer me-1"></i> Print',
                className: 'btn buttons-print',
                title: 'Near to Expiry Products',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
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
            zeroRecords: '<div class="text-center py-3 text-muted"><i class="bi bi-inbox d-block fs-4 mb-1"></i>No near to expiry products found</div>'
        },
    });
});
</script>
@endpush
