@extends('admin.layout')

@section('title', 'Управление складом')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Управление складом</h1>
        <div>
            <a href="{{ route('admin.inventory.report') }}" class="btn btn-info btn-sm">
                <i class="fas fa-chart-bar"></i> Отчет
            </a>
            <button class="btn btn-warning btn-sm" onclick="autoDeactivate()">
                <i class="fas fa-power-off"></i> Автодеактивация
            </button>
            <button class="btn btn-success btn-sm" onclick="autoActivate()">
                <i class="fas fa-power-off"></i> Автоактивация
            </button>
        </div>
    </div>

    <!-- Статистика -->
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">В наличии</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['in_stock_products'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Мало на складе</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['low_stock_products'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Нет в наличии</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['out_of_stock_products'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Фильтры -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Фильтры</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <label>Поиск</label>
                    <input type="text" name="search" class="form-control" 
                           value="{{ request('search') }}" placeholder="Название товара">
                </div>
                <div class="col-md-3">
                    <label>Статус склада</label>
                    <select name="stock_status" class="form-control">
                        <option value="">Все товары</option>
                        <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>
                            В наличии
                        </option>
                        <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>
                            Мало на складе
                        </option>
                        <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>
                            Нет в наличии
                        </option>
                        <option value="reserved" {{ request('stock_status') == 'reserved' ? 'selected' : '' }}>
                            Есть резерв
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">Применить</button>
                        <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">Сбросить</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Таблица товаров -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Складские остатки</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Товар</th>
                            <th>Тип</th>
                            <th>Текущий остаток</th>
                            <th>Резерв</th>
                            <th>Доступно</th>
                            <th>Минимум</th>
                            <th>Статус</th>
                            <th>Автодеактивация</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr class="
                            @if($product->stock_quantity_current <= 0) table-danger
                            @elseif($product->stock_quantity_current <= $product->stock_quantity_minimum) table-warning
                            @endif
                        ">
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($product->image)
                                        <img src="{{ $product->image }}" alt="" class="rounded" width="40" height="40">
                                    @else
                                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-image text-white"></i>
                                        </div>
                                    @endif
                                    <div class="ml-2">
                                        <div class="font-weight-bold">{{ $product->name }}</div>
                                        @if($product->category)
                                            <small class="text-muted">{{ $product->category->name }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($product->product_type == 'piece')
                                    <span class="badge badge-primary">Штучный</span>
                                @elseif($product->product_type == 'weight')
                                    <span class="badge badge-info">Весовой</span>
                                @elseif($product->product_type == 'package')
                                    <span class="badge badge-secondary">Упаковка</span>
                                @else
                                    <span class="badge badge-light">Не указан</span>
                                @endif
                            </td>
                            <td>{{ $product->stock_quantity_current ?? 0 }}</td>
                            <td>{{ $product->stock_quantity_reserved ?? 0 }}</td>
                            <td>{{ ($product->stock_quantity_current ?? 0) - ($product->stock_quantity_reserved ?? 0) }}</td>
                            <td>{{ $product->stock_quantity_minimum ?? 0 }}</td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge badge-success">Активен</span>
                                @else
                                    <span class="badge badge-danger">Неактивен</span>
                                @endif
                            </td>
                            <td>
                                @if($product->auto_deactivate_on_zero)
                                    <i class="fas fa-check text-success" title="Включена"></i>
                                @else
                                    <i class="fas fa-times text-danger" title="Выключена"></i>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.inventory.edit', $product) }}" 
                                       class="btn btn-primary" title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.inventory.movements', $product) }}" 
                                       class="btn btn-info" title="История">
                                        <i class="fas fa-history"></i>
                                    </a>
                                    <button class="btn btn-success" onclick="showRestockModal({{ $product->id }})" 
                                            title="Пополнить">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">Товары не найдены</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Пагинация -->
            {{ $products->links() }}
        </div>
    </div>
</div>

<!-- Модальное окно пополнения -->
<div class="modal fade" id="restockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Пополнение склада</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="restockForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Количество для добавления</label>
                        <input type="number" name="quantity" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Причина пополнения</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="Описание причины пополнения"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-success">Пополнить</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showRestockModal(productId) {
    const form = document.getElementById('restockForm');
    form.action = `/admin/inventory/${productId}/restock`;
    $('#restockModal').modal('show');
}

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
