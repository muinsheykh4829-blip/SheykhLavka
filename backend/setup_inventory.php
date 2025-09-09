<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Найдем первую категорию или создадим новую
    $category = \App\Models\Category::first();
    if (!$category) {
        $category = \App\Models\Category::create([
            'name' => 'Тестовые товары',
            'is_active' => true
        ]);
        echo "Created category: {$category->name} (ID: {$category->id})\n";
    }
    
    // Обновляем существующие продукты
    $updated = \App\Models\Product::whereNull('product_type')->update([
        'product_type' => 'piece',
        'stock_quantity_current' => 50,
        'stock_quantity_minimum' => 10,
        'auto_deactivate_on_zero' => false
    ]);
    
    echo "Updated {$updated} products with inventory data\n";
    
    // Создаем несколько тестовых продуктов с разными типами
    $products = [
        [
            'name' => 'Помидоры красные',
            'name_ru' => 'Помидоры красные',  
            'price' => 1500, // 15 сом за кг
            'category_id' => $category->id,
            'product_type' => 'weight',
            'stock_quantity_current' => 25.5,
            'stock_quantity_minimum' => 5,
            'auto_deactivate_on_zero' => true,
        ],
        [
            'name' => 'Хлеб белый',
            'name_ru' => 'Хлеб белый',
            'price' => 200, // 2 сома за штуку
            'category_id' => $category->id,
            'product_type' => 'piece', 
            'stock_quantity_current' => 100,
            'stock_quantity_minimum' => 20,
            'auto_deactivate_on_zero' => true,
        ],
        [
            'name' => 'Молоко пастеризованное',
            'name_ru' => 'Молоко пастеризованное',
            'price' => 450, // 4.50 сома за упаковку
            'category_id' => $category->id,
            'product_type' => 'package',
            'stock_quantity_current' => 35,
            'stock_quantity_minimum' => 15,
            'auto_deactivate_on_zero' => true,
        ],
        [
            'name' => 'Картошка старая (без остатка)',
            'name_ru' => 'Картошка старая (без остатка)',
            'price' => 800,
            'category_id' => $category->id,
            'product_type' => 'weight',
            'stock_quantity_current' => 0,
            'stock_quantity_minimum' => 10,
            'auto_deactivate_on_zero' => true,
            'is_active' => false,
        ],
        [
            'name' => 'Масло подсолнечное (мало остатка)',
            'name_ru' => 'Масло подсолнечное (мало остатка)',
            'price' => 950,
            'category_id' => $category->id,
            'product_type' => 'package',
            'stock_quantity_current' => 3,
            'stock_quantity_minimum' => 15,
            'auto_deactivate_on_zero' => false,
        ]
    ];
    
    foreach ($products as $productData) {
        $product = \App\Models\Product::create($productData);
        echo "Created product: {$product->name} (ID: {$product->id})\n";
    }
    
    echo "\nInventory system setup complete!\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
