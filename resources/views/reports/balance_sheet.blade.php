@extends('layouts.app')

@section('title', 'Balance Sheet')
@section('page-title', 'Balance Sheet')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Reports</a></li>
    <li class="breadcrumb-item active">Balance Sheet</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h6 class="card-title">Balance Sheet</h6>
        <p class="card-subtitle">Filter by date range, then print the balance sheet report.</p>
    </div>
    <div class="card-body">
        <form id="balanceSheetForm" class="row g-3 align-items-end">
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
            <table class="table table-striped" id="balanceSheetTable">
                <thead>
                    <tr>
                        <th>S.no</th>
                        <th>Section</th>
                        <th>Description</th>
                        <th class="text-end">Accounts</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Total Assets</th>
                        <th class="text-end" id="totalAssets">0.00</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Total Liabilities</th>
                        <th class="text-end" id="totalLiabilities">0.00</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Total Equity</th>
                        <th class="text-end" id="totalEquity">0.00</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Liabilities + Equity</th>
                        <th class="text-end" id="totalLiabilitiesEquity">0.00</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end" id="balanceStatus">Balanced</th>
                        <th class="text-end" id="balanceDifference">0.00</th>
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

    const table = $('#balanceSheetTable').DataTable({
        processing: true,
        serverSide: true,
        deferLoading: 0,
        ajax: {
            url: '{{ route('balance-sheet.search') }}',
            type: 'POST',
            data: function (d) {
                d.start_date = $('#startDate').val();
                d.end_date = $('#endDate').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'section', name: 'section', orderable: false },
            { data: 'description', name: 'description' },
            { data: 'accounts_count', name: 'accounts_count', className: 'text-end' },
            { data: 'amount', name: 'amount', className: 'text-end' },
        ],
        order: [],
        pageLength: 10,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        dom: '<"d-flex justify-content-between align-items-center mb-3"lBf>rtip',
        buttons: [
            { extend: 'copy', text: '<i class="bi bi-clipboard me-1"></i> Copy', className: 'btn buttons-copy', exportOptions: { columns: [0,1,2,3,4] } },
            { extend: 'csv', text: '<i class="bi bi-filetype-csv me-1"></i> CSV', className: 'btn buttons-csv', title: 'Balance Sheet', exportOptions: { columns: [0,1,2,3,4] } },
            { extend: 'excel', text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel', className: 'btn buttons-excel', title: 'Balance Sheet', exportOptions: { columns: [0,1,2,3,4] } },
            { extend: 'pdf', text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF', className: 'btn buttons-pdf', title: 'Balance Sheet', orientation: 'landscape', pageSize: 'A4', exportOptions: { columns: [0,1,2,3,4] } },
            { extend: 'print', text: '<i class="bi bi-printer me-1"></i> Print', className: 'btn buttons-print', title: 'Balance Sheet', exportOptions: { columns: [0,1,2,3,4] } },
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
                const difference = parseFloat(totals.difference);
                $('#totalAssets').text(parseFloat(totals.assets).toFixed(2));
                $('#totalLiabilities').text(parseFloat(totals.liabilities).toFixed(2));
                $('#totalEquity').text(parseFloat(totals.equity).toFixed(2));
                $('#totalLiabilitiesEquity').text(parseFloat(totals.liabilities_and_equity).toFixed(2));
                $('#balanceStatus').text(totals.status);
                $('#balanceDifference')
                    .text(Math.abs(difference).toFixed(2))
                    .toggleClass('text-success', Math.abs(difference) < 0.01)
                    .toggleClass('text-danger', Math.abs(difference) >= 0.01);
            } else {
                $('#totalAssets, #totalLiabilities, #totalEquity, #totalLiabilitiesEquity, #balanceDifference').text('0.00');
                $('#balanceStatus').text('Balanced');
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

        const url = '{{ route('balance-sheet.print') }}' + '?start_date=' + start + '&end_date=' + end;
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
