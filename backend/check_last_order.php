<?php
require_once 'vendor/autoload.php';

// Подключаем конфигурацию Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Получаем последний заказ
$order = \App\Models\Order::latest()->first();

if ($order) {
    echo "=== ДЕТАЛИ ПОСЛЕДНЕГО ЗАКАЗА ===\n";
    echo "ID: {$order->id}\n";
    echo "Номер заказа: {$order->order_number}\n";
    echo "Статус: {$order->status}\n";
    echo "Телефон: {$order->delivery_phone}\n";
    echo "Имя: {$order->delivery_name}\n";
    echo "Адрес: {$order->delivery_address}\n";
    echo "Товары: {$order->subtotal} драм\n";
    echo "Доставка: {$order->delivery_fee} драм\n";
    echo "Всего: {$order->total} драм\n";
    echo "Тип доставки: " . ($order->delivery_type ?? 'не указан') . "\n";
    echo "Комментарий: {$order->comment}\n";
    echo "Создан: {$order->created_at}\n";
    echo "\n";
    
    echo "=== ТОВАРЫ В ЗАКАЗЕ ===\n";
    foreach ($order->items as $item) {
        echo "- {$item->product_name}: {$item->quantity} x {$item->price} = {$item->total} драм\n";
    }
} else {
    echo "Заказы не найдены\n";
}
