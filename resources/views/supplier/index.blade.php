@extends('layouts.app')

@section('title', 'Suppliers')
@section('page-title', 'Suppliers List')

@section('breadcrumb')
    <li class="breadcrumb-item active">Suppliers</li>
@endsection


@section('content')

{{-- ── Card ─────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header justify-content-between">
        <div>
            <h6 class="card-title">All Suppliers</h6>
            <p class="card-subtitle">Manage your supplier database</p>
        </div>
        <a href="{{ route('supplier.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Supplier
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="supplierTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Balance</th>
                        <th width="120" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-3">

            <!-- Header -->
            <div class="modal-header text-white" style="background-color: #6366f1; !important">
                <h5 class="modal-title">
                    <i class="bi bi-person-lines-fill me-2"></i> Supplier Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body p-4">

                <!-- Top Profile Section -->
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center"
                         style="width: 70px; height: 70px; font-size: 24px;">
                        <span id="supplier_initials">S</span>
                    </div>

                    <div class="ms-3">
                        <h4 class="mb-0" id="supplier_name">Name</h4>
                        <small class="text-muted" id="supplier_company">Company</small>
                    </div>
                </div>

                <hr>

                <!-- Info Grid -->
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="text-muted small">Email</label>
                        <div class="fw-semibold" id="supplier_email">-</div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small">Phone</label>
                        <div class="fw-semibold" id="supplier_phone">-</div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small">Province</label>
                        <div class="fw-semibold" id="supplier_province">-</div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small">City</label>
                        <div class="fw-semibold" id="supplier_city">-</div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small">Postal Code</label>
                        <div class="fw-semibold" id="supplier_postal">-</div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small">Balance</label>
                        <div class="fw-bold text-success" id="supplier_balance">0</div>
                    </div>

                    <div class="col-12">
                        <label class="text-muted small">Address</label>
                        <div class="fw-semibold" id="supplier_address">-</div>
                    </div>

                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

</div>

@endsection

@push('scripts')
<script>
$(function () {

    const table = $('#supplierTable').DataTable({
        processing : true,
        serverSide : true,

        ajax: {
            url  : "{{ route('supplier.index') }}",
            type : 'GET',
        },

        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name',   name: 'name' , orderable: true, searchable: true },
            { data: 'email', name: 'email' },
            { data: 'phone',   name: 'phone' },
            { data: 'balance', name: 'balance' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],

        pageLength : 10,
        lengthMenu : [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        order      : [[1, 'asc']],

        // ── dom: B = Buttons, l = length, f = filter/search ──
        // ── r = processing, t = table, i = info, p = pagination ──
       dom: '<"d-flex justify-content-between align-items-center mb-3"Blf>rtip',

        buttons: [
            {
                extend    : 'copy',
                text      : '<i class="bi bi-clipboard me-1"></i> Copy',
                className : 'btn buttons-copy',
                exportOptions: { columns: [0,1,2,3,4] }   // skip Action column
            },
            {
                extend    : 'csv',
                text      : '<i class="bi bi-filetype-csv me-1"></i> CSV',
                className : 'btn buttons-csv',
                title     : 'Supplier List',
                exportOptions: { columns: [0,1,2,3,4] }
            },
            {
                extend    : 'excel',
                text      : '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
                className : 'btn buttons-excel',
                title     : 'Supplier List',
                exportOptions: { columns: [0,1,2,3,4] }
            },
            {
                extend    : 'pdf',
                text      : '<i class="bi bi-file-earmark-pdf me-1"></i> PDF',
                className : 'btn buttons-pdf',
                title     : 'Supplier List',
                orientation : 'portrait',
                pageSize    : 'A4',
                exportOptions: { columns: [0,1,2,3,4] }
            },
            {
                extend    : 'print',
                text      : '<i class="bi bi-printer me-1"></i> Print',
                className : 'btn buttons-print',
                title     : 'Supplier List',
                exportOptions: { columns: [0,1,2,3,4] }
            },
        ],

        language: {
            processing       : '<span class="spinner-border spinner-border-sm text-primary me-2"></span> Loading...',
            search           : '',
            searchPlaceholder: 'Search ...',
            lengthMenu       : 'Show _MENU_ entries',
            info             : 'Showing _START_ to _END_ of _TOTAL_ units',
            infoFiltered     : '(filtered from _MAX_ total)',
            zeroRecords      : '<div class="text-center py-3 text-muted"><i class="bi bi-inbox d-block fs-4 mb-1"></i>No units found</div>',
            paginate: {
                previous : '<i class="bi bi-chevron-left"></i>',
                next     : '<i class="bi bi-chevron-right"></i>',
            },
        },

        // Delete with AJAX so page doesn't reload

    });
    $(document).on('click', '.delete', function (e) {
    e.preventDefault();

    let url = $(this).data('url');

    Swal.fire({
        title: "Are you sure?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },

                success: function () {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Supplier has been deleted successfully.',
                        timer: 5000,
                        showConfirmButton: true
                    });

                    $('#supplierTable').DataTable().draw(false);
                }
            });

        }
    });
});

});

$(document).on('click', '.viewSupplier', function () {
    let id = $(this).data('id');

    $.ajax({
        url: '/supplier/view/' + id, // your route
        type: 'GET',
        success: function (data) {
            $('#supplier_name').text(data.first_name+ " " + data.last_name);
            $('#supplier_email').text(data.email);
            $('#supplier_phone').text(data.phone);
            $('#supplier_address').text(data.address);
            $('#supplier_province').text(data.province);
            $('#supplier_city').text(data.city);
            $('#supplier_postal').text(data.postal_code);
            $('#supplier_balance').text(data.balance);
            $('#supplier_company').text(data.company_name ?? 'No Company');

            $('#supplierModal').modal('show');
        }
    });
});
</script>
@endpush