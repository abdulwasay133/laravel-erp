@extends('layouts.app')

@section('title', 'Today Report')
@section('page-title', 'Today Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Reports</a></li>
    <li class="breadcrumb-item active">Today Report</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header justify-content-between">
        <div>
            <h6 class="card-title">Today Report</h6>
            <p class="card-subtitle">Sales, purchases, and expenses for the selected date.</p>
        </div>
        <a href="{{ route('daily-closing.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-calendar-check me-1"></i> Daily Closing
        </a>
    </div>
    <div class="card-body">
        <form id="todayReportForm" class="row g-3 align-items-end mb-4">
            <div class="col-md-4">
                <label class="form-label">Report Date</label>
                <input type="date" name="date" id="reportDate" class="form-control" value="{{ $date->format('Y-m-d') }}" />
            </div>
            <div class="col-md-4">
                <button type="button" id="loadBtn" class="btn btn-primary">
                    <i class="bi bi-arrow-clockwise me-1"></i> Load
                </button>
                <button type="button" id="printBtn" class="btn btn-outline-secondary">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </div>
        </form>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="border rounded p-3 h-100 border-primary">
                    <div class="text-muted small">Today Sales</div>
                    <div class="fs-4 fw-semibold text-primary" id="salesTotal">{{ number_format($data['sales']['total'], 2) }}</div>
                    <small class="text-muted"><span id="salesCount">{{ $data['sales']['count'] }}</span> invoice(s)</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 h-100 border-warning">
                    <div class="text-muted small">Today Purchase</div>
                    <div class="fs-4 fw-semibold text-warning" id="purchaseTotal">{{ number_format($data['purchases']['total'], 2) }}</div>
                    <small class="text-muted"><span id="purchaseCount">{{ $data['purchases']['count'] }}</span> purchase(s)</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 h-100 border-danger">
                    <div class="text-muted small">Today Expense</div>
                    <div class="fs-4 fw-semibold text-danger" id="expenseTotal">{{ number_format($data['expenses']['total'], 2) }}</div>
                    <small class="text-muted"><span id="expenseCount">{{ $data['expenses']['count'] }}</span> expense(s)</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="reportContent">
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0">Today Sales</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="salesTable">
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="salesBody">
                        @forelse($data['sales']['records'] as $sale)
                            <tr>
                                <td>{{ $sale['invoice_no'] }}</td>
                                <td>{{ $sale['customer'] }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($sale['status']) }}</span></td>
                                <td class="text-end">{{ number_format($sale['amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No sales for this date.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0">Today Purchase</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="purchaseTable">
                    <thead>
                        <tr>
                            <th>Ref No</th>
                            <th>Supplier</th>
                            <th>Status</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="purchaseBody">
                        @forelse($data['purchases']['records'] as $purchase)
                            <tr>
                                <td>{{ $purchase['ref_no'] }}</td>
                                <td>{{ $purchase['supplier'] }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($purchase['status']) }}</span></td>
                                <td class="text-end">{{ number_format($purchase['amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No purchases for this date.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h6 class="card-title mb-0">Today Expense</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="expenseTable">
                    <thead>
                        <tr>
                            <th>Voucher No</th>
                            <th>Account</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="expenseBody">
                        @forelse($data['expenses']['records'] as $expense)
                            <tr>
                                <td>{{ $expense['voucher_no'] }}</td>
                                <td>{{ $expense['account'] }}</td>
                                <td>{{ $expense['description'] }}</td>
                                <td class="text-end">{{ number_format($expense['amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No expenses for this date.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    function formatAmount(value) {
        return parseFloat(value || 0).toFixed(2);
    }

    function renderRows(records, columns, emptyMessage) {
        if (!records.length) {
            return '<tr><td colspan="' + columns + '" class="text-center text-muted">' + emptyMessage + '</td></tr>';
        }

        return records.map(function (row) {
            return row;
        }).join('');
    }

    function updateReport(data) {
        $('#salesTotal').text(formatAmount(data.sales.total));
        $('#salesCount').text(data.sales.count);
        $('#purchaseTotal').text(formatAmount(data.purchases.total));
        $('#purchaseCount').text(data.purchases.count);
        $('#expenseTotal').text(formatAmount(data.expenses.total));
        $('#expenseCount').text(data.expenses.count);

        $('#salesBody').html(
            data.sales.records.length
                ? data.sales.records.map(function (sale) {
                    return '<tr><td>' + sale.invoice_no + '</td><td>' + sale.customer + '</td><td><span class="badge bg-secondary">' + sale.status + '</span></td><td class="text-end">' + formatAmount(sale.amount) + '</td></tr>';
                }).join('')
                : '<tr><td colspan="4" class="text-center text-muted">No sales for this date.</td></tr>'
        );

        $('#purchaseBody').html(
            data.purchases.records.length
                ? data.purchases.records.map(function (purchase) {
                    return '<tr><td>' + purchase.ref_no + '</td><td>' + purchase.supplier + '</td><td><span class="badge bg-secondary">' + purchase.status + '</span></td><td class="text-end">' + formatAmount(purchase.amount) + '</td></tr>';
                }).join('')
                : '<tr><td colspan="4" class="text-center text-muted">No purchases for this date.</td></tr>'
        );

        $('#expenseBody').html(
            data.expenses.records.length
                ? data.expenses.records.map(function (expense) {
                    return '<tr><td>' + expense.voucher_no + '</td><td>' + expense.account + '</td><td>' + expense.description + '</td><td class="text-end">' + formatAmount(expense.amount) + '</td></tr>';
                }).join('')
                : '<tr><td colspan="4" class="text-center text-muted">No expenses for this date.</td></tr>'
        );
    }

    $('#loadBtn').on('click', function () {
        const date = $('#reportDate').val();
        if (!date) {
            alert('Please select a report date.');
            return;
        }

        $.get('{{ route('today-report.index') }}', { date: date, ajax: 1 })
            .done(updateReport)
            .fail(function () {
                alert('Unable to load today report.');
            });
    });

    $('#printBtn').on('click', function () {
        const date = $('#reportDate').val();
        if (!date) {
            alert('Please select a report date.');
            return;
        }
        const url = '{{ route('today-report.print') }}' + '?date=' + date;
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
