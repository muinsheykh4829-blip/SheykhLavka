<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== ПРОВЕРКА И ОБНОВЛЕНИЕ ЗАКАЗОВ ===\n\n";
    
    // Проверяем структуру таблицы orders
    $columns = DB::select('DESCRIBE orders');
    foreach ($columns as $column) {
        if ($column->Field === 'status') {
            echo "Поле status: {$column->Type}\n";
        }
    }
    
    // Показываем все заказы
    $allOrders = DB::table('orders')->get(['id', 'order_number', 'status']);
    echo "\nВсе заказы:\n";
    foreach ($allOrders as $order) {
        echo "- #{$order->id} {$order->order_number}: {$order->status}\n";
    }
    
    // Обновляем несколько существующих заказов на статус 'collected'
    $ordersToUpdate = DB::table('orders')
        ->whereIn('status', ['pending', 'confirmed'])
        ->limit(3)
        ->get(['id', 'order_number']);
        
    if ($ordersToUpdate->count() > 0) {
        $updated = DB::table('orders')
            ->whereIn('id', $ordersToUpdate->pluck('id'))
            ->update([
                'status' => 'collected',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
        echo "\nОбновлено заказов до статуса 'collected': {$updated}\n";
        
        // Добавляем записи в историю статусов
        foreach ($ordersToUpdate as $order) {
            DB::table('order_statuses')->insert([
                'order_id' => $order->id,
                'status' => 'collected',
                'changed_by_type' => 'admin',
                'changed_by_id' => 1,
                'comment' => 'Заказ собран и готов к доставке',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        echo "Добавлены записи в историю статусов\n";
    }
    
    // Показываем заказы со статусом collected
    $collectedOrders = DB::table('orders')
        ->where('status', 'collected')
        ->whereNull('courier_id')
        ->get(['id', 'order_number', 'delivery_address']);
        
    echo "\n📦 ЗАКАЗЫ ГОТОВЫЕ К ДОСТАВКЕ:\n";
    foreach ($collectedOrders as $order) {
        echo "- {$order->order_number}: {$order->delivery_address}\n";
    }
    
    echo "\n✅ Готово! Теперь у вас есть заказы для тестирования курьерского приложения.\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
}
