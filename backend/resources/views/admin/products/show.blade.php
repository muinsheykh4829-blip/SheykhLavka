@extends('admin.layout')

@section('title', $product->name)
@section('page-title', 'Просмотр продукта')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>{{ $product->name }}</h5>
                <div>
                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil"></i> Редактировать
                    </a>
                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" 
                                onclick="return confirm('Вы уверены, что хотите удалить этот продукт?')">
                            <i class="bi bi-trash"></i> Удалить
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                @if($product->description)
                <div class="mb-4">
                    <h6>Описание</h6>
                    <p class="text-muted">{{ $product->description }}</p>
                </div>
                @endif

                <div class="row mb-4">
                    <div class="col-md-4">
                        <h6>Цена</h6>
                        <div class="d-flex align-items-center">
                            @if($product->discount_price)
                                <span class="h5 text-success mb-0 me-2">{{ format_somoni($product->discount_price) }}</span>
                                <span class="text-muted text-decoration-line-through">{{ format_somoni($product->price) }}</span>
                            @else
                                <span class="h5 mb-0">{{ format_somoni($product->price) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6>Категория</h6>
                        <p class="mb-0">
                            <span class="badge bg-secondary">{{ $product->category->name ?? 'Без категории' }}</span>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6>Статус</h6>
                        <p class="mb-0">
                            @if($product->is_active)
                                <span class="badge bg-success">Активный</span>
                            @else
                                <span class="badge bg-danger">Неактивный</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <h6>Склад</h6>
                        <div class="row">
                            <div class="col-md-3"><strong>Тип:</strong> {{ $product->product_type ?? 'piece' }}</div>
                            <div class="col-md-3"><strong>Ед. склада:</strong> {{ $product->stock_unit ?? (($product->product_type === 'weight') ? 'кг' : 'шт') }}</div>
                            <div class="col-md-3"><strong>Остаток:</strong> {{ $product->stock_quantity_current ?? 0 }}</div>
                            <div class="col-md-3"><strong>Резерв:</strong> {{ $product->stock_quantity_reserved ?? 0 }}</div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-3"><strong>Доступно:</strong> {{ ($product->stock_quantity_current ?? 0) - ($product->stock_quantity_reserved ?? 0) }}</div>
                            <div class="col-md-3"><strong>Минимум:</strong> {{ $product->stock_quantity_minimum ?? 0 }}</div>
                            <div class="col-md-6"><strong>Автодеактивация:</strong> {{ ($product->auto_deactivate_on_zero ?? false) ? 'Включена' : 'Выключена' }}</div>
                        </div>
                    </div>
                </div>

                @if($product->weight || $product->unit)
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Вес/Объем</h6>
                        <p class="mb-0">{{ $product->weight ?? 'Не указан' }} {{ $product->unit ?? 'шт' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Остаток на складе</h6>
                        <p class="mb-0">{{ $product->stock_quantity ?? 'Не указан' }}</p>
                    </div>
                </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <h6>Дата создания</h6>
                        <p class="mb-0">{{ tj_date($product->created_at, 'd.m.Y H:i') }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Последнее обновление</h6>
                        <p class="mb-0">{{ tj_date($product->updated_at, 'd.m.Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5>Изображение</h5>
            </div>
            <div class="card-body text-center">
                @if($product->images)
                    @php
                        $imagePath = is_array($product->images) ? ($product->images[0] ?? '') : $product->images;
                    @endphp
                    @if($imagePath)
                        <img src="{{ asset($imagePath) }}" 
                             alt="{{ $product->name }}" 
                             class="img-fluid rounded shadow-sm" 
                             style="max-height: 300px;">
                    @else
                        <div class="text-muted">
                            <i class="bi bi-image" style="font-size: 3rem;"></i>
                            <p class="mt-2">Изображение отсутствует</p>
                        </div>
                    @endif
                @else
                    <div class="text-muted">
                        <i class="bi bi-image" style="font-size: 3rem;"></i>
                        <p class="mt-2">Изображение отсутствует</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5>Действия</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Редактировать продукт
                    </a>
                    
                    @if($product->is_active)
                        <form action="{{ route('admin.products.update', $product) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="name" value="{{ $product->name }}">
                            <input type="hidden" name="price" value="{{ $product->price }}">
                            <input type="hidden" name="category_id" value="{{ $product->category_id }}">
                            <input type="hidden" name="description" value="{{ $product->description }}">
                            <input type="hidden" name="discount_price" value="{{ $product->discount_price }}">
                            <input type="hidden" name="weight" value="{{ $product->weight }}">
                            <input type="hidden" name="unit" value="{{ $product->unit }}">
                            <input type="hidden" name="image" value="{{ is_array($product->images) ? ($product->images[0] ?? '') : $product->images }}">
                            <!-- Не передаем is_available, чтобы деактивировать -->
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="bi bi-pause-circle"></i> Деактивировать
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.products.update', $product) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="name" value="{{ $product->name }}">
                            <input type="hidden" name="price" value="{{ $product->price }}">
                            <input type="hidden" name="category_id" value="{{ $product->category_id }}">
                            <input type="hidden" name="description" value="{{ $product->description }}">
                            <input type="hidden" name="discount_price" value="{{ $product->discount_price }}">
                            <input type="hidden" name="weight" value="{{ $product->weight }}">
                            <input type="hidden" name="unit" value="{{ $product->unit }}">
                            <input type="hidden" name="image" value="{{ is_array($product->images) ? ($product->images[0] ?? '') : $product->images }}">
                            <input type="hidden" name="is_available" value="1">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-play-circle"></i> Активировать
                            </button>
                        </form>
                    @endif
                    
                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100" 
                                onclick="return confirm('Вы уверены, что хотите удалить этот продукт? Это действие нельзя отменить.')">
                            <i class="bi bi-trash"></i> Удалить продукт
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Назад к списку продуктов
        </a>
    </div>
</div>
@endsection
