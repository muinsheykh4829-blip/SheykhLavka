<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Быстрый вход в админ панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Быстрый доступ к админ панели</h4>
                    </div>
                    <div class="card-body">
                        <p>Для тестирования админ панели используйте:</p>
                        <ul>
                            <li><strong>Логин:</strong> admin</li>
                            <li><strong>Пароль:</strong> admin123</li>
                        </ul>
                        
                        <form action="/admin/login" method="POST">
                            @csrf
                            <input type="hidden" name="username" value="admin">
                            <input type="hidden" name="password" value="admin123">
                            <button type="submit" class="btn btn-primary">Войти в админ панель</button>
                        </form>
                        
                        <hr>
                        
                        <h5>Или перейдите напрямую:</h5>
                        <div class="d-grid gap-2">
                            <a href="/admin" class="btn btn-outline-primary">Главная админ панели</a>
                            <a href="/admin/categories" class="btn btn-outline-success">Категории</a>
                            <a href="/admin/products" class="btn btn-outline-info">Товары</a>
                            <a href="/admin/orders" class="btn btn-outline-warning">Заказы</a>
                        </div>
                        
                        <hr>
                        
                        <h5>Диагностика:</h5>
                        <div class="d-grid gap-2">
                            <a href="/debug" class="btn btn-outline-secondary">Диагностика БД</a>
                            <a href="/setup/check-database" class="btn btn-outline-secondary">Проверить БД</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
