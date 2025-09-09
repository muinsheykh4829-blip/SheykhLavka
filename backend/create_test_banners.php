<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Проверим, есть ли уже баннеры
    $bannersCount = DB::table('banners')->count();
    
    if ($bannersCount > 0) {
        echo "В таблице уже есть {$bannersCount} баннер(ов). Создание новых пропущено.\n";
        return;
    }
    
    // Создаем тестовые баннеры
    $banners = [
        [
            'title' => 'Скидка 20% на все товары!',
            'title_ru' => 'Скидка 20% на все товары!',
            'description' => 'Специальное предложение для всех покупателей',
            'description_ru' => 'Специальное предложение для всех покупателей',
            'image' => 'banners/banner1.jpg',
            'link_url' => 'https://example.com/sale',
            'sort_order' => 1,
            'is_active' => true,
            'target_audience' => 'all',
            'click_count' => rand(10, 100),
            'view_count' => rand(100, 1000),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'title' => 'Новые поступления',
            'title_ru' => 'Новые поступления',
            'description' => 'Свежие товары в наличии',
            'description_ru' => 'Свежие товары в наличии',
            'image' => 'banners/banner2.jpg',
            'link_url' => 'https://example.com/new',
            'sort_order' => 2,
            'is_active' => true,
            'target_audience' => 'new',
            'click_count' => rand(5, 50),
            'view_count' => rand(50, 500),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'title' => 'Premium товары',
            'title_ru' => 'Премиум товары',
            'description' => 'Эксклюзивные товары для VIP клиентов',
            'description_ru' => 'Эксклюзивные товары для VIP клиентов',
            'image' => 'banners/banner3.jpg',
            'link_url' => 'https://example.com/premium',
            'sort_order' => 3,
            'is_active' => false,
            'target_audience' => 'premium',
            'click_count' => rand(1, 20),
            'view_count' => rand(20, 200),
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(30),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ];
    
    foreach ($banners as $banner) {
        DB::table('banners')->insert($banner);
        echo "Создан баннер: {$banner['title']}\n";
    }
    
    echo "Успешно создано " . count($banners) . " тестовых баннеров!\n";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
