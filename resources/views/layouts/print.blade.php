<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Print')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --print-bg: #ffffff;
            --print-text: #1e293b;
            --print-muted: #64748b;
            --print-border: #d1d5db;
            --print-primary: #1e40af;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--print-bg);
            color: var(--print-text);
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .print-wrapper {
            max-width: 210mm;
            margin: 0 auto;
            padding: 15px 25px;
        }

        .print-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 12px;
            border-bottom: 3px double var(--print-border);
            margin-bottom: 14px;
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .header-left .company-logo {
            max-width: 80px;
            max-height: 80px;
            object-fit: contain;
            border: 1px solid var(--print-border);
            border-radius: 4px;
            padding: 2px;
        }
        .header-left .company-info h2 {
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 2px;
            color: var(--print-text);
        }
        .header-left .company-info p {
            margin: 0;
            font-size: 11px;
            color: var(--print-text);
            line-height: 1.5;
        }
        .header-right {
            text-align: right;
        }
        .header-right svg,
        .header-right img {
            width: 70px;
            height: 70px;
        }
        .header-right .qr-label {
            font-size: 9px;
            color: var(--print-muted);
            margin-top: 2px;
        }

        .title-box {
            display: flex;
            justify-content: center;
            margin-bottom: 14px;
        }
        .title-box .title-inner {
            display: inline-block;
            border: 2px solid var(--print-text);
            padding: 4px 32px;
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--print-text);
        }

        .info-grid-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2px 24px;
            margin-bottom: 14px;
        }
        .info-grid-2col .row-line {
            display: flex;
            align-items: baseline;
            gap: 4px;
            padding: 2px 0;
        }
        .info-grid-2col .label {
            font-size: 11px;
            color: var(--print-muted);
            min-width: 85px;
        }
        .info-grid-2col .value {
            font-size: 12px;
            font-weight: 600;
        }
        .badge-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            margin-right: 4px;
        }
        .badge-dot.completed { background: #10b981; }
        .badge-dot.received { background: #10b981; }
        .badge-dot.draft { background: #f59e0b; }
        .badge-dot.pending { background: #f59e0b; }
        .badge-dot.cancelled { background: #ef4444; }

        .print-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .print-table thead th {
            background: #e5e7eb;
            font-size: 11px;
            font-weight: 700;
            padding: 7px 8px;
            border: 1px solid var(--print-border);
            text-align: left;
        }
        .print-table thead th.text-end { text-align: right; }
        .print-table thead th.text-center { text-align: center; }
        .print-table tbody td {
            padding: 6px 8px;
            border: 1px solid var(--print-border);
            font-size: 11px;
        }
        .print-table tbody td.text-end { text-align: right; }
        .print-table tbody td.text-center { text-align: center; }

        .bottom-section {
            display: flex;
            gap: 20px;
            margin-top: 4px;
        }
        .bottom-left {
            flex: 1;
        }
        .bottom-right {
            width: 280px;
        }

        .terms-box {
            padding: 8px 10px;
            border: 1px solid var(--print-border);
            border-radius: 4px;
            background: #f9fafb;
            font-size: 10px;
            color: var(--print-muted);
            line-height: 1.5;
        }
        .terms-box strong {
            font-size: 11px;
            color: var(--print-text);
        }

        .summary-table {
            width: 100%;
        }
        .summary-table tr td {
            padding: 3px 6px;
            font-size: 12px;
            border: none;
        }
        .summary-table tr td:last-child {
            text-align: right;
            font-weight: 600;
        }
        .summary-table tr.total-row td {
            border-top: 2px solid var(--print-text);
            padding-top: 6px;
            font-size: 14px;
            font-weight: 700;
        }
        .summary-table tr.balance-row td {
            border-top: 2px solid var(--print-border);
            padding-top: 6px;
            font-size: 14px;
            font-weight: 700;
            color: var(--print-primary);
        }
        .summary-table tr.paid-full td {
            border-top: 2px solid var(--print-border);
            padding-top: 6px;
            font-size: 13px;
            font-weight: 700;
            color: #10b981;
        }

        .print-footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid var(--print-border);
            font-size: 10px;
            color: var(--print-muted);
            text-align: center;
        }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .print-wrapper { padding: 10px 15px; }
            .no-print { display: none !important; }
            @page { margin: 12mm 15mm; }
        }
        @media screen {
            body { background: #f0f2f8; }
            .print-wrapper {
                background: #fff;
                box-shadow: 0 2px 12px rgba(0,0,0,.08);
                margin: 30px auto;
                border-radius: 6px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="print-wrapper">
        @yield('print_content')
    </div>
    @stack('scripts')
</body>
</html>
