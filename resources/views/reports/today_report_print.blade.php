@extends('layouts.print')

@section('title', 'Today Report - ' . $date->format('d M, Y'))

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
        <div class="title-inner">{{ $documentTitle ?? 'Today Report' }}</div>
    </div>

    <div class="info-grid-2col">
        <div>
            <div class="row-line">
                <span class="label">Date:</span>
                <span class="value">{{ $date->format('d M, Y') }}</span>
            </div>
        </div>
        <div>
            <div class="row-line">
                <span class="label">Total Sales:</span>
                <span class="value">Rs. {{ number_format($data['sales']['total'], 2) }}</span>
            </div>
            <div class="row-line">
                <span class="label">Total Purchases:</span>
                <span class="value">Rs. {{ number_format($data['purchases']['total'], 2) }}</span>
            </div>
            <div class="row-line">
                <span class="label">Total Expenses:</span>
                <span class="value">Rs. {{ number_format($data['expenses']['total'], 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Sales Table --}}
    <h4 style="font-size: 13px; font-weight: 700; margin: 0 0 6px;">Sales</h4>
    <table class="print-table" style="margin-bottom: 16px;">
        <thead>
            <tr>
                <th style="width: 25%;">Invoice No</th>
                <th style="width: 35%;">Customer</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 25%;" class="text-end">Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['sales']['records'] as $sale)
            <tr>
                <td>{{ $sale['invoice_no'] }}</td>
                <td>{{ $sale['customer'] }}</td>
                <td>{{ ucfirst($sale['status']) }}</td>
                <td class="text-end">{{ number_format($sale['amount'], 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; color: var(--print-muted); padding: 12px;">No sales for this date.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Purchases Table --}}
    <h4 style="font-size: 13px; font-weight: 700; margin: 0 0 6px;">Purchases</h4>
    <table class="print-table" style="margin-bottom: 16px;">
        <thead>
            <tr>
                <th style="width: 25%;">Ref No</th>
                <th style="width: 35%;">Supplier</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 25%;" class="text-end">Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['purchases']['records'] as $purchase)
            <tr>
                <td>{{ $purchase['ref_no'] }}</td>
                <td>{{ $purchase['supplier'] }}</td>
                <td>{{ ucfirst($purchase['status']) }}</td>
                <td class="text-end">{{ number_format($purchase['amount'], 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; color: var(--print-muted); padding: 12px;">No purchases for this date.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Expenses Table --}}
    <h4 style="font-size: 13px; font-weight: 700; margin: 0 0 6px;">Expenses</h4>
    <table class="print-table" style="margin-bottom: 14px;">
        <thead>
            <tr>
                <th style="width: 20%;">Voucher No</th>
                <th style="width: 25%;">Account</th>
                <th style="width: 35%;">Description</th>
                <th style="width: 20%;" class="text-end">Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['expenses']['records'] as $expense)
            <tr>
                <td>{{ $expense['voucher_no'] }}</td>
                <td>{{ $expense['account'] }}</td>
                <td>{{ $expense['description'] }}</td>
                <td class="text-end">{{ number_format($expense['amount'], 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; color: var(--print-muted); padding: 12px;">No expenses for this date.</td>
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
                    <td>Total Sales</td>
                    <td>Rs. {{ number_format($data['sales']['total'], 2) }}</td>
                </tr>
                <tr>
                    <td>Total Purchases</td>
                    <td>Rs. {{ number_format($data['purchases']['total'], 2) }}</td>
                </tr>
                <tr>
                    <td>Total Expenses</td>
                    <td>Rs. {{ number_format($data['expenses']['total'], 2) }}</td>
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
