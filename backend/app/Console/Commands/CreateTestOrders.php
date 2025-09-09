<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Faker\Factory as Faker;

class CreateTestOrders extends Command
{
    protected $signature = 'test:orders';
    protected $description = 'Create test orders for development';

    public function handle()
    {
        $faker = Faker::create();
        
        // Найдем или создадим тестового пользователя
        $user = User::firstOrCreate([
            'phone' => '+992900123456'
        ], [
            'first_name' => 'Тест',
            'last_name' => 'Пользователь',
            'phone_verified_at' => now()
        ]);

        // Найдем несколько продуктов
        $products = Product::limit(5)->get();
        
        if ($products->isEmpty()) {
            $this->error('Нет продуктов в базе данных!');
            return 1;
        }

        // Создадим несколько тестовых заказов
        for ($i = 1; $i <= 5; $i++) {
            $subtotal = 0;
            $items = [];
            
            // Выберем случайные продукты
            $selectedProducts = $products->random(rand(1, 3));
            
            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 3);
                $price = $product->price;
                $total = $price * $quantity;
                $subtotal += $total;
                
                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $total
                ];
            }
            
            $deliveryFee = 5000;
            $discount = 0;
            $total = $subtotal + $deliveryFee - $discount;
            
            $statuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivering', 'delivered'];
            $status = $faker->randomElement($statuses);
            
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'user_id' => $user->id,
                'status' => $status,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $faker->randomElement(['cash', 'card', 'online']),
                'payment_status' => $status === 'delivered' ? 'paid' : 'pending',
                'delivery_address' => $faker->address,
                'delivery_phone' => $user->phone,
                'delivery_name' => $user->first_name . ' ' . $user->last_name,
                'delivery_time' => $faker->dateTimeBetween('now', '+1 day'),
                'comment' => $faker->optional()->sentence,
                'created_at' => $faker->dateTimeBetween('-7 days', 'now'),
            ]);
            
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['total']
                ]);
            }
            
            $this->info("Заказ #{$order->order_number} создан со статусом {$status}");
        }
        
        $this->info('Тестовые заказы успешно созданы!');
        return 0;
    }
}
