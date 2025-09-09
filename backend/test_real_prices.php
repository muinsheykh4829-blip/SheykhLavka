<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Проверка фактических цен ===\n";

// Добавим тестовый товар картошка
$potato = App\Models\Product::updateOrCreate(
    ['name' => 'Картошка'],
    [
        'name_ru' => 'Картошка',
        'price' => 12.50, // фактическая цена 12.5 сом
        'unit' => 'кг',
        'category_id' => 1,
        'is_active' => true
    ]
);

echo "Создан товар: {$potato->name}\n";
echo "Цена в БД: {$potato->price} сом\n";
echo "Цена через модель: {$potato->price} сом\n";
echo "Formatted price: {$potato->formattedPriceWithKopecks}\n";

// Проверим существующие товары
echo "\n=== Все товары с ценами ===\n";
$products = App\Models\Product::all();
foreach ($products as $product) {
    echo "- {$product->name}: {$product->price} сом\n";
}

// Тест расчета для корзины
echo "\n=== Тест расчета корзины ===\n";
echo "Картошка 12.5 сом/кг × 2 кг = " . (12.5 * 2) . " сом\n";
echo "Помидоры 12.0 сом/кг × 1.5 кг = " . (12.0 * 1.5) . " сом\n";
