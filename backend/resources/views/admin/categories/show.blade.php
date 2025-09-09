@extends('admin.layout')

@section('title', 'Категория: ' . $category->name_ru)
@section('page-title', 'Категория: ' . $category->name_ru)

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Редактировать
        </a>
        <a href="{{ route('admin.products.create') }}?category_id={{ $category->id }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Добавить товар
        </a>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5>Информация о категории</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td width="150"><strong>ID:</strong></td>
                        <td>{{ $category->id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Название (RU):</strong></td>
                        <td>{{ $category->name_ru }}</td>
                    </tr>
                    <tr>
                        <td><strong>Название (EN):</strong></td>
                        <td>{{ $category->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Slug:</strong></td>
                        <td><code>{{ $category->slug }}</code></td>
                    </tr>
                    @if($category->description)
                    <tr>
                        <td><strong>Описание:</strong></td>
                        <td>{{ $category->description }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Статус:</strong></td>
                        <td>
                            @if($category->is_active)
                                <span class="badge bg-success">Активна</span>
                            @else
                                <span class="badge bg-secondary">Неактивна</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Создана:</strong></td>
                        <td>{{ $category->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Обновлена:</strong></td>
                        <td>{{ $category->updated_at->format('d.m.Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5>Иконка категории</h5>
            </div>
            <div class="card-body text-center">
                @if($category->icon)
                    <img src="{{ asset('assets/categories/' . $category->icon) }}" 
                         alt="{{ $category->name_ru }}" 
                         class="img-fluid mb-3" 
                         style="max-width: 150px; max-height: 150px;"
                         onerror="this.style.display='none'; document.getElementById('no-icon').style.display='block';">
                @endif
                <div id="no-icon" style="{{ $category->icon ? 'display: none;' : '' }}">
                    <i class="bi bi-tag" style="font-size: 4rem; color: #ccc;"></i>
                    <p class="text-muted mt-2">Иконка не установлена</p>
                </div>
                @if($category->icon)
                    <p class="small text-muted">{{ $category->icon }}</p>
                @endif
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5>Статистика товаров</h5>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="h3 text-primary">{{ $stats['total_products'] ?? 0 }}</div>
                        <small class="text-muted">Всего товаров</small>
                    </div>
                    <div class="col-6">
                        <div class="h3 text-success">{{ $stats['active_products'] ?? 0 }}</div>
                        <small class="text-muted">Активных</small>
                    </div>
                </div>
                @if($stats['total_products'] > 0)
                <div class="row text-center">
                    <div class="col-12">
                        <div class="h5 text-info">{{ $stats['featured_products'] ?? 0 }}</div>
                        <small class="text-muted">Рекомендуемых</small>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <small class="text-muted">Цены:</small>
                        <div>
                            <span class="badge bg-light text-dark">От {{ number_format($stats['min_price'] ?? 0, 0, '.', ' ') }} с.</span>
                            <span class="badge bg-light text-dark">До {{ number_format($stats['max_price'] ?? 0, 0, '.', ' ') }} с.</span>
                        </div>
                        <div class="mt-2">
                            <small>Средняя: {{ number_format($stats['avg_price'] ?? 0, 0, '.', ' ') }} с.</small>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($stats['total_products'] > 0)
<div class="card mt-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Товары в категории</h5>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('admin.products.create') }}?category_id={{ $category->id }}" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Добавить товар
                </a>
                <a href="{{ route('admin.products.index') }}?category={{ $category->id }}" class="btn btn-primary">
                    <i class="bi bi-list"></i> Все товары
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if(isset($products) && $products->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">Фото</th>
                            <th>Название</th>
                            <th style="width: 120px;">Цена</th>
                            <th style="width: 100px;">Остаток</th>
                            <th style="width: 80px;">Статус</th>
                            <th style="width: 120px;">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>
                                @if($product->image)
                                    <img src="{{ asset('assets/products/' . $product->image) }}" 
                                         alt="{{ $product->name_ru }}" 
                                         class="img-thumbnail" 
                                         style="max-width: 40px; max-height: 40px;"
                                         onerror="this.style.display='none'">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px; border-radius: 4px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $product->name_ru ?? $product->name }}</strong>
                                    @if($product->is_featured)
                                        <span class="badge bg-warning text-dark ms-1">Рекомендуемый</span>
                                    @endif
                                    @if($product->name_ru && $product->name !== $product->name_ru)
                                        <br><small class="text-muted">{{ $product->name }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ number_format($product->price, 0, '.', ' ') }} с.</strong>
                                    @if($product->old_price && $product->old_price > $product->price)
                                        <br><small class="text-decoration-line-through text-muted">{{ number_format($product->old_price, 0, '.', ' ') }} с.</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $product->stock_quantity > 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $product->stock_quantity }} {{ $product->unit ?? 'шт' }}
                                </span>
                            </td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge bg-success">Активен</span>
                                @else
                                    <span class="badge bg-secondary">Неактивен</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.products.show', $product) }}" 
                                       class="btn btn-outline-info" title="Просмотр">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}" 
                                       class="btn btn-outline-primary" title="Редактировать">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($products->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $products->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-4">
                <i class="bi bi-box-seam fs-1 text-muted d-block mb-2"></i>
                <p class="text-muted">В этой категории пока нет товаров</p>
                <a href="{{ route('admin.products.create') }}?category_id={{ $category->id }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Добавить первый товар
                </a>
            </div>
        @endif
    </div>
</div>
@else
<div class="card mt-4">
    <div class="card-body text-center py-5">
        <i class="bi bi-box-seam fs-1 text-muted d-block mb-3"></i>
        <h5 class="text-muted">В этой категории нет товаров</h5>
        <p class="text-muted">Добавьте товары в эту категорию, чтобы она стала видимой в приложении</p>
        <a href="{{ route('admin.products.create') }}?category_id={{ $category->id }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Добавить товары
        </a>
    </div>
</div>
@endif
@endsection
