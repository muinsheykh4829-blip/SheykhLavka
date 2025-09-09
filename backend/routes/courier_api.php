<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CourierController;

/*
|--------------------------------------------------------------------------
| Courier API Routes
|--------------------------------------------------------------------------
*/

// Публичные маршруты курьера (без аутентификации)
Route::prefix('courier')->group(function () {
    Route::post('/login', [CourierController::class, 'login']);
});

// Защищенные маршруты курьера (требуется авторизация)
Route::middleware(['auth:sanctum'])->prefix('courier')->group(function () {
    Route::get('/orders', [CourierController::class, 'getOrders']);
    Route::post('/orders/{orderId}/take', [CourierController::class, 'takeOrder']);
    Route::post('/orders/{orderId}/complete', [CourierController::class, 'completeOrder']);
    Route::post('/logout', [CourierController::class, 'logout']);
});
