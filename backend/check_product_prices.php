<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Проверка цен товаров ===\n";

$products = App\Models\Product::limit(5)->get();

foreach ($products as $product) {
    echo "\n--- {$product->name} ---\n";
    echo "DB price: " . $product->getAttributes()['price'] . "\n";
    echo "Model price: " . $product->price . "\n";
    echo "Formatted price: " . number_format($product->price / 100, 2) . " сом\n";
}

echo "\n=== Последний заказ и его товары ===\n";
$order = App\Models\Order::with('items.product')->latest()->first();

if ($order) {
    echo "Заказ #{$order->id}\n";
    foreach ($order->items as $item) {
        echo "\nТовар: {$item->product_name}\n";
        echo "Цена в OrderItem: {$item->price}\n";
        echo "Количество: {$item->quantity}\n";
        echo "Сумма в OrderItem: {$item->total}\n";
        
        if ($item->product) {
            echo "Цена в Product: {$item->product->price}\n";
        }
    }
}
