@extends('layouts.app')

@section('title', 'Near to Expiry')
@section('page-title', 'Near to Expiry Products')

@section('breadcrumb')
    <li class="breadcrumb-item active">Near to Expiry</li>
@endsection

@section('content')

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Near-to-Expiry Batches</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['total_batches'] }}</h5>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Quantity</h6>
                        <h5 class="mb-0 fw-bold">{{ number_format($stats['total_quantity']) }}</h5>
                    </div>
                    <i class="bi bi-boxes fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Critical (&le;7 days)</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['critical'] }}</h5>
                    </div>
                    <i class="bi bi-clock fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Expired</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['expired'] }}</h5>
                    </div>
                    <i class="bi bi-calendar-x fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

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
                        <th>Purchase Invoice</th>
                        <th>Qty Left</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
                        <th>Action</th>
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
    const table = $('#nearExpiryTable').DataTable({
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
            { data: 'purchase_ref', name: 'purchase_ref' },
            { data: 'quantity', name: 'quantity', className: 'text-center' },
            { data: 'expiry_date', name: 'expiry_date' },
            { data: 'expiry_status', name: 'expiry_status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        pageLength: 10,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        order: [[5, 'asc']],
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

    $(document).on('click', '.waste-batch', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Waste this batch?',
            text: 'This will set the batch quantity to 0 and reduce product stock.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, waste it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("near-to-expiry.waste") }}',
                    type: 'POST',
                    data: {
                        id: id,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        Swal.fire({ icon: 'success', title: 'Success', text: res.message, timer: 2000, showConfirmButton: false });
                        table.draw(false);
                    },
                    error: function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
                    }
                });
            }
        });
    });

});
</script>
@endpush
