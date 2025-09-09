<?php
// Проверка структуры данных API

$loginUrl = 'http://localhost:8000/api/courier/login';
$loginData = json_encode(['login' => 'courier1', 'password' => '123123']);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$response = ltrim($response, '-');
$authData = json_decode($response, true);

if ($authData && isset($authData['data']['token'])) {
    $token = $authData['data']['token'];
    echo "Токен: $token\n\n";
    
    // Получаем заказы с разными статусами
    $statuses = ['ready', 'delivering', 'delivered'];
    
    foreach ($statuses as $status) {
        echo "\n=== ЗАКАЗЫ СО СТАТУСОМ: $status ===\n";
        $ordersUrl = "http://localhost:8000/api/courier/orders?status=$status";
        
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $ordersUrl);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Accept: application/json'
        ]);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        
        $ordersResponse = curl_exec($ch2);
        curl_close($ch2);
        
        $ordersResponse = ltrim($ordersResponse, '-');
        $ordersData = json_decode($ordersResponse, true);
        
        if ($ordersData && isset($ordersData['orders'])) {
            foreach ($ordersData['orders'] as $order) {
                echo "Заказ ID: {$order['id']}\n";
                echo "Номер: {$order['order_number']}\n";
                echo "Статус: {$order['status']}\n";
                echo "Клиент: " . json_encode($order['customer']) . "\n";
                echo "Адрес: " . var_export($order['address'], true) . "\n";
                echo "Товары: " . json_encode($order['items']) . "\n";
                echo "Доп. инфо: " . var_export($order['completion_info'] ?? null, true) . "\n";
                echo "---\n";
            }
        } else {
            echo "Нет заказов или ошибка\n";
        }
    }
}
