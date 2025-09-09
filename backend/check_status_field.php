<?php
require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

try {
    $columns = DB::select("SHOW COLUMNS FROM orders LIKE 'status'");
    
    if ($columns) {
        echo "Поле status:\n";
        foreach ($columns as $column) {
            echo "Type: " . $column->Type . "\n";
            echo "Null: " . $column->Null . "\n";
            echo "Key: " . $column->Key . "\n";
            echo "Default: " . $column->Default . "\n";
            echo "Extra: " . $column->Extra . "\n";
        }
    }
    
    // Также проверим какие статусы есть в базе
    echo "\nТекущие статусы в базе:\n";
    $statuses = DB::table('orders')->distinct()->pluck('status');
    foreach ($statuses as $status) {
        echo "- $status (длина: " . strlen($status) . ")\n";
    }
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage();
}
