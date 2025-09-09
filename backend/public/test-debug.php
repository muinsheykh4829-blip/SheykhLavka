<?php
// Тестирование отладочной информации

// 1. Проверим данные пользователя с ID 4
echo "<h2>1. Данные пользователя с ID 4:</h2>";
$userUrl = 'http://127.0.0.1:8000/api/v1/debug/user/4';
$userContext = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Accept: application/json'
    ]
]);
$userResponse = file_get_contents($userUrl, false, $userContext);
echo "<pre>" . htmlspecialchars($userResponse) . "</pre>";

// 2. Попробуем подтвердить код
echo "<h2>2. Подтверждение кода 581940:</h2>";
$verifyUrl = 'http://127.0.0.1:8000/api/v1/verify-code';
$verifyData = json_encode([
    'user_id' => 4,
    'code' => '581940'
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

$verifyResponse = file_get_contents($verifyUrl, false, $verifyContext);
echo "<pre>" . htmlspecialchars($verifyResponse) . "</pre>";

// 3. Попробуем разные варианты кода
echo "<h2>3. Тестирование разных форматов кода:</h2>";

$testCodes = ['581940', ' 581940 ', '581940\n', '581940\r\n'];

foreach ($testCodes as $index => $testCode) {
    echo "<h3>Тест " . ($index + 1) . ": '" . addslashes($testCode) . "'</h3>";
    
    $data = json_encode([
        'user_id' => 4,
        'code' => $testCode
    ]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            'content' => $data
        ]
    ]);
    
    $response = file_get_contents($verifyUrl, false, $context);
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}
