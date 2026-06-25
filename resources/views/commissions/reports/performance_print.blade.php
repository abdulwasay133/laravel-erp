@extends('layouts.print')

@section('title', 'Performance Report - ' . $selectedBooker->first_name . ' ' . $selectedBooker->last_name)

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
        <div class="title-inner">{{ $documentTitle ?? 'Performance Report' }}</div>
    </div>

    <div class="info-grid-2col">
        <div>
            <div class="row-line">
                <span class="label">Order Booker:</span>
                <span class="value">{{ $selectedBooker->first_name }} {{ $selectedBooker->last_name }}</span>
            </div>
            <div class="row-line">
                <span class="label">Period:</span>
                <span class="value">{{ $dateFrom->format('d M, Y') }} — {{ $dateTo->format('d M, Y') }}</span>
            </div>
        </div>
        <div>
            <div class="row-line">
                <span class="label">Total Sales:</span>
                <span class="value">Rs. {{ number_format($totalSales, 2) }}</span>
            </div>
            <div class="row-line">
                <span class="label">Total Commission:</span>
                <span class="value">Rs. {{ number_format($totalCommission, 2) }}</span>
            </div>
            <div class="row-line">
                <span class="label">Paid:</span>
                <span class="value">Rs. {{ number_format($totalPaid, 2) }}</span>
            </div>
            <div class="row-line">
                <span class="label">Pending:</span>
                <span class="value">Rs. {{ number_format($pendingAmount, 2) }}</span>
            </div>
        </div>
    </div>

    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">Invoice</th>
                <th style="width: 12%;">Date</th>
                <th style="width: 18%;" class="text-end">Sale Amount</th>
                <th style="width: 10%;" class="text-center">Rate</th>
                <th style="width: 18%;" class="text-end">Commission</th>
                <th style="width: 12%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($commissions as $c)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $c->sale?->invoice_no ?? '-' }}</td>
                <td>{{ $c->sale?->sale_date?->format('d M, Y') ?? '-' }}</td>
                <td class="text-end">Rs. {{ number_format($c->sale_amount, 2) }}</td>
                <td class="text-center">{{ $c->commission_rate }}%</td>
                <td class="text-end">Rs. {{ number_format($c->commission_amount, 2) }}</td>
                <td>{{ ucfirst($c->status) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; color: var(--print-muted); padding: 20px;">No commission records found.</td>
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
                    <td>Rs. {{ number_format($totalSales, 2) }}</td>
                </tr>
                <tr>
                    <td>Total Commission</td>
                    <td>Rs. {{ number_format($totalCommission, 2) }}</td>
                </tr>
                <tr>
                    <td>Paid</td>
                    <td>Rs. {{ number_format($totalPaid, 2) }}</td>
                </tr>
                <tr class="balance-row">
                    <td>Pending</td>
                    <td>Rs. {{ number_format($pendingAmount, 2) }}</td>
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
