@extends('layouts.app')

@section('title', 'Supplier Ledger')
@section('page-title', 'Supplier Ledger')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supplier.index') }}" class="text-decoration-none text-muted">Suppliers</a></li>
    <li class="breadcrumb-item active">Ledger</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h6 class="card-title">Supplier Ledger</h6>
        <p class="card-subtitle">Select a supplier and date range to view transactions</p>
    </div>
    <div class="card-body">
        <form id="ledgerForm" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Supplier</label>
                <select id="supplierSelect" name="supplier_id" class="form-select">
                    <option value="">-- Select Supplier --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->first_name }} {{ $supplier->last_name }} @if($supplier->company_name) ({{ $supplier->company_name }}) @endif</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date Range</label>
                <div class="date-range-group">
                    <input type="text" class="form-control flatpickr-start" placeholder="Start date">
                    <span class="range-sep">→</span>
                    <input type="text" class="form-control flatpickr-end" placeholder="End date">
                    <input type="hidden" name="start_date" id="startDate">
                    <input type="hidden" name="end_date" id="endDate">
                </div>
            </div>
            <div class="col-md-2">
                <button id="searchBtn" class="btn btn-primary">Search</button>
                <button id="printBtn" class="btn btn-outline-secondary" type="button">Print</button>
            </div>
        </form>

        <hr>

        <div class="table-responsive">
            <table class="table table-striped" id="ledgerTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Description</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th class="text-end">Balance</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Totals</th>
                        <th class="text-end" id="totalDebit">0</th>
                        <th class="text-end" id="totalCredit">0</th>
                        <th class="text-end" id="totalBalance">0</th>
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

    const table = $('#ledgerTable').DataTable({
        processing : true,
        serverSide : true,
        deferLoading: 0,
        ajax: {
            url  : '{{ route('supplier.ledger.search') }}',
            type : 'POST',
            data: function (d) {
                d.supplier_id = $('#supplierSelect').val();
                d.start_date = $('#startDate').val();
                d.end_date = $('#endDate').val();
            }
        },
        columns: [
            { data: 'date', name: 'date' },
            { data: 'reference', name: 'reference' },
            { data: 'description', name: 'description' },
            { data: 'debit', name: 'debit', className: 'text-end', render: $.fn.dataTable.render.number(',', '.', 2, '') },
            { data: 'credit', name: 'credit', className: 'text-end', render: $.fn.dataTable.render.number(',', '.', 2, '') },
            { data: 'balance', name: 'balance', className: 'text-end', render: $.fn.dataTable.render.number(',', '.', 2, '') },
        ],
        pageLength : 10,
        lengthMenu : [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        order : [[0, 'asc']],
        dom: '<"d-flex justify-content-between align-items-center mb-3"lBf>rtip',
        buttons: [
            { extend: 'copy', text: '<i class="bi bi-clipboard me-1"></i> Copy', className: 'btn buttons-copy', exportOptions: { columns: [0,1,2,3,4,5] } },
            { extend: 'csv', text: '<i class="bi bi-filetype-csv me-1"></i> CSV', className: 'btn buttons-csv', title: 'Supplier Ledger', exportOptions: { columns: [0,1,2,3,4,5] } },
            { extend: 'excel', text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel', className: 'btn buttons-excel', title: 'Supplier Ledger', exportOptions: { columns: [0,1,2,3,4,5] } },
            { extend: 'pdf', text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF', className: 'btn buttons-pdf', title: 'Supplier Ledger', orientation: 'portrait', pageSize: 'A4', exportOptions: { columns: [0,1,2,3,4,5] } },
            { extend: 'print', text: '<i class="bi bi-printer me-1"></i> Print', className: 'btn buttons-print', title: 'Supplier Ledger', exportOptions: { columns: [0,1,2,3,4,5] } },
        ],
        language: {
            processing: '<span class="spinner-border spinner-border-sm text-primary me-2"></span> Loading...',
            search: '',
            searchPlaceholder: 'Search ...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            paginate: { previous: '<i class="bi bi-chevron-left"></i>', next: '<i class="bi bi-chevron-right"></i>' },
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
        if (!$('#supplierSelect').val() || !$('#startDate').val() || !$('#endDate').val()) {
            alert('Please select supplier and date range first.');
            return;
        }
        table.draw();
    });

    $('#printBtn').on('click', function () {
        const supplier_id = $('#supplierSelect').val();
        const start = $('#startDate').val();
        const end = $('#endDate').val();
        if (!supplier_id || !start || !end) {
            alert('Please select supplier and date range first.');
            return;
        }
        const url = '{{ route('supplier.ledger.print') }}' + '?supplier_id=' + supplier_id + '&start_date=' + start + '&end_date=' + end;
        var iframe = document.createElement('iframe');
        iframe.style.position = 'fixed';
        iframe.style.top = '-10000px';
        iframe.style.left = '-10000px';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = 'none';
        document.body.appendChild(iframe);
        iframe.src = url;
        iframe.onload = function () {
            setTimeout(function () {
                iframe.contentWindow.print();
                setTimeout(function () {
                    document.body.removeChild(iframe);
                }, 500);
            }, 500);
        };
    });
});
</script>
@endpush
