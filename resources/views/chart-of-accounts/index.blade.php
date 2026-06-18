@extends('layouts.app')

@section('title', 'Chart of Accounts')
@section('page-title', 'Chart of Accounts')

@section('breadcrumb')
    <li class="breadcrumb-item active">Chart of Accounts</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header justify-content-between">
        <div>
            <h6 class="card-title">Chart of Accounts</h6>
            <p class="card-subtitle">Manage your account structure</p>
        </div>
        <a href="{{ route('chart-of-accounts.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Account
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="accountsTable" class="table table-bordered align-middle w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Head Name</th>
                        <th class="text-end">Opening Balance</th>
                        <th class="text-end">Balance</th>
                        <th width="150" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<style>
.tree-line {
    position: relative;
}
.tree-line::before {
    content: '';
    position: absolute;
    left: -15px;
    top: 0;
    bottom: 0;
    width: 1px;
    background-color: #dee2e6;
}
.tree-line::after {
    content: '';
    position: absolute;
    left: -15px;
    top: 50%;
    width: 15px;
    height: 1px;
    background-color: #dee2e6;
}
</style>
<script>
$(function () {
    const table = $('#accountsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('chart-of-accounts.index') }}",
            type: 'GET',
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'opening_balance', name: 'opening_balance', className: 'text-end' },
            { data: 'balance', name: 'current_balance', className: 'text-end' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[1, 'asc']],
        dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rtip',
        language: {
            processing: '<span class="spinner-border spinner-border-sm text-primary me-2"></span> Loading...',
            search: '',
            searchPlaceholder: 'Search accounts ...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ accounts',
            paginate: {
                previous: '<i class="bi bi-chevron-left"></i>',
                next: '<i class="bi bi-chevron-right"></i>'
            },
            zeroRecords: '<div class="text-center py-3 text-muted"><i class="bi bi-inbox d-block fs-4 mb-1"></i>No accounts found</div>'
        }
    });

    // Delete Account
    $(document).on('click', '.delete-account', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route('chart-of-accounts.destroy', ':id') }}'.replace(':id', id),
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        table.draw(false);
                    },
                    error: function (response) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.responseJSON?.message || 'Error deleting account'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush
