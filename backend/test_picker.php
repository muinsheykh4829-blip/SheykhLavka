<?php

require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Picker;

try {
    $picker = Picker::create([
        'login' => 'direct_test',
        'password' => '123456',
        'name' => 'Прямой тест',
        'phone' => '+992111222333',
        'is_active' => true
    ]);
    
    echo "Сборщик успешно создан с ID: " . $picker->id . "\n";
    echo "Логин: " . $picker->login . "\n";
    echo "Имя: " . $picker->name . "\n";
    
} catch (Exception $e) {
    echo "Ошибка при создании сборщика: " . $e->getMessage() . "\n";
    echo "Трассировка: " . $e->getTraceAsString() . "\n";
}
