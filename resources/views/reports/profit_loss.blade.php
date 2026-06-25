@extends('layouts.app')

@section('title', 'Profit & Loss')
@section('page-title', 'Profit & Loss')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Reports</a></li>
    <li class="breadcrumb-item active">Profit & Loss</li>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Sales</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['total_sales'] }}</h5>
                    </div>
                    <i class="bi bi-cart fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Sale Amount</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['sale_amount'], 2) }}</h5>
                    </div>
                    <i class="bi bi-arrow-down-circle fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Purchases</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['total_purchases'] }}</h5>
                    </div>
                    <i class="bi bi-truck fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Purchase Amount</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['purchase_amount'], 2) }}</h5>
                    </div>
                    <i class="bi bi-arrow-up-circle fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <h6 class="card-title">Profit & Loss</h6>
        <p class="card-subtitle">Filter by date range, then print the profit and loss report.</p>
    </div>
    <div class="card-body">
        <form id="profitLossForm" class="row g-3 align-items-end">
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
            <table class="table table-striped" id="profitLossTable">
                <thead>
                    <tr>
                        <th>S.no</th>
                        <th>Particular</th>
                        <th>Description</th>
                        <th class="text-end">Records</th>
                        <th>Effect</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-end">Total Income</th>
                        <th class="text-end" id="totalIncome">0.00</th>
                    </tr>
                    <tr>
                        <th colspan="5" class="text-end">Total Deduction</th>
                        <th class="text-end" id="totalDeduction">0.00</th>
                    </tr>
                    <tr>
                        <th colspan="5" class="text-end" id="profitLossLabel">Profit / Loss</th>
                        <th class="text-end" id="profitLossAmount">0.00</th>
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

    const table = $('#profitLossTable').DataTable({
        processing: true,
        serverSide: true,
        deferLoading: 0,
        ajax: {
            url: '{{ route('profit-loss.search') }}',
            type: 'POST',
            data: function (d) {
                d.start_date = $('#startDate').val();
                d.end_date = $('#endDate').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'particular', name: 'particular' },
            { data: 'description', name: 'description' },
            { data: 'records_count', name: 'records_count', className: 'text-end' },
            { data: 'effect', name: 'effect', orderable: false, searchable: false },
            { data: 'amount', name: 'amount', className: 'text-end' },
        ],
        order: [],
        pageLength: 10,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        dom: '<"d-flex justify-content-between align-items-center mb-3"lBf>rtip',
        buttons: [
            { extend: 'copy', text: '<i class="bi bi-clipboard me-1"></i> Copy', className: 'btn buttons-copy', exportOptions: { columns: [0,1,2,3,4,5] } },
            { extend: 'csv', text: '<i class="bi bi-filetype-csv me-1"></i> CSV', className: 'btn buttons-csv', title: 'Profit & Loss', exportOptions: { columns: [0,1,2,3,4,5] } },
            { extend: 'excel', text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel', className: 'btn buttons-excel', title: 'Profit & Loss', exportOptions: { columns: [0,1,2,3,4,5] } },
            { extend: 'pdf', text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF', className: 'btn buttons-pdf', title: 'Profit & Loss', orientation: 'landscape', pageSize: 'A4', exportOptions: { columns: [0,1,2,3,4,5] } },
            { extend: 'print', text: '<i class="bi bi-printer me-1"></i> Print', className: 'btn buttons-print', title: 'Profit & Loss', exportOptions: { columns: [0,1,2,3,4,5] } },
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
                const profitLoss = parseFloat(totals.profit_loss);
                $('#totalIncome').text(parseFloat(totals.income).toFixed(2));
                $('#totalDeduction').text(parseFloat(totals.deduction).toFixed(2));
                $('#profitLossLabel').text(totals.status);
                $('#profitLossAmount')
                    .text(Math.abs(profitLoss).toFixed(2))
                    .toggleClass('text-success', profitLoss >= 0)
                    .toggleClass('text-danger', profitLoss < 0);
            } else {
                $('#totalIncome, #totalDeduction, #profitLossAmount').text('0.00');
                $('#profitLossLabel').text('Profit / Loss');
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

        const url = '{{ route('profit-loss.print') }}' + '?start_date=' + start + '&end_date=' + end;
        var iframe = document.createElement('iframe');
        iframe.style.position = 'fixed';
        iframe.style.top = '-10000px';
        iframe.style.left = '-10000px';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = 'none';
        document.body.appendChild(iframe);
        iframe.onload = function () {
            setTimeout(function () {
                iframe.contentWindow.print();
                setTimeout(function () {
                    document.body.removeChild(iframe);
                }, 500);
            }, 500);
        };
        iframe.src = url;
    });
});
</script>
@endpush
