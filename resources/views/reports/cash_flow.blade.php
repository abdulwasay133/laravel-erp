@extends('layouts.app')

@section('title', 'Cash Flow')
@section('page-title', 'Cash Flow')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Reports</a></li>
    <li class="breadcrumb-item active">Cash Flow</li>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Cash Adjustments</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['cash_adjustments'] }}</h5>
                    </div>
                    <i class="bi bi-arrow-left-right fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Customer Payments</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['customer_payments'] }}</h5>
                    </div>
                    <i class="bi bi-people fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Supplier Payments</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['supplier_payments'] }}</h5>
                    </div>
                    <i class="bi bi-truck fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Cash Expenses</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['cash_expenses'] }}</h5>
                    </div>
                    <i class="bi bi-receipt fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <h6 class="card-title">Cash Flow</h6>
        <p class="card-subtitle">Filter by date range, then print the cash statement.</p>
    </div>
    <div class="card-body">
        <form id="cashFlowForm" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Date Range</label>
                <div class="date-range-group">
                    <input type="text" class="form-control flatpickr-start" placeholder="Start date">
                    <span class="range-sep">→</span>
                    <input type="text" class="form-control flatpickr-end" placeholder="End date">
                    <input type="hidden" name="start_date" id="startDate">
                    <input type="hidden" name="end_date" id="endDate">
                </div>
            </div>
            <div class="col-md-6">
                <button id="searchBtn" class="btn btn-primary">Search</button>
                <button id="printBtn" class="btn btn-outline-secondary" type="button">Print</button>
            </div>
        </form>

        <hr>

        <div class="table-responsive">
            <table class="table table-striped" id="cashFlowTable">
                <thead>
                    <tr>
                        <th>S.no</th>
                        <th>Section</th>
                        <th>Particular</th>
                        <th>Description</th>
                        <th class="text-end">Records</th>
                        <th class="text-end">Inflow</th>
                        <th class="text-end">Outflow</th>
                        <th class="text-end">Net Amount</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <th colspan="7" class="text-end">Opening Cash</th>
                        <th class="text-end" id="openingCash">0.00</th>
                    </tr>
                    <tr>
                        <th colspan="7" class="text-end">Total Inflow</th>
                        <th class="text-end" id="totalInflow">0.00</th>
                    </tr>
                    <tr>
                        <th colspan="7" class="text-end">Total Outflow</th>
                        <th class="text-end" id="totalOutflow">0.00</th>
                    </tr>
                    <tr>
                        <th colspan="7" class="text-end" id="netCashFlowLabel">Net Cash Flow</th>
                        <th class="text-end" id="netCashFlow">0.00</th>
                    </tr>
                    <tr>
                        <th colspan="7" class="text-end">Closing Cash</th>
                        <th class="text-end" id="closingCash">0.00</th>
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

    const table = $('#cashFlowTable').DataTable({
        processing: true,
        serverSide: true,
        deferLoading: 0,
        ajax: {
            url: '{{ route('cash-flow.search') }}',
            type: 'POST',
            data: function (d) {
                d.start_date = $('#startDate').val();
                d.end_date = $('#endDate').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'section', name: 'section', orderable: false },
            { data: 'particular', name: 'particular' },
            { data: 'description', name: 'description' },
            { data: 'records_count', name: 'records_count', className: 'text-end' },
            { data: 'inflow', name: 'inflow', className: 'text-end' },
            { data: 'outflow', name: 'outflow', className: 'text-end' },
            { data: 'net_amount', name: 'net_amount', className: 'text-end' },
        ],
        order: [],
        pageLength: 10,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        dom: '<"d-flex justify-content-between align-items-center mb-3"lBf>rtip',
        buttons: [
            { extend: 'copy', text: '<i class="bi bi-clipboard me-1"></i> Copy', className: 'btn buttons-copy', exportOptions: { columns: [0,1,2,3,4,5,6,7] } },
            { extend: 'csv', text: '<i class="bi bi-filetype-csv me-1"></i> CSV', className: 'btn buttons-csv', title: 'Cash Flow', exportOptions: { columns: [0,1,2,3,4,5,6,7] } },
            { extend: 'excel', text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel', className: 'btn buttons-excel', title: 'Cash Flow', exportOptions: { columns: [0,1,2,3,4,5,6,7] } },
            { extend: 'pdf', text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF', className: 'btn buttons-pdf', title: 'Cash Flow', orientation: 'landscape', pageSize: 'A4', exportOptions: { columns: [0,1,2,3,4,5,6,7] } },
            { extend: 'print', text: '<i class="bi bi-printer me-1"></i> Print', className: 'btn buttons-print', title: 'Cash Flow', exportOptions: { columns: [0,1,2,3,4,5,6,7] } },
        ],
        language: {
            processing: '<span class="spinner-border spinner-border-sm text-primary me-2"></span> Loading...',
            search: '',
            searchPlaceholder: 'Search ...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            paginate: {
                previous: '<i class="bi bi-chevron-left"></i>',
                next: '<i class="bi bi-chevron-right"></i>'
            },
            zeroRecords: '<div class="text-center py-3 text-muted"><i class="bi bi-inbox d-block fs-4 mb-1"></i>No records found</div>'
        },
        drawCallback: function (settings) {
            const totals = settings.json ? settings.json.totals : null;
            if (totals) {
                const netCashFlow = parseFloat(totals.net_cash_flow);
                const closingCash = parseFloat(totals.closing_cash);
                $('#openingCash').text(parseFloat(totals.opening_cash).toFixed(2));
                $('#totalInflow').text(parseFloat(totals.inflow).toFixed(2));
                $('#totalOutflow').text(parseFloat(totals.outflow).toFixed(2));
                $('#netCashFlowLabel').text(totals.status);
                $('#netCashFlow')
                    .text(Math.abs(netCashFlow).toFixed(2))
                    .toggleClass('text-success', netCashFlow >= 0)
                    .toggleClass('text-danger', netCashFlow < 0);
                $('#closingCash')
                    .text(closingCash.toFixed(2))
                    .toggleClass('text-success', closingCash >= 0)
                    .toggleClass('text-danger', closingCash < 0);
            } else {
                $('#openingCash, #totalInflow, #totalOutflow, #netCashFlow, #closingCash').text('0.00');
                $('#netCashFlowLabel').text('Net Cash Flow');
            }
        }
    });

    $('#searchBtn').on('click', function (e) {
        e.preventDefault();
        if (!$('#startDate').val() || !$('#endDate').val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Date Required',
                text: 'Please select a start and end date.',
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
                text: 'Please select a start and end date.',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }

        const url = '{{ route('cash-flow.print') }}' + '?start_date=' + start + '&end_date=' + end;
        window.open(url, '_blank');
    });
});
</script>
@endpush
