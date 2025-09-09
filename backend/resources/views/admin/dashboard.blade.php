@extends('admin.layout')

@section('title', 'Панель управления')
@section('page-title', 'Панель управления')

@section('content')
<div class="row">
    <!-- Статистика -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Пользователи</h5>
                        <h2>{{ $stats['users_count'] }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-people fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Продукты</h5>
                        <h2>{{ $stats['products_count'] }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-box fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Заказы</h5>
                        <h2>{{ $stats['orders_count'] }}</h2>
                        <small>Сегодня: {{ $stats['orders_today'] }}</small>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-cart3 fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Выручка</h5>
                        <h2>{{ number_format($stats['total_revenue'], 0, '.', ' ') }} с.</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-currency-dollar fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5>Быстрые действия</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle"></i> Добавить продукт
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <button class="btn btn-outline-success w-100" onclick="uploadFile('banner')">
                            <i class="bi bi-image"></i> Загрузить баннер
                        </button>
                    </div>
                    <div class="col-md-6 mb-3">
                        <button class="btn btn-outline-info w-100" onclick="uploadFile('welcome')">
                            <i class="bi bi-card-image"></i> Изменить приветствие
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Статистика заказов</h5>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">Все заказы</a>
            </div>
            <div class="card-body">
                @php
                $orderStats = [
                    'pending' => ['count' => $stats['pending_orders'] ?? 0, 'color' => 'warning', 'name' => 'Ожидают'],
                    'confirmed' => ['count' => $stats['confirmed_orders'] ?? 0, 'color' => 'info', 'name' => 'Подтверждены'],
                    'preparing' => ['count' => $stats['preparing_orders'] ?? 0, 'color' => 'primary', 'name' => 'Готовятся'],
                    'ready' => ['count' => $stats['ready_orders'] ?? 0, 'color' => 'secondary', 'name' => 'Готовы'],
                    'delivering' => ['count' => $stats['delivering_orders'] ?? 0, 'color' => 'primary', 'name' => 'Доставляются'],
                    'delivered' => ['count' => $stats['delivered_orders'] ?? 0, 'color' => 'success', 'name' => 'Доставлены'],
                    'cancelled' => ['count' => $stats['cancelled_orders'] ?? 0, 'color' => 'danger', 'name' => 'Отменены'],
                ];
                @endphp

                @foreach($orderStats as $status => $data)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-{{ $data['color'] }} me-2">{{ $data['name'] }}</span>
                    <strong>{{ $data['count'] }}</strong>
                </div>
                @endforeach

                <hr class="my-3">
                
                <div class="d-flex justify-content-between align-items-center">
                    <span>Всего заказов:</span>
                    <strong class="text-primary">{{ $stats['orders_count'] }}</strong>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <span>Сегодня:</span>
                    <strong class="text-success">{{ $stats['orders_today'] }}</strong>
                </div>

                @if(($stats['delivered_orders'] ?? 0) > 0)
                <div class="mt-3">
                    <small class="text-muted">Выручка с доставленных заказов:</small>
                    <h6 class="text-success">{{ number_format($stats['total_revenue'], 0, '.', ' ') }} с.</h6>
                </div>
                @endif
            </div>
        </div>

        <!-- Последние заказы -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>Последние заказы</h5>
            </div>
            <div class="card-body">
                @if(isset($recent_orders) && $recent_orders->count() > 0)
                    @foreach($recent_orders->take(5) as $order)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <strong>{{ $order->order_number }}</strong>
                            <br>
                            <small class="text-muted">{{ $order->created_at->format('d.m.Y H:i') }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-{{ $order->status_color }}">{{ $order->status_name }}</span>
                            <br>
                            <small><strong>{{ number_format($order->total, 0, '.', ' ') }} с.</strong></small>
                        </div>
                    </div>
                    @if(!$loop->last)<hr class="my-2">@endif
                    @endforeach
                @else
                    <div class="text-center text-muted">
                        <i class="bi bi-cart3 fs-1"></i>
                        <p>Заказы будут отображаться здесь</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для загрузки файлов -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Загрузка файла</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="fileType" name="type">
                    <div class="mb-3">
                        <label for="fileInput" class="form-label">Выберите файл</label>
                        <input type="file" class="form-control" id="fileInput" name="file" accept="image/*" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="doUpload()">Загрузить</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function uploadFile(type) {
    document.getElementById('fileType').value = type;
    new bootstrap.Modal(document.getElementById('uploadModal')).show();
}

async function doUpload() {
    const form = document.getElementById('uploadForm');
    const formData = new FormData(form);
    
    try {
        const response = await fetch('/admin/upload', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Файл загружен успешно: ' + result.file_path);
            bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
            location.reload();
        } else {
            alert('Ошибка: ' + result.message);
        }
    } catch (error) {
        alert('Ошибка загрузки файла');
        console.error(error);
    }
}
</script>
@endsection
