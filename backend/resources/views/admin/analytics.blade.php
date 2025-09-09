@extends('admin.layout')

@section('title', 'Аналитика продаж')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Статистика за последние 30 дней
                </h5>
            </div>
            <div class="card-body">
                <canvas id="salesChart" width="400" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Общие продажи</h6>
                        <h3>{{ number_format($analytics['total_sales'], 2) }} сом</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Заказов сегодня</h6>
                        <h3>{{ $analytics['orders_today'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-shopping-cart fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Средний чек</h6>
                        <h3>{{ number_format($analytics['avg_order'], 2) }} сом</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calculator fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Активных клиентов</h6>
                        <h3>{{ $analytics['active_customers'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Топ продуктов</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Продукт</th>
                                <th>Продано</th>
                                <th>Выручка</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($analytics['top_products'] as $product)
                            <tr>
                                <td>{{ $product['name'] }}</td>
                                <td>{{ $product['quantity'] }} {{ $product['unit'] }}</td>
                                <td>{{ number_format($product['revenue'], 2) }} сом</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Статус заказов</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// График продаж
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($analytics['sales_dates']) !!},
        datasets: [{
            label: 'Продажи (сом)',
            data: {!! json_encode($analytics['sales_amounts']) !!},
            borderColor: '#22A447',
            backgroundColor: 'rgba(34, 164, 71, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// График статусов
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode(array_keys($analytics['order_statuses'])) !!},
        datasets: [{
            data: {!! json_encode(array_values($analytics['order_statuses'])) !!},
            backgroundColor: [
                '#22A447',
                '#FFC107', 
                '#DC3545',
                '#17A2B8'
            ]
        }]
    },
    options: {
        responsive: true
    }
});
</script>
@endpush
