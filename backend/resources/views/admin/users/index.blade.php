@extends('admin.layout')

@section('title', 'Пользователи')
@section('page-title', 'Авторизованные клиенты приложения')

@section('page-actions')
    <div class="btn-group">
        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
            <i class="bi bi-funnel"></i> Фильтры
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">Все пользователи</a></li>
            <li><a class="dropdown-item" href="{{ route('admin.users.index', ['verified' => 'yes']) }}">Верифицированные</a></li>
            <li><a class="dropdown-item" href="{{ route('admin.users.index', ['verified' => 'no']) }}">Не верифицированные</a></li>
        </ul>
    </div>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-md-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="d-flex">
            <input type="text" name="search" class="form-control me-2" 
                   placeholder="Поиск по имени, email или телефону..." 
                   value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i> Поиск
            </button>
        </form>
    </div>
    <div class="col-md-6 text-end">
        <div class="btn-group">
            <a href="{{ route('admin.users.index', ['sort' => 'created_at', 'direction' => 'desc']) }}" 
               class="btn btn-outline-secondary {{ request('sort') == 'created_at' ? 'active' : '' }}">
                <i class="bi bi-sort-down"></i> По дате регистрации
            </a>
            <a href="{{ route('admin.users.index', ['sort' => 'name', 'direction' => 'asc']) }}" 
               class="btn btn-outline-secondary {{ request('sort') == 'name' ? 'active' : '' }}">
                <i class="bi bi-sort-alpha-down"></i> По имени
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Email</th>
                            <th>Телефон</th>
                            <th>Дата регистрации</th>
                            <th>Статус</th>
                            <th>Верификация</th>
                            <th>Заказы</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        {{ $user->name }}
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?? 'Не указан' }}</td>
                                <td>{{ $user->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    @if($user->is_active ?? true)
                                        <span class="badge bg-success">Активен</span>
                                    @else
                                        <span class="badge bg-danger">Заблокирован</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->email_verified_at)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Верифицирован
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="bi bi-exclamation-circle"></i> Не верифицирован
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $ordersCount = $user->orders()->count();
                                    @endphp
                                    @if($ordersCount > 0)
                                        <span class="badge bg-info">{{ $ordersCount }} заказов</span>
                                    @else
                                        <span class="text-muted">Нет заказов</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.users.show', $user) }}" 
                                           class="btn btn-sm btn-outline-primary" title="Просмотр">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user) }}" 
                                           class="btn btn-sm btn-outline-secondary" title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" 
                                              class="d-inline" onsubmit="return confirm('Изменить статус пользователя?')">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-sm {{ ($user->is_active ?? true) ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                    title="{{ ($user->is_active ?? true) ? 'Заблокировать' : 'Разблокировать' }}">
                                                <i class="bi bi-{{ ($user->is_active ?? true) ? 'lock' : 'unlock' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{ $users->links() }}
        @else
            <div class="text-center py-4">
                <i class="bi bi-person-x display-1 text-muted"></i>
                <h4 class="mt-3">Пользователи не найдены</h4>
                <p class="text-muted">Нет зарегистрированных пользователей или они не соответствуют критериям поиска</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
    font-weight: bold;
}
</style>
@endsection
