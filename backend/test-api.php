<?php

// Тест регистрации и авторизации через PHP curl

$baseUrl = 'http://127.0.0.1:8000/api/v1';

function makeRequest($url, $data = null, $token = null) {
    $ch = curl_init();
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

echo "=== Тест API Sheykh Lavka ===\n\n";

// 1. Регистрация
echo "1. Тестируем регистрацию...\n";
$registerData = [
    'name' => 'Тест Пользователь PHP',
    'phone' => '+998901234569',
    'password' => '123456'
];

$result = makeRequest($baseUrl . '/auth/register', $registerData);
echo "HTTP Code: " . $result['http_code'] . "\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

if ($result['http_code'] == 201 && $result['response']['success']) {
    $userId = $result['response']['data']['user_id'];
    $verificationCode = $result['response']['data']['verification_code'];
    
    echo "✅ Регистрация успешна! User ID: $userId, Code: $verificationCode\n\n";
    
    // 2. Подтверждение кода
    echo "2. Тестируем подтверждение кода...\n";
    $verifyData = [
        'user_id' => $userId,
        'code' => $verificationCode
    ];
    
    $result = makeRequest($baseUrl . '/auth/verify-code', $verifyData);
    echo "HTTP Code: " . $result['http_code'] . "\n";
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    if ($result['http_code'] == 200 && $result['response']['success']) {
        $token = $result['response']['data']['token'];
        
        echo "✅ Подтверждение успешно! Token: " . substr($token, 0, 20) . "...\n\n";
        
        // 3. Получение профиля
        echo "3. Тестируем получение профиля...\n";
        $result = makeRequest($baseUrl . '/user', null, $token);
        echo "HTTP Code: " . $result['http_code'] . "\n";
        echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        
        if ($result['http_code'] == 200) {
            echo "✅ Профиль получен успешно!\n\n";
        }
        
        // 4. Выход из системы
        echo "4. Тестируем выход из системы...\n";
        $result = makeRequest($baseUrl . '/auth/logout', [], $token);
        echo "HTTP Code: " . $result['http_code'] . "\n";
        echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        
    } else {
        echo "❌ Ошибка подтверждения кода\n";
    }
    
} else {
    echo "❌ Ошибка регистрации\n";
}

// 5. Тест публичных endpoint'ов
echo "5. Тестируем публичные данные...\n";

echo "\n--- Категории ---\n";
$result = makeRequest($baseUrl . '/categories');
echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']['success'] ?? false) {
    echo "Количество категорий: " . count($result['response']['data']) . "\n";
} else {
    echo "Ошибка загрузки категорий\n";
}

echo "\n--- Товары ---\n";
$result = makeRequest($baseUrl . '/products');
echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']['success'] ?? false) {
    echo "Количество товаров: " . count($result['response']['data']) . "\n";
} else {
    echo "Ошибка загрузки товаров\n";
}

echo "\n--- Баннеры ---\n";
$result = makeRequest($baseUrl . '/banners');
echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']['success'] ?? false) {
    echo "Количество баннеров: " . count($result['response']['data']) . "\n";
} else {
    echo "Ошибка загрузки баннеров\n";
}

echo "\n=== Тестирование завершено ===\n";
