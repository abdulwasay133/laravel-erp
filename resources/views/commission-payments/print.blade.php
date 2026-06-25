@extends('layouts.print')

@section('title', 'Voucher - ' . $payment->payment_no)

@section('print_content')
    <div class="print-header">
        <div class="header-left">
            @if(!empty($settings['company_logo']))
                <img src="{{ asset('storage/' . $settings['company_logo']) }}" alt="Logo" class="company-logo">
            @endif
            <div class="company-info">
                <h2>{{ $settings['company_name'] ?? 'Company Name' }}</h2>
                <p>
                    {{ $settings['company_address'] ?? '' }}<br>
                    Phone: {{ $settings['company_phone'] ?? '' }} &nbsp;|&nbsp; Email: {{ $settings['company_email'] ?? '' }}<br>
                    Web: {{ $settings['company_website'] ?? '' }}
                </p>
            </div>
        </div>
        <div class="header-right">
            @if(!empty($qrSvg))
                {!! $qrSvg !!}
            @endif
            <div class="qr-label">Scan to verify</div>
        </div>
    </div>

    <div class="title-box">
        <div class="title-inner">{{ $documentTitle ?? 'Payment Voucher' }}</div>
    </div>

    <div class="info-grid-2col">
        <div>
            <div class="row-line">
                <span class="label">Voucher No:</span>
                <span class="value">{{ $payment->payment_no }}</span>
            </div>
            <div class="row-line">
                <span class="label">Date:</span>
                <span class="value">{{ $payment->payment_date->format('d M, Y') }}</span>
            </div>
            <div class="row-line">
                <span class="label">Method:</span>
                <span class="value">{{ ucfirst($payment->payment_method) }}</span>
            </div>
            @if($payment->reference_no)
            <div class="row-line">
                <span class="label">Reference:</span>
                <span class="value">{{ $payment->reference_no }}</span>
            </div>
            @endif
        </div>
        <div>
            <div class="row-line">
                <span class="label">Order Booker:</span>
                <span class="value">{{ $payment->orderBooker?->first_name }} {{ $payment->orderBooker?->last_name }}</span>
            </div>
            <div class="row-line">
                <span class="label">Phone:</span>
                <span class="value">{{ $payment->orderBooker?->phone ?? '-' }}</span>
            </div>
            <div class="row-line">
                <span class="label">Paid By:</span>
                <span class="value">{{ $payment->createdBy?->name ?? '-' }}</span>
            </div>
        </div>
    </div>

    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 20%;">Invoice No</th>
                <th style="width: 20%;" class="text-center">Sale Date</th>
                <th style="width: 20%;" class="text-end">Sale Amount</th>
                <th style="width: 10%;" class="text-center">Rate</th>
                <th style="width: 15%;" class="text-end">Commission</th>
                <th style="width: 15%;" class="text-end">Paid Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payment->details as $detail)
            <tr>
                <td>{{ $detail->commission?->sale?->invoice_no ?? '-' }}</td>
                <td class="text-center">{{ $detail->commission?->sale?->sale_date?->format('d M, Y') ?? '-' }}</td>
                <td class="text-end">Rs. {{ number_format($detail->commission?->sale_amount ?? 0, 2) }}</td>
                <td class="text-center">{{ $detail->commission?->commission_rate ?? 0 }}%</td>
                <td class="text-end">Rs. {{ number_format($detail->commission?->commission_amount ?? 0, 2) }}</td>
                <td class="text-end">Rs. {{ number_format($detail->paid_amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No commission details found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="bottom-section">
        <div class="bottom-left">
            @if($payment->remarks)
            <div class="terms-box" style="margin-bottom: 8px;">
                <strong>Remarks:</strong><br>
                {{ $payment->remarks }}
            </div>
            @endif
            <div class="terms-box">
                <strong>Terms & Conditions:</strong><br>
                {{ $settings['terms_conditions'] ?? 'Thank you for your business!' }}
            </div>
        </div>
        <div class="bottom-right">
            <table class="summary-table">
                <tr>
                    <td>Total Commissions</td>
                    <td>Rs. {{ number_format($payment->amount, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>Paid Amount</td>
                    <td>Rs. {{ number_format($payment->amount, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="print-footer">
        Generated on {{ now()->format('d M, Y h:i A') }} &mdash;
        {{ $settings['company_name'] ?? 'ERP System' }}
    </div>
@endsection

@push('scripts')
<script>
    window.addEventListener('load', function() {
        if (window === window.top) {
            window.print();
        }
    });
</script>
@endpush
