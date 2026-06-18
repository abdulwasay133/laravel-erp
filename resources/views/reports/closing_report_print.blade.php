@extends('layouts.print')

@section('title', 'Closing Report' . ($startDate && $endDate ? ' - ' . $startDate->format('d M, Y') . ' — ' . $endDate->format('d M, Y') : ''))

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
        <div class="title-inner">{{ $documentTitle ?? 'Closing Report' }}</div>
    </div>

    <div class="info-grid-2col">
        <div>
            <div class="row-line">
                <span class="label">Period:</span>
                <span class="value">{{ $startDate && $endDate ? $startDate->format('d M, Y') . ' — ' . $endDate->format('d M, Y') : 'All time' }}</span>
            </div>
        </div>
        <div>
            <div class="row-line">
                <span class="label">Total Receive:</span>
                <span class="value">Rs. {{ number_format($totals['receive'], 2) }}</span>
            </div>
            <div class="row-line">
                <span class="label">Total Payment:</span>
                <span class="value">Rs. {{ number_format($totals['payment'], 2) }}</span>
            </div>
        </div>
    </div>

    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 5%;">S.No</th>
                <th style="width: 15%;">Closing Date</th>
                <th style="width: 18%;" class="text-end">Last Day Closing</th>
                <th style="width: 18%;" class="text-end">Receive (Rs.)</th>
                <th style="width: 18%;" class="text-end">Payment (Rs.)</th>
                <th style="width: 16%;" class="text-end">Balance (Rs.)</th>
                <th style="width: 10%;">Closed By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $record)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $record->closing_date->format('d M, Y') }}</td>
                <td class="text-end">{{ number_format($record->last_day_closing, 2) }}</td>
                <td class="text-end">{{ number_format($record->receive, 2) }}</td>
                <td class="text-end">{{ number_format($record->payment, 2) }}</td>
                <td class="text-end">{{ number_format($record->balance, 2) }}</td>
                <td>{{ $record->closedByUser?->name ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; color: var(--print-muted); padding: 20px;">No closing records found</td>
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
                    <td>Total Receive</td>
                    <td>Rs. {{ number_format($totals['receive'], 2) }}</td>
                </tr>
                <tr>
                    <td>Total Payment</td>
                    <td>Rs. {{ number_format($totals['payment'], 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>Closing Balance</td>
                    <td>Rs. {{ number_format($totals['balance'], 2) }}</td>
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
