<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Проверка корзины пользователей ===\n";

$cartItems = App\Models\Cart::with('product')->limit(5)->get();

foreach ($cartItems as $item) {
    echo "\n--- Позиция корзины ---\n";
    echo "User ID: {$item->user_id}\n";
    echo "Product: {$item->product->name}\n";
    echo "Quantity: {$item->quantity}\n";
    echo "Weight: " . ($item->weight ?? 'не указан') . "\n";
    echo "Product price: {$item->product->price}\n";
    echo "Expected total: " . ($item->product->price * $item->quantity) . "\n";
}

// Проверим тестовое создание заказа
echo "\n=== Тест расчета суммы ===\n";
$product = App\Models\Product::first();
if ($product) {
    echo "Товар: {$product->name}\n";
    echo "Цена в БД: {$product->price}\n";
    echo "Количество: 2\n";
    echo "Расчет: {$product->price} * 2 = " . ($product->price * 2) . "\n";
}
