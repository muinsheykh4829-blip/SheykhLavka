<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== ПРОВЕРКА ОСНОВНЫХ ДАННЫХ ===\n";
echo "Категории: " . App\Models\Category::count() . "\n";
echo "Продукты: " . App\Models\Product::count() . "\n";
echo "Баннеры: " . App\Models\Banner::count() . "\n";
echo "Пользователи: " . App\Models\User::count() . "\n";
echo "Заказы: " . App\Models\Order::count() . "\n";
echo "Курьеры: " . App\Models\Courier::count() . "\n";
echo "Сборщики: " . App\Models\Picker::count() . "\n";

echo "\n=== ПРОВЕРКА НАСТРОЕК ===\n";
$settings = App\Models\Setting::all();
foreach($settings as $setting) {
    echo "{$setting->key}: {$setting->value}\n";
}

echo "\n=== АКТИВНЫЕ БАННЕРЫ ===\n";
$banners = App\Models\Banner::where('is_active', 1)->get(['title', 'image_url']);
foreach($banners as $banner) {
    echo "- {$banner->title}: {$banner->image_url}\n";
}

echo "\n=== АКТИВНЫЕ ПРОДУКТЫ ===\n";
$products = App\Models\Product::where('is_active', 1)->get(['name', 'price', 'unit']);
foreach($products as $product) {
    echo "- {$product->name}: {$product->price} сом/{$product->unit}\n";
}
