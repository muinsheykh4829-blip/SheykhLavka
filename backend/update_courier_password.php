<?php

require_once __DIR__ . '/vendor/autoload.php';

// Загрузка Laravel приложения
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Courier;

echo "=== Обновление пароля для courier2 ===\n";

$courier = Courier::where('username', 'courier2')->first();

if (!$courier) {
    echo "Курьер courier2 не найден!\n";
    exit;
}

echo "Найден курьер: " . $courier->first_name . " " . $courier->last_name . "\n";

// Обновляем пароль
$courier->password = 'password';
$courier->save();

echo "Пароль обновлен на 'password'\n";

// Проверяем
if ($courier->checkPassword('password')) {
    echo "Проверка: пароль 'password' теперь работает!\n";
} else {
    echo "Ошибка: пароль все еще не работает!\n";
}

echo "=== Завершено ===\n";
