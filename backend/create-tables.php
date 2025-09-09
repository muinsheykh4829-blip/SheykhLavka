<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== СОЗДАНИЕ ТАБЛИЦ ===\n\n";
    
    // Создаем таблицу categories
    DB::statement('CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(255) NOT NULL,
        name_ru VARCHAR(255),
        slug VARCHAR(255) NOT NULL UNIQUE,
        icon VARCHAR(255),
        description TEXT,
        sort_order INTEGER DEFAULT 0,
        is_active BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    echo "✅ Таблица categories создана\n";
    
    // Создаем таблицу products
    DB::statement('CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id INTEGER,
        name VARCHAR(255) NOT NULL,
        name_ru VARCHAR(255),
        slug VARCHAR(255) NOT NULL UNIQUE,
        description TEXT,
        price INTEGER NOT NULL,
        old_price INTEGER,
        image VARCHAR(255),
        images TEXT,
        unit VARCHAR(50) DEFAULT "шт",
        weight VARCHAR(50),
        is_active BOOLEAN DEFAULT 1,
        is_featured BOOLEAN DEFAULT 0,
        stock_quantity INTEGER DEFAULT 0,
        sort_order INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )');
    echo "✅ Таблица products создана\n";
    
    // Создаем таблицу users
    DB::statement('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        first_name VARCHAR(255),
        last_name VARCHAR(255),
        name VARCHAR(255),
        email VARCHAR(255) UNIQUE,
        phone VARCHAR(255) UNIQUE,
        password VARCHAR(255),
        is_admin BOOLEAN DEFAULT 0,
        phone_verified_at DATETIME,
        email_verified_at DATETIME,
        remember_token VARCHAR(100),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    echo "✅ Таблица users создана\n";
    
    // Создаем таблицу orders
    DB::statement('CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_number VARCHAR(255) NOT NULL UNIQUE,
        user_id INTEGER,
        status VARCHAR(50) DEFAULT "pending",
        subtotal INTEGER NOT NULL DEFAULT 0,
        delivery_fee INTEGER NOT NULL DEFAULT 0,
        discount INTEGER NOT NULL DEFAULT 0,
        total INTEGER NOT NULL DEFAULT 0,
        payment_method VARCHAR(50) DEFAULT "cash",
        payment_status VARCHAR(50) DEFAULT "pending",
        delivery_address TEXT NOT NULL,
        delivery_phone VARCHAR(255) NOT NULL,
        delivery_name VARCHAR(255),
        delivery_time DATETIME,
        comment TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )');
    echo "✅ Таблица orders создана\n";
    
    // Создаем таблицу order_items
    DB::statement('CREATE TABLE IF NOT EXISTS order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        quantity INTEGER NOT NULL DEFAULT 1,
        price INTEGER NOT NULL,
        total INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )');
    echo "✅ Таблица order_items создана\n";
    
    // Создаем таблицу carts
    DB::statement('CREATE TABLE IF NOT EXISTS carts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        quantity INTEGER NOT NULL DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )');
    echo "✅ Таблица carts создана\n";
    
    // Создаем таблицу personal_access_tokens (для Sanctum)
    DB::statement('CREATE TABLE IF NOT EXISTS personal_access_tokens (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tokenable_type VARCHAR(255) NOT NULL,
        tokenable_id INTEGER NOT NULL,
        name VARCHAR(255) NOT NULL,
        token VARCHAR(64) UNIQUE NOT NULL,
        abilities TEXT,
        last_used_at DATETIME,
        expires_at DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    echo "✅ Таблица personal_access_tokens создана\n";
    
    // Создаем таблицу sessions
    DB::statement('CREATE TABLE IF NOT EXISTS sessions (
        id VARCHAR(255) PRIMARY KEY NOT NULL,
        user_id INTEGER,
        ip_address VARCHAR(45),
        user_agent TEXT,
        payload TEXT NOT NULL,
        last_activity INTEGER NOT NULL
    )');
    echo "✅ Таблица sessions создана\n";
    
    // Создаем таблицу migrations
    DB::statement('CREATE TABLE IF NOT EXISTS migrations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        migration VARCHAR(255) NOT NULL,
        batch INTEGER NOT NULL
    )');
    echo "✅ Таблица migrations создана\n";
    
    // Проверяем все таблицы
    $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
    
    echo "\n📊 СТАТИСТИКА ТАБЛИЦ:\n";
    foreach ($tables as $table) {
        $count = DB::table($table->name)->count();
        echo "- {$table->name}: {$count} записей\n";
    }
    
    echo "\n🎉 Все таблицы созданы успешно!\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    echo "Строка: " . $e->getLine() . "\n";
    echo "Файл: " . $e->getFile() . "\n";
}
