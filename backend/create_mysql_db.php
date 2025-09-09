<?php

// Скрипт для создания базы данных MySQL
try {
    echo "Попытка подключения к MySQL...\n";
    
    // Подключаемся к MySQL без указания базы данных
    $pdo = new PDO('mysql:host=127.0.0.1', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Подключение к MySQL успешно!\n";
    
    // Создаем базу данных
    $pdo->exec("CREATE DATABASE IF NOT EXISTS sheykh_lavka CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ База данных 'sheykh_lavka' создана успешно!\n";
    
    // Проверяем, что база создалась
    $stmt = $pdo->query("SHOW DATABASES LIKE 'sheykh_lavka'");
    if ($stmt->rowCount() > 0) {
        echo "✅ База данных существует!\n";
    }
    
    echo "\n🎉 Готово! Теперь можно запускать миграции Laravel.\n";
    
} catch (PDOException $e) {
    echo "❌ Ошибка подключения к MySQL: " . $e->getMessage() . "\n";
    echo "\n💡 Возможные решения:\n";
    echo "1. Убедитесь, что MySQL сервер запущен\n";
    echo "2. Проверьте логин/пароль (сейчас: root без пароля)\n";
    echo "3. Установите MySQL или используйте XAMPP/WAMP\n";
    echo "4. Можно продолжить работать с SQLite\n";
}
