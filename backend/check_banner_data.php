<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Количество баннеров в базе данных: " . App\Models\Banner::count() . PHP_EOL;

$banners = App\Models\Banner::all();
foreach($banners as $banner) {
    echo "ID: {$banner->id}, Название: {$banner->title}, Изображение: {$banner->image}, Активен: " . ($banner->is_active ? 'Да' : 'Нет') . PHP_EOL;
}

if ($banners->count() == 0) {
    echo "Баннеры не найдены в базе данных!" . PHP_EOL;
}
