@extends('admin.layout')

@section('title', 'Сборщики')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Управление сборщиками</h1>
        <a href="{{ route('admin.pickers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Добавить сборщика
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Список сборщиков</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Логин</th>
                            <th>Имя</th>
                            <th>Телефон</th>
                            <th>Статус</th>
                            <th>Активные заказы</th>
                            <th>Всего заказов</th>
                            <th>Создан</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pickers as $picker)
                        <tr>
                            <td>{{ $picker->id }}</td>
                            <td><strong>{{ $picker->login }}</strong></td>
                            <td>{{ $picker->name }}</td>
                            <td>{{ $picker->phone ?? '—' }}</td>
                            <td>
                                @if($picker->is_active)
                                    <span class="badge bg-success">Активен</span>
                                @else
                                    <span class="badge bg-secondary">Неактивен</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $picker->activeOrders()->count() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $picker->orders()->count() }}
                                </span>
                            </td>
                            <td>{{ $picker->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.pickers.show', $picker) }}" 
                                       class="btn btn-sm btn-outline-info" title="Просмотр">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.pickers.edit', $picker) }}" 
                                       class="btn btn-sm btn-outline-warning" title="Редактировать">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.pickers.toggle-status', $picker) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-sm {{ $picker->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}"
                                                title="{{ $picker->is_active ? 'Деактивировать' : 'Активировать' }}">
                                            <i class="bi {{ $picker->is_active ? 'bi-pause' : 'bi-play' }}"></i>
                                        </button>
                                    </form>
                                    @if($picker->activeOrders()->count() == 0)
                                    <form action="{{ route('admin.pickers.destroy', $picker) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Вы уверены, что хотите удалить сборщика?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Нет сборщиков для отображения
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($pickers->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $pickers->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
