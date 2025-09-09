<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Создание тестового курьера через SQL..." . PHP_EOL;

// Хешируем пароль
$hashedPassword = password_hash('123456', PASSWORD_DEFAULT);

// Создаем тестового курьера через прямой SQL запрос
try {
    $result = DB::insert("INSERT INTO couriers (login, password, name, phone, vehicle_type, vehicle_number, email, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
        'admin',
        $hashedPassword,
        'Тестовый Курьер',
        '+992900000001',
        'Велосипед',
        'B001',
        'courier@test.com',
        1,
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s')
    ]);
    
    if ($result) {
        echo "✅ Тестовый курьер создан успешно!" . PHP_EOL;
        echo "Логин: admin" . PHP_EOL;
        echo "Пароль: 123456" . PHP_EOL;
        echo "Имя: Тестовый Курьер" . PHP_EOL;
        echo "Телефон: +992900000001" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ Ошибка создания курьера: " . $e->getMessage() . PHP_EOL;
}
