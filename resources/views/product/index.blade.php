@extends('layouts.app')

@section('title', 'Products')
@section('page-title', 'Products List')

@section('breadcrumb')
    <li class="breadcrumb-item active">Products</li>
@endsection


@section('content')

{{-- ── Card ─────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header justify-content-between">
        <div>
            <h6 class="card-title">All Products</h6>
            <p class="card-subtitle">Manage your products database</p>
        </div>
        <a href="{{ route('product.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Products
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="supplierTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Name</th>
                        <th>Sale Price</th>
                        <th>SKU/Barcode</th>
                        <th>Unit</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th width="120" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-3">

            <!-- Header -->
            <div class="modal-header text-white" style="background-color: #85D1DB; !important">
                <h5 class="modal-title">
                    <i class="bi bi-person-lines-fill me-2"></i> Product Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body p-4">

                <!-- Info Grid -->
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="text-muted small">Product Name</label>
                        <div class="fw-semibold" id="product_name">-</div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small">Sku/Barcode</label>
                        <div class="fw-semibold" id="product_sku">-</div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small">Sale Price</label>
                        <div class="fw-semibold" id="product_sale">-</div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small">Category</label>
                        <div class="fw-semibold" id="product_category">-</div>
                    </div>

                    <div class="col-6">
                        <label class="text-muted small">Unit</label>
                        <div class="fw-semibold" id="product_unit">-</div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small">Stock</label>
                        <div class="fw-semibold text-success" id="product_stock">-</div>
                    </div>

                    <hr/>
                    
            <h6>Suppliers</h6>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Price</th>
        </tr>
    </thead>
    <tbody id="supplierTableBody"></tbody>
</table>

<hr/>
<h6>Batches</h6>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Batch No</th>
            <th>Qty</th>
            <th>Expiry</th>
            <th>Cost</th>
        </tr>
    </thead>
    <tbody id="batchTableBody"></tbody>
</table>
                    

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
            url  : "{{ route('product.index') }}",
            type : 'GET',
        },

        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name',   name: 'product.name'  },
            { data: 'price', name: 'price' },
            { data: 'sku',   name: 'sku' },
            { data: 'unit', name: 'unit' },
            { data: 'category', name: 'category' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],

        pageLength : 10,
        lengthMenu : [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        order      : [[1, 'asc']],

        // ── dom: B = Buttons, l = length, f = filter/search ──
        // ── r = processing, t = table, i = info, p = pagination ──
       dom: '<"d-flex justify-content-between align-items-center mb-3"lBf>rtip',

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

$(document).on('click', '.viewProduct', function () {

    let id = $(this).data('id');

    $.ajax({
        url: '/product/show/' + id,
        type: 'GET',

        success: function (res) {

            // ✅ PRODUCT INFO
            $('#product_name').text(res.product.name);
            $('#product_sku').text(res.product.sku);
            $('#product_sale').text(res.product.price);
            $('#product_unit').text(res.product.unit_id); // better if you send unit name
            $('#product_category').text(res.product.category);
            $('#product_unit').text(res.product.unit);

            // ✅ STOCK (calculate from batches)
            let totalStock = 0;
            res.batches.forEach(batch => {
                totalStock += parseInt(batch.quantity);
            });
            $('#product_stock').text(totalStock);

            // =========================
            // ✅ SUPPLIERS LIST
            // =========================
            let supplierHtml = '';

            res.suppliers.forEach(supplier => {
                supplierHtml += `
                    <tr>
                        <td>${supplier.name}</td>
                        <td>${supplier.cost}</td>
                    </tr>
                `;
            });

            $('#supplierTableBody').html(supplierHtml);


            // =========================
            // ✅ BATCHES LIST
            // =========================
            let batchHtml = '';

            res.batches.forEach(batch => {
                batchHtml += `
                    <tr>
                        <td>${batch.batch_number}</td>
                        <td>${batch.quantity}</td>
                        <td>${batch.expiry_date ?? '-'}</td>
                        <td>${batch.cost ?? 0}</td>
                    </tr>
                `;
            });

            $('#batchTableBody').html(batchHtml);


            // ✅ SHOW MODAL
            $('#productModal').modal('show');
        }
    });
});
</script>
@endpush
