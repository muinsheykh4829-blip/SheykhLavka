<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateTablesCommand extends Command
{
    protected $signature = 'db:create-tables';
    protected $description = 'Создать все необходимые таблицы в SQLite базе данных';

    public function handle()
    {
        $this->info('=== СОЗДАНИЕ ТАБЛИЦ SQLITE ===');
        
        try {
            // Создаем таблицу users
            DB::statement('CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                email_verified_at DATETIME NULL,
                password VARCHAR(255) NOT NULL,
                phone VARCHAR(255) NULL,
                avatar VARCHAR(255) NULL,
                is_admin BOOLEAN DEFAULT 0,
                remember_token VARCHAR(100) NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )');
            $this->info('✅ Таблица users создана');
            
            // Создаем таблицу categories
            DB::statement('CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                name_ru VARCHAR(255) NULL,
                slug VARCHAR(255) NOT NULL,
                icon VARCHAR(255) NULL,
                description TEXT NULL,
                sort_order INTEGER DEFAULT 0,
                is_active BOOLEAN DEFAULT 1,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )');
            $this->info('✅ Таблица categories создана');
            
            // Создаем таблицу products
            DB::statement('CREATE TABLE IF NOT EXISTS products (
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
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )');
            $this->info('✅ Таблица products создана');
            
            // Создаем таблицу orders
            DB::statement('CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_number VARCHAR(255) UNIQUE NOT NULL,
                user_id INTEGER NOT NULL,
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
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )');
            $this->info('✅ Таблица orders создана');
            
            // Создаем таблицу order_items
            DB::statement('CREATE TABLE IF NOT EXISTS order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL,
                price INTEGER NOT NULL,
                total INTEGER NOT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )');
            $this->info('✅ Таблица order_items создана');
            
            // Создаем таблицу carts
            DB::statement('CREATE TABLE IF NOT EXISTS carts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )');
            $this->info('✅ Таблица carts создана');
            
            // Создаем таблицу sessions
            DB::statement('CREATE TABLE IF NOT EXISTS sessions (
                id VARCHAR(255) PRIMARY KEY NOT NULL,
                user_id INTEGER NULL,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                payload TEXT NOT NULL,
                last_activity INTEGER NOT NULL
            )');
            $this->info('✅ Таблица sessions создана');
            
            // Создаем таблицу migrations
            DB::statement('CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration VARCHAR(255) NOT NULL,
                batch INTEGER NOT NULL
            )');
            $this->info('✅ Таблица migrations создана');
            
            // Создаем таблицу personal_access_tokens (для Sanctum)
            DB::statement('CREATE TABLE IF NOT EXISTS personal_access_tokens (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tokenable_type VARCHAR(255) NOT NULL,
                tokenable_id INTEGER NOT NULL,
                name VARCHAR(255) NOT NULL,
                token VARCHAR(64) UNIQUE NOT NULL,
                abilities TEXT NULL,
                last_used_at DATETIME NULL,
                expires_at DATETIME NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )');
            $this->info('✅ Таблица personal_access_tokens создана');
            
            // Проверяем созданные таблицы
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            
            $this->info('');
            $this->info('📊 СПИСОК СОЗДАННЫХ ТАБЛИЦ:');
            foreach ($tables as $table) {
                $count = DB::table($table->name)->count();
                $this->info("- {$table->name}: {$count} записей");
            }
            
            $this->info('');
            $this->info('🎉 Все таблицы созданы успешно!');
            
            // Создаем тестовые данные
            $this->createTestData();
            
        } catch (\Exception $e) {
            $this->error('❌ Ошибка создания таблиц: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
    
    private function createTestData()
    {
        $this->info('');
        $this->info('=== СОЗДАНИЕ ТЕСТОВЫХ ДАННЫХ ===');
        
        try {
            // Создаем админа
            DB::table('users')->insertOrIgnore([
                'id' => 1,
                'name' => 'Администратор',
                'email' => 'admin@sheykhlavka.com',
                'password' => bcrypt('admin123'),
                'is_admin' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $this->info('✅ Админ пользователь создан (admin@sheykhlavka.com / admin123)');
            
            // Создаем тестового пользователя
            DB::table('users')->insertOrIgnore([
                'id' => 2,
                'name' => 'Тестовый пользователь',
                'email' => 'user@test.com',
                'password' => bcrypt('123456'),
                'phone' => '+998901234567',
                'is_admin' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $this->info('✅ Тестовый пользователь создан');
            
            // Создаем категории - ОТКЛЮЧЕНО, добавляем через админ панель
            /*
            $categories = [
                ['name' => 'Fruits', 'name_ru' => 'Фрукты', 'slug' => 'fruits', 'icon' => 'fruits.png'],
                ['name' => 'Vegetables', 'name_ru' => 'Овощи', 'slug' => 'vegetables', 'icon' => 'vegetables.png'],
                ['name' => 'Dairy', 'name_ru' => 'Молочные продукты', 'slug' => 'dairy', 'icon' => 'dairy.png'],
                ['name' => 'Meat', 'name_ru' => 'Мясо', 'slug' => 'meat', 'icon' => 'meat_poultry.png'],
                ['name' => 'Bakery', 'name_ru' => 'Хлебобулочные', 'slug' => 'bakery', 'icon' => 'bakery.png'],
            ];
            
            foreach ($categories as $index => $category) {
                DB::table('categories')->insertOrIgnore([
                    'id' => $index + 1,
                    'name' => $category['name'],
                    'name_ru' => $category['name_ru'],
                    'slug' => $category['slug'],
                    'icon' => $category['icon'],
                    'sort_order' => $index,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            $this->info('✅ Категории созданы: ' . count($categories));
            */
            $this->info('ℹ️  Категории не создаются - добавьте через админ панель');
            
            // Создаем тестовые продукты - ОТКЛЮЧЕНО, добавляем через админ панель
            /*
            $products = [
                ['category_id' => 1, 'name' => 'Apple', 'name_ru' => 'Яблоко', 'price' => 5000, 'unit' => 'кг'],
                ['category_id' => 1, 'name' => 'Banana', 'name_ru' => 'Банан', 'price' => 8000, 'unit' => 'кг'],
                ['category_id' => 2, 'name' => 'Carrot', 'name_ru' => 'Морковь', 'price' => 3000, 'unit' => 'кг'],
                ['category_id' => 3, 'name' => 'Milk', 'name_ru' => 'Молоко', 'price' => 7000, 'unit' => 'л'],
                ['category_id' => 4, 'name' => 'Beef', 'name_ru' => 'Говядина', 'price' => 45000, 'unit' => 'кг'],
            ];
            
            foreach ($products as $index => $product) {
                DB::table('products')->insertOrIgnore([
                    'id' => $index + 1,
                    'category_id' => $product['category_id'],
                    'name' => $product['name'],
                    'name_ru' => $product['name_ru'],
                    'description' => 'Тестовое описание для ' . $product['name_ru'],
                    'price' => $product['price'],
                    'unit' => $product['unit'],
                    'stock_quantity' => 100,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            $this->info('✅ Продукты созданы: ' . count($products));
            */
            $this->info('ℹ️  Продукты не создаются - добавьте через админ панель');
            
            $this->info('');
            $this->info('🎉 Тестовые данные созданы успешно!');
            
        } catch (\Exception $e) {
            $this->error('❌ Ошибка создания тестовых данных: ' . $e->getMessage());
        }
    }
}
