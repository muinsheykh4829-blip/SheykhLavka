@extends('admin.layout')

@section('title', 'Пользователь: ' . $user->name)
@section('page-title', 'Детали пользователя')

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Редактировать
        </a>
        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" 
              class="d-inline" onsubmit="return confirm('Изменить статус пользователя?')">
            @csrf
            <button type="submit" 
                    class="btn {{ ($user->is_active ?? true) ? 'btn-warning' : 'btn-success' }}">
                <i class="bi bi-{{ ($user->is_active ?? true) ? 'lock' : 'unlock' }}"></i>
                {{ ($user->is_active ?? true) ? 'Заблокировать' : 'Разблокировать' }}
            </button>
        </form>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5>Информация о пользователе</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="avatar-lg bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <h4 class="mt-2">{{ $user->name }}</h4>
                    @if($user->is_active ?? true)
                        <span class="badge bg-success">Активен</span>
                    @else
                        <span class="badge bg-danger">Заблокирован</span>
                    @endif
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Email:</label>
                    <p>{{ $user->email }}</p>
                    @if($user->email_verified_at)
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> Верифицирован {{ $user->email_verified_at->format('d.m.Y') }}
                        </span>
                    @else
                        <span class="badge bg-warning">
                            <i class="bi bi-exclamation-circle"></i> Не верифицирован
                        </span>
                    @endif
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Телефон:</label>
                    <p>{{ $user->phone ?? 'Не указан' }}</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Адрес:</label>
                    <p>{{ $user->address ?? 'Не указан' }}</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Дата регистрации:</label>
                    <p>{{ $user->created_at->format('d.m.Y H:i') }}</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Последнее обновление:</label>
                    <p>{{ $user->updated_at->format('d.m.Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5>Статистика заказов</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card p-3 text-center">
                            <h3 class="text-primary">{{ $stats['total_orders'] }}</h3>
                            <p class="mb-0">Всего заказов</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card p-3 text-center">
                            <h3 class="text-success">{{ $stats['completed_orders'] }}</h3>
                            <p class="mb-0">Завершенных</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card p-3 text-center">
                            <h3 class="text-info">{{ number_format($stats['total_spent'] / 100, 2) }} сом.</h3>
                            <p class="mb-0">Потрачено</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card p-3 text-center">
                            <h3 class="text-warning">{{ number_format($stats['avg_order_value'] / 100, 2) }} сом.</h3>
                            <p class="mb-0">Средний чек</p>
                        </div>
                    </div>
                </div>
                
                @if($stats['last_order_date'])
                    <div class="mt-3">
                        <p><strong>Последний заказ:</strong> {{ $stats['last_order_date']->format('d.m.Y H:i') }}</p>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>История заказов</h5>
            </div>
            <div class="card-body">
                @if($orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Дата</th>
                                    <th>Статус</th>
                                    <th>Товаров</th>
                                    <th>Сумма</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td>{{ $order->id }}</td>
                                        <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                                        <td>
                                            @switch($order->status)
                                                @case('pending')
                                                    <span class="badge bg-warning">Ожидает</span>
                                                    @break
                                                @case('processing')
                                                    <span class="badge bg-info">Обрабатывается</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge bg-success">Завершен</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-danger">Отменен</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $order->status }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ $order->items->count() }} шт</td>
                                        <td>{{ number_format($order->total / 100, 2) }} сом.</td>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $orders->links() }}
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-cart-x display-4 text-muted"></i>
                        <h5 class="mt-3">Заказов нет</h5>
                        <p class="text-muted">Этот пользователь еще не сделал ни одного заказа</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
.avatar-lg {
    width: 80px;
    height: 80px;
    font-size: 24px;
    font-weight: bold;
}
.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    margin-bottom: 1rem;
}
</style>
@endsection
