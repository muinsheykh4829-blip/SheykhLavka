<?php

require_once __DIR__ . '/vendor/autoload.php';

// Загрузка Laravel приложения
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;

echo "=== Список всех заказов в системе ===\n";

$orders = Order::with(['user'])
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

if ($orders->isEmpty()) {
    echo "Заказы не найдены!\n";
    exit;
}

foreach ($orders as $order) {
    echo "ID: " . $order->id . "\n";
    echo "Номер: " . $order->order_number . "\n";
    echo "Статус: " . $order->status . "\n";
    echo "Сумма: " . $order->total . " с.\n";
    echo "Покупатель: " . ($order->user ? $order->user->name : 'Неизвестно') . "\n";
    echo "Дата: " . $order->created_at . "\n";
    echo "---\n";
}

echo "\n=== Завершено ===\n";
