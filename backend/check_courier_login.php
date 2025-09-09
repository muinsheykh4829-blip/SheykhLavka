<?php

require_once __DIR__ . '/vendor/autoload.php';

// Загрузка Laravel приложения
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Courier;
use Illuminate\Support\Facades\Hash;

echo "=== Проверка курьеров в системе ===\n";

$couriers = Courier::all();

if ($couriers->isEmpty()) {
    echo "Курьеры не найдены в базе данных!\n";
} else {
    foreach ($couriers as $courier) {
        echo "ID: " . $courier->id . "\n";
        echo "Username: " . $courier->username . "\n";
        echo "Имя: " . $courier->first_name . " " . $courier->last_name . "\n";
        echo "Телефон: " . $courier->phone . "\n";
        echo "Активен: " . ($courier->is_active ? 'Да' : 'Нет') . "\n";
        echo "Пароль хеш: " . substr($courier->password, 0, 20) . "...\n";
        echo "---\n";
    }
}

// Проверим логин courier2
echo "\n=== Попытка входа с логином 'courier2' ===\n";

$testLogin = 'courier2';
$testPassword = 'password';

$courier = Courier::where('username', $testLogin)->where('is_active', true)->first();

if (!$courier) {
    echo "Курьер с логином '$testLogin' не найден или не активен!\n";
    
    // Попробуем найти любого курьера с таким логином
    $anyCourier = Courier::where('username', $testLogin)->first();
    if ($anyCourier) {
        echo "Курьер найден, но не активен. is_active = " . ($anyCourier->is_active ? 'true' : 'false') . "\n";
    }
} else {
    echo "Курьер найден: " . $courier->first_name . " " . $courier->last_name . "\n";
    
    // Проверим пароль
    if ($courier->checkPassword($testPassword)) {
        echo "Пароль '$testPassword' ПРАВИЛЬНЫЙ!\n";
    } else {
        echo "Пароль '$testPassword' НЕПРАВИЛЬНЫЙ!\n";
        
        // Попробуем другие стандартные пароли
        $commonPasswords = ['123456', '123', 'courier2', 'admin', '111111'];
        foreach ($commonPasswords as $pwd) {
            if ($courier->checkPassword($pwd)) {
                echo "Правильный пароль: '$pwd'\n";
                break;
            }
        }
    }
}

echo "\n=== Завершено ===\n";
