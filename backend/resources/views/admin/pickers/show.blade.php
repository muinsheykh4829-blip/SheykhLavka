@extends('admin.layout')

@section('title', 'Просмотр сборщика')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Сборщик: {{ $picker->name }}</h1>
        <div>
            <a href="{{ route('admin.pickers.edit', $picker) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Редактировать
            </a>
            <a href="{{ route('admin.pickers.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Назад к списку
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Основная информация</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>ID:</strong></td>
                            <td>{{ $picker->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>Логин:</strong></td>
                            <td><code>{{ $picker->login }}</code></td>
                        </tr>
                        <tr>
                            <td><strong>Полное имя:</strong></td>
                            <td>{{ $picker->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Телефон:</strong></td>
                            <td>{{ $picker->phone ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Статус:</strong></td>
                            <td>
                                @if($picker->is_active)
                                    <span class="badge bg-success">Активен</span>
                                @else
                                    <span class="badge bg-secondary">Неактивен</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Создан:</strong></td>
                            <td>{{ $picker->created_at->format('d.m.Y в H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Обновлен:</strong></td>
                            <td>{{ $picker->updated_at->format('d.m.Y в H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Статистика работы</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6 mb-3">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h3 class="text-primary">{{ $picker->activeOrders()->count() }}</h3>
                                    <p class="card-text">Активные заказы</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h3 class="text-success">{{ $picker->completedOrders()->count() }}</h3>
                                    <p class="card-text">Завершенные заказы</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-md-12">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h3 class="text-info">{{ $picker->orders()->count() }}</h3>
                                    <p class="card-text">Всего заказов</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($picker->orders()->count() > 0)
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Последние заказы</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Дата</th>
                            <th>Клиент</th>
                            <th>Статус</th>
                            <th>Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($picker->orders()->latest()->limit(10)->get() as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                            <td>{{ $order->user->first_name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-primary">{{ $order->status }}</span>
                            </td>
                            <td>{{ number_format($order->total_amount, 2) }} сомони</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
