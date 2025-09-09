@extends('admin.layout')

@section('title', 'Заказ #' . $order->order_number)
@section('page-title', 'Заказ #' . $order->order_number)

@section('page-actions')
    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Назад к списку
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Товары в заказе -->
        <div class="card">
            <div class="card-header">
                <h5>Товары в заказе</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Товар</th>
                                <th>Цена</th>
                                <th>Количество/Вес</th>
                                <th>Сумма</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($item->product && $item->product->image)
                                            <img src="{{ asset($item->product->image) }}" 
                                                 alt="{{ $item->product->name }}" 
                                                 class="img-thumbnail me-3" 
                                                 style="width: 50px;">
                                        @endif
                                        <div>
                                            <strong>{{ $item->product ? $item->product->name : 'Товар удален' }}</strong>
                                            @if($item->product && $item->product->weight)
                                                <br><small class="text-muted">{{ $item->product->weight }} {{ $item->product->unit }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ number_format($item->price, 2, '.', ' ') }} сом.</td>
                                <td>
                                    @if($item->weight && $item->weight > 0)
                                        {{ number_format($item->weight, 3) }} кг
                                    @else
                                        {{ $item->quantity }} шт
                                    @endif
                                </td>
                                <td><strong>{{ number_format($item->total, 2, '.', ' ') }} сом.</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">Сумма товаров:</th>
                                <th>{{ number_format($order->subtotal, 2, '.', ' ') }} сом.</th>
                            </tr>
                            <tr>
                                <td colspan="3">Доставка:</td>
                                <td>{{ number_format($order->delivery_fee, 2, '.', ' ') }} сом.</td>
                            </tr>
                            @if($order->discount > 0)
                            <tr class="text-success">
                                <td colspan="3">Скидка:</td>
                                <td>-{{ number_format($order->discount, 2, '.', ' ') }} сом.</td>
                            </tr>
                            @endif
                            <tr class="table-primary">
                                <th colspan="3">ИТОГО:</th>
                                <th>{{ number_format(($order->subtotal + $order->delivery_fee - $order->discount), 2, '.', ' ') }} сом.</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Информация о заказе -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Информация о заказе</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Номер заказа:</strong></td>
                        <td>{{ $order->order_number }}</td>
                    </tr>
                    <tr>
                        <td><strong>Дата создания:</strong></td>
                        <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Статус:</strong></td>
                        <td>
                            @php
                                $statusColors = [
                                    'processing' => 'warning',
                                    'accepted' => 'info',
                                    'preparing' => 'primary',
                                    'ready' => 'info',
                                    'delivering' => 'primary',
                                    'delivered' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                $statusNames = [
                                    'processing' => 'В обработке',
                                    'accepted' => 'Принят', 
                                    'preparing' => 'Собирается',
                                    'ready' => 'Собран',
                                    'delivering' => 'Курьер в пути',
                                    'delivered' => 'Завершен',
                                    'cancelled' => 'Отменен'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                {{ $statusNames[$order->status] ?? $order->status }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Оплата:</strong></td>
                        <td>
                            {{ $order->payment_method === 'cash' ? 'Наличные' : 
                               ($order->payment_method === 'card' ? 'Картой' : 'Онлайн') }}
                            <br>
                            <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                {{ $order->payment_status === 'paid' ? 'Оплачен' : 'Не оплачен' }}
                            </span>
                        </td>
                    </tr>
                    @if($order->delivery_time)
                    <tr>
                        <td><strong>Время доставки:</strong></td>
                        <td>{{ \Carbon\Carbon::parse($order->delivery_time)->format('d.m.Y H:i') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Информация о клиенте -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Клиент</h5>
            </div>
            <div class="card-body">
                @if($order->user)
                    <p><strong>{{ $order->user->first_name }} {{ $order->user->last_name }}</strong></p>
                    <p><i class="bi bi-phone"></i> {{ $order->user->phone }}</p>
                    @if($order->user->email)
                        <p><i class="bi bi-envelope"></i> {{ $order->user->email }}</p>
                    @endif
                @else
                    <p><strong>{{ $order->delivery_name ?: 'Гость' }}</strong></p>
                    <p><i class="bi bi-phone"></i> {{ $order->delivery_phone }}</p>
                @endif
            </div>
        </div>

        <!-- Адрес доставки -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Доставка</h5>
            </div>
            <div class="card-body">
                <p><i class="bi bi-geo-alt"></i> {{ $order->delivery_address }}</p>
                <p><i class="bi bi-phone"></i> {{ $order->delivery_phone }}</p>
                @if($order->delivery_name)
                    <p><i class="bi bi-person"></i> {{ $order->delivery_name }}</p>
                @endif
                <p>
                    @if($order->delivery_type === 'express')
                        <i class="bi bi-lightning-charge text-warning"></i> 
                        <span class="badge bg-warning text-dark">Экспресс доставка (10 сом)</span>
                    @else
                        <i class="bi bi-truck text-success"></i> 
                        <span class="badge bg-success">Стандартная доставка (бесплатно)</span>
                    @endif
                </p>
            </div>
        </div>

        @if($order->comment)
        <!-- Комментарий -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Комментарий</h5>
            </div>
            <div class="card-body">
                <p>{{ $order->comment }}</p>
            </div>
        </div>
        @endif

        <!-- Управление статусом -->
        <div class="card">
            <div class="card-header">
                <h5>Управление заказом</h5>
            </div>
            <div class="card-body">
                <!-- Быстрые кнопки статусов -->
                <div class="mb-3">
                    <small class="text-muted">Быстрая смена статуса:</small>
                    <div class="d-grid gap-2 mt-2">
                        @if($order->status === 'processing')
                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="accepted">
                            <button type="submit" class="btn btn-info btn-sm w-100">
                                <i class="bi bi-check-circle"></i> Принять заказ
                            </button>
                        </form>
                        @endif
                        
                        @if($order->status === 'accepted')
                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="preparing">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-hourglass-split"></i> Начать сборку
                            </button>
                        </form>
                        @endif
                        
                        @if($order->status === 'preparing')
                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="ready">
                            <button type="submit" class="btn btn-info btn-sm w-100">
                                <i class="bi bi-check-all"></i> Заказ собран
                            </button>
                        </form>
                        @endif
                        
                        @if($order->status === 'ready')
                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="delivering">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-truck"></i> Курьер в пути
                            </button>
                        </form>
                        @endif
                        
                        @if($order->status === 'delivering')
                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="delivered">
                            <button type="submit" class="btn btn-success btn-sm w-100">
                                <i class="bi bi-check2-all"></i> Заказ завершен
                            </button>
                        </form>
                        @endif
                        
                        @if($order->status !== 'cancelled' && $order->status !== 'delivered')
                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="btn btn-danger btn-sm w-100" onclick="return confirm('Отменить заказ?')">
                                <i class="bi bi-x-circle"></i> Отменить заказ
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                <hr>

                <!-- Подробная форма смены статуса -->
                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="status" class="form-label">Или выберите статус вручную:</label>
                        <select name="status" id="status" class="form-select">
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>В обработке</option>
                            <option value="accepted" {{ $order->status === 'accepted' ? 'selected' : '' }}>Принят</option>
                            <option value="preparing" {{ $order->status === 'preparing' ? 'selected' : '' }}>Собирается</option>
                            <option value="ready" {{ $order->status === 'ready' ? 'selected' : '' }}>Собран</option>
                            <option value="delivering" {{ $order->status === 'delivering' ? 'selected' : '' }}>Курьер в пути</option>
                            <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Завершен</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Отменен</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-arrow-repeat"></i> Обновить статус
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
