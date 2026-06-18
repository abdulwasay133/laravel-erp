@extends('layouts.print')

@section('title', 'Purchase - ' . $purchase->ref_no)

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
        <div class="title-inner">{{ $documentTitle ?? 'Purchase Order' }}</div>
    </div>

    {{-- Info: 2 Columns --}}
    <div class="info-grid-2col">
        <div>
            <div class="row-line">
                <span class="label">Ref No:</span>
                <span class="value">{{ $purchase->ref_no }}</span>
            </div>
            <div class="row-line">
                <span class="label">Order Date:</span>
                <span class="value">{{ $purchase->order_date->format('d M, Y') }}</span>
            </div>
            <div class="row-line">
                <span class="label">Status:</span>
                <span class="value"><span class="badge-dot {{ $purchase->status }}"></span>{{ ucfirst($purchase->status) }}</span>
            </div>
            <div class="row-line">
                <span class="label">Payment:</span>
                <span class="value">{{ ucfirst($purchase->payment_method ?? 'N/A') }}</span>
            </div>
        </div>
        <div>
            <div class="row-line">
                <span class="label">Supplier:</span>
                <span class="value">{{ $purchase->supplier->first_name . ' ' . $purchase->supplier->last_name }}</span>
            </div>
            <div class="row-line">
                <span class="label">Email:</span>
                <span class="value">{{ $purchase->supplier->email ?? 'N/A' }}</span>
            </div>
            <div class="row-line">
                <span class="label">Phone:</span>
                <span class="value">{{ $purchase->supplier->phone ?? 'N/A' }}</span>
            </div>
            <div class="row-line">
                <span class="label">Company:</span>
                <span class="value">{{ $purchase->supplier->company_name ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    {{-- Items Table --}}
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 34%;">Product</th>
                <th style="width: 8%;" class="text-center">Qty</th>
                <th style="width: 14%;" class="text-end">Unit Cost</th>
                <th style="width: 14%;" class="text-end">Subtotal</th>
                <th style="width: 14%;" class="text-center">Batch</th>
                <th style="width: 16%;" class="text-center">Expiry</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-end">Rs. {{ number_format($item->unit_cost, 2) }}</td>
                <td class="text-end">Rs. {{ number_format($item->subtotal, 2) }}</td>
                <td class="text-center">{{ $item->batch_number ?? '-' }}</td>
                <td class="text-center">{{ optional($item->expiry_date)->format('d M, Y') ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Bottom: Notes + Terms & Summary --}}
    <div class="bottom-section">
        <div class="bottom-left">
            @if($purchase->notes)
            <div class="terms-box" style="margin-bottom: 8px;">
                <strong>Notes:</strong><br>
                {{ $purchase->notes }}
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
                    <td>Rs. {{ number_format($purchase->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td>Discount</td>
                    <td>- Rs. {{ number_format($purchase->discount, 2) }}</td>
                </tr>
                <tr>
                    <td>Tax</td>
                    <td>Rs. {{ number_format($purchase->tax_amount, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>Grand Total</td>
                    <td>Rs. {{ number_format($purchase->grand_total, 2) }}</td>
                </tr>
                <tr>
                    <td>Paid Amount</td>
                    <td>Rs. {{ number_format($purchase->paid_amount, 2) }}</td>
                </tr>
                @if($purchase->due_amount > 0)
                <tr class="balance-row">
                    <td>Due Amount</td>
                    <td>Rs. {{ number_format($purchase->due_amount, 2) }}</td>
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
