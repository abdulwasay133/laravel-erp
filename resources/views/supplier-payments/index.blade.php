@extends('layouts.app')

@section('title', 'Supplier Payments')
@section('page-title', 'Supplier Payments')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
    <li class="breadcrumb-item active">Supplier Payments</li>
@endsection

@section('content')

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Payments</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['total_payments'] }}</h5>
                    </div>
                    <i class="bi bi-credit-card-2-front fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Credit (In)</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['total_credit'], 2) }}</h5>
                    </div>
                    <i class="bi bi-arrow-down-circle fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Debit (Out)</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['total_debit'], 2) }}</h5>
                    </div>
                    <i class="bi bi-arrow-up-circle fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Net Amount</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['net_amount'], 2) }}</h5>
                    </div>
                    <i class="bi bi-calculator fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header justify-content-between">
        <div>
            <h6 class="card-title">All Supplier Payments</h6>
            <p class="card-subtitle">Manage supplier payment records</p>
        </div>
        <a href="{{ route('supplier-payments.create') }}" class="btn btn-primary btn-sm">
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
                        <th>Supplier</th>
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
            ajax: "{{ route('supplier-payments.index') }}",
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'voucher_no' },
                { data: 'supplier_name' },
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
                        url: '{{ route("supplier-payments.index") }}'.replace('/index', '') + '/delete/' + id,
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
