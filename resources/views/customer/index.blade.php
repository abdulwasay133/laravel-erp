@extends('layouts.app')

@section('title', 'Customers')
@section('page-title', 'Customers List')

@section('breadcrumb')
    <li class="breadcrumb-item active">Customers</li>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Customers</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['total'] }}</h5>
                    </div>
                    <i class="bi bi-people fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Active</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['active'] }}</h5>
                    </div>
                    <i class="bi bi-check-circle fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Credit Customers</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['credit_customers'] }}</h5>
                    </div>
                    <i class="bi bi-credit-card fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Balance</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['total_balance'], 0) }}</h5>
                    </div>
                    <i class="bi bi-currency-dollar fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header justify-content-between">
        <div>
            <h6 class="card-title">All Customers</h6>
            <p class="card-subtitle">Manage your customer database</p>
        </div>
        <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Customer
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="customerTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th>City</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th width="140" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    div.dataTables_wrapper div.dataTables_filter input {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 5px 12px;
        font-size: 13px;
    }
    div.dataTables_wrapper div.dataTables_length select {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 4px 8px;
        font-size: 13px;
    }
    table.dataTable thead th {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    table.dataTable tbody tr:hover { background: #f8fafc; }
    table.dataTable td { vertical-align: middle; font-size: 13px; }
</style>
@endpush

@push('scripts')
<script>
$(function () {
    const table = $('#customerTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('customers.index') }}",
            type: 'GET',
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'phone', name: 'phone' },
            { data: 'type', name: 'type' },
            { data: 'city', name: 'city' },
            { data: 'balance', name: 'balance' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        pageLength: 10,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        order: [[1, 'asc']],
        dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rtip',
        language: {
            processing: '<span class="spinner-border spinner-border-sm text-primary me-2"></span> Loading...',
            search: '',
            searchPlaceholder: 'Search customers...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ customers',
            paginate: {
                previous: '<i class="bi bi-chevron-left"></i>',
                next: '<i class="bi bi-chevron-right"></i>'
            },
            zeroRecords: '<div class="text-center py-3 text-muted"><i class="bi bi-inbox d-block fs-4 mb-1"></i>No customers found</div>',
        }
    });

    $(document).on('click', '.delete', function (e) {
        e.preventDefault();
        let url = $(this).data('url');

        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
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
                            title: 'Deleted',
                            text: 'Customer has been deleted successfully.',
                            timer: 2500,
                            showConfirmButton: false
                        });
                        table.draw(false);
                    }
                });
            }
        });
    });
});
</script>
@endpush
