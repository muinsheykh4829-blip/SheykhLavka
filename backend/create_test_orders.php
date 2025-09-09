<?php
// Создание тестовых заказов для демонстрации системы управления
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as Capsule;

// Подключаемся к базе данных
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'sqlite',
    'database'  => __DIR__ . '/database/database.sqlite',
    'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    // Проверяем, есть ли уже заказы
    if (Order::count() > 0) {
        echo "Заказы уже существуют (" . Order::count() . " шт.)\n";
        exit;
    }

    // Получаем первого пользователя или создаем тестового
    $user = User::first();
    if (!$user) {
        $user = User::create([
            'first_name' => 'Тестовый',
            'last_name' => 'Пользователь',
            'phone' => '+998901234567',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
        ]);
        echo "Создан тестовый пользователь\n";
    }

    // Получаем продукты
    $products = Product::take(5)->get();
    if ($products->count() == 0) {
        echo "Не найдены продукты для создания заказов\n";
        exit;
    }

    $statuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivering', 'delivered', 'cancelled'];
    
    echo "Создание 10 тестовых заказов...\n";
    
    for ($i = 1; $i <= 10; $i++) {
        $status = $statuses[array_rand($statuses)];
        $subtotal = rand(1000, 5000);
        $delivery_fee = 200;
        $discount = rand(0, 500);
        $total = $subtotal + $delivery_fee - $discount;
        
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-' . str_pad($i, 6, '0', STR_PAD_LEFT),
            'status' => $status,
            'subtotal' => $subtotal,
            'delivery_fee' => $delivery_fee,
            'discount' => $discount,
            'total' => $total,
            'payment_method' => ['cash', 'card', 'online'][array_rand(['cash', 'card', 'online'])],
            'payment_status' => ['pending', 'paid'][array_rand(['pending', 'paid'])],
            'delivery_address' => 'Тестовый адрес доставки ' . $i . ', г. Ташкент',
            'delivery_name' => 'Тестовый Клиент ' . $i,
            'delivery_phone' => '+998901234' . str_pad($i, 3, '0', STR_PAD_LEFT),
            'comment' => 'Тестовый комментарий для заказа ' . $i,
            'created_at' => now()->subDays(rand(0, 30)),
        ]);

        // Добавляем товары в заказ
        $selectedProducts = $products->random(rand(1, 3));
        foreach ($selectedProducts as $product) {
            $quantity = rand(1, 3);
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price,
                'total' => $product->price * $quantity,
            ]);
        }
        
        echo "Создан заказ #{$order->order_number} со статусом: {$status}\n";
    }
    
    echo "\nУспешно создано 10 тестовых заказов!\n";
    echo "Статистика по статусам:\n";
    
    foreach ($statuses as $status) {
        $count = Order::where('status', $status)->count();
        echo "- {$status}: {$count}\n";
    }
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
