<?php

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

class TestOrderSeeder extends Seeder
{
    public function run()
    {
        // Получаем первого пользователя
        $user = User::first();
        
        // Получаем несколько продуктов
        $products = Product::take(5)->get();
        
        if (!$user || $products->count() == 0) {
            echo "Нужны пользователи и продукты для создания заказов\n";
            return;
        }

        $statuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivering', 'delivered', 'cancelled'];
        
        for ($i = 1; $i <= 10; $i++) {
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'status' => $statuses[array_rand($statuses)],
                'subtotal' => rand(1000, 5000),
                'delivery_fee' => 200,
                'discount' => rand(0, 500),
                'total' => rand(1200, 5200),
                'payment_method' => ['cash', 'card', 'online'][array_rand(['cash', 'card', 'online'])],
                'payment_status' => ['pending', 'paid'][array_rand(['pending', 'paid'])],
                'delivery_address' => 'Тестовый адрес доставки ' . $i,
                'delivery_name' => 'Тестовый Клиент ' . $i,
                'delivery_phone' => '+998901234' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'comment' => 'Тестовый комментарий для заказа ' . $i,
                'created_at' => now()->subDays(rand(0, 30)),
            ]);

            // Добавляем товары в заказ
            foreach ($products->random(rand(1, 3)) as $product) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => rand(1, 3),
                    'price' => $product->price,
                    'total' => $product->price * rand(1, 3),
                ]);
            }
        }
        
        echo "Создано 10 тестовых заказов\n";
    }
}
