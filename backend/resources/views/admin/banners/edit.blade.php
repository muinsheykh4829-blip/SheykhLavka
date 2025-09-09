@extends('admin.layout')

@section('title', 'Редактировать баннер')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Редактировать баннер</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Главная</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.banners.index') }}">Баннеры</a></li>
                        <li class="breadcrumb-item active">Редактировать</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Изображение баннера</h3>
                            </div>
                            <div class="card-body">
                                @if($banner->image)
                                    <div class="mb-3">
                                        <label>Текущее изображение:</label>
                                        <div>
                                            <img src="{{ str_starts_with($banner->image, 'http') ? $banner->image : asset($banner->image) }}" 
                                                 alt="Current banner" 
                                                 class="img-fluid" style="max-height: 300px;">
                                        </div>
                                    </div>
                                @endif

                                <div class="form-group">
                                    <label for="image">{{ $banner->image ? 'Заменить изображение' : 'Изображение баннера *' }}</label>
                                    <input type="file" class="form-control-file" id="image" name="image" 
                                           accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" {{ $banner->image ? '' : 'required' }}>
                                    <small class="form-text text-muted">
                                        Поддерживаемые форматы: JPEG, PNG, JPG, GIF, WebP. Максимальный размер: 5MB.
                                        @if($banner->image)
                                            Оставьте пустым, чтобы сохранить текущее изображение.
                                        @endif
                                    </small>
                                </div>
                                
                                <div id="imagePreview" class="mt-3" style="display: none;">
                                    <label>Предпросмотр нового изображения:</label>
                                    <div>
                                        <img id="preview" src="" alt="Предпросмотр" class="img-fluid" style="max-height: 300px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Настройки</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="sort_order">Порядок показа *</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                           value="{{ old('sort_order', $banner->sort_order) }}" min="0" required>
                                    <small class="form-text text-muted">Меньшее значение = выше в списке</small>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" 
                                               value="1" {{ old('is_active', $banner->is_active) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">Активный баннер</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="start_date">Дата начала</label>
                                    <input type="datetime-local" class="form-control" id="start_date" name="start_date" 
                                           value="{{ old('start_date', $banner->start_date ? $banner->start_date->format('Y-m-d\TH:i') : '') }}">
                                    <small class="form-text text-muted">Оставьте пустым для немедленного показа</small>
                                </div>

                                <div class="form-group">
                                    <label for="end_date">Дата окончания</label>
                                    <input type="datetime-local" class="form-control" id="end_date" name="end_date" 
                                           value="{{ old('end_date', $banner->end_date ? $banner->end_date->format('Y-m-d\TH:i') : '') }}">
                                    <small class="form-text text-muted">Оставьте пустым для бессрочного показа</small>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Статистика</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="info-box bg-info">
                                            <span class="info-box-icon"><i class="fas fa-eye"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Просмотры</span>
                                                <span class="info-box-number">{{ number_format($banner->view_count) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-box bg-success">
                                            <span class="info-box-icon"><i class="fas fa-mouse-pointer"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Клики</span>
                                                <span class="info-box-number">{{ number_format($banner->click_count) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if($banner->view_count > 0)
                                    <div class="text-center">
                                        <strong>CTR: {{ round(($banner->click_count / $banner->view_count) * 100, 2) }}%</strong>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-save"></i> Сохранить изменения
                                </button>
                                <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-times"></i> Отмена
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Предпросмотр изображения
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    const preview = document.getElementById('preview');

    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
        }
    });

    // Валидация дат
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');

    function validateDates() {
        if (startDate.value && endDate.value) {
            if (new Date(startDate.value) >= new Date(endDate.value)) {
                endDate.setCustomValidity('Дата окончания должна быть позже даты начала');
            } else {
                endDate.setCustomValidity('');
            }
        } else {
            endDate.setCustomValidity('');
        }
    }

    startDate.addEventListener('change', validateDates);
    endDate.addEventListener('change', validateDates);
});
</script>
@endsection
