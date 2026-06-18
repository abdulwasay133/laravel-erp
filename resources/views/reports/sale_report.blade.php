@extends('layouts.app')

@section('title', 'Sale Report')
@section('page-title', 'Sale Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Reports</a></li>
    <li class="breadcrumb-item active">Sale Report</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h6 class="card-title">Sale Report</h6>
        <p class="card-subtitle">Advanced filters including user-wise sales report.</p>
    </div>
    <div class="card-body">
        <form id="saleReportForm" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Date Range <span class="text-danger">*</span></label>
                <div class="date-range-group">
                    <input type="text" class="form-control flatpickr-start" placeholder="Start date">
                    <span class="range-sep">→</span>
                    <input type="text" class="form-control flatpickr-end" placeholder="End date">
                    <input type="hidden" name="start_date" id="startDate">
                    <input type="hidden" name="end_date" id="endDate">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">User</label>
                <select name="created_by" id="userFilter" class="form-select">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Customer</label>
                <select name="customer_id" id="customerFilter" class="form-select">
                    <option value="">All Customers</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">
                            {{ $customer->first_name }} {{ $customer->last_name }}
                            @if($customer->company) ({{ $customer->company }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Payment Status</label>
                <select name="payment_status" id="paymentStatusFilter" class="form-select">
                    <option value="">All</option>
                    <option value="paid">Paid</option>
                    <option value="partial">Partial</option>
                    <option value="unpaid">Unpaid</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Payment Type</label>
                <select name="payment_type" id="paymentTypeFilter" class="form-select">
                    <option value="">All Types</option>
                    <option value="cash">Cash</option>
                    <option value="check">Check</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="credit">Credit</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select name="category_id" id="categoryFilter" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Invoice No</label>
                <input type="text" name="invoice_no" id="invoiceNoFilter" class="form-control" placeholder="Search invoice..." />
            </div>
            <div class="col-12">
                <button id="searchBtn" class="btn btn-primary" type="button">
                    <i class="bi bi-search me-1"></i> Search
                </button>
                <button id="resetBtn" class="btn btn-outline-secondary" type="button">Reset</button>
                <button id="printBtn" class="btn btn-outline-secondary" type="button">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </div>
        </form>

        <hr>

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="border rounded p-3">
                    <div class="text-muted small">Total Invoices</div>
                    <div class="fs-5 fw-semibold" id="totalCount">0</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3">
                    <div class="text-muted small">Total Amount</div>
                    <div class="fs-5 fw-semibold text-primary" id="summaryTotal">0.00</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3">
                    <div class="text-muted small">Total Paid</div>
                    <div class="fs-5 fw-semibold text-success" id="summaryPaid">0.00</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3">
                    <div class="text-muted small">Total Balance</div>
                    <div class="fs-5 fw-semibold text-danger" id="summaryBalance">0.00</div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped" id="saleReportTable">
                <thead>
                    <tr>
                        <th>S.no</th>
                        <th>Invoice No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Category</th>
                        <th>User</th>
                        <th>Status</th>
                        <th>Payment Status</th>
                        <th>Payment Type</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Balance</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <th colspan="9" class="text-end">Totals</th>
                        <th class="text-end" id="totalAmount">0.00</th>
                        <th class="text-end" id="totalPaid">0.00</th>
                        <th class="text-end" id="totalBalance">0.00</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    function filterData() {
        return {
            start_date: $('#startDate').val(),
            end_date: $('#endDate').val(),
            created_by: $('#userFilter').val(),
            customer_id: $('#customerFilter').val(),
            status: $('#statusFilter').val(),
            payment_status: $('#paymentStatusFilter').val(),
            payment_type: $('#paymentTypeFilter').val(),
            invoice_no: $('#invoiceNoFilter').val(),
            category_id: $('#categoryFilter').val(),
        };
    }

    function updateTotals(totals) {
        if (!totals) {
            $('#totalCount, #summaryTotal, #summaryPaid, #summaryBalance, #totalAmount, #totalPaid, #totalBalance').text('0');
            $('#summaryTotal, #summaryPaid, #summaryBalance, #totalAmount, #totalPaid, #totalBalance').text('0.00');
            return;
        }

        $('#totalCount').text(totals.count || 0);
        $('#summaryTotal, #totalAmount').text(parseFloat(totals.total_amount || 0).toFixed(2));
        $('#summaryPaid, #totalPaid').text(parseFloat(totals.paid_amount || 0).toFixed(2));
        $('#summaryBalance, #totalBalance').text(parseFloat(totals.balance || 0).toFixed(2));
    }

    const table = $('#saleReportTable').DataTable({
        processing: true,
        serverSide: true,
        deferLoading: 0,
        ajax: {
            url: '{{ route('sale-report.search') }}',
            type: 'POST',
            data: function (d) {
                Object.assign(d, filterData());
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'invoice_no', name: 'invoice_no' },
            { data: 'sale_date', name: 'sale_date' },
            { data: 'customer_name', name: 'customer_name', orderable: false },
            { data: 'category_names', name: 'category_names', orderable: false, searchable: false },
            { data: 'user_name', name: 'user_name', orderable: false },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'payment_status_label', name: 'payment_status_label', orderable: false, searchable: false },
            { data: 'payment_types', name: 'payment_types', orderable: false, searchable: false },
            { data: 'total_amount', name: 'total_amount', className: 'text-end' },
            { data: 'paid_amount', name: 'paid_amount', className: 'text-end' },
            { data: 'balance', name: 'balance', className: 'text-end' },
        ],
        order: [[2, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        dom: '<"d-flex justify-content-between align-items-center mb-3"lBf>rtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="bi bi-clipboard me-1"></i> Copy',
                className: 'btn buttons-copy',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9,10,11] }
            },
            {
                extend: 'csv',
                text: '<i class="bi bi-filetype-csv me-1"></i> CSV',
                className: 'btn buttons-csv',
                title: 'Sale Report',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9,10,11] }
            },
            {
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
                className: 'btn buttons-excel',
                title: 'Sale Report',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9,10,11] }
            },
            {
                extend: 'pdf',
                text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF',
                className: 'btn buttons-pdf',
                title: 'Sale Report',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9,10,11] }
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer me-1"></i> Print',
                className: 'btn buttons-print',
                title: 'Sale Report',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9,10,11] }
            },
        ],
        language: {
            processing: '<span class="spinner-border spinner-border-sm text-primary me-2"></span> Loading...',
            search: '',
            searchPlaceholder: 'Search table...',
            zeroRecords: '<div class="text-center py-3 text-muted"><i class="bi bi-inbox d-block fs-4 mb-1"></i>No sales found</div>'
        },
        drawCallback: function (settings) {
            updateTotals(settings.json?.totals);
        }
    });

    $('#searchBtn').on('click', function () {
        if (!$('#startDate').val() || !$('#endDate').val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Date Required',
                text: 'Please select start and end date.',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }
        table.draw();
    });

    $('#resetBtn').on('click', function () {
        $('#saleReportForm')[0].reset();
        table.clear().draw();
        updateTotals(null);
    });

    $('#printBtn').on('click', function () {
        const filters = filterData();
        if (!filters.start_date || !filters.end_date) {
            Swal.fire({
                icon: 'warning',
                title: 'Date Required',
                text: 'Please select start and end date.',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }

        const params = new URLSearchParams(filters);
        const url = '{{ route('sale-report.print') }}?' + params.toString();

        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = url;
        iframe.onload = function () {
            setTimeout(function () {
                try {
                    iframe.contentWindow.print();
                } catch (e) {
                    iframe.remove();
                }
            }, 500);
        };
        document.body.appendChild(iframe);
    });
});
</script>
@endpush
