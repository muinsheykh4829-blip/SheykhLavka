<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Fruits',
                'name_ru' => 'Фрукты',
                'slug' => 'fruits',
                'icon' => 'assets/categories/fruits.png',
                'description' => 'Свежие фрукты',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Vegetables',
                'name_ru' => 'Овощи',
                'slug' => 'vegetables',
                'icon' => 'assets/categories/vegetables.png',
                'description' => 'Свежие овощи',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Dairy',
                'name_ru' => 'Молочные продукты',
                'slug' => 'dairy',
                'icon' => 'assets/categories/dairy.png',
                'description' => 'Молоко, сыр, йогурт',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Meat & Poultry',
                'name_ru' => 'Мясо и птица',
                'slug' => 'meat-poultry',
                'icon' => 'assets/categories/meat_poultry.png',
                'description' => 'Свежее мясо и птица',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Fish & Seafood',
                'name_ru' => 'Рыба и морепродукты',
                'slug' => 'fish-seafood',
                'icon' => 'assets/categories/fish_seafood.png',
                'description' => 'Свежая рыба и морепродукты',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Bakery',
                'name_ru' => 'Хлебобулочные изделия',
                'slug' => 'bakery',
                'icon' => 'assets/categories/bakery.png',
                'description' => 'Хлеб, булочки, выпечка',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Drinks',
                'name_ru' => 'Напитки',
                'slug' => 'drinks',
                'icon' => 'assets/categories/drinks.png',
                'description' => 'Соки, газировка, вода',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Tea & Coffee',
                'name_ru' => 'Чай и кофе',
                'slug' => 'tea-coffee',
                'icon' => 'assets/categories/tea_coffee.png',
                'description' => 'Чай, кофе, горячие напитки',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Frozen Foods',
                'name_ru' => 'Замороженные продукты',
                'slug' => 'frozen',
                'icon' => 'assets/categories/frozen.png',
                'description' => 'Замороженные продукты',
                'sort_order' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Household Chemicals',
                'name_ru' => 'Бытовая химия',
                'slug' => 'household-chemicals',
                'icon' => 'assets/categories/household_chemicals.png',
                'description' => 'Моющие и чистящие средства',
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Personal Care',
                'name_ru' => 'Личная гигиена',
                'slug' => 'personal-care',
                'icon' => 'assets/categories/personal_care.png',
                'description' => 'Товары для личной гигиены',
                'sort_order' => 11,
                'is_active' => true,
            ],
            [
                'name' => 'Baby Food',
                'name_ru' => 'Детское питание',
                'slug' => 'baby-food',
                'icon' => 'assets/categories/baby_food.png',
                'description' => 'Продукты для детей',
                'sort_order' => 12,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }
    }
}
