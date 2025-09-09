@extends('admin.layout')

@section('title', 'Редактировать курьера')

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
            <li class="breadcrumb-item">
                <a href="{{ route('admin.couriers.show', $courier) }}">{{ $courier->name }}</a>
            </li>
            <li class="breadcrumb-item active">Редактировать</li>
        </ol>
    </nav>

    <!-- Заголовок -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-edit mr-2"></i>
            Редактировать курьера: {{ $courier->name }}
        </h1>
        <div>
            <a href="{{ route('admin.couriers.show', $courier) }}" class="btn btn-info mr-2">
                <i class="fas fa-eye mr-1"></i>
                Просмотр
            </a>
            <a href="{{ route('admin.couriers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Назад к списку
            </a>
        </div>
    </div>

    <!-- Форма -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-cog mr-1"></i>
                        Данные курьера
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.couriers.update', $courier) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Личные данные -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="name" class="form-label required">
                                        <i class="fas fa-user mr-1"></i>
                                        Имя курьера
                                    </label>
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $courier->name) }}" 
                                           placeholder="Введите полное имя курьера"
                                           required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Контактные данные -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone" class="form-label required">
                                        <i class="fas fa-phone mr-1"></i>
                                        Телефон
                                    </label>
                                    <input type="tel" 
                                           name="phone" 
                                           id="phone" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone', $courier->phone) }}" 
                                           placeholder="+998901234567" 
                                           required>
                                    @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope mr-1"></i>
                                        Email (необязательно)
                                    </label>
                                    <input type="email" 
                                           name="email" 
                                           id="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email', $courier->email) }}" 
                                           placeholder="courier@example.com">
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Данные для входа -->
                        <hr class="my-4">
                        <h5 class="mb-3">
                            <i class="fas fa-key mr-2"></i>
                            Данные для входа в приложение
                        </h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="login" class="form-label required">
                                        <i class="fas fa-user-tag mr-1"></i>
                                        Логин (имя пользователя)
                                    </label>
                                    <input type="text" 
                                           name="login" 
                                           id="login" 
                                           class="form-control @error('login') is-invalid @enderror" 
                                           value="{{ old('login', $courier->login) }}" 
                                           required>
                                    @error('login')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock mr-1"></i>
                                        Новый пароль (оставьте пустым если не меняете)
                                    </label>
                                    <input type="password" 
                                           name="password" 
                                           id="password" 
                                           class="form-control @error('password') is-invalid @enderror">
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Минимум 8 символов, если указываете новый пароль
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Дополнительные данные -->
                        <hr class="my-4">
                        <h5 class="mb-3">
                            <i class="fas fa-motorcycle mr-2"></i>
                            Дополнительная информация
                        </h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vehicle_type" class="form-label">
                                        <i class="fas fa-car mr-1"></i>
                                        Тип транспорта
                                    </label>
                                    <select name="vehicle_type" 
                                            id="vehicle_type" 
                                            class="form-control @error('vehicle_type') is-invalid @enderror">
                                        <option value="">Выберите тип транспорта</option>
                                        <option value="bicycle" {{ old('vehicle_type', $courier->vehicle_type) == 'bicycle' ? 'selected' : '' }}>
                                            🚲 Велосипед
                                        </option>
                                        <option value="motorcycle" {{ old('vehicle_type', $courier->vehicle_type) == 'motorcycle' ? 'selected' : '' }}>
                                            🏍️ Мотоцикл
                                        </option>
                                        <option value="car" {{ old('vehicle_type', $courier->vehicle_type) == 'car' ? 'selected' : '' }}>
                                            🚗 Автомобиль
                                        </option>
                                        <option value="walking" {{ old('vehicle_type', $courier->vehicle_type) == 'walking' ? 'selected' : '' }}>
                                            🚶 Пешком
                                        </option>
                                    </select>
                                    @error('vehicle_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vehicle_number" class="form-label">
                                        <i class="fas fa-hashtag mr-1"></i>
                                        Номер транспорта
                                    </label>
                                    <input type="text" 
                                           name="vehicle_number" 
                                           id="vehicle_number" 
                                           class="form-control @error('vehicle_number') is-invalid @enderror" 
                                           value="{{ old('vehicle_number', $courier->vehicle_number) }}" 
                                           placeholder="01A123BC">
                                    @error('vehicle_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Статус -->
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" 
                                       name="is_active" 
                                       id="is_active" 
                                       class="form-check-input @error('is_active') is-invalid @enderror" 
                                       value="1" 
                                       {{ old('is_active', $courier->is_active) ? 'checked' : '' }}>
                                <label for="is_active" class="form-check-label">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Курьер активен (может получать заказы)
                                </label>
                                @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Кнопки -->
                        <div class="form-group mt-4">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('admin.couriers.show', $courier) }}" class="btn btn-secondary">
                                    <i class="fas fa-times mr-1"></i>
                                    Отмена
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i>
                                    Сохранить изменения
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Боковая панель с информацией и действиями -->
        <div class="col-md-4">
            <!-- Статистика курьера -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-chart-bar mr-1"></i>
                        Статистика курьера
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row no-gutters align-items-center mb-3">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Назначено заказов
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $courier->assignedOrders->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>

                    <div class="row no-gutters align-items-center mb-3">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Доставлено заказов
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $courier->deliveredOrders->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>

                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Активных заказов
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $courier->assignedOrders->where('status', '!=', 'delivered')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Дополнительные действия -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-tools mr-1"></i>
                        Дополнительные действия
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Переключение статуса -->
                    <form action="{{ route('admin.couriers.toggle-status', $courier) }}" method="POST" class="mb-3">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-block {{ $courier->is_active ? 'btn-warning' : 'btn-success' }}">
                            <i class="fas {{ $courier->is_active ? 'fa-pause' : 'fa-play' }} mr-1"></i>
                            {{ $courier->is_active ? 'Деактивировать' : 'Активировать' }} курьера
                        </button>
                    </form>

                    <!-- Удаление курьера -->
                    @if($courier->assignedOrders->where('status', '!=', 'delivered')->count() == 0)
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

@push('styles')
<style>
.required::after {
    content: " *";
    color: red;
}
</style>
@endpush
@endsection