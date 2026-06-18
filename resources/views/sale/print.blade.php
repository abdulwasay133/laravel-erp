@extends('layouts.print')

@section('title', 'Invoice - ' . $sale->invoice_no)

@section('print_content')
    {{-- Header: Company Info + QR --}}
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

    {{-- Title Box --}}
    <div class="title-box">
        <div class="title-inner">{{ $documentTitle ?? 'Invoice' }}</div>
    </div>

    {{-- Info: 2 Columns --}}
    <div class="info-grid-2col">
        <div>
            <div class="row-line">
                <span class="label">Invoice No:</span>
                <span class="value">{{ $sale->invoice_no }}</span>
            </div>
            <div class="row-line">
                <span class="label">Date:</span>
                <span class="value">{{ $sale->sale_date->format('d M, Y') }}</span>
            </div>
            <div class="row-line">
                <span class="label">Status:</span>
                <span class="value"><span class="badge-dot {{ $sale->status }}"></span>{{ ucfirst($sale->status) }}</span>
            </div>
        </div>
        <div>
            <div class="row-line">
                <span class="label">Customer:</span>
                <span class="value">{{ $sale->customer->first_name . ' ' . $sale->customer->last_name }}</span>
            </div>
            <div class="row-line">
                <span class="label">Email:</span>
                <span class="value">{{ $sale->customer->email }}</span>
            </div>
            <div class="row-line">
                <span class="label">Phone:</span>
                <span class="value">{{ $sale->customer->phone }}</span>
            </div>
        </div>
    </div>

    {{-- Items Table --}}
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 38%;">Product</th>
                <th style="width: 12%;" class="text-center">Batch</th>
                <th style="width: 8%;" class="text-center">Qty</th>
                <th style="width: 14%;" class="text-end">Unit Price</th>
                <th style="width: 14%;" class="text-end">Discount</th>
                <th style="width: 14%;" class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td class="text-center">{{ $item->batch->batch_number ?? '-' }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-end">Rs. {{ number_format($item->unit_price, 2) }}</td>
                <td class="text-end">Rs. {{ number_format($item->discount_amount, 2) }}</td>
                <td class="text-end">Rs. {{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Bottom: Notes + Terms & Summary --}}
    <div class="bottom-section">
        <div class="bottom-left">
            @if($sale->notes)
            <div class="terms-box" style="margin-bottom: 8px;">
                <strong>Notes:</strong><br>
                {{ $sale->notes }}
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
                    <td>Subtotal</td>
                    <td>Rs. {{ number_format($sale->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td>Discount{{ $discountPercent > 0 ? ' (' . number_format($discountPercent, 1) . '%)' : '' }}</td>
                    <td>- Rs. {{ number_format($discountTotal, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>Total Amount</td>
                    <td>Rs. {{ number_format($sale->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Paid Amount</td>
                    <td>Rs. {{ number_format($sale->paid_amount, 2) }}</td>
                </tr>
                @if($sale->balance > 0)
                <tr class="balance-row">
                    <td>Balance Due</td>
                    <td>Rs. {{ number_format($sale->balance, 2) }}</td>
                </tr>
                @else
                <tr class="paid-full">
                    <td>Paid in Full</td>
                    <td>Rs. 0.00</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- Footer --}}
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
