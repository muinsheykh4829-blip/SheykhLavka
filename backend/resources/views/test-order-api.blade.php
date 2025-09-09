<!DOCTYPE html>
<html>
<head>
    <title>Тест API заказов</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 10px 5px; }
        .btn:hover { background: #0056b3; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .container { border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 5px; }
        input, textarea { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
    </style>
</head>
<body>
    <h1>🛒 Тест API создания заказов</h1>
    
    <div class="container">
        <h3>Шаг 1: Создать тестовый заказ</h3>
        <button class="btn" onclick="createTestOrder()">Создать заказ через API</button>
        <div id="orderResult"></div>
    </div>

    <div class="container">
        <h3>Шаг 2: Проверить заказы в админ-панели</h3>
        <a href="/admin/orders" target="_blank" class="btn">Открыть админ-панель заказов</a>
    </div>

    <div class="container">
        <h3>Шаг 3: Тестовые данные для Flutter</h3>
        <form onsubmit="return false;">
            <label>Адрес доставки:</label>
            <input type="text" id="address" value="г. Ташкент, ул. Амира Темура 15, кв. 25" required>
            
            <label>Телефон:</label>
            <input type="text" id="phone" value="+998901234567" required>
            
            <label>Имя получателя:</label>
            <input type="text" id="name" value="Тестовый Клиент">
            
            <label>Способ оплаты:</label>
            <select id="payment" style="width: 100%; padding: 10px;">
                <option value="cash">Наличные</option>
                <option value="card">Картой при получении</option>
                <option value="online">Онлайн оплата</option>
            </select>
            
            <label>Комментарий:</label>
            <textarea id="comment" rows="3">Позвонить за 10 минут до доставки</textarea>
            
            <button type="button" class="btn" onclick="createCustomOrder()">Создать заказ с этими данными</button>
        </form>
        <div id="customOrderResult"></div>
    </div>

    <div class="container">
        <h3>📋 Логи API запросов</h3>
        <div id="logs"></div>
    </div>

    <script>
        function log(message, type = 'info') {
            const logs = document.getElementById('logs');
            const timestamp = new Date().toLocaleTimeString();
            const color = type === 'error' ? 'red' : type === 'success' ? 'green' : 'blue';
            logs.innerHTML += `<div style="color: ${color};">[${timestamp}] ${message}</div>`;
        }

        async function createTestOrder() {
            try {
                log('🔄 Создание тестового заказа...');
                
                const response = await fetch('/api/test-order');
                const result = await response.json();
                
                if (result.success) {
                    log('✅ Заказ создан успешно!', 'success');
                    document.getElementById('orderResult').innerHTML = `
                        <div class="success">
                            <strong>Заказ создан!</strong><br>
                            Номер заказа: ${result.order_number}<br>
                            <a href="${result.admin_link}" target="_blank">Открыть в админ-панели</a>
                        </div>
                        <pre>${JSON.stringify(result.order, null, 2)}</pre>
                    `;
                } else {
                    log('❌ Ошибка создания заказа: ' + result.message, 'error');
                    document.getElementById('orderResult').innerHTML = `
                        <div class="error">
                            <strong>Ошибка:</strong> ${result.message}
                        </div>
                    `;
                }
            } catch (error) {
                log('❌ Сетевая ошибка: ' + error.message, 'error');
                document.getElementById('orderResult').innerHTML = `
                    <div class="error">
                        <strong>Сетевая ошибка:</strong> ${error.message}
                    </div>
                `;
            }
        }

        async function createCustomOrder() {
            try {
                const orderData = {
                    delivery_address: document.getElementById('address').value,
                    delivery_phone: document.getElementById('phone').value,
                    delivery_name: document.getElementById('name').value,
                    payment_method: document.getElementById('payment').value,
                    comment: document.getElementById('comment').value
                };

                log('🔄 Создание заказа с пользовательскими данными...');
                log('📤 Данные: ' + JSON.stringify(orderData));

                // Имитация реального API запроса
                log('⚠️ Для реального запроса нужен токен авторизации', 'error');
                
                document.getElementById('customOrderResult').innerHTML = `
                    <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;">
                        <strong>📝 Данные для отправки в Flutter:</strong>
                        <pre>${JSON.stringify(orderData, null, 2)}</pre>
                        <strong>📡 URL:</strong> POST /api/orders<br>
                        <strong>🔐 Header:</strong> Authorization: Bearer {token}
                    </div>
                `;
                
            } catch (error) {
                log('❌ Ошибка обработки данных: ' + error.message, 'error');
            }
        }

        // Загрузка при открытии страницы
        log('🚀 Страница тестирования API загружена');
    </script>
</body>
</html>
