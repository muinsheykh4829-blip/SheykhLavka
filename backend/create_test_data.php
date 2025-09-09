<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Product; 
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;

echo "=== Проверка и создание тестовых данных ===\n";

// Проверим подключение к базе данных
try {
    $userCount = User::count();
    echo "✓ Подключение к базе данных работает\n";
    echo "Пользователей в базе: $userCount\n";
} catch (Exception $e) {
    echo "✗ Ошибка подключения к базе данных: " . $e->getMessage() . "\n";
    exit(1);
}

// Создадим категории если их нет
$categoriesData = [
    ['name' => 'Овощи', 'slug' => 'vegetables', 'image' => 'categories/vegetables.png'],
    ['name' => 'Фрукты', 'slug' => 'fruits', 'image' => 'categories/fruits.png'],
    ['name' => 'Молочные продукты', 'slug' => 'dairy', 'image' => 'categories/dairy.png']
];

foreach ($categoriesData as $categoryData) {
    try {
        $category = Category::firstOrCreate(['slug' => $categoryData['slug']], $categoryData);
        echo "✓ Категория '{$category->name}' создана/найдена (ID: {$category->id})\n";
    } catch (Exception $e) {
        echo "✗ Ошибка создания категории: " . $e->getMessage() . "\n";
    }
}

// Создадим продукты если их нет
$productsData = [
    [
        'name' => 'Помидоры',
        'description' => 'Свежие красные помидоры',
        'price' => 1200,
        'weight' => '500',
        'unit' => 'г',
        'category_id' => 1,
        'image' => 'products/tomatoes.jpg',
        'in_stock' => true,
        'stock_quantity' => 100
    ],
    [
        'name' => 'Яблоки',
        'description' => 'Сладкие красные яблоки',
        'price' => 800,
        'weight' => '1',
        'unit' => 'кг',
        'category_id' => 2,
        'image' => 'products/apples.jpg',
        'in_stock' => true,
        'stock_quantity' => 50
    ],
    [
        'name' => 'Молоко',
        'description' => 'Свежее коровье молоко',
        'price' => 600,
        'weight' => '1',
        'unit' => 'л',
        'category_id' => 3,
        'image' => 'products/milk.jpg',
        'in_stock' => true,
        'stock_quantity' => 30
    ]
];

foreach ($productsData as $productData) {
    try {
        $product = Product::firstOrCreate(['name' => $productData['name']], $productData);
        echo "✓ Продукт '{$product->name}' создан/найден (ID: {$product->id})\n";
    } catch (Exception $e) {
        echo "✗ Ошибка создания продукта: " . $e->getMessage() . "\n";
    }
}

// Создадим тестового пользователя
try {
    $user = User::firstOrCreate([
        'phone' => '+992900123456'
    ], [
        'first_name' => 'Тест',
        'last_name' => 'Пользователь',
        'phone_verified_at' => now()
    ]);
    echo "✓ Тестовый пользователь создан/найден (ID: {$user->id})\n";
} catch (Exception $e) {
    echo "✗ Ошибка создания пользователя: " . $e->getMessage() . "\n";
}

// Создадим тестовые заказы
$products = Product::limit(3)->get();
if ($products->count() > 0 && isset($user)) {
    for ($i = 1; $i <= 3; $i++) {
        try {
            $subtotal = 0;
            $selectedProducts = $products->random(rand(1, 2));
            
            // Создаем заказ
            $order = Order::create([
                'order_number' => 'TEST-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'user_id' => $user->id,
                'status' => ['pending', 'confirmed', 'delivered'][array_rand(['pending', 'confirmed', 'delivered'])],
                'subtotal' => 0, // Обновим после создания товаров
                'delivery_fee' => 5000,
                'discount' => 0,
                'total' => 0, // Обновим после создания товаров
                'payment_method' => 'cash',
                'payment_status' => 'pending',
                'delivery_address' => 'г. Душанбе, ул. Тестовая, д. ' . $i,
                'delivery_phone' => $user->phone,
                'delivery_name' => $user->first_name . ' ' . $user->last_name,
                'comment' => 'Тестовый заказ №' . $i
            ]);
            
            // Добавляем товары в заказ
            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 3);
                $price = $product->price;
                $total = $price * $quantity;
                $subtotal += $total;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $total
                ]);
            }
            
            // Обновляем суммы заказа
            $order->update([
                'subtotal' => $subtotal,
                'total' => $subtotal + $order->delivery_fee - $order->discount
            ]);
            
            echo "✓ Тестовый заказ #{$order->order_number} создан (ID: {$order->id}, статус: {$order->status})\n";
        } catch (Exception $e) {
            echo "✗ Ошибка создания заказа: " . $e->getMessage() . "\n";
        }
    }
}

// Показываем статистику
try {
    $totalOrders = Order::count();
    $totalProducts = Product::count();
    $totalUsers = User::count();
    
    echo "\n=== Статистика ===\n";
    echo "Всего заказов: $totalOrders\n";
    echo "Всего продуктов: $totalProducts\n";
    echo "Всего пользователей: $totalUsers\n";
    
    if ($totalOrders > 0) {
        echo "\n=== Последние заказы ===\n";
        $orders = Order::with(['user', 'items'])->orderBy('created_at', 'desc')->limit(5)->get();
        foreach ($orders as $order) {
            $itemsCount = $order->items->count();
            echo "- Заказ #{$order->order_number} от {$order->user->first_name} ({$itemsCount} товаров, статус: {$order->status})\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Ошибка получения статистики: " . $e->getMessage() . "\n";
}

echo "\n=== Готово! ===\n";
