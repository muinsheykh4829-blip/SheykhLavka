<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Статистика заказов ===\n";

$orders = App\Models\Order::selectRaw('status, count(*) as total')
    ->groupBy('status')
    ->get();

foreach ($orders as $order) {
    echo $order->status . ': ' . $order->total . "\n";
}

echo "\n=== Активные сборщики ===\n";
$activePickers = App\Models\Picker::where('is_active', true)->count();
echo "Активных сборщиков: " . $activePickers . "\n";

echo "\n=== Последние 3 заказа ===\n";
$latestOrders = App\Models\Order::orderBy('created_at', 'desc')
    ->limit(3)
    ->get(['id', 'order_number', 'status', 'picker_id', 'created_at']);

foreach ($latestOrders as $order) {
    echo "#{$order->id} ({$order->order_number}) - {$order->status}";
    if ($order->picker_id) {
        echo " - сборщик #{$order->picker_id}";
    }
    echo " - " . $order->created_at->format('d.m.Y H:i') . "\n";
}
