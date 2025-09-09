@extends('admin.layout')

@section('title', 'Редактировать продукт')
@section('page-title', 'Редактировать продукт')

@section('content')
<form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5>Информация о продукте</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Название продукта *</label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $product->name) }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Описание</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="4">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label for="price" class="form-label">Цена продажи (сомони) *</label>
                            <input type="number" 
                                   class="form-control @error('price') is-invalid @enderror" 
                                   id="price" 
                                   name="price" 
                                   value="{{ old('price', $product->price) }}" 
                                   step="0.01" 
                                   min="0" 
                                   required>
                            <div class="form-text">Цена для покупателей в приложении</div>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="discount_price" class="form-label">Себестоимость (сомони)</label>
                            <input type="number" 
                                   class="form-control @error('discount_price') is-invalid @enderror" 
                                   id="discount_price" 
                                   name="discount_price" 
                                   value="{{ old('discount_price', $product->discount_price) }}" 
                                   step="0.01" 
                                   min="0">
                            <div class="form-text">Цена закупки для расчёта прибыли</div>
                            @error('discount_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Склад -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Склад</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="product_type" class="form-label">Тип товара</label>
                            <select class="form-select" id="product_type" name="product_type">
                                <option value="piece" {{ old('product_type', $product->product_type ?? 'piece') == 'piece' ? 'selected' : '' }}>Штучный (шт)</option>
                                <option value="weight" {{ old('product_type', $product->product_type) == 'weight' ? 'selected' : '' }}>Весовой (кг)</option>
                                <option value="package" {{ old('product_type', $product->product_type) == 'package' ? 'selected' : '' }}>Упаковка (упак)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="stock_quantity_current" class="form-label">Остаток на складе</label>
                            <input type="number"
                                   class="form-control @error('stock_quantity_current') is-invalid @enderror"
                                   id="stock_quantity_current"
                                   name="stock_quantity_current"
                                   value="{{ old('stock_quantity_current', $product->stock_quantity_current) }}"
                                   step="0.001" min="0" placeholder="0">
                            @error('stock_quantity_current')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="stock_unit" class="form-label">Единица склада</label>
                            <select class="form-select" id="stock_unit" name="stock_unit">
                                <option value="шт" {{ old('stock_unit', $product->stock_unit) == 'шт' ? 'selected' : '' }}>шт</option>
                                <option value="кг" {{ old('stock_unit', $product->stock_unit) == 'кг' ? 'selected' : '' }}>кг</option>
                                <option value="упак" {{ old('stock_unit', $product->stock_unit) == 'упак' ? 'selected' : '' }}>упак</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="stock_quantity_minimum" class="form-label">Минимальный остаток</label>
                            <input type="number"
                                   class="form-control @error('stock_quantity_minimum') is-invalid @enderror"
                                   id="stock_quantity_minimum"
                                   name="stock_quantity_minimum"
                                   value="{{ old('stock_quantity_minimum', $product->stock_quantity_minimum) }}"
                                   step="0.001" min="0" placeholder="0">
                            <div class="form-text">При достижении покажет «мало на складе»</div>
                            @error('stock_quantity_minimum')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="auto_deactivate_on_zero" name="auto_deactivate_on_zero" value="1" {{ old('auto_deactivate_on_zero', $product->auto_deactivate_on_zero ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="auto_deactivate_on_zero">Автодеактивация при нуле</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5>Параметры</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Категория *</label>
                        <select class="form-select @error('category_id') is-invalid @enderror" 
                                id="category_id" 
                                name="category_id" 
                                required>
                            <option value="">Выберите категорию</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                        {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_available" 
                                   name="is_available" 
                                   value="1" 
                                   {{ old('is_available', $product->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_available">
                                Доступен для заказа
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5>Изображения</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="image" class="form-label">Изображение (URL)</label>
                        <input type="text" 
                               class="form-control @error('image') is-invalid @enderror" 
                               id="image" 
                               name="image" 
                               value="{{ old('image') }}" 
                               placeholder="assets/products/product.jpg">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Или введите URL изображения</small>
                    </div>

                    <div class="mb-3">
                        <label for="product_images" class="form-label">Загрузить новые изображения</label>
                        <input type="file" 
                               class="form-control @error('product_images.*') is-invalid @enderror" 
                               id="product_images" 
                               name="product_images[]" 
                               accept="image/*"
                               multiple
                               onchange="previewProductImages(this)">
                        @error('product_images.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Можно выбрать несколько изображений. Поддерживаемые форматы: JPG, PNG, GIF, WEBP. Максимум 2MB каждое</small>
                        <div id="product-images-preview" class="mt-2"></div>
                    </div>
                    
                    @if($product->images && count($product->images) > 0)
                    <div class="mt-3">
                        <label class="form-label">Текущие изображения:</label>
                        <div class="row">
                            @foreach($product->images as $image)
                                <div class="col-md-3 mb-2">
                                    <img src="{{ str_starts_with($image, 'http') ? $image : asset($image) }}" 
                                         alt="{{ $product->name }}" 
                                         class="img-thumbnail" 
                                         style="width: 100%; max-height: 120px; object-fit: cover;">
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Отмена
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Обновить продукт
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Модальное окно для выбора изображения -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Выбрать изображение</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="imageGallery">
                    <!-- Изображения будут загружены через JavaScript -->
                </div>
                
                <hr>
                
                <form id="uploadImageForm" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <input type="file" class="form-control" name="file" accept="image/*" required>
                            <input type="hidden" name="type" value="product">
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-success w-100" onclick="uploadNewImage()">
                                <i class="bi bi-cloud-upload"></i> Загрузить новое
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function syncStockUnitByType() {
    const type = document.getElementById('product_type').value;
    const unit = document.getElementById('stock_unit');
    const qty = document.getElementById('stock_quantity_current');
    if (type === 'weight') {
        unit.value = 'кг';
        qty.step = '0.001';
    } else if (type === 'package') {
        unit.value = 'упак';
        qty.step = '1';
    } else {
        unit.value = 'шт';
        qty.step = '1';
    }
}

document.getElementById('product_type').addEventListener('change', syncStockUnitByType);
window.addEventListener('load', syncStockUnitByType);

function selectImage() {
    loadImageGallery();
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}

function loadImageGallery() {
    const gallery = document.getElementById('imageGallery');
    gallery.innerHTML = '<div class="col-12 text-center"><div class="spinner-border" role="status"></div></div>';
    
    // Здесь можно загрузить существующие изображения
    // Пока что просто показываем заглушку
    setTimeout(() => {
        gallery.innerHTML = `
            <div class="col-12 text-center text-muted">
                <p>Загрузите новое изображение или введите путь вручную</p>
            </div>
        `;
    }, 500);
}

async function uploadNewImage() {
    const form = document.getElementById('uploadImageForm');
    const formData = new FormData(form);
    
    try {
        const response = await fetch('/admin/upload', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('image').value = result.file_path;
            bootstrap.Modal.getInstance(document.getElementById('imageModal')).hide();
            alert('Изображение загружено успешно');
        } else {
            alert('Ошибка: ' + result.message);
        }
    } catch (error) {
        alert('Ошибка загрузки изображения');
        console.error(error);
    }
}

function previewProductImages(input) {
    const preview = document.getElementById('product-images-preview');
    const files = input.files;
    
    if (files && files.length > 0) {
        let previewHTML = '<div class="mt-2"><label class="form-label">Предварительный просмотр новых изображений:</label><div class="row">';
        
        Array.from(files).forEach((file, i) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewHTML += `
                        <div class="col-md-3 mb-2">
                            <img src="${e.target.result}" 
                                 alt="Предварительный просмотр ${i + 1}" 
                                 class="img-thumbnail" 
                                 style="width: 100%; height: 120px; object-fit: cover;">
                        </div>
                    `;
                    
                    // Если это последний файл, добавляем HTML
                    if (i === files.length - 1) {
                        previewHTML += '</div></div>';
                        preview.innerHTML = previewHTML;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    } else {
        preview.innerHTML = '';
    }
}
</script>
@endsection
