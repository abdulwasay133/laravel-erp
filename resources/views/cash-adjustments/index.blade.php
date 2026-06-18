@extends('layouts.app')

@section('title', 'Cash Adjustments')
@section('page-title', 'Cash Adjustments')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
    <li class="breadcrumb-item active">Cash Adjustments</li>
@endsection

@section('content')

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
