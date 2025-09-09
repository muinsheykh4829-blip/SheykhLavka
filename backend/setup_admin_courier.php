<?php

require_once __DIR__ . '/vendor/autoload.php';

// Загрузка Laravel приложения
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Courier;
use Illuminate\Support\Facades\Hash;

echo "=== Проверка и обновление курьера 'admin' ===\n";

$courier = Courier::where('username', 'admin')->first();

if (!$courier) {
    echo "Курьер с логином 'admin' не найден!\n";
    exit;
}

echo "Найден курьер: " . $courier->first_name . " " . $courier->last_name . "\n";
echo "Активен: " . ($courier->is_active ? 'Да' : 'Нет') . "\n";

// Попробуем стандартные пароли
$testPasswords = ['admin', 'password', '123456', '123', 'admin123'];

$foundPassword = false;
foreach ($testPasswords as $pwd) {
    if ($courier->checkPassword($pwd)) {
        echo "✅ Текущий пароль: '$pwd'\n";
        $foundPassword = true;
        break;
    }
}

if (!$foundPassword) {
    echo "❌ Ни один из стандартных паролей не подходит.\n";
    echo "Устанавливаем новый пароль 'admin'...\n";
    
    $courier->password = 'admin';
    $courier->save();
    
    if ($courier->checkPassword('admin')) {
        echo "✅ Пароль 'admin' успешно установлен!\n";
    } else {
        echo "❌ Ошибка при установке пароля!\n";
    }
}

// Проверим, что курьер активен
if (!$courier->is_active) {
    echo "⚠️ Курьер неактивен. Активируем...\n";
    $courier->is_active = true;
    $courier->save();
    echo "✅ Курьер активирован!\n";
}

echo "\n=== Финальная информация ===\n";
echo "Логин: " . $courier->username . "\n";
echo "Пароль: admin\n";
echo "Имя: " . $courier->first_name . " " . $courier->last_name . "\n";
echo "Активен: " . ($courier->is_active ? 'Да' : 'Нет') . "\n";

echo "=== Завершено ===\n";
