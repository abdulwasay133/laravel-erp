@extends('layouts.print')

@section('title', 'Profit & Loss - ' . $start->format('d M, Y') . ' — ' . $end->format('d M, Y'))

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
        <div class="title-inner">{{ $documentTitle ?? 'Profit & Loss' }}</div>
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
                <span class="label">Total Income:</span>
                <span class="value">Rs. {{ number_format($totals['income'], 2) }}</span>
            </div>
            <div class="row-line">
                <span class="label">Total Deduction:</span>
                <span class="value">Rs. {{ number_format($totals['deduction'], 2) }}</span>
            </div>
        </div>
    </div>

    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 5%;">S.No</th>
                <th style="width: 18%;">Particular</th>
                <th style="width: 38%;">Description</th>
                <th style="width: 10%;" class="text-end">Records</th>
                <th style="width: 12%;">Effect</th>
                <th style="width: 17%;" class="text-end">Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $record)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $record['particular'] }}</td>
                <td>{{ $record['description'] }}</td>
                <td class="text-end">{{ $record['records_count'] }}</td>
                <td>{{ $record['effect'] === 'income' ? 'Income' : 'Deduction' }}</td>
                <td class="text-end">{{ number_format($record['amount'], 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; color: var(--print-muted); padding: 20px;">No records found</td>
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
                    <td>Total Income</td>
                    <td>Rs. {{ number_format($totals['income'], 2) }}</td>
                </tr>
                <tr>
                    <td>Total Deduction</td>
                    <td>Rs. {{ number_format($totals['deduction'], 2) }}</td>
                </tr>
                <tr class="{{ $totals['profit_loss'] >= 0 ? 'total-row' : 'balance-row' }}">
                    <td>{{ $totals['status'] }}</td>
                    <td>Rs. {{ number_format(abs($totals['profit_loss']), 2) }}</td>
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
