<?php

require_once __DIR__ . '/vendor/autoload.php';

// Загрузка Laravel приложения
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;

echo "=== Проверка заказа SL2025090071282 ===\n";

$orderNumber = 'SL202509071282';
$order = Order::where('order_number', $orderNumber)
    ->with(['items.product', 'user', 'address'])
    ->first();

if (!$order) {
    echo "Заказ не найден!\n";
    exit;
}

echo "Заказ найден:\n";
echo "ID: " . $order->id . "\n";
echo "Номер: " . $order->order_number . "\n";
echo "Статус: " . $order->status . "\n";
echo "Subtotal (подсумма): " . $order->subtotal . " с.\n";
echo "Delivery fee (доставка): " . $order->delivery_fee . " с.\n";
echo "Discount (скидка): " . $order->discount . " с.\n";
echo "Total (итого): " . $order->total . " с.\n";
echo "Покупатель: " . ($order->user ? $order->user->name : 'Неизвестно') . "\n";
echo "Телефон: " . ($order->user ? $order->user->phone : '') . "\n";

echo "\nТовары в заказе:\n";
$calculatedSubtotal = 0;
foreach ($order->items as $item) {
    $itemTotal = $item->quantity * $item->price;
    $calculatedSubtotal += $itemTotal;
    
    echo "- " . ($item->product ? $item->product->name : 'Товар не найден') . "\n";
    echo "  Количество: " . $item->quantity . "\n";
    echo "  Цена за единицу: " . $item->price . " с.\n";
    echo "  Стоимость позиции: " . $itemTotal . " с.\n";
}

echo "\nПроверка расчета:\n";
echo "Рассчитанная подсумма: " . $calculatedSubtotal . " с.\n";
echo "Подсумма в БД: " . $order->subtotal . " с.\n";
echo "Доставка: " . $order->delivery_fee . " с.\n";
echo "Скидка: " . $order->discount . " с.\n";

$calculatedTotal = $calculatedSubtotal + $order->delivery_fee - $order->discount;
echo "Рассчитанный итог: " . $calculatedTotal . " с.\n";
echo "Итог в БД: " . $order->total . " с.\n";

if (abs($calculatedTotal - $order->total) > 0.01) {
    echo "⚠️  НЕСООТВЕТСТВИЕ В СУММЕ!\n";
} else {
    echo "✅ Сумма рассчитана правильно\n";
}

echo "\n=== Завершено ===\n";
