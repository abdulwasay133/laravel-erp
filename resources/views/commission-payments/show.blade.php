@extends('layouts.app')

@section('title', 'Commission Payment #' . $payment->payment_no)
@section('page-title', 'Commission Payment #' . $payment->payment_no)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('commission-payments.index') }}" class="text-decoration-none text-muted">Payments</a></li>
    <li class="breadcrumb-item active">{{ $payment->payment_no }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-5">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-info-circle text-primary-custom"></i>
                <div>
                    <h6 class="card-title">Payment Summary</h6>
                    <p class="card-subtitle">Payment details</p>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted">Payment No:</td>
                        <td class="fw-600">{{ $payment->payment_no }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Order Booker:</td>
                        <td class="fw-600">{{ $payment->orderBooker?->first_name }} {{ $payment->orderBooker?->last_name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Payment Date:</td>
                        <td class="fw-600">{{ $payment->payment_date->format('d M, Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Amount:</td>
                        <td class="fw-600 fs-5 text-primary">Rs. {{ number_format($payment->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Method:</td>
                        <td>
                            @if($payment->payment_method === 'cash')
                                <span class="badge bg-success">Cash</span>
                            @else
                                <span class="badge bg-primary">Bank</span>
                            @endif
                        </td>
                    </tr>
                    @if($payment->reference_no)
                    <tr>
                        <td class="text-muted">Reference No:</td>
                        <td class="fw-600">{{ $payment->reference_no }}</td>
                    </tr>
                    @endif
                    @if($payment->remarks)
                    <tr>
                        <td class="text-muted">Remarks:</td>
                        <td>{{ $payment->remarks }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Created By:</td>
                        <td class="fw-600">{{ $payment->createdBy?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created At:</td>
                        <td>{{ $payment->created_at->format('d M, Y h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-list-check text-primary-custom"></i>
                <div>
                    <h6 class="card-title">Commissions Paid</h6>
                    <p class="card-subtitle">Commissions settled in this payment</p>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Sale Amount</th>
                                <th>Rate</th>
                                <th>Commission</th>
                                <th>Paid Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payment->details as $detail)
                            <tr>
                                <td>{{ $detail->commission?->sale?->invoice_no ?? '-' }}</td>
                                <td>Rs. {{ number_format($detail->commission?->sale_amount ?? 0, 2) }}</td>
                                <td>{{ $detail->commission?->commission_rate ?? 0 }}%</td>
                                <td>Rs. {{ number_format($detail->commission?->commission_amount ?? 0, 2) }}</td>
                                <td class="fw-600">Rs. {{ number_format($detail->paid_amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No commission details found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="3"></td>
                                <td>Total:</td>
                                <td>Rs. {{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2">
    <a href="{{ route('commission-payments.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Payments
    </a>
    <button onclick="printVoucher()" class="btn btn-primary">
        <i class="bi bi-printer me-1"></i> Print
    </button>
</div>
@endsection

@push('scripts')
<script>
function printVoucher() {
    var iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.top = '-10000px';
    iframe.style.left = '-10000px';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = 'none';
    document.body.appendChild(iframe);
    iframe.src = '{{ route('commission-payments.print', $payment->id) }}';
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
