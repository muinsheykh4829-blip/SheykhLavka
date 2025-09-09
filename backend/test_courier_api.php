<?php
// Тестирование API курьера

$url = 'http://localhost:8000/api/courier/login';

// Попробуем стандартный пароль 123123
$data = [
    'login' => 'courier1',
    'password' => '123123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

// Если не работает, попробуем обновить пароль курьера напрямую
if ($httpCode != 200) {
    echo "\nПопытка обновить пароль курьера...\n";
    
    // Подключаемся к базе данных напрямую
    $host = 'localhost';
    $db = 'dastovka';  
    $user = 'root';
    $pass = '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Хешируем пароль как в Laravel
        $hashedPassword = password_hash('123123', PASSWORD_DEFAULT);
        
        // Обновляем пароль курьера
        $stmt = $pdo->prepare("UPDATE couriers SET password = ? WHERE username = 'courier1'");
        $stmt->execute([$hashedPassword]);
        
        echo "Пароль курьера обновлен!\n";
        
        // Пробуем снова
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "Повторная попытка - HTTP Code: $httpCode\n";
        echo "Response: $response\n";
        
    } catch (PDOException $e) {
        echo "Ошибка БД: " . $e->getMessage() . "\n";
    }
}
