---<?php

use Illuminate\Support\Facades\Route;
use App\Models\Address;
use App\Models\User;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

// Подключаем отладочные маршруты
require __DIR__.'/debug.php';

// Простой тест API
Route::get('/test-json', function () {
    return response()->json(['message' => 'JSON работает', 'time' => now()]);
});

// Тест создания сборщика
Route::get('/test-picker-create', function () {
    try {
        $picker = \App\Models\Picker::create([
            'login' => 'debug_test_' . time(),
            'password' => '123456',
            'name' => 'Debug Test Picker',
            'phone' => '+992123456789',
            'is_active' => true
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Сборщик создан успешно',
            'picker' => $picker
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Прямой тест POST создания сборщика
Route::post('/test-picker-store', function (\Illuminate\Http\Request $request) {
    try {
        $controller = new \App\Http\Controllers\Admin\PickerController();
        return $controller->store($request);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'request_data' => $request->all()
        ]);
    }
});

// Тест категорий
Route::get('/test-categories', function () {
    try {
        $categories = \App\Models\Category::all();
        return response()->json([
            'success' => true,
            'count' => $categories->count(),
            'categories' => $categories->toArray()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

// Добавить поле product_name в order_items
Route::get('/add-product-name-field', function () {
    try {
        // Проверим, есть ли уже поле
        $columns = DB::select("PRAGMA table_info(order_items)");
        $hasProductName = collect($columns)->pluck('name')->contains('product_name');
        
        if (!$hasProductName) {
            DB::statement("ALTER TABLE order_items ADD COLUMN product_name VARCHAR(255)");
            return response()->json([
                'success' => true,
                'message' => 'Поле product_name добавлено в таблицу order_items'
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Поле product_name уже существует'
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

// Тест API баннеров
Route::get('/test-banners-api', function () {
    try {
        $controller = new App\Http\Controllers\Api\BannerController();
        $request = new Illuminate\Http\Request();
        $response = $controller->index($request);
        return $response;
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Создать тестовый заказ
Route::get('/create-test-order', function () {
    try {
        // Найдем первого пользователя или создадим
        $user = \App\Models\User::first();
        if (!$user) {
            $user = \App\Models\User::create([
                'name' => 'Тестовый пользователь',
                'email' => 'test@example.com',
                'phone' => '+992000000000',
                'password' => bcrypt('password'),
                'is_admin' => false,
            ]);
        }

        // Создать тестовый заказ
        $order = \App\Models\Order::create([
            'order_number' => 'ORD-' . time(),
            'user_id' => $user->id,
            'status' => 'pending',
            'subtotal' => 1500, // 15.00 сом
            'delivery_fee' => 500, // 5.00 сом
            'discount' => 0,
            'total' => 2000, // 20.00 сом
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'delivery_address' => 'Тестовый адрес, дом 123',
            'delivery_phone' => '+992000000000',
            'delivery_name' => 'Тестовый пользователь',
            'comment' => 'Тестовый заказ из системы',
        ]);

        // Добавить товары в заказ
        $product = \App\Models\Product::first();
        if ($product) {
            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 2,
                'price' => $product->price,
                'total' => $product->price * 2,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Тестовый заказ создан',
            'order' => $order->toArray(),
            'admin_link' => url('/admin/orders')
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

// Тест соединения с базой данных
Route::get('/test-db', function () {
    try {
        // Тестируем соединение с базой данных
        $connection = DB::connection();
        $pdo = $connection->getPdo();
        
        // Проверяем количество таблиц
        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        
        $stats = [];
        foreach ($tables as $table) {
            try {
                $count = DB::table($table->name)->count();
                $stats[$table->name] = $count;
            } catch (\Exception $e) {
                $stats[$table->name] = "Ошибка: " . $e->getMessage();
            }
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Соединение с базой данных установлено',
            'database_path' => database_path('database.sqlite'),
            'database_exists' => file_exists(database_path('database.sqlite')),
            'database_size' => file_exists(database_path('database.sqlite')) ? filesize(database_path('database.sqlite')) : 0,
            'tables_count' => count($tables),
            'tables' => $stats,
            'time' => now()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Ошибка соединения с базой данных',
            'error' => $e->getMessage(),
            'database_path' => database_path('database.sqlite'),
            'database_exists' => file_exists(database_path('database.sqlite'))
        ], 500);
    }
});

// Пересоздание только таблицы users
Route::get('/fix-users-table', function () {
    try {
        $db = DB::connection();
        
        // Удаляем старую таблицу users
        $db->statement('DROP TABLE IF EXISTS users');
        
        // Создаем новую таблицу users с правильной структурой
        $db->statement('CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE,
            phone VARCHAR(255) UNIQUE,
            email_verified_at DATETIME NULL,
            phone_verified_at DATETIME NULL,
            password VARCHAR(255) NOT NULL,
            avatar VARCHAR(255) NULL,
            is_admin BOOLEAN DEFAULT 0,
            verification_code VARCHAR(10) NULL,
            verification_code_expires_at DATETIME NULL,
            remember_token VARCHAR(100) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');
        
        return response()->json([
            'status' => 'success',
            'message' => 'Таблица users пересоздана с правильной структурой'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

// Создание таблиц (GET для простоты)
Route::get('/create-db-tables', function () {
    try {
        $output = ['status' => 'success', 'tables' => []];
        
        $db = DB::connection();
        
        // Создаем таблицу users с правильной структурой
        $db->statement('DROP TABLE IF EXISTS users');
        $db->statement('CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE,
            phone VARCHAR(255) UNIQUE,
            email_verified_at DATETIME NULL,
            phone_verified_at DATETIME NULL,
            password VARCHAR(255) NOT NULL,
            avatar VARCHAR(255) NULL,
            is_admin BOOLEAN DEFAULT 0,
            verification_code VARCHAR(10) NULL,
            verification_code_expires_at DATETIME NULL,
            remember_token VARCHAR(100) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');
        $output['tables'][] = 'users (обновлена)';
        
        // Пересоздаем таблицу categories
        $db->statement('DROP TABLE IF EXISTS categories');
        $db->statement('CREATE TABLE categories (
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
        $output['tables'][] = 'categories (обновлена)';
        
        // Пересоздаем таблицу products  
        $db->statement('DROP TABLE IF EXISTS products');
        $db->statement('CREATE TABLE products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INTEGER NOT NULL,
            name VARCHAR(255) NOT NULL,
            name_ru VARCHAR(255) NULL,
            slug VARCHAR(255) UNIQUE,
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
        $output['tables'][] = 'products (обновлена)';
        
        // Создаем таблицу orders
        $db->statement('CREATE TABLE IF NOT EXISTS orders (
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
        $output['tables'][] = 'orders';
        
        // Создаем таблицу order_items
        $db->statement('CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL,
            price INTEGER NOT NULL,
            total INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');
        $output['tables'][] = 'order_items';
        
        // Создаем таблицу carts
        $db->statement('CREATE TABLE IF NOT EXISTS carts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');
        $output['tables'][] = 'carts';
        
        // Создаем таблицу sessions
        $db->statement('CREATE TABLE IF NOT EXISTS sessions (
            id VARCHAR(255) PRIMARY KEY NOT NULL,
            user_id INTEGER NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            payload TEXT NOT NULL,
            last_activity INTEGER NOT NULL
        )');
        $output['tables'][] = 'sessions';
        
        // Создаем таблицу personal_access_tokens
        $db->statement('CREATE TABLE IF NOT EXISTS personal_access_tokens (
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
        $output['tables'][] = 'personal_access_tokens';
        
        $output['message'] = 'Таблицы созданы успешно';
        $output['count'] = count($output['tables']);
        
        return response()->json($output);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

// Создание тестовых данных (GET для простоты)
Route::get('/create-test-data', function () {
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
        
        // Создаем категории
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
        
        // Создаем тестовые продукты
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
                'slug' => strtolower(str_replace(' ', '-', $product['name'])),
                'description' => 'Тестовое описание для ' . $product['name_ru'],
                'price' => $product['price'],
                'unit' => $product['unit'],
                'stock_quantity' => 100,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Тестовые данные созданы',
            'users' => DB::table('users')->count(),
            'categories' => DB::table('categories')->count(),
            'products' => DB::table('products')->count()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

Route::get('/', function () {
    return view('welcome');
});

// Быстрый доступ к админ панели (для разработки)
Route::get('/admin-access', function () {
    return view('admin-quick-access');
});

// Быстрый вход в админку (для разработки)
Route::get('/admin-quick-login', function () {
    session(['admin_logged_in' => true]);
    return redirect('/admin')->with('success', 'Быстрый вход выполнен успешно!');
});

// Тестовый маршрут для проверки адресов
Route::get('/test-addresses', function () {
    try {
        $users = User::count();
        $addresses = Address::count();
        
        return response()->json([
            'message' => 'API работает',
            'users_count' => $users,
            'addresses_count' => $addresses,
            'database_connected' => true
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Ошибка подключения к базе данных',
            'error' => $e->getMessage()
        ], 500);
    }
});

// Создание тестовых данных
Route::get('/create-test-data', function () {
    try {
        // Создадим категории
        $categories = [
            [
                'name' => 'Тестовая категория',
                'slug' => 'test-category',
                'icon' => 'categories/test.png',
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Фрукты',
                'slug' => 'fruits',
                'icon' => 'categories/fruits.png',
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Овощи',
                'slug' => 'vegetables',
                'icon' => 'categories/vegetables.png',
                'is_active' => true,
                'sort_order' => 3
            ]
        ];
        
        $createdCategories = [];
        foreach ($categories as $categoryData) {
            $category = \App\Models\Category::firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
            $createdCategories[] = $category;
        }
        
        // Создадим продукты для каждой категории
        $products = [
            [
                'name' => 'Тестовый продукт',
                'slug' => 'test-product',
                'description' => 'Описание тестового продукта',
                'price' => 1000,
                'unit' => 'г',
                'weight' => 500,
                'category_id' => $createdCategories[0]->id,
                'is_active' => true,
                'stock_quantity' => 100
            ],
            [
                'name' => 'Яблоки',
                'slug' => 'apples',
                'description' => 'Свежие красные яблоки',
                'price' => 800,
                'unit' => 'кг',
                'weight' => 1,
                'category_id' => $createdCategories[1]->id,
                'is_active' => true,
                'stock_quantity' => 50
            ],
            [
                'name' => 'Морковь',
                'slug' => 'carrot',
                'description' => 'Свежая морковь',
                'price' => 600,
                'unit' => 'кг',
                'weight' => 1,
                'category_id' => $createdCategories[2]->id,
                'is_active' => true,
                'stock_quantity' => 75
            ]
        ];
        
        $createdProducts = [];
        foreach ($products as $productData) {
            $product = \App\Models\Product::firstOrCreate(
                ['slug' => $productData['slug']],
                $productData
            );
            $createdProducts[] = $product;
        }
        
        // Создадим пользователя
        $user = \App\Models\User::firstOrCreate([
            'phone' => '+992900123456'
        ], [
            'first_name' => 'Тест',
            'last_name' => 'Пользователь',
            'phone_verified_at' => now()
        ]);
        
        // Создадим тестовые заказы
        $ordersCreated = 0;
        for ($i = 1; $i <= 3; $i++) {
            $orderNumber = 'TEST-' . str_pad($i, 4, '0', STR_PAD_LEFT);
            
            $order = \App\Models\Order::firstOrCreate([
                'order_number' => $orderNumber
            ], [
                'user_id' => $user->id,
                'status' => ['pending', 'confirmed', 'delivered'][array_rand(['pending', 'confirmed', 'delivered'])],
                'subtotal' => 1000,
                'delivery_fee' => 5000,
                'discount' => 0,
                'total' => 6000,
                'payment_method' => 'cash',
                'payment_status' => 'pending',
                'delivery_address' => 'г. Душанбе, ул. Тестовая, д. ' . $i,
                'delivery_phone' => $user->phone,
                'delivery_name' => $user->first_name . ' ' . $user->last_name,
                'comment' => 'Тестовый заказ №' . $i
            ]);
            
            if ($order->wasRecentlyCreated) {
                // Добавляем товар в заказ
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $createdProducts[0]->id,
                    'quantity' => 1,
                    'price' => 1000,
                    'total' => 1000
                ]);
                $ordersCreated++;
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Тестовые данные созданы!',
            'categories_created' => count($createdCategories),
            'products_created' => count($createdProducts),
            'orders_created' => $ordersCreated,
            'total_categories' => \App\Models\Category::count(),
            'total_products' => \App\Models\Product::count(),
            'total_orders' => \App\Models\Order::count(),
            'total_users' => \App\Models\User::count()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Ошибка создания тестовых данных',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ], 500);
    }
});

// Отладка заказов
Route::get('/debug-orders', function () {
    try {
        $orders = \App\Models\Order::with(['user', 'items.product'])->get();
        
        $html = '<h1>Отладка заказов</h1>';
        $html .= '<p>Всего заказов: ' . $orders->count() . '</p>';
        
        if ($orders->count() > 0) {
            $html .= '<table border="1" cellpadding="10" style="border-collapse: collapse;">';
            $html .= '<tr><th>ID</th><th>Номер</th><th>Статус</th><th>Пользователь</th><th>Сумма</th><th>Дата</th></tr>';
            
            foreach ($orders as $order) {
                $userName = $order->user ? $order->user->first_name . ' ' . $order->user->last_name : 'Без пользователя';
                $html .= '<tr>';
                $html .= '<td>' . $order->id . '</td>';
                $html .= '<td>' . $order->order_number . '</td>';
                $html .= '<td>' . $order->status . '</td>';
                $html .= '<td>' . $userName . '</td>';
                $html .= '<td>' . $order->total . ' c.</td>';
                $html .= '<td>' . $order->created_at->format('d.m.Y H:i') . '</td>';
                $html .= '</tr>';
            }
            
            $html .= '</table>';
        }
        
        return $html;
        
    } catch (\Exception $e) {
        return '<h1>Ошибка</h1><p>' . $e->getMessage() . '</p>';
    }
});

// Обновление продуктов - активация всех
Route::get('/fix-products', function () {
    try {
        $updated = \App\Models\Product::whereNull('is_active')->orWhere('is_active', false)->update(['is_active' => true]);
        
        return response()->json([
            'success' => true,
            'message' => 'Продукты обновлены',
            'updated_count' => $updated,
            'total_products' => \App\Models\Product::count(),
            'active_products' => \App\Models\Product::where('is_active', true)->count()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

// Админ маршруты
Route::prefix('admin')->group(function () {
    // Быстрый вход для тестирования
    Route::get('/quick-login', function() {
        session(['admin_logged_in' => true]);
        return redirect('/admin/couriers');
    })->name('admin.quick-login');
    
    // Страница входа
    Route::get('/login', function() {
        return view('admin.login');
    })->name('admin.login');
    
    // Обработка входа
    Route::post('/login', function(\Illuminate\Http\Request $request) {
        $username = $request->input('username');
        $password = $request->input('password');
        
        // Простая проверка (в продакшене используйте более безопасный способ)
        if ($username === 'admin' && $password === 'admin123') {
            session(['admin_logged_in' => true]);
            return redirect()->route('admin.dashboard');
        }
        
        return back()->withErrors(['message' => 'Неверный логин или пароль']);
    })->name('admin.login.post');
    
    // Выход
    Route::post('/logout', function() {
        session()->forget('admin_logged_in');
        return redirect()->route('admin.login');
    })->name('admin.logout');
    
    // Защищенные админ маршруты
    Route::middleware('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::post('/upload', [AdminController::class, 'uploadFile'])->name('admin.upload');
        
        // Тестовый маршрут для создания сборщика
        Route::get('/pickers/test', function() {
            return view('admin.pickers.test');
        })->name('admin.pickers.test');
        
        // Прямое создание сборщика для теста
        Route::get('/pickers/direct-create', function() {
            try {
                $picker = \App\Models\Picker::create([
                    'login' => 'web_test_' . time(),
                    'password' => '123456',
                    'name' => 'Веб тест ' . date('H:i:s'),
                    'phone' => '+992' . rand(100000000, 999999999),
                    'is_active' => true
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Сборщик успешно создан',
                    'picker' => $picker->toArray()
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка: ' . $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });
        
        // Продукты
        Route::resource('products', ProductController::class, ['as' => 'admin']);
        
        // Массовое добавление продуктов
        Route::get('/products-bulk-import', [\App\Http\Controllers\Admin\BulkProductController::class, 'showBulkImport'])
            ->name('admin.products.bulk-import');
        Route::get('/products-template', [\App\Http\Controllers\Admin\BulkProductController::class, 'downloadTemplate'])
            ->name('admin.products.download-template');
        Route::post('/products-add-popular', [\App\Http\Controllers\Admin\BulkProductController::class, 'addPopularFruitsVegetables'])
            ->name('admin.products.add-popular');
        Route::post('/products-add-dairy', [\App\Http\Controllers\Admin\BulkProductController::class, 'addDairyProducts'])
            ->name('admin.products.add-dairy');
        Route::post('/products-add-meat', [\App\Http\Controllers\Admin\BulkProductController::class, 'addMeatProducts'])
            ->name('admin.products.add-meat');
        
        // Категории
        Route::resource('categories', CategoryController::class, ['as' => 'admin']);
        Route::patch('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])
            ->name('admin.categories.toggle-status');
        
        // Заказы
        Route::resource('orders', AdminOrderController::class, ['as' => 'admin'])
            ->only(['index', 'show']);
        Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])
            ->name('admin.orders.update-status');
        
        // Баннеры
        Route::get('/banners-statistics', [AdminBannerController::class, 'statistics'])
            ->name('admin.banners.statistics');
        Route::resource('banners', AdminBannerController::class, ['as' => 'admin']);
        Route::post('/banners/{banner}/toggle-active', [AdminBannerController::class, 'toggleActive'])
            ->name('admin.banners.toggle-active');
        
        // Пользователи
        Route::resource('users', AdminUserController::class, ['as' => 'admin'])
            ->except(['create', 'store']);
        Route::post('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])
            ->name('admin.users.toggle-status');
        
        // Сборщики
        Route::resource('pickers', \App\Http\Controllers\Admin\PickerController::class, ['as' => 'admin']);
        Route::post('/pickers/{picker}/toggle-status', [\App\Http\Controllers\Admin\PickerController::class, 'toggleStatus'])
            ->name('admin.pickers.toggle-status');
        
        // Курьеры
        Route::resource('couriers', \App\Http\Controllers\Admin\AdminCourierController::class, ['as' => 'admin']);
        Route::patch('/couriers/{courier}/toggle-status', [\App\Http\Controllers\Admin\AdminCourierController::class, 'toggleStatus'])
            ->name('admin.couriers.toggle-status');
        
    // Управление складом
    // Имена маршрутов должны начинаться с admin.inventory.* для совместимости с вьюхами/контроллером
    Route::prefix('inventory')->name('admin.inventory.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('index');
            Route::get('/report', [\App\Http\Controllers\Admin\InventoryController::class, 'report'])->name('report');
            Route::get('/{product}/edit', [\App\Http\Controllers\Admin\InventoryController::class, 'edit'])->name('edit');
            Route::put('/{product}', [\App\Http\Controllers\Admin\InventoryController::class, 'update'])->name('update');
            Route::post('/{product}/restock', [\App\Http\Controllers\Admin\InventoryController::class, 'restock'])->name('restock');
            Route::get('/{product}/movements', [\App\Http\Controllers\Admin\InventoryController::class, 'movements'])->name('movements');
            Route::post('/update-minimum-levels', [\App\Http\Controllers\Admin\InventoryController::class, 'updateMinimumLevels'])->name('update-minimum-levels');
            Route::post('/auto-deactivate', [\App\Http\Controllers\Admin\InventoryController::class, 'autoDeactivate'])->name('auto-deactivate');
            Route::post('/auto-activate', [\App\Http\Controllers\Admin\InventoryController::class, 'autoActivate'])->name('auto-activate');
        });
        
        // API маршруты для админки
        Route::prefix('api')->group(function () {
            Route::post('/products', [ProductController::class, 'storeApi']);
            Route::put('/products/{id}', [ProductController::class, 'updateApi']);
        });
    });
});

// Тестовый маршрут для создания заказов (только для разработки)
Route::get('/create-test-orders', function () {
    if (!\App\Models\Order::count()) {
        // Получаем пользователя
        $user = \App\Models\User::first();
        if (!$user) {
            $user = \App\Models\User::create([
                'first_name' => 'Тестовый',
                'last_name' => 'Пользователь',
                'phone' => '+998901234567',
                'email' => 'test@test.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Получаем продукты
        $products = \App\Models\Product::take(5)->get();
        
        if ($products->count() == 0) {
            return 'Нет продуктов для создания заказов';
        }

        $statuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivering', 'delivered', 'cancelled'];
        
        for ($i = 1; $i <= 10; $i++) {
            $status = $statuses[array_rand($statuses)];
            $subtotal = rand(1000, 5000);
            $delivery_fee = 200;
            $discount = rand(0, 500);
            $total = $subtotal + $delivery_fee - $discount;
            
            $order = \App\Models\Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'status' => $status,
                'subtotal' => $subtotal,
                'delivery_fee' => $delivery_fee,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => ['cash', 'card', 'online'][array_rand(['cash', 'card', 'online'])],
                'payment_status' => ['pending', 'paid'][array_rand(['pending', 'paid'])],
                'delivery_address' => 'Тестовый адрес доставки ' . $i . ', г. Ташкент',
                'delivery_name' => 'Тестовый Клиент ' . $i,
                'delivery_phone' => '+998901234' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'comment' => 'Тестовый комментарий для заказа ' . $i,
                'created_at' => now()->subDays(rand(0, 30)),
            ]);

            // Добавляем товары в заказ
            $selectedProducts = $products->random(rand(1, 3));
            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 3);
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'total' => $product->price * $quantity,
                ]);
            }
        }
        
        return '✅ Создано 10 тестовых заказов! <a href="/admin/orders">Перейти к заказам</a>';
    }
    
    return 'Заказы уже существуют (' . \App\Models\Order::count() . ' шт.) <a href="/admin/orders">Посмотреть заказы</a>';
});

// Тестовая страница для проверки API заказов
Route::get('/test-order-api', function () {
    return view('test-order-api');
});

// Диагностическая страница API
Route::get('/api-debug', function () {
    return view('api-debug');
});

// Форма для тестирования создания заказов
Route::get('/test-order-form', function () {
    return view('test-order-form');
});

// Страница настройки базы данных
Route::get('/database-setup', function () {
    return view('database-setup');
});

// API для настройки базы данных
Route::prefix('setup')->group(function () {
    Route::get('/check-database', function () {
        try {
            $output = "=== ПРОВЕРКА БАЗЫ ДАННЫХ ===\n\n";
            
            // Проверяем подключение к БД
            try {
                DB::connection()->getPdo();
                $output .= "✅ Подключение к БД: OK\n";
            } catch (\Exception $e) {
                $output .= "❌ Подключение к БД: " . $e->getMessage() . "\n";
                return response($output)->header('Content-Type', 'text/plain');
            }
            
            // Проверяем существование таблиц
            $tables = ['users', 'products', 'categories', 'orders', 'order_items', 'carts'];
            $output .= "\n📋 Проверка таблиц:\n";
            
            foreach ($tables as $table) {
                try {
                    DB::table($table)->count();
                    $output .= "✅ {$table}: существует\n";
                } catch (\Exception $e) {
                    $output .= "❌ {$table}: не найдена\n";
                }
            }
            
            // Проверяем количество записей
            $output .= "\n📊 Статистика данных:\n";
            try {
                $output .= "- Пользователи: " . \App\Models\User::count() . "\n";
                $output .= "- Категории: " . \App\Models\Category::count() . "\n";  
                $output .= "- Товары: " . \App\Models\Product::count() . "\n";
                $output .= "- Заказы: " . \App\Models\Order::count() . "\n";
            } catch (\Exception $e) {
                $output .= "❌ Ошибка получения статистики: " . $e->getMessage() . "\n";
            }
            
            return response($output)->header('Content-Type', 'text/plain');
        } catch (\Exception $e) {
            return response("❌ Общая ошибка: " . $e->getMessage())->header('Content-Type', 'text/plain');
        }
    });

    Route::post('/migrate', function () {
        try {
            $output = "=== ВЫПОЛНЕНИЕ МИГРАЦИЙ ===\n\n";
            
            // Выполняем миграции программно
            Artisan::call('migrate', ['--force' => true]);
            $output .= "✅ Миграции выполнены успешно\n\n";
            $output .= Artisan::output();
            
            return response($output)->header('Content-Type', 'text/plain');
        } catch (\Exception $e) {
            return response("❌ Ошибка выполнения миграций: " . $e->getMessage())->header('Content-Type', 'text/plain');
        }
    });

    Route::post('/reset', function () {
        try {
            $output = "=== ПЕРЕСОЗДАНИЕ БАЗЫ ДАННЫХ ===\n\n";
            
            // Пересоздаем все таблицы
            Artisan::call('migrate:fresh', ['--force' => true]);
            $output .= "✅ База данных пересоздана\n\n";
            $output .= Artisan::output();
            
            return response($output)->header('Content-Type', 'text/plain');
        } catch (\Exception $e) {
            return response("❌ Ошибка пересоздания БД: " . $e->getMessage())->header('Content-Type', 'text/plain');
        }
    });

    Route::post('/seed', function () {
        try {
            $output = "=== СОЗДАНИЕ ТЕСТОВЫХ ДАННЫХ ===\n\n";
            
            // Создаем тестового пользователя
            $user = \App\Models\User::firstOrCreate(
                ['phone' => '+998901234567'],
                [
                    'first_name' => 'Тестовый',
                    'last_name' => 'Пользователь',
                    'email' => 'test@test.com',
                    'password' => bcrypt('password'),
                ]
            );
            $output .= "✅ Тестовый пользователь создан: {$user->first_name} {$user->last_name}\n";

            // Создаем категории
            $categories = [
                ['name' => 'Основные блюда', 'slug' => 'main-dishes', 'icon' => 'main-dish.png'],
                ['name' => 'Напитки', 'slug' => 'drinks', 'icon' => 'drinks.png'],
                ['name' => 'Десерты', 'slug' => 'desserts', 'icon' => 'desserts.png'],
            ];
            
            foreach ($categories as $categoryData) {
                $category = \App\Models\Category::firstOrCreate(
                    ['slug' => $categoryData['slug']],
                    $categoryData
                );
                $output .= "✅ Категория: {$category->name}\n";
            }

            // Создаем продукты
            $products = [
                ['name' => 'Плов классический', 'price' => 25000, 'category_id' => 1],
                ['name' => 'Лагман', 'price' => 20000, 'category_id' => 1],
                ['name' => 'Чай зеленый', 'price' => 5000, 'category_id' => 2],
                ['name' => 'Самса', 'price' => 8000, 'category_id' => 3],
            ];
            
            foreach ($products as $productData) {
                $product = \App\Models\Product::firstOrCreate(
                    ['name' => $productData['name']],
                    array_merge($productData, [
                        'slug' => \Illuminate\Support\Str::slug($productData['name']),
                        'description' => 'Описание для ' . $productData['name'],
                        'is_active' => true,
                    ])
                );
                $output .= "✅ Продукт: {$product->name} ({$product->price} сом)\n";
            }

            $output .= "\n📊 Итоговая статистика:\n";
            $output .= "- Пользователи: " . \App\Models\User::count() . "\n";
            $output .= "- Категории: " . \App\Models\Category::count() . "\n";
            $output .= "- Товары: " . \App\Models\Product::count() . "\n";
            
            return response($output)->header('Content-Type', 'text/plain');
        } catch (\Exception $e) {
            return response("❌ Ошибка создания тестовых данных: " . $e->getMessage())->header('Content-Type', 'text/plain');
        }
    });

    // Создание таблиц вручную (если миграции не работают)
    Route::post('/create-tables', function () {
        try {
            $output = "=== СОЗДАНИЕ ТАБЛИЦ ВРУЧНУЮ ===\n\n";
            
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
            $output .= "✅ Таблица categories создана\n";
            
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
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            $output .= "✅ Таблица products создана\n";
            
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
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            $output .= "✅ Таблица orders создана\n";
            
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
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            $output .= "✅ Таблица order_items создана\n";
            
            // Создаем таблицу carts
            $db->statement("
                CREATE TABLE IF NOT EXISTS carts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    product_id INTEGER NOT NULL,
                    quantity INTEGER NOT NULL DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            $output .= "✅ Таблица carts создана\n";
            
            $output .= "\n🎉 Все таблицы созданы успешно!\n";
            
            return response($output)->header('Content-Type', 'text/plain');
        } catch (\Exception $e) {
            return response("❌ Ошибка создания таблиц: " . $e->getMessage())->header('Content-Type', 'text/plain');
        }
    });
});

// API для диагностики базы данных
Route::get('/api/debug-db', function () {
    try {
        $output = "=== ДИАГНОСТИКА БАЗЫ ДАННЫХ ===\n\n";
        
        $output .= "📊 Статистика:\n";
        $output .= "- Заказы: " . \App\Models\Order::count() . "\n";
        $output .= "- Пользователи: " . \App\Models\User::count() . "\n";
        $output .= "- Товары: " . \App\Models\Product::count() . "\n";
        $output .= "- Категории: " . \App\Models\Category::count() . "\n\n";
        
        if (\App\Models\Order::count() > 0) {
            $output .= "📋 Последние заказы:\n";
            $orders = \App\Models\Order::orderBy('created_at', 'desc')->take(5)->get();
            foreach ($orders as $order) {
                $output .= "- #{$order->order_number} ({$order->status}) - {$order->total} сом\n";
            }
        } else {
            $output .= "❌ Заказов в базе данных НЕТ\n";
        }
        
        return response($output)->header('Content-Type', 'text/plain');
    } catch (\Exception $e) {
        return response("Ошибка БД: " . $e->getMessage())->header('Content-Type', 'text/plain');
    }
});

// API для детального создания тестового заказа
Route::get('/api/test-order-detailed', function () {
    try {
        // Шаг 1: Проверяем/создаем пользователя
        $user = \App\Models\User::where('phone', '+998901234567')->first();
        if (!$user) {
            $user = \App\Models\User::create([
                'first_name' => 'Тестовый',
                'last_name' => 'Клиент',
                'phone' => '+998901234567',
                'email' => 'test@test.com',
                'password' => bcrypt('password'),
            ]);
        }
        
        // Шаг 2: Проверяем товары
        $products = \App\Models\Product::take(2)->get();
        if ($products->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Нет товаров для создания заказа',
                'step' => 'products_check'
            ]);
        }
        
        // Шаг 3: Очищаем и заполняем корзину
        \App\Models\Cart::where('user_id', $user->id)->delete();
        foreach ($products as $product) {
            \App\Models\Cart::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => rand(1, 2),
            ]);
        }
        
        // Шаг 4: Создаем заказ через API контроллер
        $orderController = new \App\Http\Controllers\Api\OrderController();
        $request = new \Illuminate\Http\Request([
            'delivery_address' => 'г. Ташкент, ул. Тестовая 123, кв. 45',
            'delivery_phone' => '+998901234567',
            'delivery_name' => 'Тестовый Клиент API',
            'payment_method' => 'cash',
            'comment' => 'Тестовый заказ через детальную диагностику - ' . now(),
        ]);
        
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        $response = $orderController->store($request);
        $responseData = json_decode($response->getContent(), true);
        
        return response()->json([
            'success' => $responseData['success'],
            'message' => $responseData['success'] ? 'Заказ создан через API контроллер' : 'Ошибка создания заказа',
            'order' => $responseData['order'] ?? null,
            'api_response' => $responseData,
            'step' => 'order_creation',
            'user_id' => $user->id,
            'products_count' => $products->count()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Исключение: ' . $e->getMessage(),
            'details' => [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]
        ]);
    }
});
