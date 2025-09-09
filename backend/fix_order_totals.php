<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Исправление неправильных сумм в OrderItem ===\n";

$orderItems = App\Models\OrderItem::with('product')->get();

foreach ($orderItems as $item) {
    echo "OrderItem #{$item->id}:\n";
    echo "- Товар: {$item->product_name}\n";
    echo "- Текущая цена: {$item->price}\n";
    echo "- Количество: {$item->quantity}\n";
    echo "- Текущая сумма: {$item->total}\n";
    
    // Правильный расчет
    $correctTotal = $item->price * $item->quantity;
    echo "- Правильная сумма: {$correctTotal}\n";
    
    if ($item->total != $correctTotal) {
        echo "- ИСПРАВЛЯЕМ!\n";
        $item->total = $correctTotal;
        $item->save();
    }
    echo "\n";
}

// Пересчитаем суммы заказов
echo "=== Пересчет сумм заказов ===\n";
$orders = App\Models\Order::with('items')->get();

foreach ($orders as $order) {
    $correctSubtotal = $order->items->sum('total');
    echo "Заказ #{$order->id}:\n";
    echo "- Текущий subtotal: {$order->subtotal}\n";
    echo "- Правильный subtotal: {$correctSubtotal}\n";
    
    if ($order->subtotal != $correctSubtotal) {
        echo "- ИСПРАВЛЯЕМ!\n";
        $order->subtotal = $correctSubtotal;
        $order->total = $correctSubtotal + $order->delivery_fee - $order->discount;
        $order->save();
        echo "- Новый total: {$order->total}\n";
    }
    echo "\n";
}
