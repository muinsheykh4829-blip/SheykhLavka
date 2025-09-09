<?php

try {
    echo "=== Тест подключения к MySQL базе данных ===\n\n";
    
    // Подключаемся к базе данных
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=sheykh_lavka;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Подключение к базе данных 'sheykh_lavka' успешно!\n\n";
    
    // Получаем список таблиц
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📋 Таблицы в базе данных:\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
    echo "\n";
    
    // Проверим несколько ключевых таблиц
    $keyTables = ['users', 'orders', 'products', 'pickers', 'couriers'];
    
    foreach ($keyTables as $tableName) {
        if (in_array($tableName, $tables)) {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM $tableName");
            $count = $countStmt->fetchColumn();
            echo "✅ Таблица '$tableName': $count записей\n";
        } else {
            echo "❌ Таблица '$tableName' не найдена\n";
        }
    }
    
    echo "\n🎉 База данных готова к работе!\n";
    
} catch (PDOException $e) {
    echo "❌ Ошибка подключения к MySQL: " . $e->getMessage() . "\n";
}
