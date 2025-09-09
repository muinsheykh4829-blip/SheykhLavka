@extends('admin.layout')

@section('title', 'Редактирование складских данных - ' . $product->name)

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Редактирование складских данных</h1>
        <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Назад к списку
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Основные данные -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Информация о товаре</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            @if($product->image)
                                <img src="{{ $product->image }}" alt="{{ $product->name }}" class="img-fluid rounded">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                                     style="height: 150px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <h5>{{ $product->name }}</h5>
                            @if($product->name_ru)
                                <p class="text-muted">Русское название: {{ $product->name_ru }}</p>
                            @endif
                            @if($product->name_tj)
                                <p class="text-muted">Таджикское название: {{ $product->name_tj }}</p>
                            @endif
                            @if($product->category)
                                <p><strong>Категория:</strong> {{ $product->category->name }}</p>
                            @endif
                            <p><strong>Цена:</strong> {{ number_format($product->price / 100, 2) }} сом</p>
                            @if($product->discount_price)
                                <p><strong>Цена со скидкой:</strong> {{ number_format($product->discount_price / 100, 2) }} сом</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Форма редактирования складских данных -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Складские данные</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.inventory.update', $product) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Тип товара *</label>
                                    <select name="product_type" class="form-control @error('product_type') is-invalid @enderror" required>
                                        <option value="piece" {{ old('product_type', $product->product_type) == 'piece' ? 'selected' : '' }}>
                                            Штучный товар
                                        </option>
                                        <option value="weight" {{ old('product_type', $product->product_type) == 'weight' ? 'selected' : '' }}>
                                            Весовой товар
                                        </option>
                                        <option value="package" {{ old('product_type', $product->product_type) == 'package' ? 'selected' : '' }}>
                                            Упаковочный товар
                                        </option>
                                    </select>
                                    @error('product_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Текущий остаток *</label>
                                    <input type="number" name="stock_quantity_current" class="form-control @error('stock_quantity_current') is-invalid @enderror" 
                                           value="{{ old('stock_quantity_current', $product->stock_quantity_current ?? 0) }}" 
                                           step="0.01" min="0" required>
                                    @error('stock_quantity_current')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Минимальный остаток *</label>
                                    <input type="number" name="stock_quantity_minimum" class="form-control @error('stock_quantity_minimum') is-invalid @enderror" 
                                           value="{{ old('stock_quantity_minimum', $product->stock_quantity_minimum ?? 0) }}" 
                                           step="0.01" min="0" required>
                                    <small class="text-muted">При достижении этого уровня товар будет помечен как "Мало на складе"</small>
                                    @error('stock_quantity_minimum')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Зарезервировано (только для чтения)</label>
                                    <input type="number" class="form-control" 
                                           value="{{ $product->stock_quantity_reserved ?? 0 }}" 
                                           readonly>
                                    <small class="text-muted">Количество зарезервированное для заказов</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" name="auto_deactivate_on_zero" class="form-check-input" 
                                       {{ old('auto_deactivate_on_zero', $product->auto_deactivate_on_zero) ? 'checked' : '' }}>
                                <label class="form-check-label">
                                    Автоматически деактивировать товар при нулевом остатке
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Причина изменения</label>
                            <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" rows="3" 
                                      placeholder="Опишите причину изменения складских данных">{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                            <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Текущая статистика -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Текущее состояние склада</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 border-right">
                            <div class="h5 font-weight-bold text-primary">
                                {{ $product->stock_quantity_current ?? 0 }}
                            </div>
                            <div class="text-xs text-uppercase text-muted">На складе</div>
                        </div>
                        <div class="col-6">
                            <div class="h5 font-weight-bold text-warning">
                                {{ $product->stock_quantity_reserved ?? 0 }}
                            </div>
                            <div class="text-xs text-uppercase text-muted">Резерв</div>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <div class="h4 font-weight-bold text-success">
                            {{ ($product->stock_quantity_current ?? 0) - ($product->stock_quantity_reserved ?? 0) }}
                        </div>
                        <div class="text-xs text-uppercase text-muted">Доступно для продажи</div>
                    </div>
                </div>
            </div>

            <!-- Быстрое пополнение -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Быстрое пополнение</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.inventory.restock', $product) }}">
                        @csrf
                        <div class="form-group">
                            <label>Количество</label>
                            <input type="number" name="quantity" class="form-control" step="0.01" min="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Причина</label>
                            <textarea name="reason" class="form-control" rows="2" 
                                      placeholder="Причина пополнения"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-plus"></i> Пополнить
                        </button>
                    </form>
                </div>
            </div>

            <!-- Последние движения -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-info">Последние движения</h6>
                    <a href="{{ route('admin.inventory.movements', $product) }}" class="btn btn-sm btn-info">
                        Все движения
                    </a>
                </div>
                <div class="card-body">
                    @forelse($movements->take(5) as $movement)
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <div>
                                <strong>
                                    @if($movement->type == 'incoming')
                                        <span class="text-success">+{{ $movement->quantity }}</span>
                                    @elseif($movement->type == 'outgoing')
                                        <span class="text-danger">-{{ $movement->quantity }}</span>
                                    @elseif($movement->type == 'reserved')
                                        <span class="text-warning">Резерв {{ $movement->quantity }}</span>
                                    @elseif($movement->type == 'released')
                                        <span class="text-info">Освобождено {{ $movement->quantity }}</span>
                                    @else
                                        <span class="text-secondary">{{ $movement->quantity }}</span>
                                    @endif
                                </strong>
                                <br>
                                <small class="text-muted">{{ $movement->notes }}</small>
                            </div>
                            <small class="text-muted">{{ $movement->created_at->format('d.m H:i') }}</small>
                        </div>
                    @empty
                        <p class="text-muted text-center">Движений пока нет</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
