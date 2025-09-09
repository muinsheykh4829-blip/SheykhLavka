<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Диагностика админ панели</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .result { margin: 20px 0; padding: 15px; border-radius: 5px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow: auto; max-height: 400px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>🔍 Диагностика админ панели Sheykh Lavka</h1>
        
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5>🛠️ Тесты</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" onclick="testDatabase()">База данных</button>
                            <button class="btn btn-success" onclick="testCategories()">Категории</button>
                            <button class="btn btn-info" onclick="testProducts()">Товары</button>
                            <button class="btn btn-warning" onclick="testOrders()">Заказы</button>
                            <button class="btn btn-secondary" onclick="testAll()">Все тесты</button>
                        </div>
                        
                        <hr>
                        
                        <h6>⚡ Быстрые действия</h6>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary" onclick="openAdmin('categories')">Открыть категории</button>
                            <button class="btn btn-outline-success" onclick="openAdmin('products')">Открыть товары</button>
                            <button class="btn btn-outline-warning" onclick="openAdmin('orders')">Открыть заказы</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div id="result">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Выберите тест для выполнения диагностики
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function showResult(data, success = true, title = 'Результат') {
            const resultDiv = document.getElementById('result');
            const className = success ? 'success' : 'error';
            const icon = success ? '✅' : '❌';
            
            resultDiv.innerHTML = `
                <div class="result ${className}">
                    <h4>${icon} ${title}</h4>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                </div>
            `;
        }
        
        async function testDatabase() {
            try {
                const response = await fetch('/debug-admin/database');
                const data = await response.json();
                showResult(data, data.success, 'Тест базы данных');
            } catch (error) {
                showResult({error: error.message}, false, 'Ошибка теста БД');
            }
        }
        
        async function testCategories() {
            try {
                const response = await fetch('/debug-admin/categories');
                const data = await response.json();
                showResult(data, data.success, 'Тест категорий');
            } catch (error) {
                showResult({error: error.message}, false, 'Ошибка теста категорий');
            }
        }
        
        async function testProducts() {
            try {
                const response = await fetch('/debug-admin/products');
                const data = await response.json();
                showResult(data, data.success, 'Тест товаров');
            } catch (error) {
                showResult({error: error.message}, false, 'Ошибка теста товаров');
            }
        }
        
        async function testOrders() {
            try {
                const response = await fetch('/debug-admin/orders');
                const data = await response.json();
                showResult(data, data.success, 'Тест заказов');
            } catch (error) {
                showResult({error: error.message}, false, 'Ошибка теста заказов');
            }
        }
        
        async function testAll() {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<div class="alert alert-info">⏳ Выполнение всех тестов...</div>';
            
            const tests = [
                { name: 'База данных', func: testDatabase },
                { name: 'Категории', func: testCategories },
                { name: 'Товары', func: testProducts },
                { name: 'Заказы', func: testOrders }
            ];
            
            let results = [];
            
            for (const test of tests) {
                try {
                    const response = await fetch('/debug-admin/' + test.name.toLowerCase().replace(' ', '-'));
                    const data = await response.json();
                    results.push({
                        test: test.name,
                        success: data.success,
                        data: data
                    });
                } catch (error) {
                    results.push({
                        test: test.name,
                        success: false,
                        error: error.message
                    });
                }
            }
            
            showResult(results, results.every(r => r.success), 'Результаты всех тестов');
        }
        
        function openAdmin(section) {
            // Установим сессию для обхода авторизации на время тестирования
            fetch('/admin/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: 'username=admin&password=admin123'
            }).then(() => {
                window.open(`/admin/${section}`, '_blank');
            }).catch(error => {
                console.error('Ошибка авторизации:', error);
                window.open(`/admin/${section}`, '_blank');
            });
        }
        
        // Автоматически запускаем тест БД при загрузке
        window.onload = testDatabase;
    </script>
</body>
</html>
