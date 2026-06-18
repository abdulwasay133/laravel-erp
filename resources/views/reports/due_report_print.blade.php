@extends('layouts.print')

@section('title', 'Due Report - ' . $start->format('d M, Y') . ' — ' . $end->format('d M, Y'))

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
                <span class="label">Period:</span>
                <span class="value">{{ $start->format('d M, Y') }} — {{ $end->format('d M, Y') }}</span>
            </div>
        </div>
        <div>
            <div class="row-line">
                <span class="label">Due Invoices:</span>
                <span class="value">{{ $totals['count'] }}</span>
            </div>
            <div class="row-line">
                <span class="label">Total Amount:</span>
                <span class="value">Rs. {{ number_format($totals['total_amount'], 2) }}</span>
            </div>
            <div class="row-line">
                <span class="label">Total Paid:</span>
                <span class="value">Rs. {{ number_format($totals['paid_amount'], 2) }}</span>
            </div>
            <div class="row-line">
                <span class="label">Total Due:</span>
                <span class="value">Rs. {{ number_format($totals['due_amount'], 2) }}</span>
            </div>
        </div>
    </div>

    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 5%;">S.no</th>
                <th style="width: 12%;">Sale Date</th>
                <th style="width: 15%;">Invoice No</th>
                <th style="width: 25%;">Customer Name</th>
                <th style="width: 19%;" class="text-end">Total Amount</th>
                <th style="width: 19%;" class="text-end">Paid Amount</th>
                <th style="width: 19%;" class="text-end">Due Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $index => $sale)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $sale->sale_date->format('d M Y') }}</td>
                    <td>{{ $sale->invoice_no }}</td>
                    <td>{{ $sale->customer ? trim($sale->customer->first_name . ' ' . $sale->customer->last_name) : '-' }}</td>
                    <td class="text-end">{{ number_format($sale->total_amount, 2) }}</td>
                    <td class="text-end">{{ number_format($sale->paid_amount, 2) }}</td>
                    <td class="text-end">{{ number_format($sale->balance, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--print-muted); padding: 12px;">No due records found for selected date range.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-end">Totals</th>
                <th class="text-end">{{ number_format($totals['total_amount'], 2) }}</th>
                <th class="text-end">{{ number_format($totals['paid_amount'], 2) }}</th>
                <th class="text-end">{{ number_format($totals['due_amount'], 2) }}</th>
            </tr>
        </tfoot>
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
                    <td>Due Invoices</td>
                    <td>{{ $totals['count'] }}</td>
                </tr>
                <tr>
                    <td>Total Amount</td>
                    <td>Rs. {{ number_format($totals['total_amount'], 2) }}</td>
                </tr>
                <tr>
                    <td>Total Paid</td>
                    <td>Rs. {{ number_format($totals['paid_amount'], 2) }}</td>
                </tr>
                <tr>
                    <td>Total Due</td>
                    <td>Rs. {{ number_format($totals['due_amount'], 2) }}</td>
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
