<!DOCTYPE html>
<html>
<head>
    <title>Проверка API заказов</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
        .container { border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap; }
        .log { background: #f8f9fa; border-left: 4px solid #007bff; padding: 10px; margin: 5px 0; }
    </style>
</head>
<body>
    <h1>🔍 Диагностика API заказов</h1>
    
    <div class="container">
        <h3>1. Проверка базовых API маршрутов</h3>
        <button class="btn" onclick="checkApiRoutes()">Проверить API маршруты</button>
        <div id="routesResult"></div>
    </div>

    <div class="container">
        <h3>2. Проверка базы данных</h3>
        <button class="btn" onclick="checkDatabase()">Проверить заказы в БД</button>
        <div id="dbResult"></div>
    </div>

    <div class="container">
        <h3>3. Создание тестового заказа</h3>
        <button class="btn" onclick="createTestOrderDetailed()">Создать заказ (детально)</button>
        <div id="testOrderResult"></div>
    </div>

    <div class="container">
        <h3>4. Проверка авторизации</h3>
        <button class="btn" onclick="checkAuth()">Проверить авторизацию</button>
        <div id="authResult"></div>
    </div>

    <div class="container">
        <h3>📋 Логи диагностики</h3>
        <div id="logs"></div>
    </div>

    <script>
        function log(message, type = 'info') {
            const logs = document.getElementById('logs');
            const timestamp = new Date().toLocaleTimeString();
            const color = type === 'error' ? 'red' : type === 'success' ? 'green' : 'blue';
            logs.innerHTML += `<div class="log" style="border-left-color: ${color};">[${timestamp}] ${message}</div>`;
        }

        async function checkApiRoutes() {
            log('🔄 Проверка API маршрутов...');
            
            const routes = [
                '/api/products',
                '/api/categories', 
                '/api/users/profile'
            ];
            
            let results = '<h4>Результаты проверки маршрутов:</h4>';
            
            for (const route of routes) {
                try {
                    const response = await fetch(route);
                    const status = response.status;
                    const statusText = response.statusText;
                    
                    if (status === 200) {
                        results += `<div class="success">✅ ${route} - OK (${status})</div>`;
                        log(`✅ ${route} работает`, 'success');
                    } else if (status === 401) {
                        results += `<div style="color: orange;">🔐 ${route} - Требует авторизации (${status})</div>`;
                        log(`🔐 ${route} требует авторизации`, 'info');
                    } else {
                        results += `<div class="error">❌ ${route} - Ошибка ${status} (${statusText})</div>`;
                        log(`❌ ${route} ошибка ${status}`, 'error');
                    }
                } catch (error) {
                    results += `<div class="error">❌ ${route} - Сетевая ошибка: ${error.message}</div>`;
                    log(`❌ ${route} сетевая ошибка`, 'error');
                }
            }
            
            document.getElementById('routesResult').innerHTML = results;
        }

        async function checkDatabase() {
            log('🔄 Проверка базы данных...');
            
            try {
                const response = await fetch('/api/debug-db');
                
                if (response.ok) {
                    const result = await response.text();
                    document.getElementById('dbResult').innerHTML = `<pre>${result}</pre>`;
                    log('✅ База данных проверена', 'success');
                } else {
                    document.getElementById('dbResult').innerHTML = 
                        `<div class="error">Ошибка проверки БД: ${response.status}</div>`;
                    log('❌ Ошибка проверки БД', 'error');
                }
            } catch (error) {
                document.getElementById('dbResult').innerHTML = 
                    `<div class="error">Сетевая ошибка: ${error.message}</div>`;
                log('❌ Сетевая ошибка при проверке БД', 'error');
            }
        }

        async function createTestOrderDetailed() {
            log('🔄 Создание тестового заказа...');
            
            try {
                const response = await fetch('/api/test-order-detailed');
                const result = await response.json();
                
                let html = '<h4>Результат создания заказа:</h4>';
                
                if (result.success) {
                    html += `<div class="success">✅ ${result.message}</div>`;
                    if (result.order) {
                        html += `<pre>${JSON.stringify(result.order, null, 2)}</pre>`;
                    }
                    log('✅ Тестовый заказ создан', 'success');
                } else {
                    html += `<div class="error">❌ ${result.message}</div>`;
                    if (result.details) {
                        html += `<pre>${JSON.stringify(result.details, null, 2)}</pre>`;
                    }
                    log('❌ Ошибка создания заказа', 'error');
                }
                
                document.getElementById('testOrderResult').innerHTML = html;
            } catch (error) {
                document.getElementById('testOrderResult').innerHTML = 
                    `<div class="error">Сетевая ошибка: ${error.message}</div>`;
                log('❌ Сетевая ошибка при создании заказа', 'error');
            }
        }

        async function checkAuth() {
            log('🔄 Проверка системы авторизации...');
            
            try {
                // Проверяем регистрацию
                const registerResponse = await fetch('/api/register', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        first_name: 'Тест',
                        last_name: 'Пользователь',
                        phone: '+998901234999',
                        password: 'password',
                        password_confirmation: 'password'
                    })
                });
                
                let html = '<h4>Проверка авторизации:</h4>';
                
                if (registerResponse.status === 200 || registerResponse.status === 422) {
                    html += '<div class="success">✅ API регистрации работает</div>';
                    
                    // Проверяем вход
                    const loginResponse = await fetch('/api/login', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            phone: '+998901234999',
                            password: 'password'
                        })
                    });
                    
                    if (loginResponse.ok) {
                        const loginResult = await loginResponse.json();
                        html += '<div class="success">✅ API входа работает</div>';
                        if (loginResult.token) {
                            html += '<div class="success">✅ Токен авторизации выдается</div>';
                        }
                    } else {
                        html += '<div class="error">❌ Проблема с API входа</div>';
                    }
                } else {
                    html += '<div class="error">❌ API регистрации не работает</div>';
                }
                
                document.getElementById('authResult').innerHTML = html;
                log('✅ Проверка авторизации завершена', 'success');
                
            } catch (error) {
                document.getElementById('authResult').innerHTML = 
                    `<div class="error">Ошибка проверки авторизации: ${error.message}</div>`;
                log('❌ Ошибка проверки авторизации', 'error');
            }
        }

        // Автозапуск при загрузке
        window.onload = function() {
            log('🚀 Диагностика API запущена');
        };
    </script>
</body>
</html>
