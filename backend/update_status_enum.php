<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== ОБНОВЛЕНИЕ ENUM ДЛЯ СТАТУСОВ ===\n\n";
    
    // Расширяем ENUM для поля status
    DB::statement('ALTER TABLE orders MODIFY COLUMN status ENUM(
        "pending",
        "confirmed", 
        "processing",
        "accepted",
        "preparing",
        "ready",
        "collected",
        "courier_assigned",
        "in_delivery",
        "delivering",
        "delivered",
        "completed",
        "cancelled"
    ) DEFAULT "pending"');
    
    echo "✅ ENUM обновлен для поля status\n\n";
    
    // Теперь обновим заказы со статусом 'ready' на 'collected'
    $updated = DB::table('orders')
        ->where('status', 'ready')
        ->update([
            'status' => 'collected',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
    echo "✅ Обновлено заказов: {$updated}\n";
    
    // Добавляем записи в историю
    $readyOrders = DB::table('orders')->where('status', 'collected')->get(['id']);
    foreach ($readyOrders as $order) {
        // Проверяем, нет ли уже записи
        $existing = DB::table('order_statuses')
            ->where('order_id', $order->id)
            ->where('status', 'collected')
            ->first();
            
        if (!$existing) {
            DB::table('order_statuses')->insert([
                'order_id' => $order->id,
                'status' => 'collected',
                'changed_by_type' => 'admin',
                'changed_by_id' => 1,
                'comment' => 'Заказ собран и готов к доставке',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    // Показываем результат
    $collectedCount = DB::table('orders')
        ->where('status', 'collected')
        ->whereNull('courier_id')
        ->count();
        
    echo "📦 Заказов готовых к доставке: {$collectedCount}\n";
    
    echo "\n🎉 Готово! Теперь можно тестировать курьерское приложение.\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
}
