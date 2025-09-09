<!DOCTYPE html>
<html>
<head>
    <title>Настройка базы данных</title>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .container { border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn-success { background: #28a745; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-danger { background: #dc3545; }
        .result { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; white-space: pre-wrap; }
        .success { border-left: 4px solid #28a745; }
        .error { border-left: 4px solid #dc3545; }
        .warning { border-left: 4px solid #ffc107; }
    </style>
</head>
<body>
    <h1>🔧 Настройка базы данных</h1>
    
    <div class="container">
        <h3>1. Проверка базы данных</h3>
        <button class="btn" onclick="checkDatabase()">Проверить состояние БД</button>
        <div id="dbStatus"></div>
    </div>

    <div class="container">
        <h3>2. Выполнение миграций</h3>
        <button class="btn btn-success" onclick="runMigrations()">Выполнить миграции</button>
        <button class="btn btn-warning" onclick="createTablesManual()">Создать таблицы вручную</button>
        <button class="btn btn-danger" onclick="resetDatabase()">Пересоздать БД</button>
        <div id="migrationResult"></div>
    </div>

    <div class="container">
        <h3>3. Создание тестовых данных</h3>
        <button class="btn btn-success" onclick="createTestData()">Создать тестовые данные</button>
        <div id="testDataResult"></div>
    </div>

    <div class="container">
        <h3>4. Проверка работы</h3>
        <button class="btn" onclick="testOrder()">Тестовый заказ</button>
        <button class="btn" onclick="window.open('/admin/orders', '_blank')">Админ-панель</button>
        <div id="testResult"></div>
    </div>

    <script>
        async function checkDatabase() {
            try {
                const response = await fetch('/setup/check-database');
                const result = await response.text();
                
                document.getElementById('dbStatus').innerHTML = 
                    `<div class="result">${result}</div>`;
            } catch (error) {
                document.getElementById('dbStatus').innerHTML = 
                    `<div class="result error">Ошибка: ${error.message}</div>`;
            }
        }

        async function runMigrations() {
            try {
                document.getElementById('migrationResult').innerHTML = 
                    '<div class="result warning">⏳ Выполнение миграций...</div>';
                    
                const response = await fetch('/setup/migrate', {method: 'POST'});
                const result = await response.text();
                
                document.getElementById('migrationResult').innerHTML = 
                    `<div class="result success">${result}</div>`;
            } catch (error) {
                document.getElementById('migrationResult').innerHTML = 
                    `<div class="result error">Ошибка: ${error.message}</div>`;
            }
        }

        async function resetDatabase() {
            if (!confirm('Это удалит все данные! Продолжить?')) return;
            
            try {
                document.getElementById('migrationResult').innerHTML = 
                    '<div class="result warning">⏳ Пересоздание базы данных...</div>';
                    
                const response = await fetch('/setup/reset', {method: 'POST'});
                const result = await response.text();
                
                document.getElementById('migrationResult').innerHTML = 
                    `<div class="result success">${result}</div>`;
            } catch (error) {
                document.getElementById('migrationResult').innerHTML = 
                    `<div class="result error">Ошибка: ${error.message}</div>`;
            }
        }

        async function createTablesManual() {
            try {
                document.getElementById('migrationResult').innerHTML = 
                    '<div class="result warning">⏳ Создание таблиц вручную...</div>';
                    
                const response = await fetch('/setup/create-tables', {method: 'POST'});
                const result = await response.text();
                
                document.getElementById('migrationResult').innerHTML = 
                    `<div class="result success">${result}</div>`;
                    
                // Автоматически обновляем проверку БД
                setTimeout(checkDatabase, 1000);
            } catch (error) {
                document.getElementById('migrationResult').innerHTML = 
                    `<div class="result error">Ошибка: ${error.message}</div>`;
            }
        }

        async function createTestData() {
            try {
                document.getElementById('testDataResult').innerHTML = 
                    '<div class="result warning">⏳ Создание тестовых данных...</div>';
                    
                const response = await fetch('/setup/seed', {method: 'POST'});
                const result = await response.text();
                
                document.getElementById('testDataResult').innerHTML = 
                    `<div class="result success">${result}</div>`;
            } catch (error) {
                document.getElementById('testDataResult').innerHTML = 
                    `<div class="result error">Ошибка: ${error.message}</div>`;
            }
        }

        async function testOrder() {
            try {
                const response = await fetch('/api/test-order-simple', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        delivery_address: 'г. Ташкент, ул. Тестовая 123',
                        delivery_phone: '+998901234567',
                        delivery_name: 'Тестовый Клиент'
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('testResult').innerHTML = 
                        `<div class="result success">✅ Заказ создан! №${result.order.order_number}</div>`;
                } else {
                    document.getElementById('testResult').innerHTML = 
                        `<div class="result error">❌ ${result.message}</div>`;
                }
            } catch (error) {
                document.getElementById('testResult').innerHTML = 
                    `<div class="result error">Ошибка: ${error.message}</div>`;
            }
        }

        // Автоматическая проверка при загрузке
        window.onload = function() {
            checkDatabase();
        };
    </script>
</body>
</html>
