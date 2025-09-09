<?php

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class QuickTestSeeder extends Seeder
{
    public function run()
    {
        // Создадим несколько категорий
        $categories = [
            ['name' => 'Овощи', 'slug' => 'vegetables', 'image' => 'categories/vegetables.png'],
            ['name' => 'Фрукты', 'slug' => 'fruits', 'image' => 'categories/fruits.png'],
            ['name' => 'Молочные продукты', 'slug' => 'dairy', 'image' => 'categories/dairy.png']
        ];

        foreach ($categories as $categoryData) {
            $category = Category::firstOrCreate(['slug' => $categoryData['slug']], $categoryData);
        }

        // Создадим несколько продуктов
        $products = [
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

        foreach ($products as $productData) {
            Product::firstOrCreate(['name' => $productData['name']], $productData);
        }
    }
}
