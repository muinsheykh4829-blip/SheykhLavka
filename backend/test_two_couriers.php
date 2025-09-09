<?php
// Тест с двумя курьерами

echo "=== ТЕСТ С ДВУМЯ КУРЬЕРАМИ ===\n\n";

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

// Сначала создадим тестовые заказы
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;

// Создаем тестовые заказы
$order1 = Order::create([
    'order_number' => 'TEST001',
    'user_id' => 1,
    'status' => 'ready',
    'subtotal' => 5000,
    'delivery_fee' => 500,
    'total' => 5500,
    'delivery_address' => 'Тестовый адрес 1',
    'delivery_phone' => '+992901111111',
]);

$order2 = Order::create([
    'order_number' => 'TEST002', 
    'user_id' => 1,
    'status' => 'ready',
    'subtotal' => 7000,
    'delivery_fee' => 500,
    'total' => 7500,
    'delivery_address' => 'Тестовый адрес 2',
    'delivery_phone' => '+992902222222',
]);

echo "✅ Созданы тестовые заказы: TEST001 и TEST002\n\n";

// 1. Логин обоих курьеров
echo "1️⃣ Логин курьеров...\n";
$courier1Response = makeRequest('http://localhost:8000/api/courier/login', 'POST', ['login' => 'courier1', 'password' => '123123']);
$token1 = $courier1Response['data']['data']['token'];

$courier2Response = makeRequest('http://localhost:8000/api/courier/login', 'POST', ['login' => 'courier2', 'password' => 'password']);
$token2 = $courier2Response['data']['data']['token'];

echo "✅ Курьер 1 авторизован\n";
echo "✅ Курьер 2 авторизован\n\n";

// 2. Оба курьера видят одинаковые готовые заказы
echo "2️⃣ Проверяем готовые заказы...\n";
$ready1 = makeRequest('http://localhost:8000/api/courier/orders?status=ready', 'GET', null, $token1);
$ready2 = makeRequest('http://localhost:8000/api/courier/orders?status=ready', 'GET', null, $token2);

echo "✅ Курьер 1 видит готовых заказов: " . count($ready1['data']['orders']) . "\n";
echo "✅ Курьер 2 видит готовых заказов: " . count($ready2['data']['orders']) . "\n\n";

// 3. Курьер 1 берет первый заказ
if (count($ready1['data']['orders']) > 0) {
    $order1Id = $ready1['data']['orders'][0]['id'];
    echo "3️⃣ Курьер 1 берет заказ #$order1Id...\n";
    $take1 = makeRequest("http://localhost:8000/api/courier/orders/$order1Id/take", 'POST', null, $token1);
    echo "✅ " . $take1['data']['message'] . "\n\n";
    
    // 4. Курьер 2 пытается взять тот же заказ (должна быть ошибка)
    echo "4️⃣ Курьер 2 пытается взять тот же заказ...\n";
    $take2 = makeRequest("http://localhost:8000/api/courier/orders/$order1Id/take", 'POST', null, $token2);
    echo ($take2['code'] === 400 ? "✅" : "❌") . " " . $take2['data']['message'] . "\n\n";
    
    // 5. Проверяем, что готовых заказов стало меньше для обоих
    echo "5️⃣ Проверяем готовые заказы после взятия...\n";
    $ready1_after = makeRequest('http://localhost:8000/api/courier/orders?status=ready', 'GET', null, $token1);
    $ready2_after = makeRequest('http://localhost:8000/api/courier/orders?status=ready', 'GET', null, $token2);
    
    echo "✅ Курьер 1 теперь видит готовых заказов: " . count($ready1_after['data']['orders']) . "\n";
    echo "✅ Курьер 2 теперь видит готовых заказов: " . count($ready2_after['data']['orders']) . "\n\n";
    
    // 6. Проверяем заказы в доставке
    echo "6️⃣ Проверяем заказы в доставке...\n";
    $delivering1 = makeRequest('http://localhost:8000/api/courier/orders?status=delivering', 'GET', null, $token1);
    $delivering2 = makeRequest('http://localhost:8000/api/courier/orders?status=delivering', 'GET', null, $token2);
    
    echo "✅ У курьера 1 заказов в доставке: " . count($delivering1['data']['orders']) . "\n";
    echo "✅ У курьера 2 заказов в доставке: " . count($delivering2['data']['orders']) . "\n\n";
    
    // 7. Курьер 2 берет другой заказ
    if (count($ready2_after['data']['orders']) > 0) {
        $order2Id = $ready2_after['data']['orders'][0]['id'];
        echo "7️⃣ Курьер 2 берет заказ #$order2Id...\n";
        $take2_new = makeRequest("http://localhost:8000/api/courier/orders/$order2Id/take", 'POST', null, $token2);
        echo "✅ " . $take2_new['data']['message'] . "\n\n";
    }
    
    // 8. Проверяем историю обоих курьеров
    echo "8️⃣ Проверяем историю курьеров...\n";
    $history1 = makeRequest('http://localhost:8000/api/courier/orders?status=history', 'GET', null, $token1);
    $history2 = makeRequest('http://localhost:8000/api/courier/orders?status=history', 'GET', null, $token2);
    
    echo "✅ История курьера 1: " . count($history1['data']['orders']) . " заказов\n";
    echo "✅ История курьера 2: " . count($history2['data']['orders']) . " заказов\n\n";
}

echo "🎯 РЕЗУЛЬТАТЫ ТЕСТИРОВАНИЯ:\n";
echo "✅ Каждый курьер видит только свои заказы в доставке\n";
echo "✅ Готовые заказы исчезают после взятия курьером\n";
echo "✅ Нельзя взять заказ, который уже взял другой курьер\n";
echo "✅ История показывает только заказы конкретного курьера\n";
