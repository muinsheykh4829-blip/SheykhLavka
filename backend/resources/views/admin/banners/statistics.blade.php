@extends('admin.layout')

@section('title', 'Статистика баннеров')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Статистика баннеров</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Главная</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.banners.index') }}">Баннеры</a></li>
                        <li class="breadcrumb-item active">Статистика</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Общая статистика -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $stats['total'] }}</h3>
                            <p>Всего баннеров</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-images"></i>
                        </div>
                        <a href="{{ route('admin.banners.index') }}" class="small-box-footer">
                            Подробнее <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $stats['active'] }}</h3>
                            <p>Активных баннеров</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <a href="{{ route('admin.banners.index', ['status' => 'active']) }}" class="small-box-footer">
                            Подробнее <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ number_format($stats['total_views']) }}</h3>
                            <p>Всего просмотров</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="small-box-footer">
                            <i class="fas fa-eye"></i> Просмотры
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ number_format($stats['total_clicks']) }}</h3>
                            <p>Всего кликов</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-mouse-pointer"></i>
                        </div>
                        <div class="small-box-footer">
                            @if($stats['total_views'] > 0)
                                CTR: {{ round(($stats['total_clicks'] / $stats['total_views']) * 100, 2) }}%
                            @else
                                CTR: 0%
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Топ баннеров -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Топ-10 баннеров по кликам</h3>
                            <div class="card-tools">
                                <a href="{{ route('admin.banners.index') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-list"></i> Все баннеры
                                </a>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            @if($stats['top_banners']->count() > 0)
                                <table class="table table-hover text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>Позиция</th>
                                            <th>ID</th>
                                            <th>Название</th>
                                            <th>Просмотры</th>
                                            <th>Клики</th>
                                            <th>CTR</th>
                                            <th>Эффективность</th>
                                            <th>Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stats['top_banners'] as $index => $banner)
                                            @php
                                                $ctr = $banner->view_count > 0 ? ($banner->click_count / $banner->view_count) * 100 : 0;
                                                $effectiveness = '';
                                                $effectivenessClass = '';
                                                
                                                if ($ctr >= 5) {
                                                    $effectiveness = 'Отлично';
                                                    $effectivenessClass = 'badge-success';
                                                } elseif ($ctr >= 2) {
                                                    $effectiveness = 'Хорошо';
                                                    $effectivenessClass = 'badge-info';
                                                } elseif ($ctr >= 1) {
                                                    $effectiveness = 'Средне';
                                                    $effectivenessClass = 'badge-warning';
                                                } else {
                                                    $effectiveness = 'Низко';
                                                    $effectivenessClass = 'badge-danger';
                                                }
                                            @endphp
                                            <tr>
                                                <td>
                                                    @if($index == 0)
                                                        <i class="fas fa-trophy text-warning"></i>
                                                    @elseif($index == 1)
                                                        <i class="fas fa-medal text-secondary"></i>
                                                    @elseif($index == 2)
                                                        <i class="fas fa-award text-warning"></i>
                                                    @else
                                                        {{ $index + 1 }}
                                                    @endif
                                                </td>
                                                <td>{{ $banner->id }}</td>
                                                <td>{{ Str::limit($banner->title, 40) }}</td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        {{ number_format($banner->view_count) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-success">
                                                        {{ number_format($banner->click_count) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong>{{ number_format($ctr, 2) }}%</strong>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $effectivenessClass }}">
                                                        {{ $effectiveness }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('admin.banners.show', $banner->id) }}" 
                                                           class="btn btn-info btn-sm">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.banners.edit', $banner->id) }}" 
                                                           class="btn btn-warning btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="text-center p-4">
                                    <h5>Статистика отсутствует</h5>
                                    <p class="text-muted">Создайте баннеры для получения статистики</p>
                                    <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Создать первый баннер
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Дополнительная статистика -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Распределение по статусу</h3>
                        </div>
                        <div class="card-body">
                            <div class="progress mb-3">
                                <div class="progress-bar bg-success" 
                                     style="width: {{ $stats['total'] > 0 ? ($stats['active'] / $stats['total']) * 100 : 0 }}%">
                                    Активные: {{ $stats['active'] }}
                                </div>
                                <div class="progress-bar bg-secondary" 
                                     style="width: {{ $stats['total'] > 0 ? ($stats['inactive'] / $stats['total']) * 100 : 0 }}%">
                                    Неактивные: {{ $stats['inactive'] }}
                                </div>
                            </div>
                            
                            <div class="row text-center">
                                <div class="col">
                                    <div class="border-right">
                                        <h4 class="text-success">{{ $stats['active'] }}</h4>
                                        <small class="text-muted">Активных</small>
                                    </div>
                                </div>
                                <div class="col">
                                    <h4 class="text-secondary">{{ $stats['inactive'] }}</h4>
                                    <small class="text-muted">Неактивных</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Общая эффективность</h3>
                        </div>
                        <div class="card-body">
                            @if($stats['total_views'] > 0)
                                @php
                                    $overallCtr = ($stats['total_clicks'] / $stats['total_views']) * 100;
                                @endphp
                                <div class="text-center mb-3">
                                    <h2 class="text-primary">{{ number_format($overallCtr, 2) }}%</h2>
                                    <p class="text-muted">Общий CTR</p>
                                </div>
                                
                                <div class="progress mb-2">
                                    <div class="progress-bar 
                                        @if($overallCtr >= 5) bg-success
                                        @elseif($overallCtr >= 2) bg-info  
                                        @elseif($overallCtr >= 1) bg-warning
                                        @else bg-danger
                                        @endif" 
                                         style="width: {{ min($overallCtr * 10, 100) }}%">
                                    </div>
                                </div>
                                
                                <small class="text-muted">
                                    @if($overallCtr >= 5)
                                        Отличная эффективность! 🎉
                                    @elseif($overallCtr >= 2)
                                        Хорошая эффективность 👍
                                    @elseif($overallCtr >= 1)
                                        Средняя эффективность 📊
                                    @else
                                        Низкая эффективность - требуется оптимизация 📈
                                    @endif
                                </small>
                            @else
                                <div class="text-center">
                                    <h4 class="text-muted">Нет данных</h4>
                                    <p>Статистика появится после показов баннеров</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
