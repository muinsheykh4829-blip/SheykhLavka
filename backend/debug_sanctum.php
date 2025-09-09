<?php
// Тестирование API курьера

echo "=== Тест API курьера ===\n";

// 1. Логин
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
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Убираем лишние символы в начале
$response = ltrim($response, '-');

$authData = json_decode($response, true);

if ($httpCode == 200 && $authData && isset($authData['data']['token'])) {
    echo "✓ Логин успешен\n";
    $token = $authData['data']['token'];
    echo "Токен: " . substr($token, 0, 20) . "...\n";
    
    // 2. Получение заказов
    echo "\n--- Получение заказов ---\n";
    $ordersUrl = 'http://localhost:8000/api/courier/orders';
    
    $ch2 = curl_init();
    curl_setopt($ch2, CURLOPT_URL, $ordersUrl);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    
    $ordersResponse = curl_exec($ch2);
    $ordersHttpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);
    
    $ordersResponse = ltrim($ordersResponse, '-');
    $ordersData = json_decode($ordersResponse, true);
    
    if ($ordersHttpCode == 200 && $ordersData) {
        echo "✓ Заказы получены: " . count($ordersData['orders']) . " шт.\n";
        
        if (count($ordersData['orders']) > 0) {
            $orderId = $ordersData['orders'][0]['id'];
            echo "Тестовый заказ ID: $orderId\n";
            
            // 3. Взятие заказа
            echo "\n--- Взятие заказа ---\n";
            $takeUrl = "http://localhost:8000/api/courier/orders/$orderId/take";
            
            $ch3 = curl_init();
            curl_setopt($ch3, CURLOPT_URL, $takeUrl);
            curl_setopt($ch3, CURLOPT_POST, 1);
            curl_setopt($ch3, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token,
                'Accept: application/json'
            ]);
            curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
            
            $takeResponse = curl_exec($ch3);
            $takeHttpCode = curl_getinfo($ch3, CURLINFO_HTTP_CODE);
            curl_close($ch3);
            
            $takeResponse = ltrim($takeResponse, '-');
            $takeData = json_decode($takeResponse, true);
            
            if ($takeHttpCode == 200) {
                echo "✓ Заказ взят курьером\n";
                
                // 4. Завершение заказа
                echo "\n--- Завершение заказа ---\n";
                $completeUrl = "http://localhost:8000/api/courier/orders/$orderId/complete";
                
                $ch4 = curl_init();
                curl_setopt($ch4, CURLOPT_URL, $completeUrl);
                curl_setopt($ch4, CURLOPT_POST, 1);
                curl_setopt($ch4, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $token,
                    'Accept: application/json'
                ]);
                curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true);
                
                $completeResponse = curl_exec($ch4);
                $completeHttpCode = curl_getinfo($ch4, CURLINFO_HTTP_CODE);
                curl_close($ch4);
                
                $completeResponse = ltrim($completeResponse, '-');
                
                if ($completeHttpCode == 200) {
                    echo "✓ Заказ завершен\n";
                    echo "\n🎉 ВСЕ API МЕТОДЫ РАБОТАЮТ!\n";
                } else {
                    echo "✗ Ошибка завершения: $completeResponse\n";
                }
            } else {
                echo "✗ Ошибка взятия: $takeResponse\n";
            }
        }
    } else {
        echo "✗ Ошибка получения заказов: $ordersResponse\n";
    }
} else {
    echo "✗ Ошибка логина: $response\n";
}
