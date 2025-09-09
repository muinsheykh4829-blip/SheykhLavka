@extends('admin.layout')

@section('title', 'История движений - ' . $product->name)

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">История движений: {{ $product->name }}</h1>
        <div>
            <a href="{{ route('admin.inventory.edit', $product) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Редактировать
            </a>
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> К списку
            </a>
        </div>
    </div>

    <!-- Информация о товаре -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-2">
                    @if($product->image)
                        <img src="{{ $product->image }}" alt="{{ $product->name }}" class="img-fluid rounded">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 80px;">
                            <i class="fas fa-image fa-2x text-muted"></i>
                        </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <h5 class="mb-1">{{ $product->name }}</h5>
                    @if($product->category)
                        <p class="text-muted mb-1">{{ $product->category->name }}</p>
                    @endif
                    <p class="mb-0">Цена: {{ number_format($product->price / 100, 2) }} сом</p>
                </div>
                <div class="col-md-4">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="h5 font-weight-bold text-primary">{{ $product->stock_quantity_current ?? 0 }}</div>
                            <div class="text-xs text-muted">На складе</div>
                        </div>
                        <div class="col-4">
                            <div class="h5 font-weight-bold text-warning">{{ $product->stock_quantity_reserved ?? 0 }}</div>
                            <div class="text-xs text-muted">Резерв</div>
                        </div>
                        <div class="col-4">
                            <div class="h5 font-weight-bold text-success">
                                {{ ($product->stock_quantity_current ?? 0) - ($product->stock_quantity_reserved ?? 0) }}
                            </div>
                            <div class="text-xs text-muted">Доступно</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- История движений -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">История движений</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Дата/Время</th>
                            <th>Тип операции</th>
                            <th>Количество</th>
                            <th>Остаток после</th>
                            <th>Ссылка</th>
                            <th>Примечание</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $movement)
                        <tr>
                            <td>{{ $movement->created_at->format('d.m.Y H:i:s') }}</td>
                            <td>
                                @switch($movement->type)
                                    @case('incoming')
                                        <span class="badge badge-success">Поступление</span>
                                        @break
                                    @case('outgoing')
                                        <span class="badge badge-danger">Списание</span>
                                        @break
                                    @case('reserved')
                                        <span class="badge badge-warning">Резервирование</span>
                                        @break
                                    @case('released')
                                        <span class="badge badge-info">Освобождение резерва</span>
                                        @break
                                    @case('adjustment')
                                        <span class="badge badge-secondary">Корректировка</span>
                                        @break
                                    @default
                                        <span class="badge badge-light">{{ $movement->type }}</span>
                                @endswitch
                            </td>
                            <td>
                                @if($movement->type == 'incoming' || ($movement->type == 'adjustment' && $movement->quantity > 0))
                                    <span class="text-success font-weight-bold">+{{ $movement->quantity }}</span>
                                @elseif($movement->type == 'outgoing' || ($movement->type == 'adjustment' && $movement->quantity < 0))
                                    <span class="text-danger font-weight-bold">{{ $movement->quantity }}</span>
                                @else
                                    <span class="font-weight-bold">{{ $movement->quantity }}</span>
                                @endif
                            </td>
                            <td>{{ $movement->quantity_after }}</td>
                            <td>
                                @if($movement->reference_type == 'order' && $movement->reference_id)
                                    <a href="#" class="text-primary">Заказ #{{ $movement->reference_id }}</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($movement->notes)
                                    <span title="{{ $movement->notes }}">
                                        {{ Str::limit($movement->notes, 50) }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Движений пока нет</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Пагинация -->
            {{ $movements->links() }}
        </div>
    </div>

    <!-- Статистика по типам операций -->
    @if($movements->count() > 0)
    <div class="row">
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Поступления</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $movements->where('type', 'incoming')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-up fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Списания</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $movements->where('type', 'outgoing')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-down fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Резервирования</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $movements->where('type', 'reserved')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-lock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Корректировки</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $movements->where('type', 'adjustment')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-edit fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
