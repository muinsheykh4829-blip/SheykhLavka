<?php

require_once __DIR__ . '/vendor/autoload.php';

// Загрузка Laravel приложения
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\CourierController;
use Illuminate\Http\Request;

echo "=== Тестирование API входа курьера ===\n";

// Создаем тестовый запрос
$request = new Request([
    'login' => 'courier2',
    'password' => 'password'
]);

$controller = new CourierController();

try {
    $response = $controller->login($request);
    $data = json_decode($response->getContent(), true);
    
    echo "Статус ответа: " . $response->getStatusCode() . "\n";
    echo "Содержание ответа:\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if ($data['success']) {
        echo "\n✅ Вход в систему прошел успешно!\n";
        echo "Токен получен: " . substr($data['data']['token'], 0, 20) . "...\n";
    } else {
        echo "\n❌ Ошибка входа: " . $data['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Исключение: " . $e->getMessage() . "\n";
}

echo "\n=== Завершено ===\n";
