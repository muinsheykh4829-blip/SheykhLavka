@extends('admin.layout')

@section('title', 'Добавить сборщика')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Добавить нового сборщика</h1>
        <a href="{{ route('admin.pickers.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Назад к списку
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Данные сборщика</h6>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form action="{{ route('admin.pickers.store') }}" method="POST"
                          onsubmit="console.log('Form submitted'); return true;">
                        @csrf
                        
                        <div class="form-group">
                            <label for="login">Логин для входа <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('login') is-invalid @enderror" 
                                   id="login" 
                                   name="login" 
                                   value="{{ old('login') }}" 
                                   required>
                            @error('login')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Уникальный логин для входа в приложение сборщика
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="password">Пароль <span class="text-danger">*</span></label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Минимум 6 символов
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="name">Полное имя <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone">Телефон</label>
                            <input type="tel" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone') }}" 
                                   placeholder="+992xxxxxxxxx">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="is_active" 
                                       name="is_active" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Активный сборщик
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Неактивные сборщики не смогут войти в приложение
                            </small>
                        </div>

                        <hr>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Создать сборщика
                            </button>
                            <a href="{{ route('admin.pickers.index') }}" class="btn btn-secondary">
                                Отменить
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Справка</h6>
                </div>
                <div class="card-body">
                    <h6 class="text-primary">Создание сборщика</h6>
                    <p class="text-sm">
                        Сборщик сможет использовать свой логин и пароль для входа в мобильное приложение и работы с заказами.
                    </p>
                    
                    <h6 class="text-primary mt-3">Рекомендации:</h6>
                    <ul class="text-sm">
                        <li>Используйте простые и запоминающиеся логины</li>
                        <li>Пароль должен быть надежным</li>
                        <li>Указывайте реальные контактные данные</li>
                        <li>Деактивированные сборщики не смогут войти в систему</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
