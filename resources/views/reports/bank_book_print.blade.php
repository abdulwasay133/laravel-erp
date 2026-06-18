@extends('layouts.print')

@section('title', 'Bank Book - ' . $start->format('d M, Y') . ' — ' . $end->format('d M, Y'))

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
        <div class="title-inner">{{ $documentTitle ?? 'Bank Book' }}</div>
    </div>

    <div class="info-grid-2col">
        <div>
            <div class="row-line">
                <span class="label">Period:</span>
                <span class="value">{{ $start->format('d M, Y') }} — {{ $end->format('d M, Y') }}</span>
            </div>
            @if(!empty($bankAccount))
            <div class="row-line">
                <span class="label">Bank Account:</span>
                <span class="value">{{ $bankAccount->bank_name }} - {{ $bankAccount->account_title }} ({{ $bankAccount->account_number }})</span>
            </div>
            @endif
        </div>
        <div>
            <div class="row-line">
                <span class="label">Total Debit:</span>
                <span class="value">Rs. {{ number_format($totals['debit'], 2) }}</span>
            </div>
            <div class="row-line">
                <span class="label">Total Credit:</span>
                <span class="value">Rs. {{ number_format($totals['credit'], 2) }}</span>
            </div>
            <div class="row-line">
                <span class="label">Closing Balance:</span>
                <span class="value">Rs. {{ number_format($totals['balance'], 2) }}</span>
            </div>
        </div>
    </div>

    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 5%;">S.No</th>
                <th style="width: 12%;">Date</th>
                <th style="width: 14%;">Voucher No</th>
                <th style="width: 18%;">Type</th>
                <th style="width: 27%;">Remark</th>
                <th style="width: 12%;" class="text-end">Debit (Rs.)</th>
                <th style="width: 12%;" class="text-end">Credit (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $record)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($record['date'])->format('d M, Y') }}</td>
                <td>{{ $record['voucher_no'] ?? '-' }}</td>
                <td>{{ $record['type'] }}</td>
                <td>{{ $record['remark'] }}</td>
                <td class="text-end">{{ $record['debit'] ? number_format($record['debit'], 2) : '' }}</td>
                <td class="text-end">{{ $record['credit'] ? number_format($record['credit'], 2) : '' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; color: var(--print-muted); padding: 20px;">No transactions found</td>
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
                    <td>Opening</td>
                    <td>Rs. {{ number_format($totals['balance'] - $totals['credit'] + $totals['debit'], 2) }}</td>
                </tr>
                <tr>
                    <td>Total Debit</td>
                    <td>Rs. {{ number_format($totals['debit'], 2) }}</td>
                </tr>
                <tr>
                    <td>Total Credit</td>
                    <td>Rs. {{ number_format($totals['credit'], 2) }}</td>
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
