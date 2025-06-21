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
                        <span class="stat-value">1,248</span>
                        <span class="stat-label">Заказов</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon bg-green-100">
                        <i class="fas fa-users text-green-600"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">5,421</span>
                        <span class="stat-label">Пользователей</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon bg-purple-100">
                        <i class="fas fa-box text-purple-600"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">1,024</span>
                        <span class="stat-label">Товаров</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon bg-amber-100">
                        <i class="fas fa-wallet text-amber-600"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">$42,890</span>
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
                        <div class="order-item">
                            <div class="order-info">
                                <span class="order-id">#ORD-00789</span>
                                <span class="order-customer">Иван Петров</span>
                            </div>
                            <div class="order-details">
                                <span class="order-amount">$1,240.00</span>
                                <span class="order-status badge-success">Доставлен</span>
                            </div>
                        </div>

                        <div class="order-item">
                            <div class="order-info">
                                <span class="order-id">#ORD-00788</span>
                                <span class="order-customer">Мария Сидорова</span>
                            </div>
                            <div class="order-details">
                                <span class="order-amount">$890.50</span>
                                <span class="order-status badge-warning">В обработке</span>
                            </div>
                        </div>

                        <div class="order-item">
                            <div class="order-info">
                                <span class="order-id">#ORD-00787</span>
                                <span class="order-customer">Алексей Иванов</span>
                            </div>
                            <div class="order-details">
                                <span class="order-amount">$2,150.00</span>
                                <span class="order-status badge-success">Доставлен</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Популярные товары -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2><i class="fas fa-star mr-2"></i> Популярные товары</h2>
                    </div>
                    <div class="product-list">
                        <div class="product-item">
                            <div class="product-image">
                                <img src="https://via.placeholder.com/60" alt="Product">
                            </div>
                            <div class="product-info">
                                <span class="product-name">iPhone 14 Pro</span>
                                <span class="product-sales">128 продаж</span>
                            </div>
                        </div>

                        <div class="product-item">
                            <div class="product-image">
                                <img src="https://via.placeholder.com/60" alt="Product">
                            </div>
                            <div class="product-info">
                                <span class="product-name">Samsung Galaxy S23</span>
                                <span class="product-sales">98 продаж</span>
                            </div>
                        </div>

                        <div class="product-item">
                            <div class="product-image">
                                <img src="https://via.placeholder.com/60" alt="Product">
                            </div>
                            <div class="product-info">
                                <span class="product-name">Sony WH-1000XM5</span>
                                <span class="product-sales">76 продаж</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const salesData = {
                labels: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн'],
                datasets: [{
                    label: 'Продажи ($)',
                    data: [12000, 19000, 15000, 18000, 22000, 25000],
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            };

            const config = {
                type: 'line',
                data: salesData,
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

            new Chart(document.getElementById('salesChart'), config);
        });
    </script>
@endsection
