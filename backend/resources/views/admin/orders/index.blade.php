@extends('admin.layout')

@section('title', 'Заказы')
@section('page-title', 'Управление заказами')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <h5>Все заказы</h5>
            </div>
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <select name="status" class="form-select me-2">
                        <option value="">Все статусы</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Ожидает</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Подтвержден</option>
                        <option value="preparing" {{ request('status') == 'preparing' ? 'selected' : '' }}>Готовится</option>
                        <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>Готов</option>
                        <option value="delivering" {{ request('status') == 'delivering' ? 'selected' : '' }}>Доставляется</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Доставлен</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Отменен</option>
                    </select>
                    <input type="text" 
                           name="search" 
                           class="form-control me-2" 
                           placeholder="Поиск..." 
                           value="{{ request('search') }}">
                    <button type="submit" class="btn btn-outline-primary">Поиск</button>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>№ заказа</th>
                        <th>Клиент</th>
                        <th>Дата</th>
                        <th>Сумма</th>
                        <th>Доставка</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>
                            <strong>{{ $order->order_number }}</strong>
                        </td>
                        <td>
                            <div>
                                @if($order->user)
                                    <strong>{{ $order->user->first_name }} {{ $order->user->last_name }}</strong>
                                    <br><small class="text-muted">{{ $order->user->phone }}</small>
                                @else
                                    <strong>{{ $order->delivery_name ?: 'Не указано' }}</strong>
                                    <br><small class="text-muted">{{ $order->delivery_phone }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div>{{ $order->created_at->format('d.m.Y H:i') }}</div>
                            @if($order->delivery_time)
                                <small class="text-info">
                                    Доставка: {{ \Carbon\Carbon::parse($order->delivery_time)->format('d.m.Y H:i') }}
                                </small>
                            @endif
                        </td>
                        <td>
                            <strong>{{ number_format($order->total, 2, '.', ' ') }} сом.</strong>
                            @if($order->discount > 0)
                                <br><small class="text-success">Скидка: {{ number_format($order->discount, 2, '.', ' ') }} сом.</small>
                            @endif
                        </td>
                        <td>
                            @if($order->delivery_type === 'express')
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-lightning-charge"></i> Экспресс (10 с.)
                                </span>
                            @else
                                <span class="badge bg-success">
                                    <i class="bi bi-truck"></i> Стандарт (бесплатно)
                                </span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'confirmed' => 'info',
                                    'preparing' => 'primary',
                                    'ready' => 'secondary',
                                    'delivering' => 'primary',
                                    'delivered' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                $statusNames = [
                                    'pending' => 'Ожидает',
                                    'confirmed' => 'Подтвержден', 
                                    'preparing' => 'Готовится',
                                    'ready' => 'Готов',
                                    'delivering' => 'Доставляется',
                                    'delivered' => 'Доставлен',
                                    'cancelled' => 'Отменен'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                {{ $statusNames[$order->status] ?? $order->status }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.orders.show', $order) }}" 
                                   class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                <div class="btn-group btn-group-sm">
                                    <button type="button" 
                                            class="btn btn-outline-success dropdown-toggle" 
                                            data-bs-toggle="dropdown">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="confirmed">
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bi bi-check text-info"></i> Подтвердить
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="preparing">
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bi bi-hourglass text-primary"></i> В работе
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="ready">
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bi bi-box-seam text-secondary"></i> Готов
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="delivering">
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bi bi-truck text-primary"></i> Доставляется
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="delivered">
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bi bi-check-circle text-success"></i> Доставлен
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-x-circle"></i> Отменить
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-cart3 fs-1 d-block mb-2"></i>
                            Заказы не найдены
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($orders->hasPages())
            <div class="d-flex justify-content-center">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
