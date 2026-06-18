@extends('layouts.app')

@section('title', 'Expenses')
@section('page-title', 'Expenses')

@section('breadcrumb')
    <li class="breadcrumb-item active">Expenses</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header justify-content-between">
        <div>
            <h6 class="card-title">All Expenses</h6>
            <p class="card-subtitle">Manage business expenses and payments</p>
        </div>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Expense
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="expenseTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Voucher No</th>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Account</th>
                        <th>Payment</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th class="text-center">Action</th>
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
    const table = $('#expenseTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('expenses.index') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'voucher_no' },
            { data: 'expense_date' },
            { data: 'title' },
            { data: 'account_head' },
            { data: 'payment_method_label', orderable: false },
            { data: 'amount' },
            { data: 'description' },
            { data: 'action', orderable: false, searchable: false, className: 'text-center' },
        ],
        order: [[2, 'desc']],
    });

    $(document).on('click', '.delete-expense', function () {
        if (!confirm('Delete this expense? Financial balances will be reverted.')) return;

        const id = $(this).data('id');
        $.ajax({
            url: '{{ route('expenses.destroy', '__ID__') }}'.replace('__ID__', id),
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function () {
                table.draw();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message || 'Unable to delete expense.');
            }
        });
    });
});
</script>
@endpush
