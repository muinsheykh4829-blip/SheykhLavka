<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Детали заказов с весовыми товарами ===\n";

$orders = App\Models\Order::with('items')->orderBy('created_at', 'desc')->limit(3)->get();

foreach ($orders as $order) {
    echo "\n--- Заказ #{$order->id} ({$order->order_number}) - {$order->total} сом ---\n";
    
    foreach ($order->items as $item) {
        echo "Товар: {$item->product_name}\n";
        echo "- Количество: {$item->quantity}\n";
        echo "- Вес: " . ($item->weight ?? 'не указан') . "\n";
        echo "- Цена за единицу/кг: {$item->price} сом\n";
        echo "- Сумма: {$item->total} сом\n";
        
        if ($item->weight) {
            echo "- Расчет: {$item->price} сом/кг × {$item->weight} кг = " . ($item->price * $item->weight) . " сом\n";
        } else {
            echo "- Расчет: {$item->price} сом × {$item->quantity} шт = " . ($item->price * $item->quantity) . " сом\n";
        }
        echo "\n";
    }
}
