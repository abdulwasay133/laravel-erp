{{-- Thermal receipt — rendered for printing --}}
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Receipt</title>
    <style>
        @page { margin: 0; size: 80mm auto; }
        body {
            font-family: 'Courier New', 'Courier', monospace;
            font-size: 12px;
            width: 72mm;
            margin: 0 auto;
            padding: 8px 3mm;
            line-height: 1.35;
        }
        .center { text-align: center; }
        .line-dash { border-top: 1px dashed #333; margin: 5px 0; }
        .line-solid { border-top: 1px solid #333; margin: 5px 0; }
        .line-double { border-top: 3px double #333; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 1px 0; vertical-align: top; }
        .right { text-align: right; }
        .bold { font-weight: 700; }
        .company-name { font-size: 16px; font-weight: 700; text-align: center; }
        .receipt-label { font-size: 14px; font-weight: 700; text-align: center; letter-spacing: 2px; }
        .info-label { color: #555; }
        .footer { text-align: center; margin-top: 8px; font-size: 10px; }
        .qr { text-align: center; margin: 8px 0; }
        .qr svg { width: 120px; height: 120px; }
        .grand-total { font-size: 16px; font-weight: 800; }
    </style>
</head>
<body>
    @php
        $thermal = app(\App\Services\POS\POSReceiptService::class)->getThermalData($transaction);
        $companyName = \App\Models\Setting::getValue('company_name', 'Store');
        $companyAddress = \App\Models\Setting::getValue('company_address', '');
        $companyPhone = \App\Models\Setting::getValue('company_phone', '');
        $footer = \App\Models\Setting::getValue('pos_receipt_footer', 'Thank you!');
        $customerName = $transaction->customer
            ? trim($transaction->customer->first_name . ' ' . $transaction->customer->last_name)
            : ($transaction->customer_name ?? 'Walk-in');
    @endphp

    {{-- Company Header --}}
    <div class="company-name">{{ $companyName }}</div>
    @if($companyAddress)
        <div class="center" style="font-size:10px;">{!! nl2br(e($companyAddress)) !!}</div>
    @endif
    @if($companyPhone)
        <div class="center" style="font-size:10px;">Tel: {{ $companyPhone }}</div>
    @endif

    <div class="line-double"></div>
    <div class="receipt-label">SALE INVOICE</div>
    <div class="line-double"></div>

    {{-- Info --}}
    <table>
        <tr><td class="info-label">Receipt:</td><td class="right">{{ $transaction->receipt_no }}</td></tr>
        <tr><td class="info-label">Date:</td><td class="right">{{ $transaction->transaction_at->format('d M Y h:i A') }}</td></tr>
        <tr><td class="info-label">Cashier:</td><td class="right">{{ $transaction->session->user->name ?? '-' }}</td></tr>
        <tr><td class="info-label">Customer:</td><td class="right">{{ $customerName }}</td></tr>
        @if($transaction->customer?->phone)
        <tr><td class="info-label">Phone:</td><td class="right">{{ $transaction->customer->phone }}</td></tr>
        @endif
    </table>

    <div class="line-dash"></div>

    {{-- Items --}}
    <table>
        <tr style="font-weight:600; font-size:10px;">
            <td style="width:40%;">Item</td>
            <td class="right" style="width:15%;">Qty</td>
            <td class="right" style="width:20%;">Price</td>
            <td class="right" style="width:25%;">Total</td>
        </tr>
        <div class="line-dash" style="margin:2px 0;"></div>
        @foreach ($transaction->items as $item)
        <tr>
            <td>{{ $item->product_name }}</td>
            <td class="right">{{ number_format($item->quantity, 0) }}</td>
            <td class="right">{{ number_format($item->unit_price, 0) }}</td>
            <td class="right">{{ number_format($item->line_total, 0) }}</td>
        </tr>
        @endforeach
    </table>

    <div class="line-dash"></div>

    {{-- Totals --}}
    <table>
        <tr><td>Subtotal</td><td class="right">{{ number_format($transaction->subtotal, 0) }}</td></tr>
        @if((float)$transaction->discount_amount > 0)
        <tr><td>Discount</td><td class="right">- {{ number_format($transaction->discount_amount, 0) }}</td></tr>
        @endif
    </table>
    <div class="line-double"></div>
    <table>
        <tr class="grand-total"><td>GRAND TOTAL</td><td class="right">Rs. {{ number_format($transaction->grand_total, 0) }}</td></tr>
    </table>
    <div class="line-double"></div>
    <table>
        <tr><td>Tendered</td><td class="right">{{ number_format($transaction->tendered_amount, 0) }}</td></tr>
        <tr><td>Change</td><td class="right">{{ number_format($transaction->change_amount, 0) }}</td></tr>
        @foreach ($transaction->payments as $payment)
        <tr><td>Paid via {{ ucfirst($payment->method) }}</td><td class="right">{{ number_format($payment->amount, 0) }}</td></tr>
        @endforeach
    </table>

    <div class="line-dash"></div>

    {{-- QR Code --}}
    @if(!empty($thermal['qr_svg']))
    <div class="qr">{!! $thermal['qr_svg'] !!}</div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        @foreach(explode("\n", $footer) as $footerLine)
            @if(trim($footerLine))
            <div>{{ trim($footerLine) }}</div>
            @endif
        @endforeach
    </div>

    <div class="line-dash"></div>

    <script>
        window.onload = function () {
            window.print();
            setTimeout(() => window.close(), 500);
        };
    </script>
</body>
</html>
