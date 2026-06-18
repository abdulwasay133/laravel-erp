@extends('layouts.app')

@section('title', 'Purchase Orders')
@section('page-title', 'Purchase Orders')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="card-title">Purchase Orders</h6>
            <p class="card-subtitle">List of purchase orders and status</p>
        </div>
        <a href="{{ route('purchase.create') }}" class="btn btn-primary">Create Purchase</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped" id="purchaseTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Supplier</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
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
    $('#purchaseTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.location.href,
            type: 'GET',
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'order_date', name: 'order_date' },
            { data: 'ref_no', name: 'ref_no' },
            { data: 'supplier_name', name: 'supplier_name' },
            { data: 'grand_total', name: 'grand_total', className: 'text-end' },
            { data: 'paid_amount', name: 'paid_amount', className: 'text-end' },
            { data: 'due_amount', name: 'due_amount', className: 'text-end' },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' },
        ],
        order: [[1, 'desc']],
        pageLength: 10,
        responsive: true,
    });
});

</script>
@endpush

