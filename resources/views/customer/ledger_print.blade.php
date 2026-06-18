@extends('layouts.print')

@section('title', 'Customer Ledger - ' . $customer->first_name . ' ' . $customer->last_name)

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
        <div class="title-inner">{{ $documentTitle ?? 'Customer Ledger' }}</div>
    </div>

    {{-- Info: 2 Columns --}}
    <div class="info-grid-2col">
        <div>
            <div class="row-line">
                <span class="label">Customer:</span>
                <span class="value">{{ $customer->first_name . ' ' . $customer->last_name }}</span>
            </div>
            <div class="row-line">
                <span class="label">Company:</span>
                <span class="value">{{ $customer->company ?? 'N/A' }}</span>
            </div>
            <div class="row-line">
                <span class="label">Email:</span>
                <span class="value">{{ $customer->email ?? 'N/A' }}</span>
            </div>
            <div class="row-line">
                <span class="label">Phone:</span>
                <span class="value">{{ $customer->phone ?? 'N/A' }}</span>
            </div>
        </div>
        <div>
            <div class="row-line">
                <span class="label">Period:</span>
                <span class="value">{{ $start->format('d M, Y') }} — {{ $end->format('d M, Y') }}</span>
            </div>
            <div class="row-line">
                <span class="label">Total Debit:</span>
                <span class="value">Rs. {{ number_format($totals['debit'], 2) }}</span>
            </div>
            <div class="row-line">
                <span class="label">Total Credit:</span>
                <span class="value">Rs. {{ number_format($totals['credit'], 2) }}</span>
            </div>
            <div class="row-line">
                <span class="label">Balance:</span>
                <span class="value">Rs. {{ number_format($totals['balance'], 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 12%;">Date</th>
                <th style="width: 16%;">Reference</th>
                <th style="width: 34%;">Description</th>
                <th style="width: 12%;" class="text-end">Debit (Rs.)</th>
                <th style="width: 12%;" class="text-end">Credit (Rs.)</th>
                <th style="width: 14%;" class="text-end">Balance (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $r)
            <tr>
                <td>{{ $r->date->format('d M, Y') }}</td>
                <td>{{ $r->reference ?? '-' }}</td>
                <td>{{ $r->description ?? '-' }}</td>
                <td class="text-end">{{ number_format($r->debit, 2) }}</td>
                <td class="text-end">{{ number_format($r->credit, 2) }}</td>
                <td class="text-end">{{ number_format($r->balance, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; color: var(--print-muted); padding: 20px;">No transactions found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Summary --}}
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
                    <td>Rs. {{ number_format($records->isNotEmpty() ? $records->first()->balance - $records->first()->debit + $records->first()->credit : 0, 2) }}</td>
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
