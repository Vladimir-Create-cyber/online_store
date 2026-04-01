@extends('admin.layouts.app')

@section('content')
    <div class="dashboard-container">
        <!-- Заголовок панели управления -->
        <div class="dashboard-header mb-5">
            <h1 class="dashboard-title">Панель управления</h1>
        </div>

        <!-- Статистика -->
        <div class="dashboard-stats mb-6">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon bg-blue-100">
                        <i class="fas fa-shopping-cart text-blue-600"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ number_format($totalOrders, 0, '', ' ') }}</span>
                        <span class="stat-label">Заказов</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon bg-green-100">
                        <i class="fas fa-users text-green-600"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ number_format($totalUsers, 0, '', ' ') }}</span>
                        <span class="stat-label">Пользователей</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon bg-purple-100">
                        <i class="fas fa-box text-purple-600"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ number_format($totalProducts, 0, '', ' ') }}</span>
                        <span class="stat-label">Товаров</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon bg-amber-100">
                        <i class="fas fa-wallet text-amber-600"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ number_format($totalRevenue, 2, ',', ' ') }} грн</span>
                        <span class="stat-label">Доход</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Основной контент -->
        <div class="dashboard-content">
            <!-- График продаж -->
            <div class="dashboard-section mb-6">
                <div class="section-header">
                    <h2><i class="fas fa-chart-line mr-2"></i> Статистика продаж</h2>
                </div>
                <form method="GET" action="{{ route('admin.dashboard') }}" class="chart-filters">
                    <label>
                        Год:
                        <select name="year">
                            <option value="">Все</option>
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" {{ (string)$selectedYear === (string)$year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        Месяц:
                        <select name="month" {{ $selectedYear ? '' : 'disabled' }}>
                            <option value="">Все</option>
                            @foreach($monthsMap as $monthNumber => $monthName)
                                <option value="{{ $monthNumber }}"
                                    {{ (string)$selectedMonth === (string)$monthNumber ? 'selected' : '' }}
                                    {{ $selectedYear && $availableMonths->contains($monthNumber) ? '' : 'disabled' }}>
                                    {{ $monthName }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        Сортировка:
                        <select name="sort">
                            <option value="asc" {{ $sortDirection === 'asc' ? 'selected' : '' }}>По возрастанию</option>
                            <option value="desc" {{ $sortDirection === 'desc' ? 'selected' : '' }}>По убыванию</option>
                        </select>
                    </label>
                    <button type="submit" class="btn-filter">Применить</button>
                </form>
                @if($isFallbackDataUsed)
                    <p class="chart-fallback-note">
                        По выбранным фильтрам данных нет. Показаны последние доступные продажи.
                    </p>
                @endif
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Нижняя часть с заказами и товарами -->
            <div class="dashboard-grid">
                <!-- Последние заказы -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2><i class="fas fa-clock mr-2"></i> Последние заказы</h2>
                    </div>
                    <div class="order-list">
                        @forelse($latestOrders as $order)
                            <div class="order-item">
                                <div class="order-info">
                                    <span class="order-id">#{{ $order->id }}</span>
                                    <span class="order-customer">{{ optional($order->user)->name ?? 'Гость' }}</span>
                                </div>
                                <div class="order-details">
                                    <span class="order-amount">{{ number_format($order->total, 2, ',', ' ') }} грн</span>
                                    <span class="order-status badge-{{ $order->status }}">{{ $order->status_text }}</span>
                                </div>
                            </div>
                        @empty
                            <p>Заказов пока нет.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Популярные товары -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2><i class="fas fa-star mr-2"></i> Популярные товары</h2>
                    </div>
                    <div class="product-list">
                        @forelse($topProducts as $product)
                            <div class="product-item">
                                <div class="product-image">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                    @else
                                        <img src="{{ asset('images/placeholder.png') }}" alt="{{ $product->name }}">
                                    @endif
                                </div>
                                <div class="product-info">
                                    <span class="product-name">{{ $product->name }}</span>
                                    <span class="product-sales">{{ $product->total_sold }} продаж</span>
                                </div>
                            </div>
                        @empty
                            <p>Пока нет данных по продажам товаров.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const salesLabels = @json($salesLabels);
            const salesData = @json($salesData);
            const chartData = {
                labels: salesLabels,
                datasets: [{
                    label: 'Продажи (грн)',
                    data: salesData,
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            };

            const config = {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(226, 232, 240, 0.5)' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            };

            const chartEl = document.getElementById('salesChart');
            if (chartEl) {
                new Chart(chartEl, config);
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        .chart-filters { display: flex; flex-wrap: wrap; gap: 12px; align-items: end; margin-bottom: 14px; }
        .chart-filters label { display: flex; flex-direction: column; gap: 4px; font-size: 14px; color: #475569; }
        .chart-filters select { min-width: 160px; height: 36px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 10px; background: #fff; }
        .chart-filters select:disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; }
        .btn-filter { height: 36px; border: none; background: #3b82f6; color: #fff; border-radius: 8px; padding: 0 14px; cursor: pointer; }
        .chart-fallback-note { margin: 2px 0 10px; color: #b45309; font-size: 13px; }
    </style>
@endpush
