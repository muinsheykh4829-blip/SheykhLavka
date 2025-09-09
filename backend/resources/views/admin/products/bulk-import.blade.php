@extends('admin.layout')

@section('title', 'Массовое добавление продуктов')

@section('actions')
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>
        Назад к списку
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-upload me-2"></i>
                    Массовое добавление продуктов
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.products.bulk-import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="csv_file" class="form-label">Файл CSV с продуктами</label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                        <div class="form-text">
                            Загрузите CSV файл с колонками: название, описание, цена, категория, единица, количество
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="default_category" class="form-label">Категория по умолчанию</label>
                        <select class="form-select" id="default_category" name="default_category_id">
                            <option value="">Выберите категорию</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="update_existing" name="update_existing">
                            <label class="form-check-label" for="update_existing">
                                Обновлять существующие продукты
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload me-1"></i>
                        Импортировать продукты
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Формат CSV файла
                </h5>
            </div>
            <div class="card-body">
                <p class="small">Файл должен содержать следующие колонки:</p>
                <ul class="small">
                    <li><strong>name</strong> - Название продукта</li>
                    <li><strong>description</strong> - Описание</li>
                    <li><strong>price</strong> - Цена в сомах</li>
                    <li><strong>category</strong> - Название категории</li>
                    <li><strong>unit</strong> - Единица измерения</li>
                    <li><strong>stock_quantity</strong> - Количество</li>
                    <li><strong>weight</strong> - Вес (опционально)</li>
                </ul>
                
                <div class="mt-3">
                    <a href="{{ route('admin.products.download-template') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download me-1"></i>
                        Скачать шаблон
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-magic me-2"></i>
                    Быстрое добавление
                </h5>
            </div>
            <div class="card-body">
                <p class="small">Добавить популярные продукты одним кликом:</p>
                
                <form action="{{ route('admin.products.add-popular') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-success btn-sm mb-2">
                        <i class="fas fa-apple-alt me-1"></i>
                        Добавить фрукты и овощи
                    </button>
                </form>
                
                <form action="{{ route('admin.products.add-dairy') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-info btn-sm mb-2">
                        <i class="fas fa-cheese me-1"></i>
                        Добавить молочные продукты
                    </button>
                </form>
                
                <form action="{{ route('admin.products.add-meat') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm mb-2">
                        <i class="fas fa-drumstick-bite me-1"></i>
                        Добавить мясные продукты
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('csv_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const fileName = file.name;
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        console.log(`Выбран файл: ${fileName} (${fileSize} MB)`);
    }
});
</script>
@endpush
