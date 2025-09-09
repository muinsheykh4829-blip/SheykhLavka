<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;
use App\Models\User;

echo "Создание тестовых заказов для курьера...\n";

// Найдем или создадим пользователя
$user = User::first();
if (!$user) {
    $user = User::create([
        'first_name' => 'Тестовый',
        'last_name' => 'Клиент',
        'phone' => '+998901111111',
        'phone_verified_at' => now()
    ]);
    echo "Создан тестовый пользователь\n";
}

// Создаем несколько заказов готовых к доставке
for ($i = 1; $i <= 3; $i++) {
    $order = Order::create([
        'order_number' => 'SL' . date('Ymd') . str_pad($i + 1300, 4, '0', STR_PAD_LEFT),
        'user_id' => $user->id,
        'status' => 'ready', // готов к доставке
        'subtotal' => 15000 + ($i * 5000),
        'delivery_fee' => 5000,
        'total' => 20000 + ($i * 5000),
        'payment_method' => 'cash',
        'payment_status' => 'pending',
        'delivery_address' => "Тестовый адрес $i, дом 123, кв. $i",
        'delivery_phone' => $user->phone,
        'delivery_name' => $user->first_name . ' ' . $user->last_name,
        'delivery_type' => 'delivery',
        'created_at' => now()->subMinutes($i * 10)
    ]);
    
    echo "Создан заказ {$order->order_number} на сумму {$order->total} с.\n";
}

echo "\nТестовые заказы созданы!\n";
echo "Логин курьера: courier1\n";
echo "Пароль: 123123\n";
