<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "Структура таблицы couriers:" . PHP_EOL;
$columns = Schema::getColumnListing('couriers');
foreach($columns as $column) {
    echo "- " . $column . PHP_EOL;
}

echo PHP_EOL . "Проверим, есть ли тестовые курьеры:" . PHP_EOL;
try {
    $couriers = DB::table('couriers')->get();
    echo "Количество курьеров: " . $couriers->count() . PHP_EOL;
    foreach($couriers as $courier) {
        echo "ID: {$courier->id}, Name: {$courier->name}, Phone: {$courier->phone}" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . PHP_EOL;
}
