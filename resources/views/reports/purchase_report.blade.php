@extends('layouts.app')

@section('title', 'Purchase Report')
@section('page-title', 'Purchase Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Reports</a></li>
    <li class="breadcrumb-item active">Purchase Report</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h6 class="card-title">Purchase Report</h6>
        <p class="card-subtitle">Filter purchases by date range, category, supplier, and more.</p>
    </div>
    <div class="card-body">
        <form id="purchaseReportForm" class="row g-3 align-items-end">
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
                <label class="form-label">Category</label>
                <select name="category_id" id="categoryFilter" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" id="supplierFilter" class="form-select">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">
                            {{ $supplier->first_name }} {{ $supplier->last_name }}
                            @if($supplier->company_name) ({{ $supplier->company_name }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="received">Received</option>
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
                <label class="form-label">Ref No</label>
                <input type="text" name="ref_no" id="refNoFilter" class="form-control" placeholder="Search ref no..." />
            </div>
            <div class="col-md-3">
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
                    <div class="text-muted small">Total Purchases</div>
                    <div class="fs-5 fw-semibold" id="totalCount">0</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3">
                    <div class="text-muted small">Grand Total</div>
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
                    <div class="text-muted small">Total Due</div>
                    <div class="fs-5 fw-semibold text-danger" id="summaryDue">0.00</div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped" id="purchaseReportTable">
                <thead>
                    <tr>
                        <th>S.no</th>
                        <th>Ref No</th>
                        <th>Order Date</th>
                        <th>Supplier</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Payment Status</th>
                        <th class="text-end">Grand Total</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Due</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <th colspan="7" class="text-end">Totals</th>
                        <th class="text-end" id="totalAmount">0.00</th>
                        <th class="text-end" id="totalPaid">0.00</th>
                        <th class="text-end" id="totalDue">0.00</th>
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
            category_id: $('#categoryFilter').val(),
            supplier_id: $('#supplierFilter').val(),
            status: $('#statusFilter').val(),
            payment_status: $('#paymentStatusFilter').val(),
            ref_no: $('#refNoFilter').val(),
        };
    }

    function updateTotals(totals) {
        if (!totals) {
            $('#totalCount').text('0');
            $('#summaryTotal, #summaryPaid, #summaryDue, #totalAmount, #totalPaid, #totalDue').text('0.00');
            return;
        }

        $('#totalCount').text(totals.count || 0);
        $('#summaryTotal, #totalAmount').text(parseFloat(totals.grand_total || 0).toFixed(2));
        $('#summaryPaid, #totalPaid').text(parseFloat(totals.paid_amount || 0).toFixed(2));
        $('#summaryDue, #totalDue').text(parseFloat(totals.due_amount || 0).toFixed(2));
    }

    const table = $('#purchaseReportTable').DataTable({
        processing: true,
        serverSide: true,
        deferLoading: 0,
        ajax: {
            url: '{{ route('purchase-report.search') }}',
            type: 'POST',
            data: function (d) {
                Object.assign(d, filterData());
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'ref_no', name: 'ref_no' },
            { data: 'order_date', name: 'order_date' },
            { data: 'supplier_name', name: 'supplier_name', orderable: false },
            { data: 'category_names', name: 'category_names', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'payment_status_label', name: 'payment_status_label', orderable: false, searchable: false },
            { data: 'grand_total', name: 'grand_total', className: 'text-end' },
            { data: 'paid_amount', name: 'paid_amount', className: 'text-end' },
            { data: 'due_amount', name: 'due_amount', className: 'text-end' },
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
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9] }
            },
            {
                extend: 'csv',
                text: '<i class="bi bi-filetype-csv me-1"></i> CSV',
                className: 'btn buttons-csv',
                title: 'Purchase Report',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9] }
            },
            {
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
                className: 'btn buttons-excel',
                title: 'Purchase Report',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9] }
            },
            {
                extend: 'pdf',
                text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF',
                className: 'btn buttons-pdf',
                title: 'Purchase Report',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9] }
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer me-1"></i> Print',
                className: 'btn buttons-print',
                title: 'Purchase Report',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9] }
            },
        ],
        language: {
            processing: '<span class="spinner-border spinner-border-sm text-primary me-2"></span> Loading...',
            search: '',
            searchPlaceholder: 'Search table...',
            zeroRecords: '<div class="text-center py-3 text-muted"><i class="bi bi-inbox d-block fs-4 mb-1"></i>No purchases found</div>'
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
        $('#purchaseReportForm')[0].reset();
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
        window.open('{{ route('purchase-report.print') }}?' + params.toString(), '_blank');
    });
});
</script>
@endpush
