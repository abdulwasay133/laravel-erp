@extends('layouts.print')

@section('title', 'Cash Flow - ' . $start->format('d M, Y') . ' — ' . $end->format('d M, Y'))

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
        <div class="title-inner">{{ $documentTitle ?? 'Cash Flow' }}</div>
    </div>

    <div class="info-grid-2col">
        <div>
            <div class="row-line">
                <span class="label">Period:</span>
                <span class="value">{{ $start->format('d M, Y') }} — {{ $end->format('d M, Y') }}</span>
            </div>
        </div>
        <div>
            <div class="row-line">
                <span class="label">Opening Cash:</span>
                <span class="value">Rs. {{ number_format($totals['opening_cash'], 2) }}</span>
            </div>
            <div class="row-line">
                <span class="label">Closing Cash:</span>
                <span class="value">Rs. {{ number_format($totals['closing_cash'], 2) }}</span>
            </div>
        </div>
    </div>

    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 4%;">S.No</th>
                <th style="width: 13%;">Section</th>
                <th style="width: 17%;">Particular</th>
                <th style="width: 28%;">Description</th>
                <th style="width: 8%;" class="text-end">Records</th>
                <th style="width: 10%;" class="text-end">Inflow (Rs.)</th>
                <th style="width: 10%;" class="text-end">Outflow (Rs.)</th>
                <th style="width: 10%;" class="text-end">Net (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $record)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $record['section'] }}</td>
                <td>{{ $record['particular'] }}</td>
                <td>{{ $record['description'] }}</td>
                <td class="text-end">{{ $record['records_count'] }}</td>
                <td class="text-end">{{ $record['inflow'] ? number_format($record['inflow'], 2) : '' }}</td>
                <td class="text-end">{{ $record['outflow'] ? number_format($record['outflow'], 2) : '' }}</td>
                <td class="text-end">{{ number_format($record['net_amount'], 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; color: var(--print-muted); padding: 20px;">No records found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="bottom-section">
        <div class="bottom-left">
            <div class="terms-box">
                <strong>Terms & Conditions:</strong><br>
                {{ $settings['terms_conditions'] ?? 'Thank you for your business!' }}
            </div>
        </div>
        <div class="bottom-right">
            <table class="summary-table">
                <tr>
                    <td>Opening Cash</td>
                    <td>Rs. {{ number_format($totals['opening_cash'], 2) }}</td>
                </tr>
                <tr>
                    <td>Total Inflow</td>
                    <td>Rs. {{ number_format($totals['inflow'], 2) }}</td>
                </tr>
                <tr>
                    <td>Total Outflow</td>
                    <td>Rs. {{ number_format($totals['outflow'], 2) }}</td>
                </tr>
                <tr class="{{ $totals['net_cash_flow'] >= 0 ? 'total-row' : 'balance-row' }}">
                    <td>{{ $totals['status'] }}</td>
                    <td>Rs. {{ number_format(abs($totals['net_cash_flow']), 2) }}</td>
                </tr>
                <tr class="paid-full">
                    <td>Closing Cash</td>
                    <td>Rs. {{ number_format($totals['closing_cash'], 2) }}</td>
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
