<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\PickerController;
use App\Http\Controllers\Admin\AdminController;

// Подключаем курьерские маршруты
require_once __DIR__ . '/courier_api.php';

// CORS preflight handler
Route::options('{any}', function () {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
})->where('any', '.*');

// Добавляем CORS заголовки ко всем ответам
Route::middleware(['api'])->group(function () {

// Публичные маршруты (без авторизации)
Route::prefix('v1')->group(function () {
    
    // Тест подключения
    Route::get('/test', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'API работает',
            'timestamp' => now(),
            'server' => 'Laravel ' . app()->version()
        ]);
    });
    
    // Аутентификация
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/verify-code', [AuthController::class, 'verifyCode']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/send-login-sms', [AuthController::class, 'sendLoginSms']);
    Route::post('/auth/resend-code', [AuthController::class, 'resendCode']);
    
    // Публичные данные
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/products/category/{category}', [ProductController::class, 'byCategory']);
    Route::get('/products/search', [ProductController::class, 'search']);
    Route::get('/banners', [BannerController::class, 'index']);
    
    // Простой тест POST запроса
    Route::post('/test-post', function(\Illuminate\Http\Request $request) {
        return response()->json([
            'success' => true,
            'message' => 'POST запрос работает!',
            'data' => $request->all(),
            'headers' => $request->headers->all()
        ]);
    });
    
    // Настройки приложения
    Route::get('/settings', [AdminController::class, 'getSettingsApi']);
    
});

// Защищенные маршруты (требуют авторизации)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    
    // Профиль пользователя
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/user/profile', [AuthController::class, 'getProfile']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // Корзина
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::put('/cart/update/{product}', [CartController::class, 'update']);
    Route::delete('/cart/remove/{product}', [CartController::class, 'remove']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
    
    // Заказы
    Route::post('/orders', [OrderController::class, 'store']); // Создание заказа (теперь требует авторизации)
    Route::post('/orders/create', [OrderController::class, 'store']); // Дополнительный маршрут
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::put('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    
    // Адреса пользователя
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{id}', [AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
    Route::put('/addresses/{id}/default', [AddressController::class, 'setDefault']);
    
});

// API для приложения сборщика
Route::prefix('v1/picker')->group(function () {
    // Авторизация сборщика (без middleware)
    Route::post('/login', [PickerController::class, 'login']);
    
    // Маршруты сборщика с middleware аутентификации
    Route::middleware('auth:picker')->group(function () {
        Route::get('/orders', [PickerController::class, 'getOrders']);
        Route::get('/orders/{id}', [PickerController::class, 'getOrderDetails']);
        Route::post('/orders/{id}/take', [PickerController::class, 'takeOrder']);
        Route::post('/orders/{id}/complete', [PickerController::class, 'completeOrder']);
        Route::get('/statistics', [PickerController::class, 'getStatistics']);
        
        // Тестовый роут для проверки авторизации
        Route::get('/auth-test', [App\Http\Controllers\Api\TestPickerController::class, 'checkAuth']);
    });
});

}); // Закрываем middleware группу

// Тестовый маршрут для создания заказа (для проверки интеграции)
Route::get('/api/test-order', function () {
    try {
        // Получаем или создаем тестового пользователя
        $user = \App\Models\User::firstOrCreate(
            ['phone' => '+998901234567'],
            [
                'first_name' => 'Тестовый',
                'last_name' => 'Клиент',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
            ]
        );

        // Получаем продукты
        $products = \App\Models\Product::take(3)->get();
        
        if ($products->count() == 0) {
            return response()->json(['error' => 'Нет продуктов для создания заказа']);
        }

        // Очищаем корзину и добавляем товары
        \App\Models\Cart::where('user_id', $user->id)->delete();
        
        foreach ($products as $product) {
            \App\Models\Cart::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => rand(1, 3),
            ]);
        }

        // Имитируем создание заказа через API
        $request = new \Illuminate\Http\Request([
            'delivery_address' => 'Тестовый адрес доставки, г. Ташкент, ул. Тестовая 123',
            'delivery_phone' => '+998901234567',
            'delivery_name' => 'Тестовый Клиент',
            'payment_method' => 'cash',
            'comment' => 'Тестовый заказ через API',
        ]);
        
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $orderController = new \App\Http\Controllers\Api\OrderController();
        $response = $orderController->store($request);
        
        $responseData = json_decode($response->getContent(), true);
        
        if ($responseData['success']) {
            return response()->json([
                'success' => true,
                'message' => '✅ Заказ успешно создан через API!',
                'order_number' => $responseData['order']['order_number'],
                'admin_link' => url('/admin/orders'),
                'order' => $responseData['order']
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка создания заказа',
                'error' => $responseData
            ]);
        }
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Ошибка: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Простой API endpoint для создания заказа без авторизации (только для тестов)
Route::post('/test-order-simple', function (Illuminate\Http\Request $request) {
    try {
        // Создаем заказ напрямую в базе данных
        $order = \App\Models\Order::create([
            'order_number' => 'TEST-' . now()->format('YmdHis'),
            'user_id' => null, // Заказ без пользователя (гость)
            'status' => 'pending',
            'subtotal' => 25000,
            'delivery_fee' => 5000,
            'discount' => 0,
            'total' => 30000,
            'payment_method' => $request->get('payment_method', 'cash'),
            'payment_status' => 'pending',
            'delivery_address' => $request->get('delivery_address', 'Тестовый адрес'),
            'delivery_phone' => $request->get('delivery_phone', '+998901234567'),
            'delivery_name' => $request->get('delivery_name', 'Тестовый клиент'),
            'comment' => $request->get('comment', 'Тестовый заказ напрямую в БД'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Заказ создан напрямую в БД',
            'order' => $order
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Ошибка: ' . $e->getMessage()
        ]);
    }
});
