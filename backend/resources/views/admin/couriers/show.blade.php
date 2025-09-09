@extends('admin.layout')

@section('title', 'Профиль курьера')

@section('content')
<div class="container-fluid">
    <!-- Хлебные крошки -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-home"></i> Главная
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.couriers.index') }}">Курьеры</a>
            </li>
            <li class="breadcrumb-item active">{{ $courier->name }}</li>
        </ol>
    </nav>

    <!-- Заголовок -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center mr-3" 
                 style="width: 60px; height: 60px; font-size: 24px;">
                {{ strtoupper(substr($courier->name, 0, 1)) }}
            </div>
            <div>
                <h1 class="h3 mb-0 text-gray-800">{{ $courier->name }}</h1>
                <p class="text-muted mb-0">
                    <i class="fas fa-user-tag mr-1"></i>
                    {{ $courier->login }}
                    @if($courier->is_active)
                        <span class="badge badge-success ml-2">Активен</span>
                    @else
                        <span class="badge badge-secondary ml-2">Неактивен</span>
                    @endif
                </p>
            </div>
        </div>
        <div>
            <a href="{{ route('admin.couriers.edit', $courier) }}" class="btn btn-primary mr-2">
                <i class="fas fa-edit mr-1"></i>
                Редактировать
            </a>
            <a href="{{ route('admin.couriers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Назад к списку
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Основная информация -->
        <div class="col-lg-8">
            <!-- Личные данные -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user mr-1"></i>
                        Личные данные
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">Имя</label>
                                <div class="h5">{{ $courier->name }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Телефон</label>
                                <div class="h6">
                                    <i class="fas fa-phone mr-1"></i>
                                    {{ $courier->phone }}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Логин</label>
                                <div class="h6">
                                    <i class="fas fa-user-tag mr-1"></i>
                                    {{ $courier->login }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                                <!-- Фамилия удалена: используем одно поле name -->
                            <div class="mb-3">
                                <label class="text-muted small">Email</label>
                                <div class="h6">
                                    <i class="fas fa-envelope mr-1"></i>
                                    {{ $courier->email ?: 'Не указан' }}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Дата регистрации</label>
                                <div class="h6">
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ $courier->created_at->format('d.m.Y H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Транспорт -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-motorcycle mr-1"></i>
                        Транспорт
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">Тип транспорта</label>
                                <div class="h6">
                                    @switch($courier->vehicle_type)
                                        @case('bicycle')
                                            🚲 Велосипед
                                            @break
                                        @case('motorcycle')
                                            🏍️ Мотоцикл
                                            @break
                                        @case('car')
                                            🚗 Автомобиль
                                            @break
                                        @case('walking')
                                            🚶 Пешком
                                            @break
                                        @default
                                            Не указан
                                    @endswitch
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">Номер транспорта</label>
                                <div class="h6">
                                    <i class="fas fa-hashtag mr-1"></i>
                                    {{ $courier->vehicle_number ?: 'Не указан' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- История заказов -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-history mr-1"></i>
                        Последние заказы
                    </h6>
                </div>
                <div class="card-body">
                    @if($courier->assignedOrders->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">У этого курьера пока нет назначенных заказов</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Дата</th>
                                        <th>Клиент</th>
                                        <th>Сумма</th>
                                        <th>Статус</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($courier->assignedOrders->sortByDesc('created_at')->take(10) as $order)
                                    <tr>
                                        <td>#{{ $order->id }}</td>
                                        <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                                        <td>{{ $order->customer_name ?? 'Не указан' }}</td>
                                        <td>{{ number_format($order->total_amount, 0, ',', ' ') }} сум</td>
                                        <td>
                                            @switch($order->status)
                                                @case('pending')
                                                    <span class="badge badge-warning">Ожидает</span>
                                                    @break
                                                @case('assigned')
                                                    <span class="badge badge-info">Назначен</span>
                                                    @break
                                                @case('picked_up')
                                                    <span class="badge badge-primary">Забран</span>
                                                    @break
                                                @case('in_delivery')
                                                    <span class="badge badge-secondary">В доставке</span>
                                                    @break
                                                @case('delivered')
                                                    <span class="badge badge-success">Доставлен</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge badge-danger">Отменен</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-light">{{ $order->status }}</span>
                                            @endswitch
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($courier->assignedOrders->count() > 10)
                        <div class="text-center mt-3">
                            <small class="text-muted">Показаны последние 10 заказов из {{ $courier->assignedOrders->count() }}</small>
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Боковая панель -->
        <div class="col-lg-4">
            <!-- Статистика -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-chart-bar mr-1"></i>
                        Статистика
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Всего назначено -->
                    <div class="row no-gutters align-items-center mb-4">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Всего назначено заказов
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $totalAssigned }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>

                    <!-- Доставлено -->
                    <div class="row no-gutters align-items-center mb-4">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Доставлено заказов
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $totalDelivered }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>

                    <!-- Активные -->
                    <div class="row no-gutters align-items-center mb-4">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Активных заказов
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $activeOrders }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>

                    <!-- Процент выполнения -->
                    @if($totalAssigned > 0)
                    <div class="mb-4">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Процент выполнения
                        </div>
                        <div class="progress">
                            @php
                                $completionRate = round(($totalDelivered / $totalAssigned) * 100, 1);
                            @endphp
                            <div class="progress-bar bg-success" 
                                 role="progressbar" 
                                 style="width: {{ $completionRate }}%">
                                {{ $completionRate }}%
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Среднее время доставки -->
                    @if($averageDeliveryTime)
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Среднее время доставки
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $averageDeliveryTime }} мин</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-stopwatch fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Действия -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-tools mr-1"></i>
                        Действия
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Редактировать -->
                    <a href="{{ route('admin.couriers.edit', $courier) }}" 
                       class="btn btn-block btn-primary mb-3">
                        <i class="fas fa-edit mr-1"></i>
                        Редактировать профиль
                    </a>

                    <!-- Переключение статуса -->
                    <form action="{{ route('admin.couriers.toggle-status', $courier) }}" 
                          method="POST" 
                          class="mb-3">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="btn btn-block {{ $courier->is_active ? 'btn-warning' : 'btn-success' }}"
                                onclick="return confirm('Вы уверены, что хотите {{ $courier->is_active ? 'деактивировать' : 'активировать' }} этого курьера?')">
                            <i class="fas {{ $courier->is_active ? 'fa-pause' : 'fa-play' }} mr-1"></i>
                            {{ $courier->is_active ? 'Деактивировать' : 'Активировать' }}
                        </button>
                    </form>

                    <!-- Удаление -->
                    @if($activeOrders == 0)
                    <form action="{{ route('admin.couriers.destroy', $courier) }}" 
                          method="POST"
                          onsubmit="return confirm('Вы уверены, что хотите удалить этого курьера? Это действие нельзя отменить!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-block btn-danger">
                            <i class="fas fa-trash mr-1"></i>
                            Удалить курьера
                        </button>
                    </form>
                    @else
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle mr-1"></i>
                            Нельзя удалить курьера с активными заказами
                        </small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection