<?php
// Тест видимости заказов для разных курьеров

echo "=== ТЕСТ ВИДИМОСТИ ЗАКАЗОВ ===\n\n";

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

// Логин курьеров
echo "1️⃣ Авторизация курьеров...\n";
$courier1 = makeRequest('http://localhost:8000/api/courier/login', 'POST', ['login' => 'courier1', 'password' => '123123']);
$courier2 = makeRequest('http://localhost:8000/api/courier/login', 'POST', ['login' => 'courier2', 'password' => 'password']);

if ($courier1['code'] !== 200 || $courier2['code'] !== 200) {
    echo "❌ Ошибка авторизации\n";
    echo "Курьер 1: " . $courier1['body'] . "\n";
    echo "Курьер 2: " . $courier2['body'] . "\n";
    exit;
}

$token1 = $courier1['data']['data']['token'];
$token2 = $courier2['data']['data']['token'];

echo "✅ Курьер 1 (Иван): " . substr($token1, 0, 20) . "...\n";
echo "✅ Курьер 2 (Петр): " . substr($token2, 0, 20) . "...\n\n";

// Проверяем все типы заказов для обоих курьеров
$statuses = ['ready', 'delivering', 'history'];

foreach ($statuses as $status) {
    echo "2️⃣ Заказы со статусом '$status':\n";
    
    $orders1 = makeRequest("http://localhost:8000/api/courier/orders?status=$status", 'GET', null, $token1);
    $orders2 = makeRequest("http://localhost:8000/api/courier/orders?status=$status", 'GET', null, $token2);
    
    echo "  Курьер 1 (Иван) видит: ";
    if ($orders1['code'] === 200) {
        $count1 = count($orders1['data']['orders']);
        echo "$count1 заказов\n";
        if ($count1 > 0) {
            foreach ($orders1['data']['orders'] as $order) {
                echo "    - #{$order['order_number']} (ID: {$order['id']}, статус: {$order['status']})\n";
            }
        }
    } else {
        echo "Ошибка: " . $orders1['body'] . "\n";
    }
    
    echo "  Курьер 2 (Петр) видит: ";
    if ($orders2['code'] === 200) {
        $count2 = count($orders2['data']['orders']);
        echo "$count2 заказов\n";
        if ($count2 > 0) {
            foreach ($orders2['data']['orders'] as $order) {
                echo "    - #{$order['order_number']} (ID: {$order['id']}, статус: {$order['status']})\n";
            }
        }
    } else {
        echo "Ошибка: " . $orders2['body'] . "\n";
    }
    
    echo "\n";
}

echo "🔍 АНАЛИЗ:\n";
echo "- Готовые заказы должны быть одинаковыми для обоих курьеров\n";
echo "- Заказы в доставке должны быть разными (только свои)\n";
echo "- История должна быть персональной для каждого курьера\n";
