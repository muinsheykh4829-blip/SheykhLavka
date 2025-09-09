<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Sheykh Lavka Admin</title>

    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
            color: #2DD4BF !important;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
        }
        .btn {
            border-radius: 0.375rem;
        }
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
        }
        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.2);
        }
        .main-content {
            padding: 2rem;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-store mr-2"></i>
                Sheykh Lavka - Админ панель
            </a>
            
            <div class="navbar-nav ml-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                        <i class="fas fa-user mr-1"></i>
                        Админ
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-cog mr-2"></i>
                            Настройки
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Выйти
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 px-0">
                <div class="sidebar">
                    <nav class="nav flex-column pt-3">
                        <a class="nav-link" href="/admin">
                            <i class="fas fa-tachometer-alt mr-2"></i>
                            Панель управления
                        </a>
                        
                        <hr class="my-2" style="border-color: rgba(255,255,255,0.2);">
                        
                        <a class="nav-link" href="/admin/orders">
                            <i class="fas fa-shopping-bag mr-2"></i>
                            Заказы
                        </a>
                        
                        <a class="nav-link" href="/admin/products">
                            <i class="fas fa-box mr-2"></i>
                            Товары
                        </a>
                        
                        <a class="nav-link" href="/admin/categories">
                            <i class="fas fa-tags mr-2"></i>
                            Категории
                        </a>
                        
                        <a class="nav-link" href="/admin/users">
                            <i class="fas fa-users mr-2"></i>
                            Пользователи
                        </a>
                        
                        <hr class="my-2" style="border-color: rgba(255,255,255,0.2);">
                        
                        <a class="nav-link {{ request()->is('admin/couriers*') ? 'active' : '' }}" href="{{ route('admin.couriers.index') }}">
                            <i class="fas fa-motorcycle mr-2"></i>
                            Курьеры
                        </a>
                        
                        <a class="nav-link" href="/admin/pickers">
                            <i class="fas fa-user-tie mr-2"></i>
                            Сборщики
                        </a>
                        
                        <hr class="my-2" style="border-color: rgba(255,255,255,0.2);">
                        
                        <a class="nav-link" href="/admin/banners">
                            <i class="fas fa-image mr-2"></i>
                            Баннеры
                        </a>
                        
                        <a class="nav-link" href="/admin/settings">
                            <i class="fas fa-cog mr-2"></i>
                            Настройки
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <div class="main-content">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    @yield('scripts')
    @stack('scripts')
</body>
</html>
