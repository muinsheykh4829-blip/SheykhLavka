<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Исправление сумм OrderItem ===\n";

try {
    DB::beginTransaction();
    
    $orderItems = App\Models\OrderItem::all();
    
    foreach ($orderItems as $item) {
        $oldTotal = $item->total;
        
        if ($item->weight && $item->weight > 0) {
            // Для весовых товаров: цена за кг × вес в кг
            $newTotal = $item->price * $item->weight;
        } else {
            // Для штучных товаров: цена × количество
            $newTotal = $item->price * $item->quantity;
        }
        
        $item->total = $newTotal;
        $item->save();
        
        echo "OrderItem #{$item->id} ({$item->product_name}):\n";
        echo "- Старая сумма: {$oldTotal}\n";
        echo "- Новая сумма: {$newTotal}\n";
        if ($item->weight) {
            echo "- Расчет: {$item->price} × {$item->weight} кг = {$newTotal}\n";
        } else {
            echo "- Расчет: {$item->price} × {$item->quantity} шт = {$newTotal}\n";
        }
        echo "\n";
    }
    
    // Теперь пересчитаем суммы заказов
    echo "=== Пересчет сумм заказов ===\n";
    $orders = App\Models\Order::with('items')->get();
    
    foreach ($orders as $order) {
        $oldTotal = $order->total;
        $newSubtotal = $order->items->sum('total');
        $newTotal = $newSubtotal + $order->delivery_fee - $order->discount;
        
        $order->subtotal = $newSubtotal;
        $order->total = $newTotal;
        $order->save();
        
        echo "Заказ #{$order->id}:\n";
        echo "- Старая сумма: {$oldTotal}\n";
        echo "- Новая сумма: {$newTotal} (subtotal: {$newSubtotal}, delivery: {$order->delivery_fee})\n\n";
    }
    
    DB::commit();
    echo "✅ Исправление завершено!\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
}
