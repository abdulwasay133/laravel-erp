@extends('layouts.app')

@section('title', 'Customer Payments')
@section('page-title', 'Customer Payments')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
    <li class="breadcrumb-item active">Customer Payments</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header justify-content-between">
        <div>
            <h6 class="card-title">All Customer Payments</h6>
            <p class="card-subtitle">Manage customer payment records</p>
        </div>
        <a href="{{ route('customer-payments.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Payment
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="paymentTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Voucher No</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Reference</th>
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
    $(document).ready(function () {
        $('#paymentTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('customer-payments.index') }}",
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'voucher_no' },
                { data: 'customer_name' },
                { data: 'payment_date' },
                { data: 'payment_type' },
                { data: 'payment_method' },
                { data: 'amount' },
                { data: 'reference' },
                { data: 'action', orderable: false, searchable: false }
            ],
            order: [[3, 'desc']],
            columnDefs: [
                { targets: -1, className: 'text-center' }
            ],
            pageLength: 10,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search payments..."
            }
        });

        $(document).on('click', '.delete-payment', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("customer-payments.index") }}'.replace('/index', '') + '/delete/' + id,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        success: function (response) {
                            Swal.fire('Deleted!', response.message, 'success');
                            $('#paymentTable').DataTable().ajax.reload();
                        },
                        error: function (error) {
                            let msg = error.responseJSON?.message || 'Something went wrong.';
                            Swal.fire('Error!', msg, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
