<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Banner;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Свежие фрукты',
                'subtitle' => 'Скидки до 20% на все фрукты',
                'image' => 'assets/banners/banner1.jpg',
                'link_type' => 'category',
                'link_id' => 1, // ID категории фрукты
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Молочные продукты',
                'subtitle' => 'Натуральные и полезные',
                'image' => 'assets/banners/banner2.jpg',
                'link_type' => 'category',
                'link_id' => 3, // ID категории молочные
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Хлебобулочные изделия',
                'subtitle' => 'Свежая выпечка каждый день',
                'image' => 'assets/banners/banner3.jpg',
                'link_type' => 'category',
                'link_id' => 6, // ID категории хлеб
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Овощи',
                'subtitle' => 'Прямо с грядки',
                'image' => 'assets/banners/banner4.jpg',
                'link_type' => 'category',
                'link_id' => 2, // ID категории овощи
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'title' => 'Мясные продукты',
                'subtitle' => 'Качество премиум класса',
                'image' => 'assets/banners/banner5.jpg',
                'link_type' => 'category',
                'link_id' => 4, // ID категории мясо
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($banners as $bannerData) {
            Banner::create($bannerData);
        }
    }
}
