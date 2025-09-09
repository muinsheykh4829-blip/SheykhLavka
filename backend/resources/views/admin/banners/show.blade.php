@extends('admin.layout')

@section('title', 'Просмотр баннера')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Просмотр баннера</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Главная</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.banners.index') }}">Баннеры</a></li>
                        <li class="breadcrumb-item active">Просмотр</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Информация о баннере</h3>
                            <div class="card-tools">
                                <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Редактировать
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($banner->image)
                                <div class="text-center mb-4">
                                    <img src="{{ $banner->image_url }}" alt="Banner" class="img-fluid" style="max-height: 300px;">
                                </div>
                            @endif

                            <table class="table table-bordered">
                                <tr>
                                    <th width="200">ID</th>
                                    <td>{{ $banner->id }}</td>
                                </tr>
                                <tr>
                                    <th>Заголовок</th>
                                    <td>{{ $banner->title }}</td>
                                </tr>
                                @if($banner->title_ru)
                                <tr>
                                    <th>Заголовок (русский)</th>
                                    <td>{{ $banner->title_ru }}</td>
                                </tr>
                                @endif
                                @if($banner->description)
                                <tr>
                                    <th>Описание</th>
                                    <td>{{ $banner->description }}</td>
                                </tr>
                                @endif
                                @if($banner->description_ru)
                                <tr>
                                    <th>Описание (русский)</th>
                                    <td>{{ $banner->description_ru }}</td>
                                </tr>
                                @endif
                                @if($banner->link_url)
                                <tr>
                                    <th>Ссылка</th>
                                    <td>
                                        <a href="{{ $banner->link_url }}" target="_blank" rel="noopener">
                                            {{ $banner->link_url }}
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Порядок сортировки</th>
                                    <td>{{ $banner->sort_order }}</td>
                                </tr>
                                <tr>
                                    <th>Статус</th>
                                    <td>
                                        <span class="badge {{ $banner->is_active ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $banner->is_active ? 'Активный' : 'Неактивный' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Целевая аудитория</th>
                                    <td>
                                        @switch($banner->target_audience)
                                            @case('all')
                                                <span class="badge badge-primary">Все пользователи</span>
                                                @break
                                            @case('new')
                                                <span class="badge badge-info">Новые пользователи</span>
                                                @break
                                            @case('active')
                                                <span class="badge badge-success">Активные пользователи</span>
                                                @break
                                            @case('premium')
                                                <span class="badge badge-warning">Премиум пользователи</span>
                                                @break
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <th>Период показа</th>
                                    <td>
                                        @if($banner->start_date || $banner->end_date)
                                            {{ $banner->start_date ? $banner->start_date->format('d.m.Y H:i') : 'С самого начала' }} - 
                                            {{ $banner->end_date ? $banner->end_date->format('d.m.Y H:i') : 'Бессрочно' }}
                                        @else
                                            <span class="text-muted">Всегда активный</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Создан</th>
                                    <td>{{ $banner->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Обновлен</th>
                                    <td>{{ $banner->updated_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Статистика</h3>
                        </div>
                        <div class="card-body">
                            <div class="info-box bg-info mb-3">
                                <span class="info-box-icon"><i class="fas fa-eye"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Всего просмотров</span>
                                    <span class="info-box-number">{{ number_format($banner->view_count) }}</span>
                                </div>
                            </div>

                            <div class="info-box bg-success mb-3">
                                <span class="info-box-icon"><i class="fas fa-mouse-pointer"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Всего кликов</span>
                                    <span class="info-box-number">{{ number_format($banner->click_count) }}</span>
                                </div>
                            </div>

                            @if($banner->view_count > 0)
                                <div class="info-box bg-warning mb-3">
                                    <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">CTR (Click Through Rate)</span>
                                        <span class="info-box-number">{{ round(($banner->click_count / $banner->view_count) * 100, 2) }}%</span>
                                    </div>
                                </div>
                            @endif

                            <div class="info-box {{ $banner->isCurrentlyActive() ? 'bg-success' : 'bg-secondary' }}">
                                <span class="info-box-icon">
                                    <i class="fas {{ $banner->isCurrentlyActive() ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Статус показа</span>
                                    <span class="info-box-number">
                                        {{ $banner->isCurrentlyActive() ? 'Показывается' : 'Не показывается' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Действия</h3>
                        </div>
                        <div class="card-body">
                            <div class="btn-group-vertical btn-block">
                                <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Редактировать
                                </a>
                                
                                <button type="button" class="btn {{ $banner->is_active ? 'btn-secondary' : 'btn-success' }} toggle-active" 
                                        data-id="{{ $banner->id }}">
                                    <i class="fas {{ $banner->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                    {{ $banner->is_active ? 'Деактивировать' : 'Активировать' }}
                                </button>

                                <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" class="mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-block" 
                                            onclick="return confirm('Вы уверены, что хотите удалить этот баннер? Это действие нельзя отменить.')">
                                        <i class="fas fa-trash"></i> Удалить
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    @if($banner->link_url)
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Тест ссылки</h3>
                        </div>
                        <div class="card-body">
                            <a href="{{ $banner->link_url }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-external-link-alt"></i> Открыть ссылку в новой вкладке
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Переключение статуса активности
    document.querySelector('.toggle-active')?.addEventListener('click', function() {
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
                // Перезагрузим страницу для обновления всех данных
                location.reload();
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
</script>
@endsection
