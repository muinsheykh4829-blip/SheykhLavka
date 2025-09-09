<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Конвертация цен из копеек в сомы ===\n";
echo "ВНИМАНИЕ: Скрипт должен быть запущен ТОЛЬКО один раз. Повторный запуск занизит цены (деление повторится).\n";

try {
    DB::beginTransaction();
    
    // 1. Обновляем цены товаров
    echo "Обновляем товары...\n";
    $products = App\Models\Product::all();
    foreach ($products as $product) {
        $oldPrice = $product->price;
        $newPrice = $oldPrice / 100; // конвертируем в сомы
        
        $product->price = $newPrice;
        if ($product->discount_price) {
            $product->discount_price = $product->discount_price / 100;
        }
        $product->save();
        
        echo "- {$product->name}: {$oldPrice} -> {$newPrice}\n";
    }
    
    // 2. Обновляем заказы
    echo "\nОбновляем заказы...\n";
    $orders = App\Models\Order::all();
    foreach ($orders as $order) {
        $order->subtotal = $order->subtotal / 100;
        $order->delivery_fee = $order->delivery_fee / 100;
        $order->discount = $order->discount / 100;
        $order->total = $order->total / 100;
        $order->save();
        
        echo "- Заказ #{$order->id}: новая сумма {$order->total}\n";
    }
    
    // 3. Обновляем элементы заказов
    echo "\nОбновляем элементы заказов...\n";
    $orderItems = App\Models\OrderItem::all();
    foreach ($orderItems as $item) {
        $item->price = $item->price / 100;
        $item->total = $item->total / 100;
        $item->save();
        
        echo "- OrderItem #{$item->id}: цена {$item->price}, сумма {$item->total}\n";
    }
    
    DB::commit();
    echo "\n✅ Конвертация завершена успешно!\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "\n❌ Ошибка: " . $e->getMessage() . "\n";
}

echo "\n=== Проверка результата ===\n";
$product = App\Models\Product::first();
echo "Товар: {$product->name} - цена: {$product->price} сом\n";

$order = App\Models\Order::latest()->first();
if ($order) {
    echo "Заказ #{$order->id} - сумма: {$order->total} сом\n";
}
