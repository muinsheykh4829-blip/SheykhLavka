<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sheykh_lavka', 'root', '');
    $stmt = $pdo->query('DESCRIBE orders');
    $columns = $stmt->fetchAll();
    
    echo "Поля таблицы orders:\n";
    foreach ($columns as $column) {
        echo "  {$column['Field']} - {$column['Type']}\n";
    }
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
?>
