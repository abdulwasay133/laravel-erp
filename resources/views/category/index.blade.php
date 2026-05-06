@extends('layouts.app')

@section('title', 'Categories')
@section('page-title', 'Categories List')

@section('breadcrumb')
    <li class="breadcrumb-item active">Categories</li>
@endsection


@section('content')

{{-- ── Card ─────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header justify-content-between">
        <div>
            <h6 class="card-title">All Categories</h6>
            <p class="card-subtitle">Manage your category database</p>
        </div>
        <a href="{{ route('category.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Category
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="categoryTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th width="120" class="text-center">Action</th>
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

    const table = $('#categoryTable').DataTable({
        processing : true,
        serverSide : true,

        ajax: {
            url  : "{{ route('category.index') }}",
            type : 'GET',
        },

        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name',   name: 'name' , orderable: true, searchable: true },
            { data: 'slug', name: 'slug' },
            { data: 'description',   name: 'description' },
            { data: 'status', name: 'status' },
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
                title     : 'Category List',
                exportOptions: { columns: [0,1,2,3,4] }
            },
            {
                extend    : 'excel',
                text      : '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
                className : 'btn buttons-excel',
                title     : 'Category List',
                exportOptions: { columns: [0,1,2,3,4] }
            },
            {
                extend    : 'pdf',
                text      : '<i class="bi bi-file-earmark-pdf me-1"></i> PDF',
                className : 'btn buttons-pdf',
                title     : 'Category List',
                orientation : 'portrait',
                pageSize    : 'A4',
                exportOptions: { columns: [0,1,2,3,4] }
            },
            {
                extend    : 'print',
                text      : '<i class="bi bi-printer me-1"></i> Print',
                className : 'btn buttons-print',
                title     : 'Category List',
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
                        text: 'Category has been deleted successfully.',
                        timer: 5000,
                        showConfirmButton: true
                    });

                    $('#categoryTable').DataTable().draw(false);
                }
            });

        }
    });
});

});
</script>
@endpush