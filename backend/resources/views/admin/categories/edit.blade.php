@extends('admin.layout')

@section('title', 'Редактировать категорию')
@section('page-title', 'Редактировать категорию: ' . $category->name_ru)

@section('content')
<form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5>Информация о категории</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name_ru" class="form-label">Название (RU) *</label>
                        <input type="text" 
                               class="form-control @error('name_ru') is-invalid @enderror" 
                               id="name_ru" 
                               name="name_ru" 
                               value="{{ old('name_ru', $category->name_ru) }}" 
                               required
                               onkeyup="generateSlug()">
                        @error('name_ru')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug *</label>
                        <input type="text" 
                               class="form-control @error('slug') is-invalid @enderror" 
                               id="slug" 
                               name="slug" 
                               value="{{ old('slug', $category->slug) }}" 
                               required>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Используется в URL, только латинские буквы, цифры и дефисы</small>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Описание</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="3">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                        <label for="icon" class="form-label">Иконка (URL)</label>
                        <input type="text" 
                               class="form-control @error('icon') is-invalid @enderror" 
                               id="icon" 
                               name="icon" 
                               value="{{ old('icon', $category->icon) }}" 
                               placeholder="assets/categories/vegetables.png"
                               onkeyup="showIconPreview()">
                        @error('icon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Или введите URL изображения</small>
                    </div>

                    <div class="mb-3">
                        <label for="icon_file" class="form-label">Загрузить новую иконку</label>
                        <input type="file" 
                               class="form-control @error('icon_file') is-invalid @enderror" 
                               id="icon_file" 
                               name="icon_file" 
                               accept="image/*"
                               onchange="previewUploadedImage(this)">
                        @error('icon_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Поддерживаемые форматы: JPG, PNG, GIF, WEBP. Максимум 2MB</small>
                        
                        @if($category->icon)
                            <div class="mt-2">
                                <label class="form-label">Текущая иконка:</label><br>
                                <img src="{{ str_starts_with($category->icon, 'http') ? $category->icon : asset($category->icon) }}" 
                                     alt="Иконка категории" 
                                     style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; padding: 5px;">
                            </div>
                        @endif
                        
                        <div id="icon-preview" class="mt-2"></div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Категория активна
                        </label>
                    </div>
                    
                    @if($category->products()->count() > 0)
                        <div class="alert alert-info mt-3">
                            <small>
                                <i class="bi bi-info-circle"></i>
                                В этой категории {{ $category->products()->count() }} товаров
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Сохранить изменения
                </button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Отмена
                </a>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
// Список доступных иконок  
const availableIcons = [
    'assets/categories/vegetables.png',
    'assets/categories/fruits.png', 
    'assets/categories/greens_spices.png',
    'assets/categories/dairy.png',
    'assets/categories/meat_poultry.png',
    'assets/categories/bakery.png',
    'assets/categories/fish_seafood.png',
    'assets/categories/eggs.png',
    'assets/categories/grains_pasta_legumes.png',
    'assets/categories/flour_sugar_salt.png',
    'assets/categories/oils_sauces.png',
    'assets/categories/drinks.png',
    'assets/categories/tea_coffee.png',
    'assets/categories/candies_chocolate.png',
    'assets/categories/canned_preserves.png',
    'assets/categories/spices_seasonings.png',
    'assets/categories/frozen.png',
    'assets/categories/baby_food.png',
    'assets/categories/breakfast.png',
    'assets/categories/sausages_semifinished.png',
    'assets/categories/household_chemicals.png',
    'assets/categories/personal_care.png',
    'assets/categories/pet_products.png',
    'assets/categories/household_goods.png'
];

function generateSlug() {
    const nameRu = document.getElementById('name_ru').value;
    const name = document.getElementById('name').value;
    
    // Если не заполнено английское название, используем русское
    if (!name) {
        document.getElementById('name').value = nameRu;
    }
    
    // Генерируем slug из русского названия
    const slug = nameRu.toLowerCase()
        .replace(/[а-я]/g, function(char) {
            const translit = {
                'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo',
                'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
                'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
                'ф': 'f', 'х': 'kh', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'shch',
                'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya'
            };
            return translit[char] || char;
        })
        .replace(/[^a-z0-9]/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
    
    document.getElementById('slug').value = slug;
}

function selectCategoryIcon() {
    let options = 'Выберите иконку:\n\n';
    availableIcons.forEach((icon, index) => {
        const name = icon.split('/').pop().replace('.png', '').replace(/_/g, ' ');
        options += `${index + 1}. ${name}\n`;
    });
    
    const choice = prompt(options + '\nВведите номер иконки:');
    if (choice && choice > 0 && choice <= availableIcons.length) {
        document.getElementById('icon').value = availableIcons[choice - 1];
        showIconPreview();
    }
}

function showIconPreview() {
    const iconPath = document.getElementById('icon').value;
    const preview = document.getElementById('icon-preview');
    
    if (iconPath) {
        preview.innerHTML = `<img src="/${iconPath}" class="img-thumbnail" style="max-width: 60px;" onerror="this.style.display='none'">`;
    } else {
        preview.innerHTML = '';
    }
}

function previewUploadedImage(input) {
    const preview = document.getElementById('icon-preview');
    const file = input.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <div class="mt-2">
                    <label class="form-label">Предварительный просмотр новой иконки:</label><br>
                    <img src="${e.target.result}" 
                         alt="Предварительный просмотр" 
                         style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; padding: 5px;">
                </div>
            `;
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
}

// Показать превью при изменении поля
document.getElementById('icon').addEventListener('input', showIconPreview);

// Показать превью при загрузке страницы
window.addEventListener('load', function() {
    const iconInput = document.getElementById('icon');
    if (iconInput.value) {
        showIconPreview();
    }
});
</script>
@endsection
