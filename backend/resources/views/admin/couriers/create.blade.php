@extends('admin.layout')

@section('title', 'Добавить курьера')

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
            <li class="breadcrumb-item active">Добавить курьера</li>
        </ol>
    </nav>

    <!-- Заголовок -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-plus mr-2"></i>
            Добавить нового курьера
        </h1>
        <a href="{{ route('admin.couriers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>
            Назад к списку
        </a>
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
                    <form action="{{ route('admin.couriers.store') }}" method="POST">
                        @csrf
                        
                        <!-- Личные данные -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="name" class="form-label required">
                                        <i class="fas fa-user mr-1"></i>
                                        Полное имя
                                    </label>
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name') }}" 
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
                                           value="{{ old('phone') }}" 
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
                                           value="{{ old('email') }}" 
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
                                           value="{{ old('login') }}" 
                                           required>
                                    @error('login')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password" class="form-label required">
                                        <i class="fas fa-lock mr-1"></i>
                                        Пароль
                                    </label>
                                    <input type="password" 
                                           name="password" 
                                           id="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           required>
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Минимум 8 символов
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
                                        <option value="bicycle" {{ old('vehicle_type') == 'bicycle' ? 'selected' : '' }}>
                                            🚲 Велосипед
                                        </option>
                                        <option value="motorcycle" {{ old('vehicle_type') == 'motorcycle' ? 'selected' : '' }}>
                                            🏍️ Мотоцикл
                                        </option>
                                        <option value="car" {{ old('vehicle_type') == 'car' ? 'selected' : '' }}>
                                            🚗 Автомобиль
                                        </option>
                                        <option value="walking" {{ old('vehicle_type') == 'walking' ? 'selected' : '' }}>
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
                                           value="{{ old('vehicle_number') }}" 
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
                                       {{ old('is_active', '1') ? 'checked' : '' }}>
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
                                <a href="{{ route('admin.couriers.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times mr-1"></i>
                                    Отмена
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i>
                                    Сохранить курьера
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Боковая панель с подсказками -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle mr-1"></i>
                        Подсказки
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <strong><i class="fas fa-lightbulb mr-1"></i> Совет:</strong>
                        Используйте понятные логины для курьеров (например, ivan_petrov).
                    </div>

                    <div class="alert alert-warning mb-3">
                        <strong><i class="fas fa-exclamation-triangle mr-1"></i> Внимание:</strong>
                        Пароли должны быть надежными. Рекомендуется использовать минимум 8 символов.
                    </div>

                    <div class="alert alert-success mb-0">
                        <strong><i class="fas fa-check mr-1"></i> Информация:</strong>
                        После создания курьер сможет войти в мобильное приложение используя логин и пароль.
                    </div>
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