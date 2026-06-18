@extends('layouts.app')

@section('title', 'Opening Balances')
@section('page-title', 'Opening Balances')

@section('breadcrumb')
    <li class="breadcrumb-item active">Opening Balances</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header justify-content-between">
        <div>
            <h6 class="card-title">Opening Balances</h6>
            <p class="card-subtitle">Manage opening balance entries</p>
        </div>
        <a href="{{ route('opening-balances.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Opening Balance
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="openingBalancesTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Voucher No</th>
                        <th>Date</th>
                        <th>Account Head</th>
                        <th class="text-end">Amount</th>
                        <th>Description</th>
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
<script>
$(function () {
    const table = $('#openingBalancesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('opening-balances.index') }}",
            type: 'GET',
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'voucher_no', name: 'voucher_no' },
            { data: 'voucher_date', name: 'voucher_date' },
            { data: 'account_head', name: 'chart_of_account_id' },
            { data: 'amount', name: 'amount', className: 'text-end' },
            { data: 'description', name: 'description' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[2, 'desc']],
        dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rtip',
        language: {
            processing: '<span class="spinner-border spinner-border-sm text-primary me-2"></span> Loading...',
            search: '',
            searchPlaceholder: 'Search opening balances ...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            paginate: {
                previous: '<i class="bi bi-chevron-left"></i>',
                next: '<i class="bi bi-chevron-right"></i>'
            },
            zeroRecords: '<div class="text-center py-3 text-muted"><i class="bi bi-inbox d-block fs-4 mb-1"></i>No opening balances found</div>'
        }
    });

    // Delete Opening Balance
    $(document).on('click', '.delete-opening-balance', function () {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to delete this opening balance?')) {
            $.ajax({
                url: '/opening-balances/delete/' + id,
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Opening balance deleted successfully',
                        timer: 2000
                    });
                    table.draw(false);
                },
                error: function (response) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.responseJSON?.message || 'Error deleting opening balance'
                    });
                }
            });
        }
    });
});
</script>
@endpush
