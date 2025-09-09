<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Показать все заказы
     */
    public function index(Request $request)
    {
        try {
            $query = Order::with(['user', 'items.product']);

            // Фильтр по статусу
            if ($request->has('status') && $request->status != '') {
                $query->where('status', $request->status);
            }

            // Поиск по номеру заказа, имени пользователя или телефону
            if ($request->has('search') && $request->search != '') {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('delivery_name', 'like', "%{$search}%")
                      ->orWhere('delivery_phone', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('first_name', 'like', "%{$search}%")
                                   ->orWhere('last_name', 'like', "%{$search}%")
                                   ->orWhere('phone', 'like', "%{$search}%")
                                   ->orWhere('name', 'like', "%{$search}%");
                      });
                });
            }

            // Фильтр по дате
            if ($request->has('date_from') && $request->date_from != '') {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->has('date_to') && $request->date_to != '') {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Сортировка
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $allowedSortFields = ['created_at', 'order_number', 'total', 'status'];
            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'created_at';
            }
            
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'desc';
            }
            
            $query->orderBy($sortBy, $sortOrder);

            $orders = $query->paginate(20)->withQueryString();

            // Добавляем статистику для отображения
            $statistics = [
                'total_orders' => Order::count(),
                'processing_orders' => Order::where('status', 'processing')->count(),
                'accepted_orders' => Order::where('status', 'accepted')->count(),
                'preparing_orders' => Order::where('status', 'preparing')->count(),
                'ready_orders' => Order::where('status', 'ready')->count(),
                'delivering_orders' => Order::where('status', 'delivering')->count(),
                'delivered_orders' => Order::where('status', 'delivered')->count(),
                'cancelled_orders' => Order::where('status', 'cancelled')->count(),
                'today_orders' => Order::whereDate('created_at', today())->count(),
                'total_revenue' => Order::where('status', 'delivered')->sum('total'),
                'today_revenue' => Order::where('status', 'delivered')
                                       ->whereDate('created_at', today())
                                       ->sum('total'),
            ];

            return view('admin.orders.index-simple', compact('orders', 'statistics'));
            
        } catch (\Exception $e) {
            return response("Ошибка загрузки заказов: " . $e->getMessage(), 500);
        }
    }

    /**
     * Показать детали заказа
     */
    public function show(Order $order)
    {
        $order->load(['user', 'items.product']);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Обновить статус заказа
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:processing,accepted,preparing,ready,delivering,delivered,cancelled'
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        $order->update(['status' => $newStatus]);

        // Создаем сообщение с учетом смены статуса
        $statusMessages = [
            'processing' => 'Заказ переведен в статус "В обработке"',
            'accepted' => 'Заказ принят',
            'preparing' => 'Заказ собирается',
            'ready' => 'Заказ собран',
            'delivering' => 'Заказ передан курьеру',
            'delivered' => 'Заказ успешно завершен',
            'cancelled' => 'Заказ отменен'
        ];

        $message = $statusMessages[$newStatus] ?? 'Статус заказа обновлен';
        
        return redirect()->back()->with('success', $message);
    }

    /**
     * Получить статистику заказов для API
     */
    public function statistics()
    {
        $stats = [
            'total_orders' => Order::count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'accepted_orders' => Order::where('status', 'accepted')->count(),
            'preparing_orders' => Order::where('status', 'preparing')->count(),
            'ready_orders' => Order::where('status', 'ready')->count(),
            'delivering_orders' => Order::where('status', 'delivering')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'total_revenue' => Order::where('status', 'delivered')->sum('total'),
            'today_revenue' => Order::where('status', 'delivered')
                                   ->whereDate('created_at', today())
                                   ->sum('total'),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $stats
        ]);
    }

    /**
     * API для обновления статуса заказа
     */
    public function updateStatusApi(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            
            $request->validate([
                'status' => 'required|in:processing,accepted,preparing,ready,delivering,delivered,cancelled'
            ]);

            $order->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Статус заказа обновлен',
                'order' => $order->load(['user', 'items.product'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении статуса'
            ], 500);
        }
    }

    /**
     * Получить заказы для мобильного приложения (админ)
     */
    public function getOrdersApi(Request $request)
    {
        try {
            $query = Order::with(['user', 'items.product']);

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('limit')) {
                $orders = $query->orderBy('created_at', 'desc')
                               ->limit($request->limit)
                               ->get();
            } else {
                $orders = $query->orderBy('created_at', 'desc')->get();
            }

            return response()->json([
                'success' => true,
                'orders' => $orders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении заказов'
            ], 500);
        }
    }
}
