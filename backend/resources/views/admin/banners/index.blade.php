@extends('admin.layout')

@section('title', 'Управление баннерами')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Баннеры</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Главная</a></li>
                        <li class="breadcrumb-item active">Баннеры</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-sm-6">
                                    <h3 class="card-title">Список баннеров</h3>
                                </div>
                                <div class="col-sm-6">
                                    <div class="float-right">
                                        <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Добавить баннер
                                        </a>
                                        <a href="{{ route('admin.banners.statistics') }}" class="btn btn-info">
                                            <i class="fas fa-chart-bar"></i> Статистика
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Фильтры -->
                        <div class="card-body border-bottom">
                            <form method="GET" action="{{ route('admin.banners.index') }}" class="row">
                                <div class="col-md-3">
                                    <select name="status" class="form-control">
                                        <option value="all">Все статусы</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активные</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Неактивные</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="target" class="form-control">
                                        <option value="all">Все аудитории</option>
                                        <option value="all" {{ request('target') == 'all' ? 'selected' : '' }}>Все пользователи</option>
                                        <option value="new" {{ request('target') == 'new' ? 'selected' : '' }}>Новые пользователи</option>
                                        <option value="active" {{ request('target') == 'active' ? 'selected' : '' }}>Активные пользователи</option>
                                        <option value="premium" {{ request('target') == 'premium' ? 'selected' : '' }}>Премиум пользователи</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-secondary">Фильтровать</button>
                                    <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary">Сбросить</a>
                                </div>
                            </form>
                        </div>

                        <div class="card-body table-responsive p-0">
                            @if($banners->count() > 0)
                                <table class="table table-hover text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Изображение</th>
                                            <th>Заголовок</th>
                                            <th>Порядок</th>
                                            <th>Статус</th>
                                            <th>Период показа</th>
                                            <th>Аудитория</th>
                                            <th>Статистика</th>
                                            <th>Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($banners as $banner)
                                            <tr>
                                                <td>{{ $banner->id }}</td>
                                                <td>
                                                    @if($banner->image)
                                                        <img src="{{ $banner->image_url }}" alt="Banner" class="img-thumbnail" style="max-width: 80px; max-height: 50px;">
                                                    @else
                                                        <span class="badge badge-secondary">Нет изображения</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div>{{ $banner->title }}</div>
                                                    @if($banner->title_ru)
                                                        <small class="text-muted">{{ $banner->title_ru }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $banner->sort_order }}</td>
                                                <td>
                                                    <button type="button" 
                                                            class="btn btn-sm toggle-active {{ $banner->is_active ? 'btn-success' : 'btn-secondary' }}"
                                                            data-id="{{ $banner->id }}">
                                                        <i class="fas {{ $banner->is_active ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                                        {{ $banner->is_active ? 'Активный' : 'Неактивный' }}
                                                    </button>
                                                </td>
                                                <td>
                                                    @if($banner->start_date || $banner->end_date)
                                                        <small>
                                                            {{ $banner->start_date ? $banner->start_date->format('d.m.Y') : '∞' }} - 
                                                            {{ $banner->end_date ? $banner->end_date->format('d.m.Y') : '∞' }}
                                                        </small>
                                                    @else
                                                        <span class="text-muted">Всегда</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @switch($banner->target_audience)
                                                        @case('all')
                                                            <span class="badge badge-primary">Все</span>
                                                            @break
                                                        @case('new')
                                                            <span class="badge badge-info">Новые</span>
                                                            @break
                                                        @case('active')
                                                            <span class="badge badge-success">Активные</span>
                                                            @break
                                                        @case('premium')
                                                            <span class="badge badge-warning">Премиум</span>
                                                            @break
                                                    @endswitch
                                                </td>
                                                <td>
                                                    <small>
                                                        <div>👁 {{ number_format($banner->view_count) }}</div>
                                                        <div>🖱 {{ number_format($banner->click_count) }}</div>
                                                        @if($banner->view_count > 0)
                                                            <div class="text-info">CTR: {{ round(($banner->click_count / $banner->view_count) * 100, 2) }}%</div>
                                                        @endif
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('admin.banners.show', $banner) }}" class="btn btn-info btn-sm">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-warning btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="text-center p-4">
                                    <h5>Баннеры не найдены</h5>
                                    <p class="text-muted">Создайте первый баннер для начала работы</p>
                                    <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">Создать баннер</a>
                                </div>
                            @endif
                        </div>

                        @if($banners->hasPages())
                            <div class="card-footer">
                                {{ $banners->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Переключение статуса активности
    document.querySelectorAll('.toggle-active').forEach(button => {
        button.addEventListener('click', function() {
            const bannerId = this.dataset.id;
            
            fetch(`/admin/banners/${bannerId}/toggle-active`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Обновляем кнопку
                    const icon = this.querySelector('i');
                    const text = this.querySelector('i').nextSibling;
                    
                    if (data.is_active) {
                        this.classList.remove('btn-secondary');
                        this.classList.add('btn-success');
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                        text.textContent = ' Активный';
                    } else {
                        this.classList.remove('btn-success');
                        this.classList.add('btn-secondary');
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                        text.textContent = ' Неактивный';
                    }
                    
                    // Показываем уведомление
                    alert(data.message);
                } else {
                    alert('Ошибка при изменении статуса');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка');
            });
        });
    });
});
</script>
@endsection
