@extends('layouts.app')

@section('title', 'Closing Report')
@section('page-title', 'Closing Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Reports</a></li>
    <li class="breadcrumb-item active">Closing Report</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header justify-content-between">
        <div>
            <h6 class="card-title">Closing Report</h6>
            <p class="card-subtitle">All daily closings date-wise.</p>
        </div>
        <a href="{{ route('daily-closing.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-calendar-check me-1"></i> Daily Closing
        </a>
    </div>
    <div class="card-body">
        <form id="closingReportForm" class="row g-3 align-items-end mb-3">
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
                <button id="searchBtn" class="btn btn-primary" type="button">Search</button>
                <button id="resetBtn" class="btn btn-outline-secondary" type="button">Reset</button>
                <button id="printBtn" class="btn btn-outline-secondary" type="button">Print</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped" id="closingReportTable">
                <thead>
                    <tr>
                        <th>S.no</th>
                        <th>Closing Date</th>
                        <th class="text-end">Last Day Closing</th>
                        <th class="text-end">Receive</th>
                        <th class="text-end">Payment</th>
                        <th class="text-end">Balance</th>
                        <th>Closed By</th>
                        <th>Closed At</th>
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
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    const table = $('#closingReportTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('closing-report.search') }}',
            type: 'POST',
            data: function (d) {
                d.start_date = $('#startDate').val();
                d.end_date = $('#endDate').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'closing_date', name: 'closing_date' },
            { data: 'last_day_closing', name: 'last_day_closing', className: 'text-end' },
            { data: 'receive', name: 'receive', className: 'text-end' },
            { data: 'payment', name: 'payment', className: 'text-end' },
            { data: 'balance', name: 'balance', className: 'text-end' },
            { data: 'closed_by_name', name: 'closed_by_name' },
            { data: 'created_at', name: 'created_at' },
        ],
        order: [[1, 'desc']],
        pageLength: 10,
        language: {
            processing: '<span class="spinner-border spinner-border-sm text-primary me-2"></span> Loading...',
            zeroRecords: '<div class="text-center py-3 text-muted"><i class="bi bi-inbox d-block fs-4 mb-1"></i>No closing records found</div>'
        }
    });

    $('#searchBtn').on('click', function () {
        table.draw();
    });

    $('#resetBtn').on('click', function () {
        $('#startDate, #endDate').val('');
        table.draw();
    });

    $('#printBtn').on('click', function () {
        const start = $('#startDate').val();
        const end = $('#endDate').val();
        let url = '{{ route('closing-report.print') }}';
        url += '?start_date=' + (start || '') + '&end_date=' + (end || '');
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
