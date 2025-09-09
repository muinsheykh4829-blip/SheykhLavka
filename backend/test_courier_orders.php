<?php
// Тестирование получения заказов курьера

$loginUrl = 'http://localhost:8000/api/courier/login';
$ordersUrl = 'http://localhost:8000/api/courier/orders';

// Авторизуемся
$loginData = [
    'login' => 'courier1',
    'password' => '123123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $authData = json_decode($response, true);
    $token = $authData['data']['token'];
    echo "✓ Авторизация успешна\n";
    echo "Токен: " . substr($token, 0, 20) . "...\n\n";
    
    // Получаем готовые к доставке заказы
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ordersUrl . '?status=ready');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Запрос готовых заказов - HTTP Code: $httpCode\n";
    if ($httpCode == 200) {
        $ordersData = json_decode($response, true);
        echo "Найдено заказов: " . count($ordersData['orders']) . "\n";
        
        foreach ($ordersData['orders'] as $order) {
            echo "- Заказ {$order['order_number']}: {$order['total']} с. (статус: {$order['status']})\n";
        }
    } else {
        echo "Ошибка получения заказов: $response\n";
    }
    
} else {
    echo "✗ Ошибка авторизации: $response\n";
}
