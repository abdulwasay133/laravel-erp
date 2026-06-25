@extends('layouts.app')

@section('title', 'Commission Due Report')
@section('page-title', 'Commission Due Report')

@section('breadcrumb')
    <li class="breadcrumb-item active">Due Report</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-600">Order Booker</label>
                <select name="order_booker_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Order Bookers</option>
                    @foreach($orderBookers as $booker)
                        <option value="{{ $booker->id }}" {{ $selectedBookerId == $booker->id ? 'selected' : '' }}>
                            {{ $booker->first_name }} {{ $booker->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <a href="{{ request()->url() }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x-lg me-1"></i> Reset
                </a>
                <button type="button" onclick="printReport()" class="btn btn-primary w-100">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </div>
        </form>
    </div>
</div>

@php
    function agingText($date) {
        $diff = $date->diff(now());
        $parts = [];
        if ($diff->y > 0) $parts[] = $diff->y . ' year' . ($diff->y > 1 ? 's' : '');
        if ($diff->m > 0) $parts[] = $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
        if ($diff->d > 0) $parts[] = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
        return !empty($parts) ? implode(' ', $parts) : 'Today';
    }
@endphp

@forelse($commissions as $bookerId => $bookerCommissions)
@php $booker = $bookerCommissions->first()->orderBooker; @endphp
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="card-title">{{ $booker->first_name }} {{ $booker->last_name }}</h6>
            <p class="card-subtitle">{{ $booker->territory ? 'Territory: ' . $booker->territory : '' }}</p>
        </div>
        <div class="text-end">
            <span class="fw-bold">Due: Rs. {{ number_format($bookerCommissions->sum('commission_amount'), 2) }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Date</th>
                        <th>Sale Amount</th>
                        <th>Rate</th>
                        <th>Commission</th>
                        <th>Status</th>
                        <th>Aging</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookerCommissions as $c)
                    <tr>
                        <td>{{ $c->sale?->invoice_no ?? '-' }}</td>
                        <td>{{ $c->sale?->sale_date?->format('d M, Y') ?? '-' }}</td>
                        <td>Rs. {{ number_format($c->sale_amount, 2) }}</td>
                        <td>{{ $c->commission_rate }}%</td>
                        <td>Rs. {{ number_format($c->commission_amount, 2) }}</td>
                        <td>
                            @if($c->status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @else
                                <span class="badge bg-info">Approved</span>
                            @endif
                        </td>
                        <td>{{ agingText($c->created_at) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="4" class="text-end">Total Due:</td>
                        <td>Rs. {{ number_format($bookerCommissions->sum('commission_amount'), 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@empty
<div class="card">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-check-circle fs-1 text-success mb-3 d-block"></i>
        <h5>No Due Commissions</h5>
        <p>All commissions have been paid.</p>
    </div>
</div>
@endforelse
@endsection

@push('scripts')
<script>
function printReport() {
    @if($commissions->isEmpty())
        Swal.fire({ icon: 'warning', title: 'No Data', text: 'No due commissions found to print.', confirmButtonColor: '#0d6efd' });
        return;
    @endif
    var params = new URLSearchParams();
    @if($selectedBookerId)
        params.set('order_booker_id', '{{ $selectedBookerId }}');
    @endif
    var iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.top = '-10000px';
    iframe.style.left = '-10000px';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = 'none';
    document.body.appendChild(iframe);
    iframe.src = '{{ route('commissions.reports.due.print') }}' + '?' + params.toString();
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
