@extends('layouts.app')

@section('title', 'Order Booker Performance Report')
@section('page-title', 'Order Booker Performance Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('commissions.reports.performance') }}" class="text-decoration-none text-muted">Reports</a></li>
    <li class="breadcrumb-item active">Performance</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-funnel text-primary-custom"></i>
        <div>
            <h6 class="card-title">Filter</h6>
            <p class="card-subtitle">Select order booker and date range</p>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3" id="performanceForm">
            <div class="col-md-4">
                <label class="form-label fw-600">Order Booker</label>
                <select name="order_booker_id" id="orderBookerSelect" class="form-select">
                    <option value="">-- Select --</option>
                    @foreach($orderBookers as $booker)
                        <option value="{{ $booker->id }}" {{ request('order_booker_id') == $booker->id ? 'selected' : '' }}>
                            {{ $booker->first_name }} {{ $booker->last_name }}
                        </option>
                    @endforeach
                </select>
                <div id="bookerError" class="text-danger small mt-1" style="display: none;">Please select an order booker first.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-600">From Date</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-600">To Date</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to', now()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> Generate
                </button>
            </div>
        </form>
    </div>
</div>

@if($selectedBooker && $reportData)
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <h6 class="text-white-50">Total Sales</h6>
                <h4 class="fw-bold">Rs. {{ number_format($reportData['total_sales'], 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <h6 class="text-white-50">Total Commission</h6>
                <h4 class="fw-bold">Rs. {{ number_format($reportData['total_commission'], 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body text-white">
                <h6 class="text-white-50">Paid</h6>
                <h4 class="fw-bold">Rs. {{ number_format($reportData['total_paid'], 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <h6 class="text-white-50">Pending</h6>
                <h4 class="fw-bold">Rs. {{ number_format($reportData['pending_amount'], 2) }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h6 class="card-title">Commission Breakdown</h6>
            <p class="card-subtitle">{{ $selectedBooker->first_name }} {{ $selectedBooker->last_name }}</p>
        </div>
        <button onclick="printReport()" class="btn btn-primary btn-sm">
            <i class="bi bi-printer me-1"></i> Print
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="reportTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice</th>
                        <th>Date</th>
                        <th>Sale Amount</th>
                        <th>Rate</th>
                        <th>Commission</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData['commissions'] as $c)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $c->sale?->invoice_no ?? '-' }}</td>
                        <td>{{ $c->sale?->sale_date?->format('d M, Y') ?? '-' }}</td>
                        <td>Rs. {{ number_format($c->sale_amount, 2) }}</td>
                        <td>{{ $c->commission_rate }}%</td>
                        <td>Rs. {{ number_format($c->commission_amount, 2) }}</td>
                        <td>
                            @switch($c->status)
                                @case('pending') <span class="badge bg-warning text-dark">Pending</span> @break
                                @case('approved') <span class="badge bg-info">Approved</span> @break
                                @case('paid') <span class="badge bg-success">Paid</span> @break
                                @case('cancelled') <span class="badge bg-danger">Cancelled</span> @break
                            @endswitch
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No commission records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
$(function () {
    if (document.getElementById('reportTable')) {
        $('#reportTable').DataTable({
            pageLength: 25,
            dom: '<"d-flex justify-content-between align-items-center mb-3"lBf>rtip',
            buttons: [
                { extend: 'csv', text: '<i class="bi bi-filetype-csv me-1"></i> CSV', className: 'btn buttons-csv' },
                { extend: 'excel', text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel', className: 'btn buttons-excel' },
                { extend: 'print', text: '<i class="bi bi-printer me-1"></i> Print', className: 'btn buttons-print' },
            ],
        });
    }

    $('#performanceForm').on('submit', function (e) {
        if (!$('#orderBookerSelect').val()) {
            e.preventDefault();
            $('#bookerError').show();
            $('#orderBookerSelect').addClass('is-invalid');
        }
    });

    $('#orderBookerSelect').on('change', function () {
        if ($(this).val()) {
            $('#bookerError').hide();
            $(this).removeClass('is-invalid');
        }
    });
});

function printReport() {
    var params = new URLSearchParams({
        order_booker_id: '{{ $selectedBooker?->id }}',
        date_from: '{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}',
        date_to: '{{ request('date_to', now()->format('Y-m-d')) }}',
    });
    var iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.top = '-10000px';
    iframe.style.left = '-10000px';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = 'none';
    document.body.appendChild(iframe);
    iframe.src = '{{ route('commissions.reports.performance.print') }}' + '?' + params.toString();
    iframe.onload = function () {
        setTimeout(function () {
            iframe.contentWindow.print();
            setTimeout(function () {
                document.body.removeChild(iframe);
            }, 500);
        }, 500);
    };
}
</script>
@endpush
