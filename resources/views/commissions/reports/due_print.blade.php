@extends('layouts.print')

@section('title', 'Commission Due Report')

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
        <div class="title-inner">{{ $documentTitle ?? 'Due Report' }}</div>
    </div>

    <div class="info-grid-2col">
        <div>
            <div class="row-line">
                <span class="label">Generated:</span>
                <span class="value">{{ now()->format('d M, Y') }}</span>
            </div>
            <div class="row-line">
                <span class="label">Total Due:</span>
                <span class="value">Rs. {{ number_format($grandTotal, 2) }}</span>
            </div>
        </div>
        <div>
            <div class="row-line">
                <span class="label">Bookers with Due:</span>
                <span class="value">{{ $commissions->count() }}</span>
            </div>
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
    <h4 style="margin: 12px 0 4px; font-size: 13px;">{{ $booker->first_name }} {{ $booker->last_name }}</h4>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 15%;">Invoice</th>
                <th style="width: 12%;">Date</th>
                <th style="width: 20%;" class="text-end">Sale Amount</th>
                <th style="width: 8%;" class="text-center">Rate</th>
                <th style="width: 18%;" class="text-end">Commission</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 10%;" class="text-center">Aging</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookerCommissions as $c)
            <tr>
                <td>{{ $c->sale?->invoice_no ?? '-' }}</td>
                <td>{{ $c->sale?->sale_date?->format('d M, Y') ?? '-' }}</td>
                <td class="text-end">Rs. {{ number_format($c->sale_amount, 2) }}</td>
                <td class="text-center">{{ $c->commission_rate }}%</td>
                <td class="text-end">Rs. {{ number_format($c->commission_amount, 2) }}</td>
                <td>{{ ucfirst($c->status) }}</td>
                <td class="text-center">{{ agingText($c->created_at) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: 700; background: #f9fafb;">
                <td colspan="4" class="text-end">Subtotal Due:</td>
                <td class="text-end">Rs. {{ number_format($bookerCommissions->sum('commission_amount'), 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
    @empty
    <p style="text-align: center; color: var(--print-muted); padding: 40px;">No due commissions found.</p>
    @endforelse

    <div class="bottom-section">
        <div class="bottom-left">
            <div class="terms-box">
                <strong>Terms & Conditions:</strong><br>
                {{ $settings['terms_conditions'] ?? 'Thank you for your business!' }}
            </div>
        </div>
        <div class="bottom-right">
            <table class="summary-table">
                <tr class="total-row">
                    <td>Total Due Amount</td>
                    <td>Rs. {{ number_format($grandTotal, 2) }}</td>
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
