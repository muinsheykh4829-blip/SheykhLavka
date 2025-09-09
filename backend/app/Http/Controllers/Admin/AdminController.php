<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Показать главную страницу админки
     */
    public function index()
    {
        try {
            $stats = [
                'users_count' => User::count(),
                'products_count' => Product::count(),
                'categories_count' => Category::count(),
                'orders_count' => Order::count(),
                'orders_today' => Order::whereDate('created_at', today())->count(),
                'total_revenue' => Order::where('status', 'delivered')->sum('total'),
                
                // Подробная статистика по статусам заказов
                'pending_orders' => Order::where('status', 'pending')->count(),
                'confirmed_orders' => Order::where('status', 'confirmed')->count(),
                'preparing_orders' => Order::where('status', 'preparing')->count(),
                'ready_orders' => Order::where('status', 'ready')->count(),
                'delivering_orders' => Order::where('status', 'delivering')->count(),
                'delivered_orders' => Order::where('status', 'delivered')->count(),
                'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            ];

            // Получаем последние заказы для отображения
            $recent_orders = Order::with(['user'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

        } catch (\Exception $e) {
            $stats = [
                'users_count' => 0,
                'products_count' => 0,
                'categories_count' => 0,
                'orders_count' => 0,
                'orders_today' => 0,
                'total_revenue' => 0,
                'pending_orders' => 0,
                'confirmed_orders' => 0,
                'preparing_orders' => 0,
                'ready_orders' => 0,
                'delivering_orders' => 0,
                'delivered_orders' => 0,
                'cancelled_orders' => 0,
            ];
            $recent_orders = collect([]);
        }

        return view('admin.dashboard', compact('stats', 'recent_orders'));
    }

    /**
     * API для получения настроек (для мобильного приложения)
     */
    public function getSettingsApi(Request $request)
    {
        $group = $request->get('group', 'all');
        
        if ($group === 'all') {
            $settings = Setting::where('is_active', true)->get();
        } else {
            $settings = Setting::getByGroup($group);
        }

        return response()->json([
            'success' => true,
            'settings' => $settings->pluck('value', 'key'),
        ]);
    }

    /**
     * Загрузка файла
     */
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type' => 'required|in:banner,welcome,product,category'
        ]);

        $file = $request->file('file');
        $type = $request->get('type');
        
        // Создаем папку если не существует
        $uploadPath = public_path("assets/{$type}");
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Генерируем имя файла
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($uploadPath, $fileName);

        $filePath = "assets/{$type}/{$fileName}";

        return response()->json([
            'success' => true,
            'file_path' => $filePath,
            'message' => 'Файл загружен успешно'
        ]);
    }
}
