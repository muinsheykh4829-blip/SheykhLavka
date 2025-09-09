@extends('admin.layout')

@section('title', 'Управление курьерами')

@section('content')
<div class="container-fluid">
    <!-- Заголовок с кнопкой добавления -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-motorcycle mr-2"></i>
            Управление курьерами
        </h1>
        <a href="{{ route('admin.couriers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i>
            Добавить курьера
        </a>
    </div>

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Всего курьеров
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalCouriers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Активных курьеров
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeCouriers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Всего доставок
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalDeliveries }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shipping-fast fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Средний рейтинг
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">4.8</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица курьеров -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Список курьеров</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="couriersTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Логин</th>
                            <th>Телефон</th>
                            <th>Статус</th>
                            <th>Назначено заказов</th>
                            <th>Доставлено</th>
                            <th>Активных заказов</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($couriers as $courier)
                        <tr>
                            <td>{{ $courier->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($courier->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <strong>{{ $courier->name }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $courier->login }}</td>
                            <td>{{ $courier->phone }}</td>
                            <td>
                                @if($courier->is_active)
                                    <span class="badge badge-success">Активен</span>
                                @else
                                    <span class="badge badge-secondary">Неактивен</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $courier->assigned_orders_count }}</span>
                            </td>
                            <td>
                                <span class="badge badge-success">{{ $courier->delivered_orders_count }}</span>
                            </td>
                            <td>
                                @if($courier->active_orders_count > 0)
                                    <span class="badge badge-warning">{{ $courier->active_orders_count }}</span>
                                @else
                                    <span class="badge badge-light">0</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.couriers.show', $courier) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="Просмотр">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.couriers.edit', $courier) }}" 
                                       class="btn btn-sm btn-primary" 
                                       title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.couriers.toggle-status', $courier) }}" 
                                          method="POST" 
                                          style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="btn btn-sm {{ $courier->is_active ? 'btn-warning' : 'btn-success' }}" 
                                                title="{{ $courier->is_active ? 'Деактивировать' : 'Активировать' }}">
                                            <i class="fas {{ $courier->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                        </button>
                                    </form>
                                    @if($courier->active_orders_count == 0)
                                    <form action="{{ route('admin.couriers.destroy', $courier) }}" 
                                          method="POST" 
                                          style="display: inline;"
                                          onsubmit="return confirm('Вы уверены, что хотите удалить этого курьера?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-danger" 
                                                title="Удалить">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#couriersTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Russian.json"
        },
        "order": [[0, "desc"]],
        "pageLength": 25
    });
});
</script>
@endpush
@endsection