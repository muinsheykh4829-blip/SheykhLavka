@extends('admin.layout')

@section('title', 'Тест создания сборщика')

@section('content')
<div class="container-fluid">
    <h1>Тест создания сборщика</h1>
    
    <div class="row">
        <div class="col-md-6">
            <form action="{{ route('admin.pickers.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="login" class="form-label">Логин:</label>
                    <input type="text" class="form-control" id="login" name="login" value="test_simple" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Пароль:</label>
                    <input type="password" class="form-control" id="password" name="password" value="123456" required>
                </div>
                
                <div class="mb-3">
                    <label for="name" class="form-label">Имя:</label>
                    <input type="text" class="form-control" id="name" name="name" value="Простой Тест" required>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">Телефон:</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="+992123456789">
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">Активный</label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Создать сборщика</button>
            </form>
        </div>
        
        <div class="col-md-6">
            <h3>Отладочная информация</h3>
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
