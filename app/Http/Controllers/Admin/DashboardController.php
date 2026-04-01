<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Отображает дашборд администратора с ключевой статистикой.
     */
    public function index(Request $request)
    {
        $totalOrders = Order::count();
        $totalUsers = User::count();
        $totalProducts = Product::count();
        $totalRevenue = (float) Order::where('status', 'completed')->sum('total');

        $latestOrders = Order::with('user')->latest()->take(5)->get();

        $topProducts = Product::select('products.*')
            ->join('order_items', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'completed')
            ->groupBy('products.id')
            ->orderByRaw('SUM(order_items.quantity) DESC')
            ->selectRaw('SUM(order_items.quantity) as total_sold')
            ->take(5)
            ->get();

        $availableYears = Order::query()
            ->selectRaw('YEAR(created_at) as year')
            ->whereNotNull('created_at')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->values();

        $selectedYear = $request->query('year');
        $selectedYear = is_numeric($selectedYear) && $availableYears->contains((int) $selectedYear)
            ? (int) $selectedYear
            : null;

        $selectedMonth = $request->query('month');
        $selectedMonth = is_numeric($selectedMonth) && (int) $selectedMonth >= 1 && (int) $selectedMonth <= 12
            ? (int) $selectedMonth
            : null;

        $sortDirection = in_array($request->query('sort'), ['asc', 'desc'], true)
            ? $request->query('sort')
            : 'asc';

        $monthsMap = [
            1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
            5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
            9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь',
        ];

        $availableMonths = collect();
        if ($selectedYear) {
            $availableMonths = Order::whereYear('created_at', $selectedYear)
                ->selectRaw('MONTH(created_at) as month')
                ->distinct()
                ->orderBy('month')
                ->pluck('month')
                ->map(fn ($month) => (int) $month)
                ->values();
        }

        if ($selectedMonth && $selectedYear && ! $availableMonths->contains($selectedMonth)) {
            $selectedMonth = null;
        }

        if (! $selectedYear) {
            $selectedMonth = null;
        }

        $salesQuery = Order::where('status', 'completed');
        if ($selectedYear) {
            $salesQuery->whereYear('created_at', $selectedYear);
        }
        if ($selectedMonth) {
            $salesQuery->whereMonth('created_at', $selectedMonth);
        }

        if ($selectedYear && $selectedMonth) {
            $rawByDay = $salesQuery
                ->selectRaw("DAY(created_at) as day_num, SUM(total) as sum")
                ->groupBy('day_num')
                ->orderBy('day_num')
                ->get()
                ->keyBy(fn ($row) => (int) $row->day_num);

            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);
            $rows = collect();
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $rows->push([
                    'period_key' => $day,
                    'period_label' => str_pad((string) $day, 2, '0', STR_PAD_LEFT),
                    'sum' => (float) ($rawByDay->get($day)->sum ?? 0),
                ]);
            }

            $salesRaw = $sortDirection === 'desc' ? $rows->reverse()->values() : $rows;
        } elseif ($selectedYear) {
            $rawByMonth = $salesQuery
                ->selectRaw("MONTH(created_at) as month_num, SUM(total) as sum")
                ->groupBy('month_num')
                ->orderBy('month_num')
                ->get()
                ->keyBy(fn ($row) => (int) $row->month_num);

            $rows = collect();
            for ($month = 1; $month <= 12; $month++) {
                $rows->push([
                    'period_key' => $month,
                    'period_label' => $monthsMap[$month] ?? ('Месяц ' . $month),
                    'sum' => (float) ($rawByMonth->get($month)->sum ?? 0),
                ]);
            }

            $salesRaw = $sortDirection === 'desc' ? $rows->reverse()->values() : $rows;
        } else {
            // Когда год не выбран, показываем по месяцам за все годы (YYYY-MM).
            $rawByMonth = $salesQuery
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period_key, DATE_FORMAT(created_at, '%Y-%m') as period_label, SUM(total) as sum")
                ->groupBy('period_key', 'period_label')
                ->orderBy('period_key', $sortDirection)
                ->get();

            $rangeQuery = Order::where('status', 'completed');
            $minDate = (clone $rangeQuery)->min('created_at');
            $maxDate = (clone $rangeQuery)->max('created_at');

            if ($minDate && $maxDate) {
                $start = Carbon::parse($minDate)->startOfMonth();
                $end = Carbon::parse($maxDate)->startOfMonth();
                $indexed = $rawByMonth->keyBy('period_key');
                $rows = collect();

                for ($cursor = $start->copy(); $cursor->lte($end); $cursor->addMonth()) {
                    $key = $cursor->format('Y-m');
                    $rows->push([
                        'period_key' => $key,
                        'period_label' => $key,
                        'sum' => (float) ($indexed->get($key)->sum ?? 0),
                    ]);
                }

                $salesRaw = $sortDirection === 'desc' ? $rows->reverse()->values() : $rows;
            } else {
                $salesRaw = $rawByMonth;
            }
        }

        $isFallbackDataUsed = false;
        if ($salesRaw->isEmpty()) {
            $salesRaw = Order::where('status', 'completed')
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period_key, DATE_FORMAT(created_at, '%Y-%m') as period_label, SUM(total) as sum")
                ->groupBy('period_key', 'period_label')
                ->orderBy('period_key', 'desc')
                ->take(6)
                ->get()
                ->sortBy('period_key')
                ->values();

            $isFallbackDataUsed = true;
        }

        $salesLabels = $salesRaw->pluck('period_label')->values();
        $salesData = $salesRaw->pluck('sum')->map(fn ($value) => (float) $value)->values();

        return view('admin.dashboard', compact(
            'totalOrders',
            'totalUsers',
            'totalProducts',
            'totalRevenue',
            'latestOrders',
            'topProducts',
            'availableYears',
            'selectedYear',
            'selectedMonth',
            'availableMonths',
            'sortDirection',
            'monthsMap',
            'salesLabels',
            'salesData',
            'isFallbackDataUsed'
        ));
    }
}
