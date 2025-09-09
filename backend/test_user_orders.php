<?php
require_once 'vendor/autoload.php';

// Подключаем конфигурацию Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Получаем пользователя с ID 14 (из логов)
$user = \App\Models\User::find(14);

if ($user) {
    echo "=== ПОЛЬЗОВАТЕЛЬ ===\n";
    echo "ID: {$user->id}\n";
    echo "Имя: {$user->name}\n";
    echo "Телефон: {$user->phone}\n";
    echo "\n";
    
    // Получаем заказы пользователя
    $orders = $user->orders()
        ->with(['items.product'])
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "=== ЗАКАЗЫ ПОЛЬЗОВАТЕЛЯ ===\n";
    echo "Всего заказов: " . $orders->count() . "\n";
    echo "\n";
    
    foreach ($orders as $order) {
        echo "Заказ #{$order->order_number}\n";
        echo "  ID: {$order->id}\n";
        echo "  Статус: {$order->status}\n";
        echo "  Товары: {$order->subtotal} (тип: " . gettype($order->subtotal) . ")\n";
        echo "  Доставка: {$order->delivery_fee} (тип: " . gettype($order->delivery_fee) . ")\n";
        echo "  Скидка: {$order->discount} (тип: " . gettype($order->discount) . ")\n";
        echo "  Всего: {$order->total} (тип: " . gettype($order->total) . ")\n";
        echo "  Создан: {$order->created_at}\n";
        echo "  Товаров в заказе: " . $order->items->count() . "\n";
        echo "\n";
    }
    
    // Покажем JSON ответ как его видит API
    echo "=== JSON ОТВЕТ API ===\n";
    $response = [
        'success' => true,
        'orders' => $orders
    ];
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} else {
    echo "Пользователь с ID 14 не найден\n";
}
