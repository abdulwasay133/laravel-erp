@extends('layouts.app')

@section('title', 'Cashbook')
@section('page-title', 'Cashbook')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Reports</a></li>
    <li class="breadcrumb-item active">Cashbook</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h6 class="card-title">Cashbook</h6>
        <p class="card-subtitle">Filter by date range and print the cashbook report.</p>
    </div>
    <div class="card-body">
        <form id="cashbookForm" class="row g-3 align-items-end">
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
            <table class="table table-striped" id="cashbookTable">
                <thead>
                    <tr>
                        <th>S.no</th>
                        <th>Date</th>
                        <th>Voucher No</th>
                        <th>Voucher Type</th>
                        <th>Remark</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th class="text-end">Balance</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-end">Totals</th>
                        <th class="text-end" id="totalDebit">0.00</th>
                        <th class="text-end" id="totalCredit">0.00</th>
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

    const table = $('#cashbookTable').DataTable({
        processing: true,
        serverSide: true,
        deferLoading: 0,
        ajax: {
            url: '{{ route('cashbook.search') }}',
            type: 'POST',
            data: function (d) {
                d.start_date = $('#startDate').val();
                d.end_date = $('#endDate').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'date', name: 'date' },
            { data: 'voucher_no', name: 'voucher_no' },
            { data: 'voucher_type', name: 'voucher_type' },
            { data: 'remark', name: 'remark' },
            { data: 'debit', name: 'debit', className: 'text-end' },
            { data: 'credit', name: 'credit', className: 'text-end' },
            { data: 'balance', name: 'balance', className: 'text-end' },
        ],
        order: [[1, 'asc']],
        pageLength: 10,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        dom: '<"d-flex justify-content-between align-items-center mb-3"lBf>rtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="bi bi-clipboard me-1"></i> Copy',
                className: 'btn buttons-copy',
                exportOptions: { columns: [0,1,2,3,4,5,6,7] }
            },
            {
                extend: 'csv',
                text: '<i class="bi bi-filetype-csv me-1"></i> CSV',
                className: 'btn buttons-csv',
                title: 'Cashbook',
                exportOptions: { columns: [0,1,2,3,4,5,6,7] }
            },
            {
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
                className: 'btn buttons-excel',
                title: 'Cashbook',
                exportOptions: { columns: [0,1,2,3,4,5,6,7] }
            },
            {
                extend: 'pdf',
                text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF',
                className: 'btn buttons-pdf',
                title: 'Cashbook',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: { columns: [0,1,2,3,4,5,6,7] }
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer me-1"></i> Print',
                className: 'btn buttons-print',
                title: 'Cashbook',
                exportOptions: { columns: [0,1,2,3,4,5,6,7] }
            },
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
            const json = settings.json;
            if (json && json.totals) {
                $('#totalDebit').text(parseFloat(json.totals.debit).toFixed(2));
                $('#totalCredit').text(parseFloat(json.totals.credit).toFixed(2));
                $('#totalBalance').text(parseFloat(json.totals.balance).toFixed(2));
            } else {
                $('#totalDebit, #totalCredit, #totalBalance').text('0.00');
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
        const url = '{{ route('cashbook.print') }}' + '?start_date=' + start + '&end_date=' + end;
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
