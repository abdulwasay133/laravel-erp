@extends('layouts.app')

@section('title', 'Cash Adjustments')
@section('page-title', 'Cash Adjustments')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
    <li class="breadcrumb-item active">Cash Adjustments</li>
@endsection

@section('content')

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Adjustments</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['total_adjustments'] }}</h5>
                    </div>
                    <i class="bi bi-arrow-left-right fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Increase</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['total_increase'], 2) }}</h5>
                    </div>
                    <i class="bi bi-plus-circle fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Decrease</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['total_decrease'], 2) }}</h5>
                    </div>
                    <i class="bi bi-dash-circle fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Net Adjustment</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['net_adjustment'], 2) }}</h5>
                    </div>
                    <i class="bi bi-calculator fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Card ─────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header justify-content-between">
        <div>
            <h6 class="card-title">All Cash Adjustments</h6>
            <p class="card-subtitle">Manage cash adjustment records</p>
        </div>
        <a href="{{ route('cash-adjustments.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Cash Adjustment
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="cashAdjustmentTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Voucher No</th>
                        <th>Date</th>
                        <th>Account</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Description</th>
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
        // Initialize DataTable
        $('#cashAdjustmentTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('cash-adjustments.index') }}",
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'voucher_no' },
                { data: 'adjustment_date' },
                { data: 'account_head' },
                { data: 'adjustment_type' },
                { data: 'amount' },
                { data: 'description' },
                { data: 'reference' },
                { data: 'action', orderable: false, searchable: false }
            ],
            order: [[2, 'desc']],
            columnDefs: [
                {
                    targets: -1,
                    className: 'text-center'
                }
            ],
            pageLength: 10,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search cash adjustments..."
            }
        });

        // Delete Cash Adjustment
        $(document).on('click', '.delete-cash-adjustment', function () {
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
                        url: '{{ route("cash-adjustments.index") }}'.replace('/index', '') + '/delete/' + id,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        success: function (response) {
                            Swal.fire(
                                'Deleted!',
                                response.message || 'Cash adjustment has been deleted.',
                                'success'
                            );
                            $('#cashAdjustmentTable').DataTable().ajax.reload();
                        },
                        error: function (error) {
                            let errorMsg = 'Something went wrong.';
                            if (error.responseJSON && error.responseJSON.message) {
                                errorMsg = error.responseJSON.message;
                            } else if (error.statusText) {
                                errorMsg = error.statusText;
                            }
                            Swal.fire('Error!', errorMsg, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
