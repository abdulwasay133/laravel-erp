@extends('layouts.print')

@section('title', 'Monthly Commission Report - ' . $year)

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
        <div class="title-inner">{{ $documentTitle ?? 'Monthly Report' }}</div>
    </div>

    <div class="info-grid-2col">
        <div>
            <div class="row-line">
                <span class="label">Year:</span>
                <span class="value">{{ $year }}</span>
            </div>
        </div>
        <div>
            <div class="row-line">
                <span class="label">Generated:</span>
                <span class="value">{{ now()->format('d M, Y') }}</span>
            </div>
        </div>
    </div>

    @forelse($monthlyData as $bookerId => $months)
    @php $booker = $months->first()->orderBooker; @endphp
    <h4 style="margin: 12px 0 4px; font-size: 13px;">{{ $booker->first_name }} {{ $booker->last_name }}</h4>
    <table class="print-table">
        <thead>
            <tr>
                <th>Month</th>
                <th class="text-end">Total Sales</th>
                <th class="text-end">Total Commission</th>
                <th class="text-end">Paid Commission</th>
                <th class="text-end">Pending</th>
            </tr>
        </thead>
        <tbody>
            @php
                $monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            @endphp
            @foreach($months as $m)
            <tr>
                <td>{{ $monthNames[$m->month] ?? $m->month }} {{ $m->year }}</td>
                <td class="text-end">Rs. {{ number_format($m->total_sales, 2) }}</td>
                <td class="text-end">Rs. {{ number_format($m->total_commission, 2) }}</td>
                <td class="text-end">Rs. {{ number_format($m->paid_commission, 2) }}</td>
                <td class="text-end">Rs. {{ number_format($m->total_commission - $m->paid_commission, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: 700; background: #f9fafb;">
                <td>Total</td>
                <td class="text-end">Rs. {{ number_format($months->sum('total_sales'), 2) }}</td>
                <td class="text-end">Rs. {{ number_format($months->sum('total_commission'), 2) }}</td>
                <td class="text-end">Rs. {{ number_format($months->sum('paid_commission'), 2) }}</td>
                <td class="text-end">Rs. {{ number_format($months->sum('total_commission') - $months->sum('paid_commission'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
    @empty
    <p style="text-align: center; color: var(--print-muted); padding: 40px;">No data found for {{ $year }}.</p>
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
                @php
                    $grandSales = $monthlyData->sum(fn($m) => $m->sum('total_sales'));
                    $grandCommission = $monthlyData->sum(fn($m) => $m->sum('total_commission'));
                    $grandPaid = $monthlyData->sum(fn($m) => $m->sum('paid_commission'));
                @endphp
                <tr>
                    <td>Total Sales</td>
                    <td>Rs. {{ number_format($grandSales, 2) }}</td>
                </tr>
                <tr>
                    <td>Total Commission</td>
                    <td>Rs. {{ number_format($grandCommission, 2) }}</td>
                </tr>
                <tr>
                    <td>Total Paid</td>
                    <td>Rs. {{ number_format($grandPaid, 2) }}</td>
                </tr>
                <tr class="balance-row">
                    <td>Total Pending</td>
                    <td>Rs. {{ number_format($grandCommission - $grandPaid, 2) }}</td>
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
