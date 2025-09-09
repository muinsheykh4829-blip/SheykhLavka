@extends('admin.layout')

@section('title', 'Редактировать пользователя')
@section('page-title', 'Редактировать пользователя: ' . $user->name)

@section('content')
<form action="{{ route('admin.users.update', $user) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5>Основная информация</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Имя *</label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $user->name) }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $user->email) }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Телефон</label>
                        <input type="tel" 
                               class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" 
                               name="phone" 
                               value="{{ old('phone', $user->phone) }}"
                               placeholder="+992 XX XXX XX XX">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Адрес</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" 
                                  name="address" 
                                  rows="3"
                                  placeholder="Адрес доставки">{{ old('address', $user->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5>Настройки</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Пользователь активен
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            Неактивные пользователи не могут войти в приложение
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Дата регистрации</label>
                        <p class="form-control-plaintext">{{ $user->created_at->format('d.m.Y H:i') }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email верифицирован</label>
                        <p class="form-control-plaintext">
                            @if($user->email_verified_at)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> {{ $user->email_verified_at->format('d.m.Y') }}
                                </span>
                            @else
                                <span class="badge bg-warning">
                                    <i class="bi bi-exclamation-circle"></i> Не верифицирован
                                </span>
                            @endif
                        </p>
                    </div>
                    
                    @php
                        $ordersCount = $user->orders()->count();
                        $totalSpent = $user->orders()->where('status', 'completed')->sum('total');
                    @endphp
                    
                    <div class="mb-3">
                        <label class="form-label">Статистика заказов</label>
                        <div class="small">
                            <div>Всего заказов: <strong>{{ $ordersCount }}</strong></div>
                            <div>Потрачено: <strong>{{ number_format($totalSpent / 100, 2) }} сом.</strong></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Сохранить изменения
                        </button>
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Назад к просмотру
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-list"></i> К списку пользователей
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
