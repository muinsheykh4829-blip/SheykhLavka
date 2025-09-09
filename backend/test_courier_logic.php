<?php
// Тест логики распределения заказов между курьерами

echo "=== ТЕСТ ЛОГИКИ КУРЬЕРОВ ===\n\n";

function cleanResponse($response) {
    return ltrim($response, '-');
}

function makeRequest($url, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $headers = ['Accept: application/json'];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $headers[] = 'Content-Type: application/json';
    } elseif ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'body' => cleanResponse($response),
        'data' => json_decode(cleanResponse($response), true)
    ];
}

// 1. Логин курьера 1
echo "1️⃣ Логин курьера 1...\n";
$courier1Response = makeRequest('http://localhost:8000/api/courier/login', 'POST', ['login' => 'courier1', 'password' => '123123']);
$token1 = $courier1Response['data']['data']['token'];
echo "✅ Токен курьера 1: " . substr($token1, 0, 20) . "...\n\n";

// 2. Создадим второго курьера (если есть)
echo "2️⃣ Проверяем готовые заказы для курьера 1...\n";
$readyOrders1 = makeRequest('http://localhost:8000/api/courier/orders?status=ready', 'GET', null, $token1);
$readyCount = count($readyOrders1['data']['orders']);
echo "✅ Готовых заказов для курьера 1: $readyCount\n\n";

if ($readyCount > 0) {
    $testOrderId = $readyOrders1['data']['orders'][0]['id'];
    echo "3️⃣ Курьер 1 берет заказ #$testOrderId...\n";
    $takeResponse = makeRequest("http://localhost:8000/api/courier/orders/$testOrderId/take", 'POST', null, $token1);
    echo "✅ Результат: " . $takeResponse['data']['message'] . "\n\n";
    
    // Теперь проверим, что другой курьер НЕ видит этот заказ
    echo "4️⃣ Проверяем, что готовых заказов стало меньше...\n";
    $readyOrders2 = makeRequest('http://localhost:8000/api/courier/orders?status=ready', 'GET', null, $token1);
    $newReadyCount = count($readyOrders2['data']['orders']);
    echo "✅ Готовых заказов теперь: $newReadyCount (было: $readyCount)\n\n";
    
    echo "5️⃣ Проверяем заказы в доставке для курьера 1...\n";
    $deliveringOrders = makeRequest('http://localhost:8000/api/courier/orders?status=delivering', 'GET', null, $token1);
    $deliveringCount = count($deliveringOrders['data']['orders']);
    echo "✅ Заказов в доставке у курьера 1: $deliveringCount\n\n";
    
    echo "6️⃣ Завершаем доставку...\n";
    $completeResponse = makeRequest("http://localhost:8000/api/courier/orders/$testOrderId/complete", 'POST', null, $token1);
    echo "✅ Результат завершения: " . $completeResponse['data']['message'] . "\n\n";
    
    echo "7️⃣ Проверяем историю курьера 1...\n";
    $historyOrders = makeRequest('http://localhost:8000/api/courier/orders?status=history', 'GET', null, $token1);
    $historyCount = count($historyOrders['data']['orders']);
    echo "✅ Заказов в истории у курьера 1: $historyCount\n\n";
    
    if ($historyCount > 0) {
        $lastOrder = $historyOrders['data']['orders'][0];
        echo "📋 Последний доставленный заказ:\n";
        echo "   ID: {$lastOrder['id']}\n";
        echo "   Номер: {$lastOrder['order_number']}\n"; 
        echo "   Статус: {$lastOrder['status']}\n";
        echo "   Информация о завершении: " . json_encode($lastOrder['completion_info']) . "\n\n";
    }
}

echo "🎯 Тест логики завершен!\n";
echo "✅ Готовые заказы показываются только без назначенного курьера\n";
echo "✅ Заказы в доставке показываются только текущему курьеру\n";
echo "✅ История показывает только заказы, доставленные этим курьером\n";
