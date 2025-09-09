<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fruits = Category::where('slug', 'fruits')->first();
        $vegetables = Category::where('slug', 'vegetables')->first();
        $dairy = Category::where('slug', 'dairy')->first();
        $meat = Category::where('slug', 'meat-poultry')->first();
        $bakery = Category::where('slug', 'bakery')->first();
        $drinks = Category::where('slug', 'drinks')->first();

        $products = [
            // Фрукты
            [
                'name' => 'Apple',
                'name_ru' => 'Яблоки красные',
                'slug' => 'apple-red',
                'description' => 'Свежие красные яблоки из Ферганской долины',
                'short_description' => 'Сладкие и сочные',
                'price' => 15000.00,
                'discount_price' => 12000.00,
                'unit' => 'кг',
                'stock_quantity' => 50,
                'sku' => 'FRU-APP-001',
                'category_id' => $fruits->id,
                'is_active' => true,
                'is_featured' => true,
                'weight' => 1.000,
                'images' => ['products/apples-red.jpg'],
            ],
            [
                'name' => 'Banana',
                'name_ru' => 'Бананы',
                'slug' => 'banana',
                'description' => 'Спелые бананы из Эквадора',
                'short_description' => 'Сладкие и питательные',
                'price' => 25000.00,
                'unit' => 'кг',
                'stock_quantity' => 30,
                'sku' => 'FRU-BAN-001',
                'category_id' => $fruits->id,
                'is_active' => true,
                'is_featured' => true,
                'weight' => 1.000,
                'images' => ['products/banana.jpg'],
            ],
            [
                'name' => 'Orange',
                'name_ru' => 'Апельсины',
                'slug' => 'orange',
                'description' => 'Сочные апельсины, богатые витамином C',
                'short_description' => 'Свежие и сочные',
                'price' => 20000.00,
                'unit' => 'кг',
                'stock_quantity' => 25,
                'sku' => 'FRU-ORA-001',
                'category_id' => $fruits->id,
                'is_active' => true,
                'weight' => 1.000,
                'images' => ['products/orange.jpg'],
            ],

            // Овощи
            [
                'name' => 'Tomato',
                'name_ru' => 'Помидоры',
                'slug' => 'tomato',
                'description' => 'Свежие красные помидоры',
                'short_description' => 'Сочные и ароматные',
                'price' => 18000.00,
                'unit' => 'кг',
                'stock_quantity' => 40,
                'sku' => 'VEG-TOM-001',
                'category_id' => $vegetables->id,
                'is_active' => true,
                'weight' => 1.000,
                'images' => ['products/tomato.jpg'],
            ],
            [
                'name' => 'Cucumber',
                'name_ru' => 'Огурцы',
                'slug' => 'cucumber',
                'description' => 'Свежие хрустящие огурцы',
                'short_description' => 'Идеально для салатов',
                'price' => 12000.00,
                'unit' => 'кг',
                'stock_quantity' => 35,
                'sku' => 'VEG-CUC-001',
                'category_id' => $vegetables->id,
                'is_active' => true,
                'is_featured' => true,
                'weight' => 1.000,
                'images' => ['products/cucumber.jpg'],
            ],

            // Молочные продукты
            [
                'name' => 'Milk',
                'name_ru' => 'Молоко 3.2%',
                'slug' => 'milk-32',
                'description' => 'Свежее коровье молоко 3.2% жирности',
                'short_description' => 'Натуральное и полезное',
                'price' => 9000.00,
                'unit' => 'л',
                'stock_quantity' => 20,
                'sku' => 'DAI-MIL-001',
                'category_id' => $dairy->id,
                'is_active' => true,
                'is_featured' => true,
                'weight' => 1.000,
                'images' => ['products/milk.jpg'],
            ],
            [
                'name' => 'Cheese',
                'name_ru' => 'Сыр Российский',
                'slug' => 'cheese-russian',
                'description' => 'Традиционный российский сыр',
                'short_description' => 'Нежный и ароматный',
                'price' => 45000.00,
                'unit' => 'кг',
                'stock_quantity' => 15,
                'sku' => 'DAI-CHE-001',
                'category_id' => $dairy->id,
                'is_active' => true,
                'weight' => 1.000,
                'images' => ['products/cheese.jpg'],
            ],

            // Мясо
            [
                'name' => 'Chicken Breast',
                'name_ru' => 'Куриная грудка',
                'slug' => 'chicken-breast',
                'description' => 'Свежая куриная грудка без кости',
                'short_description' => 'Диетическое мясо',
                'price' => 35000.00,
                'unit' => 'кг',
                'stock_quantity' => 10,
                'sku' => 'MEA-CHI-001',
                'category_id' => $meat->id,
                'is_active' => true,
                'weight' => 1.000,
                'images' => ['products/chicken-breast.jpg'],
            ],

            // Хлеб
            [
                'name' => 'Bread',
                'name_ru' => 'Хлеб белый',
                'slug' => 'bread-white',
                'description' => 'Свежий белый хлеб',
                'short_description' => 'Мягкий и ароматный',
                'price' => 3000.00,
                'unit' => 'шт',
                'stock_quantity' => 25,
                'sku' => 'BAK-BRE-001',
                'category_id' => $bakery->id,
                'is_active' => true,
                'weight' => 0.400,
                'images' => ['products/bread-white.jpg'],
            ],

            // Напитки
            [
                'name' => 'Water',
                'name_ru' => 'Вода питьевая',
                'slug' => 'water-drinking',
                'description' => 'Чистая питьевая вода',
                'short_description' => 'Освежающая и чистая',
                'price' => 2000.00,
                'unit' => 'л',
                'stock_quantity' => 100,
                'sku' => 'DRI-WAT-001',
                'category_id' => $drinks->id,
                'is_active' => true,
                'is_featured' => true,
                'weight' => 1.000,
                'images' => ['products/water.jpg'],
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }
    }
}
