<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Проверка URLs изображений баннеров:" . PHP_EOL;

$banners = App\Models\Banner::active()->get();
foreach($banners as $banner) {
    echo "ID: {$banner->id}" . PHP_EOL;
    echo "Название: {$banner->title}" . PHP_EOL;
    echo "Путь в БД: {$banner->image}" . PHP_EOL;
    echo "URL изображения: {$banner->image_url}" . PHP_EOL;
    echo "---" . PHP_EOL;
}
