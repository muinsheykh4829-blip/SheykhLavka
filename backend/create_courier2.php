<?php
// Создаем второго курьера для тестирования

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Courier;

// Создаем второго курьера
$courier2 = Courier::updateOrCreate(
    ['username' => 'courier2'],
    [
        'username' => 'courier2',
        'password' => 'password',
        'first_name' => 'Петр',
        'last_name' => 'Доставщиков',
        'phone' => '+998901234568',
        'is_active' => true
    ]
);

echo "✅ Создан курьер 2: {$courier2->first_name} {$courier2->last_name} (ID: {$courier2->id})\n";
echo "   Логин: courier2\n";
echo "   Пароль: password\n";
