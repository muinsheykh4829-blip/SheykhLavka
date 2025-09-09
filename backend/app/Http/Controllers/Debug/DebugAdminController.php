<?php

namespace App\Http\Controllers\Debug;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DebugAdminController extends Controller
{
    public function testCategories()
    {
        try {
            $categories = Category::withCount('products')->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Тест категорий прошел успешно',
                'categories_count' => $categories->count(),
                'categories' => $categories->map(function($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name_ru ?? $category->name,
                        'slug' => $category->slug,
                        'is_active' => $category->is_active,
                        'products_count' => $category->products_count ?? 0
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ]);
        }
    }
    
    public function testProducts()
    {
        try {
            $products = Product::with('category')->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Тест товаров прошел успешно',
                'products_count' => $products->count(),
                'products' => $products->map(function($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name_ru ?? $product->name,
                        'price' => $product->price,
                        'category' => $product->category ? $product->category->name_ru : 'Без категории',
                        'is_active' => $product->is_active
                    ];
                })->take(5)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ]);
        }
    }
    
    public function testOrders()
    {
        try {
            $orders = Order::with(['user', 'items'])->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Тест заказов прошел успешно',
                'orders_count' => $orders->count(),
                'orders' => $orders->map(function($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'total' => $order->total,
                        'user' => $order->user ? $order->user->first_name : 'Без пользователя',
                        'items_count' => $order->items->count()
                    ];
                })->take(5)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ]);
        }
    }
    
    public function testDatabase()
    {
        try {
            $stats = [
                'users' => User::count(),
                'categories' => Category::count(),
                'products' => Product::count(),
                'orders' => Order::count()
            ];
            
            // Проверим структуру таблиц
            $tables = [];
            foreach (['users', 'categories', 'products', 'orders', 'order_items'] as $table) {
                try {
                    $count = DB::table($table)->count();
                    $tables[$table] = "OK ({$count} записей)";
                } catch (\Exception $e) {
                    $tables[$table] = "Ошибка: " . $e->getMessage();
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Тест базы данных прошел успешно',
                'database_config' => [
                    'driver' => config('database.default'),
                    'database' => config('database.connections.sqlite.database')
                ],
                'statistics' => $stats,
                'tables' => $tables
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ]);
        }
    }
}
