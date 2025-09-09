<?php

echo "=== ДИАГНОСТИКА БАЗЫ ДАННЫХ ===\n\n";

// Проверяем путь к базе данных
$dbPath = __DIR__ . '/database/database.sqlite';
echo "Путь к базе данных: $dbPath\n";
echo "Существует ли файл: " . (file_exists($dbPath) ? "ДА" : "НЕТ") . "\n";

// Создаем файл базы данных, если он не существует
if (!file_exists($dbPath)) {
    echo "Создаем файл базы данных...\n";
    if (!is_dir(dirname($dbPath))) {
        mkdir(dirname($dbPath), 0755, true);
        echo "Создана папка database\n";
    }
    touch($dbPath);
    echo "Файл базы данных создан\n";
}

// Проверяем размер файла
echo "Размер файла: " . filesize($dbPath) . " байт\n\n";

try {
    // Подключаемся к SQLite
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Подключение к SQLite успешно\n\n";
    
    // Создаем таблицы
    echo "=== СОЗДАНИЕ ТАБЛИЦ ===\n";
    
    // users
    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        email_verified_at DATETIME NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(255) NULL,
        avatar VARCHAR(255) NULL,
        is_admin BOOLEAN DEFAULT 0,
        remember_token VARCHAR(100) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    echo "✅ Таблица users создана\n";
    
    // categories
    $pdo->exec('CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(255) NOT NULL,
        name_ru VARCHAR(255) NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        icon VARCHAR(255) NULL,
        description TEXT NULL,
        sort_order INTEGER DEFAULT 0,
        is_active BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    echo "✅ Таблица categories создана\n";
    
    // products
    $pdo->exec('CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id INTEGER NOT NULL,
        name VARCHAR(255) NOT NULL,
        name_ru VARCHAR(255) NULL,
        description TEXT NULL,
        price INTEGER NOT NULL,
        old_price INTEGER NULL,
        image VARCHAR(255) NULL,
        images TEXT NULL,
        unit VARCHAR(50) DEFAULT "шт",
        weight VARCHAR(50) NULL,
        is_active BOOLEAN DEFAULT 1,
        is_featured BOOLEAN DEFAULT 0,
        stock_quantity INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    echo "✅ Таблица products создана\n";
    
    // orders
    $pdo->exec('CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_number VARCHAR(255) UNIQUE NOT NULL,
        user_id INTEGER NULL,
        status VARCHAR(50) DEFAULT "pending",
        subtotal INTEGER NOT NULL,
        delivery_fee INTEGER DEFAULT 0,
        discount INTEGER DEFAULT 0,
        total INTEGER NOT NULL,
        payment_method VARCHAR(50) DEFAULT "cash",
        payment_status VARCHAR(50) DEFAULT "pending",
        delivery_address TEXT NOT NULL,
        delivery_phone VARCHAR(255) NOT NULL,
        delivery_name VARCHAR(255) NULL,
        delivery_time DATETIME NULL,
        comment TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    echo "✅ Таблица orders создана\n";
    
    // order_items
    $pdo->exec('CREATE TABLE IF NOT EXISTS order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        quantity INTEGER NOT NULL,
        price INTEGER NOT NULL,
        total INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    echo "✅ Таблица order_items создана\n";
    
    // carts
    $pdo->exec('CREATE TABLE IF NOT EXISTS carts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        quantity INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    echo "✅ Таблица carts создана\n";
    
    // sessions
    $pdo->exec('CREATE TABLE IF NOT EXISTS sessions (
        id VARCHAR(255) PRIMARY KEY NOT NULL,
        user_id INTEGER NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        payload TEXT NOT NULL,
        last_activity INTEGER NOT NULL
    )');
    echo "✅ Таблица sessions создана\n";
    
    // migrations
    $pdo->exec('CREATE TABLE IF NOT EXISTS migrations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        migration VARCHAR(255) NOT NULL,
        batch INTEGER NOT NULL
    )');
    echo "✅ Таблица migrations создана\n";
    
    // personal_access_tokens
    $pdo->exec('CREATE TABLE IF NOT EXISTS personal_access_tokens (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tokenable_type VARCHAR(255) NOT NULL,
        tokenable_id INTEGER NOT NULL,
        name VARCHAR(255) NOT NULL,
        token VARCHAR(64) UNIQUE NOT NULL,
        abilities TEXT NULL,
        last_used_at DATETIME NULL,
        expires_at DATETIME NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    echo "✅ Таблица personal_access_tokens создана\n";
    
    // Проверяем созданные таблицы
    echo "\n=== СПИСОК ТАБЛИЦ ===\n";
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        $countStmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $countStmt->fetchColumn();
        echo "- $table: $count записей\n";
    }
    
    // Создаем тестовые данные
    echo "\n=== СОЗДАНИЕ ТЕСТОВЫХ ДАННЫХ ===\n";
    
    // Админ
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO users (id, name, email, password, is_admin, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([1, 'Администратор', 'admin@sheykhlavka.com', password_hash('admin123', PASSWORD_DEFAULT), 1, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]);
    echo "✅ Админ создан (admin@sheykhlavka.com / admin123)\n";
    
    // Категории
    $categories = [
        ['name' => 'Fruits', 'name_ru' => 'Фрукты', 'slug' => 'fruits', 'icon' => 'fruits.png'],
        ['name' => 'Vegetables', 'name_ru' => 'Овощи', 'slug' => 'vegetables', 'icon' => 'vegetables.png'],
        ['name' => 'Dairy', 'name_ru' => 'Молочные продукты', 'slug' => 'dairy', 'icon' => 'dairy.png'],
        ['name' => 'Meat', 'name_ru' => 'Мясо', 'slug' => 'meat', 'icon' => 'meat_poultry.png'],
        ['name' => 'Bakery', 'name_ru' => 'Хлебобулочные', 'slug' => 'bakery', 'icon' => 'bakery.png'],
    ];
    
    foreach ($categories as $index => $category) {
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO categories (id, name, name_ru, slug, icon, sort_order, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$index + 1, $category['name'], $category['name_ru'], $category['slug'], $category['icon'], $index, 1, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]);
    }
    echo "✅ Категории созданы: " . count($categories) . "\n";
    
    // Продукты
    $products = [
        ['category_id' => 1, 'name' => 'Apple', 'name_ru' => 'Яблоко', 'price' => 5000, 'unit' => 'кг'],
        ['category_id' => 1, 'name' => 'Banana', 'name_ru' => 'Банан', 'price' => 8000, 'unit' => 'кг'],
        ['category_id' => 2, 'name' => 'Carrot', 'name_ru' => 'Морковь', 'price' => 3000, 'unit' => 'кг'],
        ['category_id' => 3, 'name' => 'Milk', 'name_ru' => 'Молоко', 'price' => 7000, 'unit' => 'л'],
        ['category_id' => 4, 'name' => 'Beef', 'name_ru' => 'Говядина', 'price' => 45000, 'unit' => 'кг'],
    ];
    
    foreach ($products as $index => $product) {
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO products (id, category_id, name, name_ru, description, price, unit, stock_quantity, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$index + 1, $product['category_id'], $product['name'], $product['name_ru'], 'Тестовое описание для ' . $product['name_ru'], $product['price'], $product['unit'], 100, 1, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]);
    }
    echo "✅ Продукты созданы: " . count($products) . "\n";
    
    echo "\n🎉 БАЗА ДАННЫХ ГОТОВА К ИСПОЛЬЗОВАНИЮ!\n";
    echo "Размер файла БД: " . filesize($dbPath) . " байт\n";
    
} catch (PDOException $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
}
