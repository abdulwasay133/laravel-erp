@extends('layouts.app')

@section('title', 'Monthly Commission Report')
@section('page-title', 'Monthly Commission Report')

@section('breadcrumb')
    <li class="breadcrumb-item active">Monthly Commission</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-600">Year</label>
                <select name="year" class="form-select">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ ($year ?? now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-600">Order Booker</label>
                <select name="order_booker_id" class="form-select">
                    <option value="">All Order Bookers</option>
                    @foreach($orderBookers as $booker)
                        <option value="{{ $booker->id }}" {{ $orderBookerId == $booker->id ? 'selected' : '' }}>
                            {{ $booker->first_name }} {{ $booker->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> Generate
                </button>
                <button type="button" onclick="printReport()" class="btn btn-outline-primary w-100">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </div>
        </form>
    </div>
</div>

@forelse($monthlyData as $bookerId => $months)
@php $booker = $months->first()->orderBooker; @endphp
<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title">{{ $booker->first_name }} {{ $booker->last_name }}</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Total Sales</th>
                        <th>Total Commission</th>
                        <th>Paid Commission</th>
                        <th>Pending</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m)
                    @php
                        $monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                    @endphp
                    <tr>
                        <td>{{ $monthNames[$m->month] ?? $m->month }} {{ $m->year }}</td>
                        <td>Rs. {{ number_format($m->total_sales, 2) }}</td>
                        <td>Rs. {{ number_format($m->total_commission, 2) }}</td>
                        <td>Rs. {{ number_format($m->paid_commission, 2) }}</td>
                        <td>Rs. {{ number_format($m->total_commission - $m->paid_commission, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td>Total</td>
                        <td>Rs. {{ number_format($months->sum('total_sales'), 2) }}</td>
                        <td>Rs. {{ number_format($months->sum('total_commission'), 2) }}</td>
                        <td>Rs. {{ number_format($months->sum('paid_commission'), 2) }}</td>
                        <td>Rs. {{ number_format($months->sum('total_commission') - $months->sum('paid_commission'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@empty
<div class="card">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-bar-chart fs-1 mb-3 d-block"></i>
        <h5>No Data</h5>
        <p>No commission records found for the selected period.</p>
    </div>
</div>
@endforelse
@endsection

@push('scripts')
<script>
function printReport() {
    @if($monthlyData->isEmpty())
        Swal.fire({ icon: 'warning', title: 'No Data', text: 'No commission records found for the selected period.', confirmButtonColor: '#0d6efd' });
        return;
    @endif
    var params = new URLSearchParams();
    params.set('year', '{{ $year }}');
    @if($orderBookerId)
        params.set('order_booker_id', '{{ $orderBookerId }}');
    @endif
    var iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.top = '-10000px';
    iframe.style.left = '-10000px';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = 'none';
    document.body.appendChild(iframe);
    iframe.src = '{{ route('commissions.reports.monthly.print') }}' + '?' + params.toString();
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
