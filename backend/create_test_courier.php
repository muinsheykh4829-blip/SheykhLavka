<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Courier;

// Создаем тестового курьера
$courier = new Courier();
$courier->login = 'courier1';
$courier->password = 'password'; // будет автоматически захешировано
$courier->name = 'Курьер Тестовый';
$courier->phone = '+7900123456';
$courier->is_active = true;
$courier->save();

echo "Тестовый курьер создан:\n";
echo "Логин: courier1\n";
echo "Пароль: password\n";
echo "Имя: Курьер Тестовый\n";
echo "Телефон: +7900123456\n";
