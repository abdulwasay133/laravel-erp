<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\SaleItem;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'monthly');

        $stats = $this->getStats($period);
        $chartData = $this->getChartData($period);
        $todayData = $this->getTodayData();
        $recentData = $this->getRecentData();
        $bestSelling = $this->getBestSellingProducts($period);
        $expenseChart = $this->getExpenseChartData($period);

        if ($request->ajax()) {
            return response()->json(compact(
                'stats', 'chartData', 'todayData', 'recentData',
                'bestSelling', 'expenseChart', 'period'
            ));
        }

        return view('dashboard', compact(
            'stats', 'chartData', 'todayData', 'recentData',
            'bestSelling', 'expenseChart', 'period'
        ));
    }

    private function getDateRange($period): array
    {
        $now = Carbon::now();
        return match ($period) {
            'daily'   => ['start' => $now->copy()->startOfDay(), 'end' => $now->copy()->endOfDay()],
            'weekly'  => ['start' => $now->copy()->startOfWeek(), 'end' => $now->copy()->endOfWeek()],
            'yearly'  => ['start' => $now->copy()->startOfYear(), 'end' => $now->copy()->endOfYear()],
            default   => ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfMonth()],
        };
    }

    private function getStats($period): array
    {
        $dates = $this->getDateRange($period);

        $salesTotal = (float) Sale::whereBetween('sale_date', [$dates['start'], $dates['end']])
            ->sum('total_amount');

        $purchasesTotal = (float) Purchase::whereBetween('order_date', [$dates['start'], $dates['end']])
            ->where('status', 'received')
            ->sum('grand_total');

        $expensesTotal = (float) Expense::whereBetween('expense_date', [$dates['start'], $dates['end']])
            ->sum('amount');

        $receivables = (float) Customer::sum('balance');

        $payables = (float) Purchase::where('due_amount', '>', 0)->sum('due_amount');

        $profitLoss = $salesTotal - ($purchasesTotal + $expensesTotal);

        return compact('salesTotal', 'purchasesTotal', 'expensesTotal', 'receivables', 'payables', 'profitLoss');
    }

    private function getTodayData(): array
    {
        $today = Carbon::today();

        return [
            'todaySales'        => (float) Sale::where('sale_date', $today)->sum('total_amount'),
            'todayPurchases'    => (float) Purchase::where('order_date', $today)->sum('grand_total'),
            'todayExpenses'     => (float) Expense::where('expense_date', $today)->sum('amount'),
            'todaySaleCount'    => Sale::where('sale_date', $today)->count(),
            'todayPurchaseCount'=> Purchase::where('order_date', $today)->count(),
        ];
    }

    private function getRecentData(): array
    {
        $lastSale = Sale::with('customer')->orderByDesc('created_at')->first();
        $lastPurchase = Purchase::with('supplier')->orderByDesc('created_at')->first();

        $recentSales = Sale::with('customer')->orderByDesc('created_at')->take(5)->get();
        $recentPurchases = Purchase::with('supplier')->orderByDesc('created_at')->take(5)->get();
        $recentExpenses = Expense::with('chartOfAccount')->orderByDesc('created_at')->take(5)->get();

        return compact('lastSale', 'lastPurchase', 'recentSales', 'recentPurchases', 'recentExpenses');
    }

    private function getBestSellingProducts($period)
    {
        $dates = $this->getDateRange($period);

        return SaleItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(line_total) as total_amount')
            )
            ->whereHas('sale', function ($query) use ($dates) {
                $query->whereBetween('sale_date', [$dates['start'], $dates['end']]);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(10)
            ->with('product')
            ->get();
    }

    private function getChartData($period): array
    {
        $now = Carbon::now();

        switch ($period) {
            case 'daily':
                $interval = 'day';
                $labels = [];
                $salesData = [];
                $purchasesData = [];
                $expensesData = [];
                $profitData = [];
                for ($i = 6; $i >= 0; $i--) {
                    $date = $now->copy()->subDays($i);
                    $key = $date->toDateString();
                    $labels[] = $date->format('D');
                    $s = (float) Sale::where('sale_date', $key)->sum('total_amount');
                    $p = (float) Purchase::where('order_date', $key)->sum('grand_total');
                    $e = (float) Expense::where('expense_date', $key)->sum('amount');
                    $salesData[] = $s;
                    $purchasesData[] = $p;
                    $expensesData[] = $e;
                    $profitData[] = $s - $p - $e;
                }
                break;
            case 'weekly':
                $interval = 'week';
                $labels = [];
                $salesData = [];
                $purchasesData = [];
                $expensesData = [];
                $profitData = [];
                for ($i = 3; $i >= 0; $i--) {
                    $start = $now->copy()->subWeeks($i)->startOfWeek();
                    $end = $now->copy()->subWeeks($i)->endOfWeek();
                    $labels[] = 'W' . $now->copy()->subWeeks($i)->format('W');
                    $s = (float) Sale::whereBetween('sale_date', [$start, $end])->sum('total_amount');
                    $p = (float) Purchase::whereBetween('order_date', [$start, $end])->sum('grand_total');
                    $e = (float) Expense::whereBetween('expense_date', [$start, $end])->sum('amount');
                    $salesData[] = $s;
                    $purchasesData[] = $p;
                    $expensesData[] = $e;
                    $profitData[] = $s - $p - $e;
                }
                break;
            case 'yearly':
                $interval = 'year';
                $labels = [];
                $salesData = [];
                $purchasesData = [];
                $expensesData = [];
                $profitData = [];
                for ($i = 4; $i >= 0; $i--) {
                    $year = $now->copy()->subYears($i)->format('Y');
                    $labels[] = $year;
                    $s = (float) Sale::whereYear('sale_date', $year)->sum('total_amount');
                    $p = (float) Purchase::whereYear('order_date', $year)->sum('grand_total');
                    $e = (float) Expense::whereYear('expense_date', $year)->sum('amount');
                    $salesData[] = $s;
                    $purchasesData[] = $p;
                    $expensesData[] = $e;
                    $profitData[] = $s - $p - $e;
                }
                break;
            default: // monthly
                $interval = 'month';
                $labels = [];
                $salesData = [];
                $purchasesData = [];
                $expensesData = [];
                $profitData = [];
                for ($i = 11; $i >= 0; $i--) {
                    $date = $now->copy()->subMonths($i);
                    $labels[] = $date->format('M Y');
                    $month = $date->format('m');
                    $year = $date->format('Y');
                    $s = (float) Sale::whereYear('sale_date', $year)->whereMonth('sale_date', $month)->sum('total_amount');
                    $p = (float) Purchase::whereYear('order_date', $year)->whereMonth('order_date', $month)->sum('grand_total');
                    $e = (float) Expense::whereYear('expense_date', $year)->whereMonth('expense_date', $month)->sum('amount');
                    $salesData[] = $s;
                    $purchasesData[] = $p;
                    $expensesData[] = $e;
                    $profitData[] = $s - $p - $e;
                }
                break;
        }

        return compact('labels', 'salesData', 'purchasesData', 'expensesData', 'profitData', 'interval');
    }

    private function getExpenseChartData($period): array
    {
        $dates = $this->getDateRange($period);

        $expenses = Expense::select(
                'chart_of_account_id',
                DB::raw('SUM(amount) as total')
            )
            ->whereBetween('expense_date', [$dates['start'], $dates['end']])
            ->groupBy('chart_of_account_id')
            ->with('chartOfAccount')
            ->get();

        $labels = $expenses->pluck('chartOfAccount.name', 'chart_of_account_id')->values()->toArray();
        $data = $expenses->pluck('total')->map(fn ($v) => (float) $v)->toArray();
        $colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69', '#2e59d9', '#17a673', '#2c9faf'];

        return compact('labels', 'data', 'colors');
    }
}
