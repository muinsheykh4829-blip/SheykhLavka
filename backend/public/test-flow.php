<?php
// Быстрый тест регистрации и подтверждения

echo "<h2>Тест полного потока регистрации + подтверждение</h2>";

// 1. Регистрируем нового пользователя
$registerUrl = 'http://127.0.0.1:8000/api/v1/register';
$registerData = json_encode([
    'name' => 'Test User ' . time(),
    'phone' => '+77' . rand(100000000, 999999999),
    'password' => 'testpassword123',
    'password_confirmation' => 'testpassword123'
]);

$registerContext = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        'content' => $registerData
    ]
]);

echo "<h3>1. Регистрация:</h3>";
echo "<pre>Отправляем данные: " . htmlspecialchars($registerData) . "</pre>";

$registerResponse = file_get_contents($registerUrl, false, $registerContext);
echo "<pre>Ответ: " . htmlspecialchars($registerResponse) . "</pre>";

// Парсим ответ регистрации
$registerResult = json_decode($registerResponse, true);

if ($registerResult && $registerResult['success']) {
    $userId = $registerResult['data']['user_id'];
    $verificationCode = $registerResult['data']['verification_code'];
    
    echo "<h3>2. Подтверждение кода:</h3>";
    echo "<p>User ID: $userId</p>";
    echo "<p>Код: $verificationCode</p>";
    
    // Сразу пытаемся подтвердить код
    $verifyUrl = 'http://127.0.0.1:8000/api/v1/verify-code';
    $verifyData = json_encode([
        'user_id' => $userId,
        'code' => $verificationCode
    ]);
    
    $verifyContext = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            'content' => $verifyData
        ]
    ]);
    
    echo "<pre>Отправляем: " . htmlspecialchars($verifyData) . "</pre>";
    
    $verifyResponse = file_get_contents($verifyUrl, false, $verifyContext);
    echo "<pre>Ответ: " . htmlspecialchars($verifyResponse) . "</pre>";
    
    // Проверим пользователя через debug endpoint
    echo "<h3>3. Debug информация о пользователе:</h3>";
    $debugUrl = "http://127.0.0.1:8000/api/v1/debug/user/$userId";
    $debugContext = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Accept: application/json'
        ]
    ]);
    
    $debugResponse = file_get_contents($debugUrl, false, $debugContext);
    echo "<pre>" . htmlspecialchars($debugResponse) . "</pre>";
    
} else {
    echo "<h3>Ошибка регистрации!</h3>";
    echo "<pre>" . htmlspecialchars($registerResponse) . "</pre>";
}
?>
