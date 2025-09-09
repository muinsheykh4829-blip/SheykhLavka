<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $columns = DB::getSchemaBuilder()->getColumnListing('banners');
    echo "Столбцы таблицы banners:\n";
    foreach ($columns as $column) {
        echo "- " . $column . "\n";
    }
    
    // Проверим существование нужных столбцов
    $requiredColumns = [
        'title_ru', 'description_ru', 'target_audience', 
        'click_count', 'view_count', 'start_date', 'end_date'
    ];
    
    echo "\nПроверка необходимых столбцов:\n";
    foreach ($requiredColumns as $col) {
        $exists = in_array($col, $columns);
        echo "- {$col}: " . ($exists ? "✓ Есть" : "✗ Отсутствует") . "\n";
    }
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
