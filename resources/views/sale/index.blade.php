@extends('layouts.app')

@section('title', 'Sales')
@section('page-title', 'Sales List')

@section('breadcrumb')
    <li class="breadcrumb-item active">Sales</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h6 class="card-title">All Sales</h6>
        <p class="card-subtitle">Manage your sales transactions</p>
    </div>
    <div class="card-body">
        <a href="{{ route('sale.create') }}" class="btn btn-primary btn-sm mb-3">
            <i class="bi bi-plus-lg me-1"></i> New Sale
        </a>

        <div class="table-responsive">
            <table id="saleTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Balance</th>
                        <th>Status</th>
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
    const table = $('#saleTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('sale.index') }}",
            type: 'GET',
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'invoice_no', name: 'invoice_no' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'sale_date', name: 'sale_date', render: function(data) { return new Date(data).toLocaleDateString(); } },
            { data: 'total_amount', name: 'total_amount', className: 'text-end' },
            { data: 'paid_amount', name: 'paid_amount', className: 'text-end' },
            { data: 'balance', name: 'balance', className: 'text-end' },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        pageLength: 10,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        order: [[3, 'desc']],
        dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rtip',
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
            zeroRecords: '<div class="text-center py-3 text-muted"><i class="bi bi-inbox d-block fs-4 mb-1"></i>No sales found</div>'
        }
    });

    // Delete Sale
    $(document).on('click', '.delete-sale', function () {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to delete this sale?')) {
            $.ajax({
                url: '/sale/delete/' + id,
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Sale deleted successfully',
                        timer: 2000
                    });
                    table.draw(false);
                },
                error: function (response) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.responseJSON?.message ?? 'Error deleting sale'
                    });
                }
            });
        }
    });
});
</script>
@endpush
