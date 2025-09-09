<?php
// Проверка состояния заказов в базе данных

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;
use App\Models\Courier;

echo "=== АНАЛИЗ ЗАКАЗОВ В БАЗЕ ДАННЫХ ===\n\n";

// Получаем все заказы
$orders = Order::all();

echo "📊 Всего заказов в системе: " . $orders->count() . "\n\n";

echo "📋 ДЕТАЛЬНАЯ ИНФОРМАЦИЯ О ЗАКАЗАХ:\n";
echo str_repeat("-", 80) . "\n";
printf("%-5s %-15s %-12s %-12s %-15s %-15s\n", "ID", "Номер", "Статус", "courier_id", "delivered_by", "Клиент");
echo str_repeat("-", 80) . "\n";

foreach ($orders as $order) {
    printf("%-5d %-15s %-12s %-12s %-15s %-15s\n", 
        $order->id,
        $order->order_number,
        $order->status,
        $order->courier_id ?? 'NULL',
        $order->delivered_by ?? 'NULL',
        substr($order->user->name ?? 'Неизвестно', 0, 14)
    );
}

echo "\n📈 СТАТИСТИКА ПО СТАТУСАМ:\n";
$statusCounts = Order::selectRaw('status, COUNT(*) as count')
    ->groupBy('status')
    ->get();

foreach ($statusCounts as $stat) {
    echo "  {$stat->status}: {$stat->count} заказов\n";
}

echo "\n🚚 ЗАКАЗЫ ПО КУРЬЕРАМ:\n";

// Заказы без курьера (готовые к доставке)
$readyOrders = Order::where('status', 'ready')->whereNull('courier_id')->count();
echo "  Готовы к доставке (без курьера): $readyOrders\n";

// Заказы в доставке по курьерам
$courierOrders = Order::selectRaw('courier_id, COUNT(*) as count')
    ->whereNotNull('courier_id')
    ->whereIn('status', ['delivering', 'in_delivery'])
    ->groupBy('courier_id')
    ->get();

foreach ($courierOrders as $courierOrder) {
    $courier = Courier::find($courierOrder->courier_id);
    $courierName = $courier ? "{$courier->first_name} {$courier->last_name}" : "Курьер #{$courierOrder->courier_id}";
    echo "  $courierName: {$courierOrder->count} заказов в доставке\n";
}

// Доставленные заказы по курьерам
echo "\n📦 ДОСТАВЛЕННЫЕ ЗАКАЗЫ:\n";
$deliveredOrders = Order::selectRaw('delivered_by, COUNT(*) as count')
    ->where('status', 'delivered')
    ->whereNotNull('delivered_by')
    ->groupBy('delivered_by')
    ->get();

foreach ($deliveredOrders as $deliveredOrder) {
    $courier = Courier::find($deliveredOrder->delivered_by);
    $courierName = $courier ? "{$courier->first_name} {$courier->last_name}" : "Курьер #{$deliveredOrder->delivered_by}";
    echo "  $courierName: {$deliveredOrder->count} доставленных заказов\n";
}

echo "\n👥 СПИСОК КУРЬЕРОВ:\n";
$couriers = Courier::all();
foreach ($couriers as $courier) {
    echo "  ID {$courier->id}: {$courier->first_name} {$courier->last_name} (логин: {$courier->username})\n";
}
