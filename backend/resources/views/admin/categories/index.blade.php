@extends('admin.layout')

@section('title', 'Категории')
@section('page-title', 'Управление категориями')

@php
    use Illuminate\Support\Str;
@endphp

@section('page-actions')
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Добавить категорию
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <h5>Список категорий</h5>
            </div>
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <input type="text" 
                           name="search" 
                           class="form-control me-2" 
                           placeholder="Поиск категорий..." 
                           value="{{ request('search') }}">
                    <button type="submit" class="btn btn-outline-primary">Поиск</button>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Иконка</th>
                        <th>Название</th>
                        <th>Slug</th>
                        <th>Товаров</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td>
                            <a href="{{ route('admin.categories.show', $category) }}" class="text-decoration-none">
                                @if($category->icon)
                                    <img src="{{ asset('assets/categories/' . $category->icon) }}" 
                                         alt="{{ $category->name_ru }}" 
                                         class="img-thumbnail" 
                                         style="max-width: 40px; max-height: 40px;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px; border-radius: 4px; display: none;">
                                        <i class="bi bi-tag text-muted"></i>
                                    </div>
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px; border-radius: 4px;">
                                        <i class="bi bi-tag text-muted"></i>
                                    </div>
                                @endif
                            </a>
                        </td>
                        <td>
                            <div>
                                <a href="{{ route('admin.categories.show', $category) }}" class="text-decoration-none">
                                    <strong>{{ $category->name_ru }}</strong>
                                </a>
                                @if($category->name !== $category->name_ru)
                                    <br><small class="text-muted">{{ $category->name }}</small>
                                @endif
                                @if($category->description)
                                    <br><small class="text-muted">{{ Str::limit($category->description, 50) }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <code>{{ $category->slug }}</code>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                {{ $category->products_count ?? 0 }} 
                                {{ $category->products_count == 1 ? 'товар' : ($category->products_count < 5 ? 'товара' : 'товаров') }}
                            </span>
                            @if($category->products_count > 0)
                                <a href="{{ route('admin.products.index') }}?category={{ $category->id }}" 
                                   class="btn btn-sm btn-link p-0 ms-1" title="Показать товары">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.categories.toggle-status', $category) }}" 
                                  method="POST" 
                                  class="d-inline status-toggle-form">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="btn btn-sm p-0 border-0 bg-transparent"
                                        title="{{ $category->is_active ? 'Деактивировать категорию' : 'Активировать категорию' }}">
                                    @if($category->is_active)
                                        <span class="badge bg-success">Активна</span>
                                    @else
                                        <span class="badge bg-secondary">Неактивна</span>
                                    @endif
                                </button>
                            </form>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.categories.show', $category) }}" 
                                   class="btn btn-outline-info" title="Просмотр">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.categories.edit', $category) }}" 
                                   class="btn btn-outline-primary" title="Редактировать">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.categories.destroy', $category) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Удалить категорию {{ $category->name_ru }}?\n\nВнимание: если в категории есть товары, удаление будет невозможно.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Удалить">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-tags fs-1 d-block mb-2"></i>
                            Категории не найдены
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($categories->hasPages())
            <div class="d-flex justify-content-center">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Подтверждение переключения статуса
    document.querySelectorAll('.status-toggle-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const badge = this.querySelector('.badge');
            const isActive = badge.classList.contains('bg-success');
            const action = isActive ? 'деактивировать' : 'активировать';
            const categoryName = this.closest('tr').querySelector('strong').textContent;
            
            if (!confirm(`Вы уверены, что хотите ${action} категорию "${categoryName}"?`)) {
                e.preventDefault();
            }
        });
    });
    
    // Автозапуск поиска при вводе
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.form.submit();
            }, 500);
        });
    }
});
</script>
@endsection
