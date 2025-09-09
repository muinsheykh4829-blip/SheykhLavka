<?php

require_once __DIR__ . '/vendor/autoload.php';

// Загрузка Laravel приложения
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Courier;
use Illuminate\Support\Facades\Hash;

echo "=== Тест создания курьера (симуляция веб-админки) ===\n";

// Симулируем создание курьера как это делает веб-админка
$courierData = [
    'first_name' => 'Тест',
    'last_name' => 'Курьеров',
    'username' => 'test_courier',
    'phone' => '+999000111222',
    'email' => 'test@example.com',
    'password' => 'testpass123',
    'is_active' => true,
];

echo "Создаем курьера с данными:\n";
echo "Username: " . $courierData['username'] . "\n";
echo "Password: " . $courierData['password'] . "\n";
echo "Name: " . $courierData['first_name'] . " " . $courierData['last_name'] . "\n";

// Удаляем курьера если он уже существует
$existingCourier = Courier::where('username', $courierData['username'])->first();
if ($existingCourier) {
    echo "Удаляем существующего курьера...\n";
    $existingCourier->delete();
}

// Создаем курьера (модель должна автоматически хешировать пароль)
$courier = Courier::create($courierData);

echo "✅ Курьер создан с ID: " . $courier->id . "\n";

// Проверяем пароль
echo "\n=== Проверка пароля ===\n";
if ($courier->checkPassword('testpass123')) {
    echo "✅ Пароль 'testpass123' работает!\n";
} else {
    echo "❌ Пароль 'testpass123' НЕ работает!\n";
}

// Попробуем войти через API
echo "\n=== Проверка через API ===\n";
echo "Тестируем API вход...\n";

use App\Http\Controllers\Api\CourierController;
use Illuminate\Http\Request;

$request = new Request([
    'login' => 'test_courier',
    'password' => 'testpass123'
]);

$controller = new CourierController();

try {
    $response = $controller->login($request);
    $data = json_decode($response->getContent(), true);
    
    if ($data['success']) {
        echo "✅ API вход успешен!\n";
        echo "Токен: " . substr($data['data']['token'], 0, 20) . "...\n";
    } else {
        echo "❌ API вход неудачен: " . $data['message'] . "\n";
    }
} catch (Exception $e) {
    echo "❌ Ошибка API: " . $e->getMessage() . "\n";
}

echo "\n=== Тест завершен ===\n";
