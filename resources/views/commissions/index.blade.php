@extends('layouts.app')

@section('title', 'Order Bookers Commissions')
@section('page-title', 'Order Bookers Commissions')

@section('breadcrumb')
    <li class="breadcrumb-item active">Commissions</li>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Pending Commission</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['total_pending'], 2) }}</h5>
                    </div>
                    <i class="bi bi-clock fs-1 text-white-50"></i>
                </div>
                <small>{{ $stats['pending_count'] }} pending records</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Approved</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['total_approved'], 2) }}</h5>
                    </div>
                    <i class="bi bi-check-circle fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Paid</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['total_paid'], 2) }}</h5>
                    </div>
                    <i class="bi bi-cash-coin fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Order Bookers</h6>
                        <h5 class="mb-0 fw-bold">{{ $orderBookers->count() }}</h5>
                    </div>
                    <i class="bi bi-people fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="card-title">All Commissions</h6>
            <p class="card-subtitle">Manage order booker commission records</p>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="commissionTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Invoice No</th>
                        <th>Order Booker</th>
                        <th>Sale Amount</th>
                        <th>Rate</th>
                        <th>Commission</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th width="120">Action</th>
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
    const table = $('#commissionTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('commissions.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'invoice_no', name: 'sale.invoice_no' },
            { data: 'booker_name', name: 'orderBooker.first_name' },
            { data: 'sale_amount', name: 'sale_amount' },
            { data: 'commission_rate', name: 'commission_rate' },
            { data: 'commission_amount', name: 'commission_amount' },
            { data: 'status_badge', name: 'status' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        pageLength: 10,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        order: [[1, 'asc']],
        dom: '<"d-flex justify-content-between align-items-center mb-3"lBf>rtip',
        buttons: [
            { extend: 'csv', text: '<i class="bi bi-filetype-csv me-1"></i> CSV', className: 'btn buttons-csv', title: 'Commissions', exportOptions: { columns: [0,1,2,3,4,5,6] } },
            { extend: 'excel', text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel', className: 'btn buttons-excel', title: 'Commissions', exportOptions: { columns: [0,1,2,3,4,5,6] } },
            { extend: 'print', text: '<i class="bi bi-printer me-1"></i> Print', className: 'btn buttons-print', title: 'Commissions', exportOptions: { columns: [0,1,2,3,4,5,6] } },
        ],
        language: {
            processing: '<span class="spinner-border spinner-border-sm text-primary me-2"></span> Loading...',
            search: '', searchPlaceholder: 'Search ...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            paginate: { previous: '<i class="bi bi-chevron-left"></i>', next: '<i class="bi bi-chevron-right"></i>' },
            zeroRecords: '<div class="text-center py-3 text-muted"><i class="bi bi-inbox d-block fs-4 mb-1"></i>No commissions found</div>'
        },
    });

    $(document).on('click', '.approve-commission', function (e) {
        e.preventDefault();
        let url = $(this).data('url');
        Swal.fire({
            title: "Approve Commission?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            confirmButtonText: "Yes, approve it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(url, { _token: '{{ csrf_token() }}' }, function () {
                    Swal.fire({ icon: 'success', title: 'Approved!', timer: 2000, showConfirmButton: false });
                    $('#commissionTable').DataTable().draw(false);
                }).fail(function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.error || 'Something went wrong.' });
                });
            }
        });
    });

    $(document).on('click', '.cancel-commission', function (e) {
        e.preventDefault();
        let url = $(this).data('url');
        Swal.fire({
            title: "Cancel Commission?",
            text: "This action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Yes, cancel it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(url, { _token: '{{ csrf_token() }}', _method: 'DELETE' }, function () {
                    Swal.fire({ icon: 'success', title: 'Cancelled!', timer: 2000, showConfirmButton: false });
                    $('#commissionTable').DataTable().draw(false);
                }).fail(function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.error || 'Something went wrong.' });
                });
            }
        });
    });
});
</script>
@endpush
