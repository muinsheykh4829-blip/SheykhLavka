@extends('admin.layout')

@section('title', 'Заказы')
@section('page-title', 'Управление заказами')

@section('content')
<!-- Общая статистика -->
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h4>{{ $statistics['total_orders'] ?? 0 }}</h4>
                <p class="mb-0">Всего заказов</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4>{{ $statistics['today_orders'] ?? 0 }}</h4>
                <p class="mb-0">Заказов сегодня</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4>{{ format_somoni($statistics['total_revenue'] ?? 0) }}</h4>
                <p class="mb-0">Общий доход</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h4>{{ format_somoni($statistics['today_revenue'] ?? 0) }}</h4>
                <p class="mb-0">Доход сегодня</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <!-- Статистика заказов -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-warning">{{ $statistics['processing_orders'] ?? 0 }}</h5>
                                <p class="card-text small">В обработке</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-info">{{ $statistics['accepted_orders'] ?? 0 }}</h5>
                                <p class="card-text small">Принятые</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-primary">{{ $statistics['preparing_orders'] ?? 0 }}</h5>
                                <p class="card-text small">Собираются</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-info">{{ $statistics['ready_orders'] ?? 0 }}</h5>
                                <p class="card-text small">Собраны</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-primary">{{ $statistics['delivering_orders'] ?? 0 }}</h5>
                                <p class="card-text small">В пути</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-success">{{ $statistics['delivered_orders'] ?? 0 }}</h5>
                                <p class="card-text small">Завершены</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-danger">{{ $statistics['cancelled_orders'] ?? 0 }}</h5>
                                <p class="card-text small">Отменены</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Фильтры -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Статус</label>
                        <select name="status" class="form-select">
                            <option value="">Все статусы</option>
                            <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>В обработке</option>
                            <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Принятые</option>
                            <option value="preparing" {{ request('status') === 'preparing' ? 'selected' : '' }}>Собираются</option>
                            <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>Собраны</option>
                            <option value="delivering" {{ request('status') === 'delivering' ? 'selected' : '' }}>В пути</option>
                            <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Завершены</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Отменены</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Поиск</label>
                        <input type="text" name="search" class="form-control" placeholder="№ заказа, имя, телефон" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Дата с</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Дата до</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Фильтр</button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Сброс</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Список заказов -->
        <div class="card">
            <div class="card-header">
                <h5>Все заказы ({{ $orders->total() }})</h5>
            </div>
            <div class="card-body">
                @if($orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'order_number', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-decoration-none text-dark">
                                            № заказа
                                            @if(request('sort_by') === 'order_number')
                                                <i class="bi bi-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Клиент</th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'status', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-decoration-none text-dark">
                                            Статус
                                            @if(request('sort_by') === 'status')
                                                <i class="bi bi-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'total', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-decoration-none text-dark">
                                            Сумма
                                            @if(request('sort_by') === 'total')
                                                <i class="bi bi-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-decoration-none text-dark">
                                            Дата
                                            @if(request('sort_by') === 'created_at' || !request('sort_by'))
                                                <i class="bi bi-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr>
                                    <td>
                                        <strong>{{ $order->order_number }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $order->items->count() }} товар(ов)</small>
                                    </td>
                                    <td>
                                        @if($order->user)
                                            {{ $order->user->first_name }} {{ $order->user->last_name }}
                                            <br><small>{{ $order->user->phone }}</small>
                                        @else
                                            {{ $order->delivery_name ?: 'Гость' }}
                                            <br><small>{{ $order->delivery_phone }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $order->status_color }}">{{ $order->status_name }}</span>
                                        
                                        <!-- Быстрое изменение статуса -->
                                        <div class="mt-1">
                                            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="d-inline quick-status-form">
                                                @csrf
                                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="font-size: 0.75rem;">
                                                    <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>В обработке</option>
                                                    <option value="accepted" {{ $order->status === 'accepted' ? 'selected' : '' }}>Принят</option>
                                                    <option value="preparing" {{ $order->status === 'preparing' ? 'selected' : '' }}>Собирается</option>
                                                    <option value="ready" {{ $order->status === 'ready' ? 'selected' : '' }}>Собран</option>
                                                    <option value="delivering" {{ $order->status === 'delivering' ? 'selected' : '' }}>Курьер в пути</option>
                                                    <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Завершен</option>
                                                    <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Отменен</option>
                                                </select>
                                            </form>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ format_somoni($order->total) }}</strong>
                                        @if($order->delivery_fee > 0)
                                            <br><small class="text-muted">вкл. доставка {{ format_somoni($order->delivery_fee) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ tj_date($order->created_at) }}
                                        <br><small class="text-muted">{{ tj_date_human($order->created_at) }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Просмотр
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $orders->links() }}
                @else
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Заказы не найдены
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
// Добавляем подтверждение при изменении статуса
document.addEventListener('DOMContentLoaded', function() {
    const statusSelects = document.querySelectorAll('.quick-status-form select');
    
    statusSelects.forEach(select => {
        const originalValue = select.value;
        
        select.addEventListener('change', function(e) {
            if (!confirm('Изменить статус заказа?')) {
                e.preventDefault();
                this.value = originalValue;
                return false;
            }
        });
    });
});
</script>
@endsection
