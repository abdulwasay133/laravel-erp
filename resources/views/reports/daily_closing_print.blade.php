@extends('layouts.print')

@section('title', 'Daily Closing - ' . $date->format('d M, Y'))

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
        <div class="title-inner">{{ $documentTitle ?? 'Daily Closing' }}</div>
    </div>

    <div class="info-grid-2col">
        <div>
            <div class="row-line">
                <span class="label">Date:</span>
                <span class="value">{{ $date->format('d M, Y') }}</span>
            </div>
            <div class="row-line">
                <span class="label">Status:</span>
                <span class="value">{{ $existingClosing ? 'Closed' : 'Not Closed' }}</span>
            </div>
        </div>
        <div>
            <div class="row-line">
                <span class="label">Receive:</span>
                <span class="value">Rs. {{ number_format($figures['receive'], 2) }}</span>
            </div>
            <div class="row-line">
                <span class="label">Payment:</span>
                <span class="value">Rs. {{ number_format($figures['payment'], 2) }}</span>
            </div>
        </div>
    </div>

    <table class="print-table" style="width: 60%; margin: 0 auto 14px;">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-end">Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Last Day Closing</td>
                <td class="text-end">{{ number_format($figures['last_day_closing'], 2) }}</td>
            </tr>
            <tr>
                <td>Receive</td>
                <td class="text-end text-success">{{ number_format($figures['receive'], 2) }}</td>
            </tr>
            <tr>
                <td>Payment</td>
                <td class="text-end text-danger">{{ number_format($figures['payment'], 2) }}</td>
            </tr>
            <tr>
                <td style="border-top: 2px solid var(--print-text); font-weight: 700;">Balance</td>
                <td class="text-end" style="border-top: 2px solid var(--print-text); font-weight: 700;">{{ number_format($figures['balance'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="bottom-section">
        <div class="bottom-left">
            <div class="terms-box">
                <strong>Terms & Conditions:</strong><br>
                {{ $settings['terms_conditions'] ?? 'Thank you for your business!' }}
            </div>
            @if($existingClosing)
            <div style="margin-top: 8px; font-size: 10px; color: var(--print-muted);">
                Closed by {{ $existingClosing->closedByUser?->name ?? 'Unknown' }}
                on {{ $existingClosing->created_at->format('d M Y h:i A') }}
            </div>
            @endif
        </div>
        <div class="bottom-right">
            <table class="summary-table">
                <tr>
                    <td>Last Day Closing</td>
                    <td>Rs. {{ number_format($figures['last_day_closing'], 2) }}</td>
                </tr>
                <tr>
                    <td>Receive</td>
                    <td>Rs. {{ number_format($figures['receive'], 2) }}</td>
                </tr>
                <tr>
                    <td>Payment</td>
                    <td>Rs. {{ number_format($figures['payment'], 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>Balance</td>
                    <td>Rs. {{ number_format($figures['balance'], 2) }}</td>
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
