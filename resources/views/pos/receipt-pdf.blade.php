<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Receipt - {{ $transaction->receipt_no }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 20px; }
        .header p { margin: 2px 0; color: #555; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; font-size: 10px; text-transform: uppercase; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: 700; }
        .totals { margin-top: 10px; }
        .totals .row { display: flex; justify-content: flex-end; padding: 3px 0; }
        .totals .label { width: 150px; text-align: right; padding-right: 15px; }
        .totals .value { width: 100px; text-align: right; }
        .footer { text-align: center; margin-top: 30px; color: #777; font-size: 10px; }
        .info { margin-bottom: 15px; }
        .info div { padding: 2px 0; }
        .receipt-label { font-size: 14px; font-weight: 700; text-align: center; letter-spacing: 2px; margin: 10px 0; }
        .line { border-top: 2px solid #333; margin: 10px 0; }
        .grand-total { font-size: 16px; font-weight: 800; }
        .qr { text-align: center; margin: 15px 0; }
    </style>
</head>
<body>
    {{-- Company Header --}}
    <div class="header">
        <h2>{{ $settings['company_name'] ?? 'Store' }}</h2>
        @if(!empty($settings['company_address']))
            @foreach(explode("\n", $settings['company_address']) as $line)
                <p>{{ $line }}</p>
            @endforeach
        @endif
        @if(!empty($settings['company_phone']))<p>Tel: {{ $settings['company_phone'] }}</p>@endif
        @if(!empty($settings['company_email']))<p>Email: {{ $settings['company_email'] }}</p>@endif
    </div>

    <div class="line"></div>
    <div class="receipt-label">SALE INVOICE</div>
    <div class="line"></div>

    {{-- Customer / Invoice Info --}}
    <table style="margin-bottom:5px;">
        <tr>
            <td style="width:50%; border:none;">
                <strong>Bill To:</strong><br>
                {{ $transaction->customer ? trim($transaction->customer->first_name . ' ' . $transaction->customer->last_name) : ($transaction->customer_name ?? 'Walk-in') }}<br>
                @if($transaction->customer?->phone)Phone: {{ $transaction->customer->phone }}<br>@endif
                @if($transaction->customer?->email)Email: {{ $transaction->customer->email }}@endif
            </td>
            <td style="width:50%; border:none; text-align:right;">
                <strong>Receipt:</strong> {{ $transaction->receipt_no }}<br>
                <strong>Date:</strong> {{ $transaction->transaction_at->format('d M Y') }}<br>
                <strong>Time:</strong> {{ $transaction->transaction_at->format('h:i A') }}<br>
                <strong>Cashier:</strong> {{ $transaction->session->user->name ?? '-' }}
            </td>
        </tr>
    </table>

    {{-- Items Table --}}
    <table>
        <thead>
            <tr>
                <th style="width:40%;">Item</th>
                <th class="text-end" style="width:15%;">Qty</th>
                <th class="text-end" style="width:20%;">Price</th>
                <th class="text-end" style="width:25%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaction->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td class="text-end">{{ number_format($item->quantity, 0) }}</td>
                <td class="text-end">Rs. {{ number_format($item->unit_price, 0) }}</td>
                <td class="text-end">Rs. {{ number_format($item->line_total, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals">
        <div class="row"><span class="label">Subtotal:</span><span class="value">Rs. {{ number_format($transaction->subtotal, 0) }}</span></div>
        @if((float)$transaction->discount_amount > 0)
        <div class="row"><span class="label">Discount:</span><span class="value">- Rs. {{ number_format($transaction->discount_amount, 0) }}</span></div>
        @endif
        <div class="row grand-total"><span class="label">Grand Total:</span><span class="value">Rs. {{ number_format($transaction->grand_total, 0) }}</span></div>
        <div class="row"><span class="label">Tendered:</span><span class="value">Rs. {{ number_format($transaction->tendered_amount, 0) }}</span></div>
        <div class="row"><span class="label">Change:</span><span class="value">Rs. {{ number_format($transaction->change_amount, 0) }}</span></div>
    </div>

    {{-- Payments --}}
    @if($transaction->payments->count())
    <div class="info" style="margin-top:15px;">
        <strong>Payment Details:</strong>
        @foreach ($transaction->payments as $payment)
        <div>{{ ucfirst($payment->method) }}: Rs. {{ number_format($payment->amount, 0) }}</div>
        @endforeach
    </div>
    @endif

    {{-- QR Code --}}
    @php
        $qrSvg = app(\App\Services\POS\POSReceiptService::class)->generateQrSvg(
            ($settings['company_name'] ?? 'Store') . "\n" .
            'Receipt: ' . $transaction->receipt_no . "\n" .
            'Date: ' . $transaction->transaction_at->format('d M Y h:i A') . "\n" .
            'Total: Rs. ' . number_format($transaction->grand_total, 2)
        );
    @endphp
    @if($qrSvg)
    <div class="qr">{!! $qrSvg !!}</div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>{{ \App\Models\Setting::getValue('pos_receipt_footer', 'Thank you for your purchase!') }}</p>
    </div>
</body>
</html>
