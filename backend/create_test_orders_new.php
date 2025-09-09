<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Найдем пользователя или создадим
    $user = App\Models\User::first();
    if (!$user) {
        $user = App\Models\User::create([
            'name' => 'Тестовый пользователь',
            'phone' => '+992901234567',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
        ]);
    }

    // Создаем заказы с разными статусами
    $statuses = ['processing', 'accepted', 'preparing', 'ready', 'delivering', 'delivered', 'cancelled'];
    
    foreach ($statuses as $status) {
        $total = rand(1000, 5000);
        $deliveryFee = ($status === 'delivered' || $status === 'delivering') ? 500 : 0;
        
        App\Models\Order::create([
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'user_id' => $user->id,
            'status' => $status,
            'subtotal' => $total,
            'delivery_fee' => $deliveryFee,
            'discount' => 0,
            'total' => $total + $deliveryFee,
            'payment_method' => 'cash',
            'payment_status' => $status === 'delivered' ? 'paid' : 'pending',
            'delivery_address' => 'Тестовый адрес для статуса ' . $status,
            'delivery_phone' => '+992901234567',
            'delivery_name' => 'Тест ' . $status,
            'comment' => 'Тестовый заказ со статусом ' . $status,
        ]);
        echo "Создан заказ со статусом: $status\n";
    }
    
    echo "Тестовые заказы созданы успешно!\n";
    echo "Общее количество заказов: " . App\Models\Order::count() . "\n";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
