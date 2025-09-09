<!DOCTYPE html>
<html>
<head>
    <title>Тест создания заказа</title>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
        .container { border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        input, textarea { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .result { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { border-left: 4px solid #28a745; }
        .error { border-left: 4px solid #dc3545; }
    </style>
</head>
<body>
    <h1>🧪 Тест создания заказа</h1>
    
    <div class="container">
        <h3>Создать заказ напрямую в БД</h3>
        <form onsubmit="createOrderDirect(event)">
            <input type="text" id="address" placeholder="Адрес доставки" value="г. Ташкент, ул. Тестовая 123">
            <input type="text" id="phone" placeholder="Телефон" value="+998901234567">
            <input type="text" id="name" placeholder="Имя клиента" value="Тестовый Клиент">
            <textarea id="comment" placeholder="Комментарий">Тестовый заказ для проверки</textarea>
            <button type="submit" class="btn">Создать заказ</button>
        </form>
        <div id="directResult"></div>
    </div>

    <div class="container">
        <h3>Проверить заказы в админ-панели</h3>
        <button class="btn" onclick="window.open('/admin/orders', '_blank')">Открыть админ-панель</button>
        <button class="btn" onclick="checkDatabase()">Проверить БД</button>
        <div id="dbResult"></div>
    </div>

    <script>
        async function createOrderDirect(event) {
            event.preventDefault();
            
            const data = {
                delivery_address: document.getElementById('address').value,
                delivery_phone: document.getElementById('phone').value,
                delivery_name: document.getElementById('name').value,
                comment: document.getElementById('comment').value
            };

            try {
                const response = await fetch('/api/test-order-simple', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('directResult').innerHTML = 
                        `<div class="result success"><strong>✅ Успешно!</strong><br>
                         Заказ №${result.order.order_number} создан<br>
                         ID: ${result.order.id}<br>
                         Статус: ${result.order.status}</div>`;
                } else {
                    document.getElementById('directResult').innerHTML = 
                        `<div class="result error"><strong>❌ Ошибка:</strong><br>${result.message}</div>`;
                }
            } catch (error) {
                document.getElementById('directResult').innerHTML = 
                    `<div class="result error"><strong>❌ Сетевая ошибка:</strong><br>${error.message}</div>`;
            }
        }

        async function checkDatabase() {
            try {
                const response = await fetch('/api/debug-db');
                const result = await response.text();
                
                document.getElementById('dbResult').innerHTML = 
                    `<div class="result"><pre>${result}</pre></div>`;
            } catch (error) {
                document.getElementById('dbResult').innerHTML = 
                    `<div class="result error">Ошибка: ${error.message}</div>`;
            }
        }
    </script>
</body>
</html>
