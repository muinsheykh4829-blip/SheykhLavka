<?php
require_once __DIR__ . '/vendor/autoload.php';

// Загружаем Laravel приложение
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ПРОВЕРКА ЗАКАЗОВ В БАЗЕ ДАННЫХ ===\n\n";

try {
    $totalOrders = \App\Models\Order::count();
    echo "📊 Всего заказов в базе: {$totalOrders}\n\n";
    
    if ($totalOrders > 0) {
        echo "📋 Последние 5 заказов:\n";
        $orders = \App\Models\Order::with(['user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        foreach ($orders as $order) {
            echo "- Заказ #{$order->order_number}\n";
            echo "  Статус: {$order->status}\n";
            echo "  Сумма: {$order->total} сом\n";
            echo "  Дата: {$order->created_at}\n";
            echo "  Клиент: " . ($order->user ? $order->user->first_name . ' ' . $order->user->last_name : $order->delivery_name) . "\n";
            echo "  Телефон: {$order->delivery_phone}\n\n";
        }
    } else {
        echo "❌ Заказов в базе данных нет!\n";
        echo "💡 Возможные причины:\n";
        echo "   1. Заказы не сохраняются через API\n";
        echo "   2. Проблема с подключением к базе данных\n";
        echo "   3. Ошибка в API контроллере\n\n";
    }
    
    // Проверяем последнюю активность пользователей
    $totalUsers = \App\Models\User::count();
    echo "👥 Всего пользователей: {$totalUsers}\n";
    
    if ($totalUsers > 0) {
        $recentUsers = \App\Models\User::orderBy('created_at', 'desc')->take(3)->get();
        echo "👤 Последние пользователи:\n";
        foreach ($recentUsers as $user) {
            echo "- {$user->first_name} {$user->last_name} ({$user->phone})\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка подключения к базе данных:\n";
    echo $e->getMessage() . "\n";
}
