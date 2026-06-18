@extends('layouts.app')

@section('title', 'Due Report')
@section('page-title', 'Due Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Reports</a></li>
    <li class="breadcrumb-item active">Due Report</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h6 class="card-title">Due Report</h6>
        <p class="card-subtitle">Sales with outstanding due amount by date range.</p>
    </div>
    <div class="card-body">
        <form id="dueReportForm" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Date Range <span class="text-danger">*</span></label>
                <div class="date-range-group">
                    <input type="text" class="form-control flatpickr-start" placeholder="Start date">
                    <span class="range-sep">→</span>
                    <input type="text" class="form-control flatpickr-end" placeholder="End date">
                    <input type="hidden" name="start_date" id="startDate">
                    <input type="hidden" name="end_date" id="endDate">
                </div>
            </div>
            <div class="col-md-6">
                <button id="searchBtn" class="btn btn-primary" type="button">
                    <i class="bi bi-search me-1"></i> Search
                </button>
                <button id="printBtn" class="btn btn-outline-secondary" type="button">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </div>
        </form>

        <hr>

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="border rounded p-3">
                    <div class="text-muted small">Due Invoices</div>
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
                    <div class="text-muted small">Total Due</div>
                    <div class="fs-5 fw-semibold text-danger" id="summaryDue">0.00</div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped" id="dueReportTable">
                <thead>
                    <tr>
                        <th>S.no</th>
                        <th>Sale Date</th>
                        <th>Invoice No</th>
                        <th>Customer Name</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Paid Amount</th>
                        <th class="text-end">Due Amount</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Totals</th>
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

    function updateTotals(totals) {
        if (!totals) {
            $('#totalCount').text('0');
            $('#summaryTotal, #summaryPaid, #summaryDue, #totalAmount, #totalPaid, #totalDue').text('0.00');
            return;
        }

        $('#totalCount').text(totals.count || 0);
        $('#summaryTotal, #totalAmount').text(parseFloat(totals.total_amount || 0).toFixed(2));
        $('#summaryPaid, #totalPaid').text(parseFloat(totals.paid_amount || 0).toFixed(2));
        $('#summaryDue, #totalDue').text(parseFloat(totals.due_amount || 0).toFixed(2));
    }

    const table = $('#dueReportTable').DataTable({
        processing: true,
        serverSide: true,
        deferLoading: 0,
        ajax: {
            url: '{{ route('due-report.search') }}',
            type: 'POST',
            data: function (d) {
                d.start_date = $('#startDate').val();
                d.end_date = $('#endDate').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'sale_date', name: 'sale_date' },
            { data: 'invoice_no', name: 'invoice_no' },
            { data: 'customer_name', name: 'customer_name', orderable: false },
            { data: 'total_amount', name: 'total_amount', className: 'text-end' },
            { data: 'paid_amount', name: 'paid_amount', className: 'text-end' },
            { data: 'due_amount', name: 'balance', className: 'text-end' },
        ],
        order: [[1, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        dom: '<"d-flex justify-content-between align-items-center mb-3"lBf>rtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="bi bi-clipboard me-1"></i> Copy',
                className: 'btn buttons-copy',
                exportOptions: { columns: [0,1,2,3,4,5,6] }
            },
            {
                extend: 'csv',
                text: '<i class="bi bi-filetype-csv me-1"></i> CSV',
                className: 'btn buttons-csv',
                title: 'Due Report',
                exportOptions: { columns: [0,1,2,3,4,5,6] }
            },
            {
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
                className: 'btn buttons-excel',
                title: 'Due Report',
                exportOptions: { columns: [0,1,2,3,4,5,6] }
            },
            {
                extend: 'pdf',
                text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF',
                className: 'btn buttons-pdf',
                title: 'Due Report',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: { columns: [0,1,2,3,4,5,6] }
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer me-1"></i> Print',
                className: 'btn buttons-print',
                title: 'Due Report',
                exportOptions: { columns: [0,1,2,3,4,5,6] }
            },
        ],
        language: {
            processing: '<span class="spinner-border spinner-border-sm text-primary me-2"></span> Loading...',
            search: '',
            searchPlaceholder: 'Search table...',
            zeroRecords: '<div class="text-center py-3 text-muted"><i class="bi bi-inbox d-block fs-4 mb-1"></i>No due records found</div>'
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

    $('#printBtn').on('click', function () {
        const start = $('#startDate').val();
        const end = $('#endDate').val();
        if (!start || !end) {
            Swal.fire({
                icon: 'warning',
                title: 'Date Required',
                text: 'Please select start and end date.',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }

        const url = '{{ route('due-report.print') }}?start_date=' + start + '&end_date=' + end;

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
