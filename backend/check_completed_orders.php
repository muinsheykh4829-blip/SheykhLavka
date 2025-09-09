<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\Picker;

echo "Проверка заказов с completed_by:\n";

$ordersWithCompletedBy = Order::whereNotNull('completed_by')
    ->with('completedBy')
    ->get(['id', 'order_number', 'status', 'picker_id', 'completed_by']);

echo "Найдено заказов: " . $ordersWithCompletedBy->count() . "\n";

foreach ($ordersWithCompletedBy as $order) {
    echo "Заказ #{$order->order_number}: статус={$order->status}, picker_id={$order->picker_id}, completed_by={$order->completed_by}";
    if ($order->completedBy) {
        echo " (завершил: {$order->completedBy->name})";
    }
    echo "\n";
}

echo "\nВсе сборщики:\n";
$pickers = Picker::all(['id', 'login', 'name']);
foreach ($pickers as $picker) {
    $completedCount = Order::where('completed_by', $picker->id)->count();
    echo "Сборщик #{$picker->id} ({$picker->login} - {$picker->name}): завершил {$completedCount} заказов\n";
}
