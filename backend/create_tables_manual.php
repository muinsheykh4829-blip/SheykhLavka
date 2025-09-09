<?php
// Прямое создание таблиц в SQLite

require_once __DIR__ . '/vendor/autoload.php';

// Загружаем Laravel приложение
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== СОЗДАНИЕ ТАБЛИЦ ВРУЧНУЮ ===\n\n";
    
    // Получаем подключение к БД
    $db = DB::connection();
    
    // Создаем таблицу categories
    $db->statement("
        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            description TEXT,
            icon VARCHAR(255),
            sort_order INTEGER DEFAULT 0,
            is_active BOOLEAN DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Таблица categories создана\n";
    
    // Создаем таблицу products
    $db->statement("
        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INTEGER,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            description TEXT,
            price INTEGER NOT NULL,
            image VARCHAR(255),
            weight VARCHAR(50),
            unit VARCHAR(20) DEFAULT 'шт',
            is_active BOOLEAN DEFAULT 1,
            sort_order INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id)
        )
    ");
    echo "✅ Таблица products создана\n";
    
    // Создаем таблицу orders
    $db->statement("
        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_number VARCHAR(255) NOT NULL UNIQUE,
            user_id INTEGER,
            status VARCHAR(50) DEFAULT 'pending',
            subtotal INTEGER NOT NULL DEFAULT 0,
            delivery_fee INTEGER NOT NULL DEFAULT 0,
            discount INTEGER NOT NULL DEFAULT 0,
            total INTEGER NOT NULL DEFAULT 0,
            payment_method VARCHAR(50) DEFAULT 'cash',
            payment_status VARCHAR(50) DEFAULT 'pending',
            delivery_address TEXT NOT NULL,
            delivery_phone VARCHAR(20) NOT NULL,
            delivery_name VARCHAR(255),
            delivery_time TIMESTAMP,
            comment TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");
    echo "✅ Таблица orders создана\n";
    
    // Создаем таблицу order_items
    $db->statement("
        CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL DEFAULT 1,
            price INTEGER NOT NULL,
            total INTEGER NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id)
        )
    ");
    echo "✅ Таблица order_items создана\n";
    
    // Создаем таблицу carts
    $db->statement("
        CREATE TABLE IF NOT EXISTS carts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )
    ");
    echo "✅ Таблица carts создана\n";
    
    // Создаем индексы
    $db->statement("CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id)");
    $db->statement("CREATE INDEX IF NOT EXISTS idx_orders_user ON orders(user_id)");
    $db->statement("CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status)");
    $db->statement("CREATE INDEX IF NOT EXISTS idx_order_items_order ON order_items(order_id)");
    $db->statement("CREATE INDEX IF NOT EXISTS idx_carts_user ON carts(user_id)");
    
    echo "✅ Индексы созданы\n\n";
    
    // Добавляем тестовые данные
    echo "=== ДОБАВЛЕНИЕ ТЕСТОВЫХ ДАННЫХ ===\n\n";
    
    // Тестовые категории
    $db->table('categories')->insertOrIgnore([
        ['name' => 'Основные блюда', 'slug' => 'main-dishes', 'icon' => 'main-dish.png'],
        ['name' => 'Напитки', 'slug' => 'drinks', 'icon' => 'drinks.png'],
        ['name' => 'Десерты', 'slug' => 'desserts', 'icon' => 'desserts.png'],
    ]);
    echo "✅ Тестовые категории добавлены\n";
    
    // Тестовые продукты
    $db->table('products')->insertOrIgnore([
        ['category_id' => 1, 'name' => 'Плов классический', 'slug' => 'plov-classic', 'price' => 25000, 'description' => 'Традиционный узбекский плов'],
        ['category_id' => 1, 'name' => 'Лагман', 'slug' => 'lagman', 'price' => 20000, 'description' => 'Домашняя лапша с мясом'],
        ['category_id' => 2, 'name' => 'Чай зеленый', 'slug' => 'green-tea', 'price' => 5000, 'description' => 'Ароматный зеленый чай'],
        ['category_id' => 3, 'name' => 'Самса', 'slug' => 'samsa', 'price' => 8000, 'description' => 'Слоеная выпечка с мясом'],
    ]);
    echo "✅ Тестовые продукты добавлены\n";
    
    echo "\n🎉 ВСЕ ГОТОВО!\n";
    echo "Статистика:\n";
    echo "- Категории: " . $db->table('categories')->count() . "\n";
    echo "- Продукты: " . $db->table('products')->count() . "\n";
    echo "- Заказы: " . $db->table('orders')->count() . "\n";
    
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    echo "Детали: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
