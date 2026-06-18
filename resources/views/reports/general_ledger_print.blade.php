@extends('layouts.print')

@section('title', 'General Ledger - ' . $start->format('d M, Y') . ' — ' . $end->format('d M, Y'))

@push('styles')
@if(!$withDetails)
<style>
    .print-table-general th:nth-child(1),
    .print-table-general td:nth-child(1) { width: 5%; }
    .print-table-general th:nth-child(2),
    .print-table-general td:nth-child(2) { width: 11%; }
    .print-table-general th:nth-child(3),
    .print-table-general td:nth-child(3) { width: 12%; }
    .print-table-general th:nth-child(4),
    .print-table-general td:nth-child(4) { width: 18%; }
    .print-table-general th:nth-child(5),
    .print-table-general td:nth-child(5) { width: 18%; }
    .print-table-general th:nth-child(6),
    .print-table-general td:nth-child(6) { width: 12%; }
    .print-table-general th:nth-child(7),
    .print-table-general td:nth-child(7) { width: 12%; }
    .print-table-general th:nth-child(8),
    .print-table-general td:nth-child(8) { width: 12%; }
</style>
@else
<style>
    .print-table-general th:nth-child(1),
    .print-table-general td:nth-child(1) { width: 4%; }
    .print-table-general th:nth-child(2),
    .print-table-general td:nth-child(2) { width: 10%; }
    .print-table-general th:nth-child(3),
    .print-table-general td:nth-child(3) { width: 11%; }
    .print-table-general th:nth-child(4),
    .print-table-general td:nth-child(4) { width: 15%; }
    .print-table-general th:nth-child(5),
    .print-table-general td:nth-child(5) { width: 15%; }
    .print-table-general th:nth-child(6),
    .print-table-general td:nth-child(6) { width: 10%; }
    .print-table-general th:nth-child(7),
    .print-table-general td:nth-child(7) { width: 14%; }
    .print-table-general th:nth-child(8),
    .print-table-general td:nth-child(8) { width: 10%; }
    .print-table-general th:nth-child(9),
    .print-table-general td:nth-child(9) { width: 11%; }
</style>
@endif
@endpush

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
        <div class="title-inner">{{ $documentTitle ?? 'General Ledger' }}</div>
    </div>

    <div class="info-grid-2col">
        <div>
            <div class="row-line">
                <span class="label">Period:</span>
                <span class="value">{{ $start->format('d M, Y') }} — {{ $end->format('d M, Y') }}</span>
            </div>
            @if(!empty($generalHead))
            <div class="row-line">
                <span class="label">General Head:</span>
                <span class="value">{{ $generalHead->code }} - {{ $generalHead->name }}</span>
            </div>
            @endif
            @if(!empty($transactionHead))
            <div class="row-line">
                <span class="label">Transaction Head:</span>
                <span class="value">{{ $transactionHead->code }} - {{ $transactionHead->name }}</span>
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

    <table class="print-table print-table-general">
        <thead>
            <tr>
                <th>S.No</th>
                <th>Date</th>
                <th>Voucher No</th>
                <th>General Head</th>
                <th>Transaction Head</th>
                <th>Type</th>
                @if($withDetails)
                    <th>Remark</th>
                @endif
                <th class="text-end">Debit (Rs.)</th>
                <th class="text-end">Credit (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $record)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($record['date'])->format('d M, Y') }}</td>
                <td>{{ $record['voucher_no'] ?? '-' }}</td>
                <td>{{ $record['general_head'] }}</td>
                <td>{{ $record['transaction_head'] }}</td>
                <td>{{ $record['type'] }}</td>
                @if($withDetails)
                    <td>{{ $record['remark'] ?? '-' }}</td>
                @endif
                <td class="text-end">{{ $record['debit'] ? number_format($record['debit'], 2) : '' }}</td>
                <td class="text-end">{{ $record['credit'] ? number_format($record['credit'], 2) : '' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $withDetails ? 9 : 8 }}" style="text-align: center; color: var(--print-muted); padding: 20px;">No transactions found</td>
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
