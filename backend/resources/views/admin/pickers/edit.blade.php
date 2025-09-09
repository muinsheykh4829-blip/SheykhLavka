@extends('admin.layout')

@section('title', 'Редактировать сборщика')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Редактировать сборщика: {{ $picker->name }}</h1>
        <div>
            <a href="{{ route('admin.pickers.show', $picker) }}" class="btn btn-info">
                <i class="bi bi-eye"></i> Просмотр
            </a>
            <a href="{{ route('admin.pickers.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Назад к списку
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Редактирование данных</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.pickers.update', $picker) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group mb-3">
                            <label for="login">Логин для входа <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('login') is-invalid @enderror" 
                                   id="login" 
                                   name="login" 
                                   value="{{ old('login', $picker->login) }}" 
                                   required>
                            @error('login')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="password">Новый пароль</label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Оставьте пустым, чтобы не изменять">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Оставьте поле пустым, если не хотите изменять пароль
                            </small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="name">Полное имя <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $picker->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="phone">Телефон</label>
                            <input type="tel" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', $picker->phone) }}" 
                                   placeholder="+992xxxxxxxxx">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="is_active" 
                                       name="is_active" 
                                       {{ old('is_active', $picker->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Активный сборщик
                                </label>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Сохранить изменения
                            </button>
                            <a href="{{ route('admin.pickers.show', $picker) }}" class="btn btn-secondary">
                                Отменить
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">Внимание</h6>
                </div>
                <div class="card-body">
                    <p><strong>ID сборщика:</strong> {{ $picker->id }}</p>
                    <p><strong>Создан:</strong> {{ $picker->created_at->format('d.m.Y в H:i') }}</p>
                    <p><strong>Последнее обновление:</strong> {{ $picker->updated_at->format('d.m.Y в H:i') }}</p>
                    
                    <hr>
                    
                    <h6 class="text-primary">Статистика:</h6>
                    <ul class="list-unstyled">
                        <li><strong>Активные заказы:</strong> {{ $picker->activeOrders()->count() }}</li>
                        <li><strong>Всего заказов:</strong> {{ $picker->orders()->count() }}</li>
                    </ul>
                    
                    @if($picker->activeOrders()->count() > 0)
                        <div class="alert alert-warning">
                            <small>
                                <i class="bi bi-exclamation-triangle"></i>
                                У сборщика есть активные заказы. Деактивация может повлиять на текущие задачи.
                            </small>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Опасная зона</h6>
                </div>
                <div class="card-body">
                    @if($picker->activeOrders()->count() == 0)
                        <form action="{{ route('admin.pickers.destroy', $picker) }}" 
                              method="POST" 
                              onsubmit="return confirm('Вы уверены, что хотите удалить этого сборщика? Это действие нельзя отменить!')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash"></i> Удалить сборщика
                            </button>
                        </form>
                        <small class="text-muted">Удаление необратимо!</small>
                    @else
                        <p class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            Нельзя удалить сборщика с активными заказами.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
