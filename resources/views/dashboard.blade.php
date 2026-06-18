@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@push('styles')
<style>
    .chart-box { height: 230px; position: relative; }
    .chart-box.sm { height: 200px; }
    .progress-track { height: 6px; border-radius: 999px; background: #F3F4F6; overflow: hidden; }
    .progress-line { height: 100%; border-radius: inherit; }
    .metric-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }
    .metric-tile {
        border-radius: 12px; padding: 20px;
        position: relative; overflow: hidden;
        display: flex; flex-direction: column; gap: 6px;
        border: 1px solid rgba(0,0,0,0.04);
        transition: all 0.2s ease;
    }
    .metric-tile:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
    .metric-tile .icon-circle {
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 17px; margin-bottom: 10px;
    }
    .metric-tile .value { font-size: 22px; font-weight: 800; line-height: 1.15; word-break: break-word; color: #1F2937; }
    .metric-tile .label { font-size: 12px; font-weight: 600; color: #6B7280; }
    .metric-tile .change { font-size: 11px; font-weight: 500; margin-top: 2px; color: #9CA3AF; }
    .tile-rose { background: linear-gradient(135deg, #FFF5F6, #FFE4E6); }
    .tile-cream { background: linear-gradient(135deg, #FFFBEB, #FEF3C7); }
    .tile-mint { background: linear-gradient(135deg, #ECFDF5, #D1FAE5); }
    .tile-lilac { background: linear-gradient(135deg, #F5F3FF, #EDE9FE); }
    .tile-blue { background: linear-gradient(135deg, #EFF6FF, #DBEAFE); }
    .tile-coral { background: linear-gradient(135deg, #FFF7ED, #FFEDD5); }
    .icon-rose { background: linear-gradient(135deg, #FB7185, #F43F5E); color: #fff; }
    .icon-orange { background: linear-gradient(135deg, #FBBF24, #F59E0B); color: #fff; }
    .icon-green { background: linear-gradient(135deg, #34D399, #10B981); color: #fff; }
    .icon-purple { background: linear-gradient(135deg, #A78BFA, #8B5CF6); color: #fff; }
    .icon-blue { background: linear-gradient(135deg, #60A5FA, #3B82F6); color: #fff; }
    .icon-red { background: linear-gradient(135deg, #FB7185, #EF4444); color: #fff; }
    .stat-today {
        border-radius: 12px; padding: 18px;
        display: flex; align-items: center; gap: 14px;
        background: #F9FAFB;
        transition: all 0.2s ease;
    }
    .stat-today:hover { background: #F3F4F6; }
    .dash-table thead th {
        font-size: 11px; font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.5px;
        color: #6B7280;
        background: #F9FAFB;
        border-bottom: 2px solid #E5E7EB !important;
        padding: 12px 14px;
        white-space: nowrap;
    }
    .dash-table td { padding: 12px 14px; font-size: 13px; vertical-align: middle; border-bottom: 1px solid #F3F4F6; }
    .dash-table tbody tr { transition: background 0.15s ease; }
    .dash-table tbody tr:hover { background: #F9FAFB; }
    .stat-today .icon-circle {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }
    .stat-today .stat-value { font-size: 18px; font-weight: 800; line-height: 1.2; color: #1F2937; }
    .stat-today .stat-label { font-size: 12px; font-weight: 600; color: #6B7280; }
    .stat-today .stat-change { font-size: 11px; font-weight: 500; color: #9CA3AF; }
    .quick-stat {
        border-radius: 12px; padding: 22px;
        display: flex; flex-direction: column; gap: 6px;
        border: 1px solid #E5E7EB;
        background: #fff;
        transition: all 0.2s ease;
    }
    .quick-stat:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.06); transform: translateY(-1px); }
    .quick-stat .icon-circle { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 17px; }
    .quick-stat .value { font-size: 24px; font-weight: 800; color: #1F2937; }
    .quick-stat .label { font-size: 12px; font-weight: 600; color: #6B7280; }
    .last-card {
        border-radius: 12px; padding: 22px;
        display: flex; flex-direction: column; gap: 8px;
        border: 1px solid #E5E7EB;
        background: #fff;
        transition: all 0.2s ease;
    }
    .last-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
    .last-card .icon-circle { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 17px; }
    .last-card .value { font-size: 20px; font-weight: 800; color: #1F2937; }
    .last-card .meta { font-size: 12px; font-weight: 500; color: #6B7280; }
    @media (max-width: 1199px) { .metric-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 767px) { .metric-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
    <div class="page-title">Dashboard</div>
    <form id="periodForm" class="d-flex align-items-center gap-2">
        <select name="period" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()" aria-label="Dashboard period">
            <option value="daily"   {{ $period == 'daily' ? 'selected' : '' }}>Daily</option>
            <option value="weekly"  {{ $period == 'weekly' ? 'selected' : '' }}>Weekly</option>
            <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Monthly</option>
            <option value="yearly"  {{ $period == 'yearly' ? 'selected' : '' }}>Yearly</option>
        </select>
    </form>
</div>

    @php
        $statCards = [
            ['label' => 'Total Sales', 'value' => $stats['salesTotal'], 'tile' => 'tile-rose', 'icon' => 'bi-cart', 'iconBg' => 'icon-rose', 'change' => 'For selected period'],
            ['label' => 'Total Purchases', 'value' => $stats['purchasesTotal'], 'tile' => 'tile-cream', 'icon' => 'bi-bag', 'iconBg' => 'icon-orange', 'change' => 'Received purchases'],
            ['label' => 'Expenses', 'value' => $stats['expensesTotal'], 'tile' => 'tile-mint', 'icon' => 'bi-cash-stack', 'iconBg' => 'icon-green', 'change' => 'Operating expenses'],
            ['label' => 'Receivables', 'value' => $stats['receivables'], 'tile' => 'tile-lilac', 'icon' => 'bi-people', 'iconBg' => 'icon-purple', 'change' => 'Customer balance'],
            ['label' => 'Payables', 'value' => $stats['payables'], 'tile' => 'tile-blue', 'icon' => 'bi-credit-card-2-back', 'iconBg' => 'icon-blue', 'change' => 'Supplier dues'],
            ['label' => 'Profit / Loss', 'value' => $stats['profitLoss'], 'tile' => 'tile-coral', 'icon' => 'bi-graph-up', 'iconBg' => $stats['profitLoss'] >= 0 ? 'icon-green' : 'icon-red', 'change' => $stats['profitLoss'] >= 0 ? 'Positive margin' : 'Negative margin'],
        ];
    @endphp

    <div class="row g-3 mb-3">
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header justify-content-between">
                    <div>
                        <h6 class="card-title">Today's Sales</h6>
                        <p class="card-subtitle">Sales summary</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-light text-muted">
                        <i class="bi bi-download me-1"></i> Export
                    </button>
                </div>
                <div class="card-body">
                    <div class="metric-grid">
                        @foreach (array_slice($statCards, 0, 4) as $card)
                        <div class="metric-tile {{ $card['tile'] }}">
                            <div class="icon-circle {{ $card['iconBg'] }}"><i class="bi {{ $card['icon'] }}"></i></div>
                            <div class="value">Rs. {{ number_format($card['value'], 0) }}</div>
                            <div class="label text-muted">{{ $card['label'] }}</div>
                            <div class="change text-muted">{{ $card['change'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h6 class="card-title">Profit / Loss Trend</h6>
                        <p class="card-subtitle">Visitor insights</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-box sm">
                        <canvas id="profitChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-7 col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h6 class="card-title">Total Revenue</h6>
                        <p class="card-subtitle">Sales vs purchases vs expenses</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-box">
                        <canvas id="pnlChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5 col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h6 class="card-title">Expense Breakdown</h6>
                        <p class="card-subtitle">Expenses by account</p>
                    </div>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div class="chart-box" style="height:240px;">
                        <canvas id="expenseChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h6 class="card-title">Top Products</h6>
                        <p class="card-subtitle">Best selling products ({{ ucfirst($period) }})</p>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if ($bestSelling->count())
                    <div class="table-responsive">
                        <table class="table dash-table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Popularity</th>
                                    <th class="text-end">Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $maxQty = max(1, (float) $bestSelling->max('total_qty')); @endphp
                                @foreach ($bestSelling->take(4) as $i => $item)
                                @php
                                    $percent = min(100, round(((float) $item->total_qty / $maxQty) * 100));
                                    $colors = ['#3498db', '#2ed573', '#a55eea', '#f39c12'];
                                @endphp
                                <tr>
                                    <td class="text-muted">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                    <td class="fw-600">{{ $item->product->name ?? 'Deleted' }}</td>
                                    <td>
                                        <div class="progress-track">
                                            <div class="progress-line" style="width: {{ $percent }}%; background: {{ $colors[$i % count($colors)] }};"></div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge" style="background: {{ $colors[$i % count($colors)] }}18; color: {{ $colors[$i % count($colors)] }}; font-weight:600;">{{ $percent }}%</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted p-3 mb-0">No product sales in this period.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h6 class="card-title">Today's Overview</h6>
                        <p class="card-subtitle">Daily movement</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-4">
                            <div class="stat-today flex-column align-items-center text-center">
                                <div class="icon-circle" style="background:#e8f4fd;color:#3498db;"><i class="bi bi-cart"></i></div>
                                <div class="w-100">
                                    <div class="stat-value">Rs. {{ number_format($todayData['todaySales'], 0) }}</div>
                                    <div class="stat-label text-muted">Sales</div>
                                    <div class="stat-change text-muted">{{ $todayData['todaySaleCount'] }} transactions</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-today flex-column align-items-center text-center">
                                <div class="icon-circle" style="background:#e8f8ef;color:#27ae60;"><i class="bi bi-bag"></i></div>
                                <div class="w-100">
                                    <div class="stat-value">Rs. {{ number_format($todayData['todayPurchases'], 0) }}</div>
                                    <div class="stat-label text-muted">Purchases</div>
                                    <div class="stat-change text-muted">{{ $todayData['todayPurchaseCount'] }} transactions</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-today flex-column align-items-center text-center">
                                <div class="icon-circle" style="background:#fde8e8;color:#e74c3c;"><i class="bi bi-cash-stack"></i></div>
                                <div class="w-100">
                                    <div class="stat-value">Rs. {{ number_format($todayData['todayExpenses'], 0) }}</div>
                                    <div class="stat-label text-muted">Expenses</div>
                                    <div class="stat-change text-muted">Today</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="row g-3 h-100">
                @foreach (array_slice($statCards, 4, 2) as $card)
                <div class="col-12">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="quick-stat">
                                <div class="icon-circle {{ $card['iconBg'] }}"><i class="bi {{ $card['icon'] }}"></i></div>
                                <div class="value" style="color: var(--primary);">Rs. {{ number_format($card['value'], 0) }}</div>
                                <div class="label text-muted">{{ $card['label'] }}</div>
                                <div class="text-muted" style="font-size:11px;">{{ $card['change'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="last-card">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <div class="icon-circle" style="background:#e8f4fd;color:#3498db;"><i class="bi bi-cart-check"></i></div>
                            <h6 class="card-title mb-0">Last Sale</h6>
                        </div>
                        @if ($recentData['lastSale'])
                        <div class="meta text-muted">{{ $recentData['lastSale']->invoice_no }}</div>
                        <div class="value" style="color: var(--primary);">Rs. {{ number_format($recentData['lastSale']->total_amount, 0) }}</div>
                        <div class="meta text-muted">
                            {{ $recentData['lastSale']->customer ? $recentData['lastSale']->customer->first_name . ' ' . $recentData['lastSale']->customer->last_name : 'No customer' }}
                            <br>{{ $recentData['lastSale']->sale_date?->format('d M Y') ?? 'No date' }}
                        </div>
                        @else
                        <p class="text-muted mb-0">No sales yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="last-card">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <div class="icon-circle" style="background:#e8f8ef;color:#27ae60;"><i class="bi bi-box-seam"></i></div>
                            <h6 class="card-title mb-0">Last Purchase</h6>
                        </div>
                        @if ($recentData['lastPurchase'])
                        <div class="meta text-muted">{{ $recentData['lastPurchase']->ref_no }}</div>
                        <div class="value text-success">Rs. {{ number_format($recentData['lastPurchase']->grand_total, 0) }}</div>
                        <div class="meta text-muted">
                            {{ $recentData['lastPurchase']->supplier ? $recentData['lastPurchase']->supplier->first_name . ' ' . $recentData['lastPurchase']->supplier->last_name : 'No supplier' }}
                            <br>{{ $recentData['lastPurchase']->order_date?->format('d M Y') ?? 'No date' }}
                        </div>
                        @else
                        <p class="text-muted mb-0">No purchases yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h6 class="card-title">Recent Transactions</h6>
                        <p class="card-subtitle">Sales, purchases and expenses</p>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table dash-table mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Reference</th>
                                    <th>Name</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentData['recentSales']->take(2) as $sale)
                                <tr>
                                    <td><span class="badge bg-primary-subtle text-primary">Sale</span></td>
                                    <td><a href="{{ route('sale.show', $sale->id) }}" class="text-decoration-none">{{ $sale->invoice_no }}</a></td>
                                    <td class="text-muted">{{ $sale->customer ? $sale->customer->first_name . ' ' . $sale->customer->last_name : 'No customer' }}</td>
                                    <td class="text-end fw-600">Rs. {{ number_format($sale->total_amount, 0) }}</td>
                                </tr>
                                @empty
                                @endforelse

                                @forelse ($recentData['recentPurchases']->take(2) as $purchase)
                                <tr>
                                    <td><span class="badge bg-success-subtle text-success">Purchase</span></td>
                                    <td><a href="{{ route('purchase.show', $purchase->id) }}" class="text-decoration-none">{{ $purchase->ref_no }}</a></td>
                                    <td class="text-muted">{{ $purchase->supplier ? $purchase->supplier->first_name . ' ' . $purchase->supplier->last_name : 'No supplier' }}</td>
                                    <td class="text-end fw-600">Rs. {{ number_format($purchase->grand_total, 0) }}</td>
                                </tr>
                                @empty
                                @endforelse

                                @forelse ($recentData['recentExpenses']->take(2) as $expense)
                                <tr>
                                    <td><span class="badge bg-warning-subtle text-warning">Expense</span></td>
                                    <td>{{ $expense->title }}</td>
                                    <td class="text-muted">{{ $expense->chartOfAccount->name ?? 'No account' }}</td>
                                    <td class="text-end fw-600">Rs. {{ number_format($expense->amount, 0) }}</td>
                                </tr>
                                @empty
                                @endforelse

                                @if (!$recentData['recentSales']->count() && !$recentData['recentPurchases']->count() && !$recentData['recentExpenses']->count())
                                <tr><td colspan="4" class="text-muted text-center py-3">No recent transactions.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
$(function () {
    const chartData = @json($chartData);
    const expenseChartData = @json($expenseChart);

    const blue    = '#3498db';
    const green   = '#2ed573';
    const cyan    = '#1abc9c';
    const orange  = '#f39c12';
    const red     = '#e74c3c';
    const purple  = '#a55eea';

    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#94a3b8';

    const gridColor = '#f1f3f5';

    const ctx1 = document.getElementById('pnlChart').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [
                { label: 'Sales', data: chartData.salesData, backgroundColor: blue, borderRadius: 4, maxBarThickness: 12, borderSkipped: false },
                { label: 'Purchases', data: chartData.purchasesData, backgroundColor: green, borderRadius: 4, maxBarThickness: 12, borderSkipped: false },
                { label: 'Expenses', data: chartData.expensesData, backgroundColor: cyan, borderRadius: 4, maxBarThickness: 12, borderSkipped: false },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true, pointStyle: 'circle', padding: 16, font: { size: 11 } } }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { beginAtZero: true, grid: { color: gridColor }, ticks: { font: { size: 10 }, callback: v => 'Rs. ' + (v/1000).toFixed(0) + 'k' } }
            }
        }
    });

    const ctx2 = document.getElementById('profitChart').getContext('2d');
    const gradient = ctx2.createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0, 'rgba(165, 94, 234, 0.25)');
    gradient.addColorStop(1, 'rgba(165, 94, 234, 0.01)');
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Profit / Loss',
                data: chartData.profitData,
                borderColor: purple,
                backgroundColor: gradient,
                fill: true,
                tension: .4,
                borderWidth: 2.5,
                pointRadius: 3,
                pointBackgroundColor: chartData.profitData.map(v => v >= 0 ? green : red),
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { grid: { color: gridColor }, ticks: { font: { size: 10 }, callback: v => 'Rs. ' + (v/1000).toFixed(0) + 'k' } }
            }
        }
    });

    const ctx3 = document.getElementById('expenseChart').getContext('2d');
    new Chart(ctx3, {
        type: 'doughnut',
        data: {
            labels: expenseChartData.labels.length ? expenseChartData.labels : ['No Data'],
            datasets: [{
                data: expenseChartData.data.length ? expenseChartData.data : [1],
                backgroundColor: expenseChartData.colors ?? ['#3498db', '#2ed573', '#1abc9c', '#f39c12', '#e74c3c', '#a55eea'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, padding: 14, font: { size: 11 }, usePointStyle: true, pointStyle: 'circle' }
                }
            }
        }
    });
});
</script>
@endpush
