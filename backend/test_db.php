<?php

// Простая проверка подключения к базе данных
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=sheykh_lavka;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Подключение к базе данных успешно\n";
    
    // Проверим таблицы
    $tables = ['users', 'categories', 'products', 'orders', 'order_items'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "Таблица '$table': $count записей\n";
    }
    
    // Создадим тестовые данные напрямую через SQL
    echo "\n=== Создание тестовых данных ===\n";
    
    // Создаем категорию
    $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, slug, image, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
    $stmt->execute(['Тестовая категория', 'test-category', 'categories/test.png']);
    echo "✓ Категория создана\n";
    
    // Получаем ID категории
    $stmt = $pdo->query("SELECT id FROM categories WHERE slug = 'test-category' LIMIT 1");
    $categoryId = $stmt->fetchColumn();
    
    // Создаем продукт
    $stmt = $pdo->prepare("INSERT IGNORE INTO products (name, description, price, weight, unit, category_id, image, in_stock, stock_quantity, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute(['Тестовый продукт', 'Описание тестового продукта', 1000, '500', 'г', $categoryId, 'products/test.jpg', 1, 100]);
    echo "✓ Продукт создан\n";
    
    // Создаем пользователя
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (first_name, last_name, phone, phone_verified_at, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW(), NOW())");
    $stmt->execute(['Тест', 'Пользователь', '+992900123456']);
    echo "✓ Пользователь создан\n";
    
    // Получаем ID пользователя и продукта
    $stmt = $pdo->query("SELECT id FROM users WHERE phone = '+992900123456' LIMIT 1");
    $userId = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT id FROM products WHERE name = 'Тестовый продукт' LIMIT 1");
    $productId = $stmt->fetchColumn();
    
    // Создаем заказ
    $orderNumber = 'TEST-' . time();
    $stmt = $pdo->prepare("INSERT INTO orders (order_number, user_id, status, subtotal, delivery_fee, discount, total, payment_method, payment_status, delivery_address, delivery_phone, delivery_name, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$orderNumber, $userId, 'pending', 1000, 5000, 0, 6000, 'cash', 'pending', 'Тестовый адрес', '+992900123456', 'Тест Пользователь']);
    $orderId = $pdo->lastInsertId();
    echo "✓ Заказ создан (ID: $orderId)\n";
    
    // Создаем товар заказа
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, total, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$orderId, $productId, 1, 1000, 1000]);
    echo "✓ Товар заказа создан\n";
    
    echo "\n=== Финальная статистика ===\n";
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "Таблица '$table': $count записей\n";
    }
    
    echo "\n✓ Тестовые данные успешно созданы!\n";
    
} catch (PDOException $e) {
    echo "✗ Ошибка подключения к базе данных: " . $e->getMessage() . "\n";
    echo "Убедитесь, что MySQL запущен и база данных 'sheykh_lavka' существует\n";
}
