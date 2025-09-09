<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Обновляем существующие баннеры с правильными путями изображений
    $banners = [
        [
            'id' => 1,
            'title' => 'Скидка 20% на все товары!',
            'title_ru' => 'Скидка 20% на все товары!',
            'description' => 'Специальное предложение для всех покупателей',
            'description_ru' => 'Специальное предложение для всех покупателей',
            'image' => 'banners/banner1.jpg',
            'link_url' => 'https://example.com/sale',
            'is_active' => 1,
            'target_audience' => 'all',
        ],
        [
            'id' => 2,
            'title' => 'Новые поступления',
            'title_ru' => 'Новые поступления',
            'description' => 'Свежие товары в наличии',
            'description_ru' => 'Свежие товары в наличии', 
            'image' => 'banners/banner2.jpg',
            'link_url' => 'https://example.com/new',
            'is_active' => 1,
            'target_audience' => 'new',
        ],
        [
            'id' => 3,
            'title' => 'Premium товары',
            'title_ru' => 'Премиум товары',
            'description' => 'Эксклюзивные товары для VIP клиентов',
            'description_ru' => 'Эксклюзивные товары для VIP клиентов',
            'image' => 'banners/banner3.jpg',
            'link_url' => 'https://example.com/premium',
            'is_active' => 1,
            'target_audience' => 'premium',
        ],
    ];
    
    foreach ($banners as $banner) {
        DB::table('banners')
            ->where('id', $banner['id'])
            ->update([
                'title' => $banner['title'],
                'title_ru' => $banner['title_ru'],
                'description' => $banner['description'],
                'description_ru' => $banner['description_ru'],
                'image' => $banner['image'],
                'link_url' => $banner['link_url'],
                'is_active' => $banner['is_active'],
                'target_audience' => $banner['target_audience'],
                'updated_at' => now(),
            ]);
        echo "Обновлен баннер ID {$banner['id']}: {$banner['title']}\n";
    }
    
    echo "Баннеры успешно обновлены!\n";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
