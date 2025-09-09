@extends('admin.layout')

@section('title', 'Складские отчеты')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Складские отчеты</h1>
        <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> К управлению складом
        </a>
    </div>

    <!-- Общая статистика -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Всего товаров</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_products'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Активные товары</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_products'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Общий остаток</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_stock_value'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-warehouse fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">В резерве</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['reserved_stock_value'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-lock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <!-- Товары с низким остатком -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-warning">Товары с низким остатком</h6>
                    <span class="badge badge-warning">{{ $lowStockProducts->count() }}</span>
                </div>
                <div class="card-body">
                    @forelse($lowStockProducts->take(10) as $product)
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 
                                    @if(!$loop->last) border-bottom @endif">
                            <div class="d-flex align-items-center">
                                @if($product->image)
                                    <img src="{{ $product->image }}" alt="" class="rounded mr-2" width="30" height="30">
                                @endif
                                <div>
                                    <div class="font-weight-bold">{{ $product->name }}</div>
                                    @if($product->category)
                                        <small class="text-muted">{{ $product->category->name }}</small>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-weight-bold text-warning">
                                    {{ $product->stock_quantity_current }} / {{ $product->stock_quantity_minimum }}
                                </div>
                                <small class="text-muted">остаток / минимум</small>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">Товаров с низким остатком нет</p>
                    @endforelse
                    
                    @if($lowStockProducts->count() > 10)
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.inventory.index', ['stock_status' => 'low_stock']) }}" 
                               class="btn btn-sm btn-warning">
                                Показать все ({{ $lowStockProducts->count() }})
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <!-- Товары без остатка -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-danger">Товары без остатка</h6>
                    <span class="badge badge-danger">{{ $outOfStockProducts->count() }}</span>
                </div>
                <div class="card-body">
                    @forelse($outOfStockProducts->take(10) as $product)
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 
                                    @if(!$loop->last) border-bottom @endif">
                            <div class="d-flex align-items-center">
                                @if($product->image)
                                    <img src="{{ $product->image }}" alt="" class="rounded mr-2" width="30" height="30">
                                @endif
                                <div>
                                    <div class="font-weight-bold">{{ $product->name }}</div>
                                    @if($product->category)
                                        <small class="text-muted">{{ $product->category->name }}</small>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                @if($product->is_active)
                                    <span class="badge badge-success">Активен</span>
                                @else
                                    <span class="badge badge-danger">Неактивен</span>
                                @endif
                                <br>
                                <small class="text-muted">{{ $product->stock_quantity_current }} шт</small>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">Товаров без остатка нет</p>
                    @endforelse
                    
                    @if($outOfStockProducts->count() > 10)
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.inventory.index', ['stock_status' => 'out_of_stock']) }}" 
                               class="btn btn-sm btn-danger">
                                Показать все ({{ $outOfStockProducts->count() }})
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Диаграмма распределения товаров -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Распределение по статусу склада</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="stockStatusChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <div class="row">
                            <div class="col-6">
                                <span class="mr-2">
                                    <i class="fas fa-circle text-success"></i>
                                    В наличии: {{ $stats['in_stock_products'] }}
                                </span>
                            </div>
                            <div class="col-6">
                                <span class="mr-2">
                                    <i class="fas fa-circle text-warning"></i>
                                    Мало: {{ $stats['low_stock_products'] }}
                                </span>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <span class="mr-2">
                                    <i class="fas fa-circle text-danger"></i>
                                    Нет в наличии: {{ $stats['out_of_stock_products'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Действия</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <button class="list-group-item list-group-item-action" onclick="autoDeactivate()">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">
                                    <i class="fas fa-power-off text-warning"></i>
                                    Автоматическая деактивация
                                </h6>
                            </div>
                            <p class="mb-1">Деактивировать все товары с нулевым остатком</p>
                            <small>{{ $outOfStockProducts->where('is_active', true)->count() }} товаров будет деактивировано</small>
                        </button>
                        
                        <button class="list-group-item list-group-item-action" onclick="autoActivate()">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">
                                    <i class="fas fa-power-off text-success"></i>
                                    Автоматическая активация
                                </h6>
                            </div>
                            <p class="mb-1">Активировать товары с ненулевым остатком</p>
                            <small>Товары с остатком > 0 будут активированы</small>
                        </button>
                        
                        <a href="{{ route('admin.inventory.index') }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">
                                    <i class="fas fa-warehouse text-primary"></i>
                                    Управление складом
                                </h6>
                            </div>
                            <p class="mb-1">Перейти к детальному управлению складскими запасами</p>
                        </a>
                        
                        <a href="{{ route('admin.products.create') }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">
                                    <i class="fas fa-plus text-success"></i>
                                    Добавить товар
                                </h6>
                            </div>
                            <p class="mb-1">Добавить новый товар с настройками склада</p>
                        </a>
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
// Диаграмма статуса склада
var ctx = document.getElementById("stockStatusChart");
var myPieChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ["В наличии", "Мало на складе", "Нет в наличии"],
        datasets: [{
            data: [
                {{ $stats['in_stock_products'] }}, 
                {{ $stats['low_stock_products'] }}, 
                {{ $stats['out_of_stock_products'] }}
            ],
            backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
            hoverBackgroundColor: ['#17a673', '#f4b619', '#d73925'],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }],
    },
    options: {
        maintainAspectRatio: false,
        tooltips: {
            backgroundColor: "rgb(255,255,255)",
            bodyFontColor: "#858796",
            borderColor: '#dddfeb',
            borderWidth: 1,
            xPadding: 15,
            yPadding: 15,
            displayColors: false,
            caretPadding: 10,
        },
        legend: {
            display: false
        },
        cutoutPercentage: 80,
    },
});

function autoDeactivate() {
    if (confirm('Вы уверены, что хотите деактивировать все товары с нулевым остатком?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.inventory.auto-deactivate") }}';
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }
}

function autoActivate() {
    if (confirm('Вы уверены, что хотите активировать все товары с ненулевым остатком?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.inventory.auto-activate") }}';
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
