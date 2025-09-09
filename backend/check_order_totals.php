<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Проверка сумм заказов ===\n";

$orders = App\Models\Order::with('items')->orderBy('created_at', 'desc')->limit(3)->get();

foreach ($orders as $order) {
    echo "\n--- Заказ #{$order->id} ({$order->order_number}) ---\n";
    echo "Статус: {$order->status}\n";
    
    // Сырые значения из базы данных
    $rawAttributes = $order->getAttributes();
    echo "DB subtotal: " . $rawAttributes['subtotal'] . "\n";
    echo "DB delivery_fee: " . $rawAttributes['delivery_fee'] . "\n";
    echo "DB discount: " . $rawAttributes['discount'] . "\n";
    echo "DB total: " . $rawAttributes['total'] . "\n";
    
    // Через модель (с cast)
    echo "Model subtotal: " . $order->subtotal . "\n";
    echo "Model delivery_fee: " . $order->delivery_fee . "\n";
    echo "Model discount: " . $order->discount . "\n";
    echo "Model total: " . $order->total . "\n";
    echo "Formatted total: " . $order->formatted_total . "\n";
    
    // Пересчет суммы товаров
    $itemsSum = 0;
    echo "Товары:\n";
    foreach ($order->items as $item) {
        $itemTotal = $item->total;
        echo "- {$item->product_name}: {$item->quantity} x {$item->price} = {$itemTotal}\n";
        $itemsSum += $itemTotal;
    }
    echo "Сумма товаров: {$itemsSum}\n";
    echo "Итого должно быть: " . ($itemsSum + $order->delivery_fee - $order->discount) . "\n";
}
