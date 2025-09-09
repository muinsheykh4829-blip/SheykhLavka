@extends('admin.layout')

@section('title', 'Продукты')
@section('page-title', 'Управление продуктами')

@section('page-actions')
    <div class="btn-group me-2">
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Добавить продукт
        </a>
        <button type="button" class="btn btn-success dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
            <span class="visually-hidden">Больше действий</span>
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('admin.products.bulk-import') }}">
                <i class="fas fa-upload me-2"></i>Массовое добавление
            </a></li>
            <li><a class="dropdown-item" href="{{ route('admin.products.download-template') }}">
                <i class="fas fa-download me-2"></i>Скачать шаблон CSV
            </a></li>
            <li><hr class="dropdown-divider"></li>
            <li><h6 class="dropdown-header">Быстрое добавление:</h6></li>
            <li>
                <form action="{{ route('admin.products.add-popular') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i class="fas fa-apple-alt me-2"></i>Фрукты и овощи
                    </button>
                </form>
            </li>
            <li>
                <form action="{{ route('admin.products.add-dairy') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i class="fas fa-cheese me-2"></i>Молочные продукты
                    </button>
                </form>
            </li>
            <li>
                <form action="{{ route('admin.products.add-meat') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i class="fas fa-drumstick-bite me-2"></i>Мясные продукты
                    </button>
                </form>
            </li>
        </ul>
    </div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <h5>Список продуктов</h5>
            </div>
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <select name="category_id" class="form-select me-2">
                        <option value="">Все категории</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" 
                                    {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <input type="text" 
                           name="search" 
                           class="form-control me-2" 
                           placeholder="Поиск..." 
                           value="{{ request('search') }}">
                    <button type="submit" class="btn btn-outline-primary">Поиск</button>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Изображение</th>
                        <th>Название</th>
                        <th>Категория</th>
                        <th>Цена</th>
                        <th>Склад</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>
                            @if($product->images && count($product->images) > 0)
                                <img src="{{ asset($product->images[0]) }}" 
                                     alt="{{ $product->name }}" 
                                     class="img-thumbnail" 
                                     style="max-width: 50px;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $product->name }}</strong>
                            @if($product->description)
                                <br><small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $product->category->name }}</span>
                        </td>
                        <td>
                            <strong>{{ format_somoni($product->price) }}</strong>
                            @if($product->discount_price && $product->discount_price < $product->price)
                                <br><small class="text-success">
                                    Скидка: {{ format_somoni($product->discount_price) }}
                                </small>
                            @endif
                        </td>
                        <td>
                            @php
                                $type = $product->product_type ?? 'piece';
                                $typeLabel = $type === 'weight' ? 'кг' : ($type === 'package' ? 'упак' : 'шт');
                                $available = ($product->stock_quantity_current ?? 0) - ($product->stock_quantity_reserved ?? 0);
                            @endphp
                            <span class="badge bg-light text-dark">{{ $type }}</span>
                            <div><strong>{{ $available }}</strong> {{ $product->stock_unit ?? $typeLabel }}</div>
                        </td>
                        <td>
                            @if($product->is_active)
                                <span class="badge bg-success">Доступен</span>
                            @else
                                <span class="badge bg-danger">Недоступен</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.products.show', $product) }}" 
                                   class="btn btn-outline-info" 
                                   title="Просмотр">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.products.edit', $product) }}" 
                                   class="btn btn-outline-primary"
                                   title="Редактировать">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.products.destroy', $product) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Вы уверены, что хотите удалить продукт \'{{ $product->name }}\'?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-outline-danger"
                                            title="Удалить">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-box fs-1 d-block mb-2"></i>
                            Продукты не найдены
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($products->hasPages())
            <div class="d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
