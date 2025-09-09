<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Picker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class PickerController extends Controller
{
    /**
     * Авторизация сборщика
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string'
        ]);

        // Поиск сборщика по логину
        $picker = Picker::where('login', $request->login)
                       ->where('is_active', true)
                       ->first();

        if (!$picker) {
            return response()->json([
                'success' => false,
                'message' => 'Сборщик не найден'
            ], 401);
        }

        if (!$picker->checkPassword($request->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный пароль'
            ], 401);
        }

        // Создаем Sanctum токен
        $token = $picker->createToken('picker-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Успешная авторизация',
            'data' => [
                'token' => $token,
                'picker' => [
                    'id' => $picker->id,
                    'name' => $picker->name,
                    'login' => $picker->login,
                    'phone' => $picker->phone
                ]
            ]
        ]);
    }

    /**
     * Получить заказы для сборки
     */
    public function getOrders(Request $request)
    {
        try {
            $status = $request->get('status', 'accepted'); // По умолчанию принятые заказы
            $picker = $request->user(); // Текущий сборщик
            
            $orders = Order::with(['items.product', 'user', 'completedBy'])
                ->when($status === 'accepted', function($query) {
                    // Для новых заказов - показывать только те, что не назначены никому
                    return $query->where('status', 'accepted')
                                 ->whereNull('picker_id');
                })
                ->when($status === 'preparing', function($query) use ($picker) {
                    // Для заказов в работе - показывать только назначенные текущему сборщику
                    return $query->where('status', 'preparing')
                                 ->where('picker_id', $picker->id);
                })
                ->when($status === 'ready', function($query) use ($picker) {
                    // Для готовых заказов - показывать только собранные текущим сборщиком
                    return $query->where('status', 'ready')
                                 ->where('picker_id', $picker->id);
                })
                ->when($status === 'completed', function($query) use ($picker) {
                    // Для завершенных заказов - показывать только собранные текущим сборщиком
                    return $query->where('status', 'ready')
                                 ->where('picker_id', $picker->id);
                })
                ->when($status === 'history', function($query) use ($picker) {
                    // Для истории - показывать все заказы, завершенные текущим сборщиком, независимо от текущего статуса
                    return $query->where('completed_by', $picker->id);
                })
                ->when($status === 'all', function($query) use ($picker) {
                    // Для всех заказов - новые (без сборщика) + назначенные текущему сборщику
                    return $query->where(function($q) use ($picker) {
                        $q->where('status', 'accepted')->whereNull('picker_id')
                          ->orWhere('picker_id', $picker->id);
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'orders' => $orders->map(function($order) {
                    $orderData = [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'status_name' => $this->getStatusName($order->status),
                        'total' => $order->total,
                        'created_at' => $order->created_at->format('d.m.Y H:i'),
                        'delivery_address' => $order->delivery_address,
                        'delivery_phone' => $order->delivery_phone,
                        'delivery_name' => $order->delivery_name,
                        'delivery_type' => $order->delivery_type,
                        'delivery_type_name' => $this->getDeliveryTypeName($order->delivery_type),
                        'comment' => $order->comment,
                        'items_count' => $order->items->count(),
                    ];

                    // Добавляем информацию о завершении для всех заказов, которые были завершены
                    if ($order->completedBy) {
                        $orderData['completed_by'] = $order->completedBy->name;
                        $orderData['completed_at'] = $order->picking_completed_at ? 
                            $order->picking_completed_at->format('d.m.Y H:i') : null;
                    }

                    $orderData['items'] = $order->items->map(function($item) {
                        return [
                            'id' => $item->id,
                            'product_name' => $item->product_name,
                            'quantity' => $item->quantity,
                            'weight' => $item->weight,
                            'price' => $item->price,
                            'total' => $item->total,
                            'product' => $item->product ? [
                                'id' => $item->product->id,
                                'name_ru' => $item->product->name_ru,
                                'description_ru' => $item->product->description,
                                'price' => $item->product->price,
                                'category_id' => $item->product->category_id,
                                'image_url' => $item->product->image_url
                            ] : null
                        ];
                    });

                    return $orderData;
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении заказов: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Начать сборку заказа
     */
    public function startPicking(Request $request, $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            
            if ($order->status !== 'accepted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Заказ не может быть взят в работу. Текущий статус: ' . $this->getStatusName($order->status)
                ], 400);
            }

            $order->update([
                'status' => 'preparing',
                'picker_id' => $request->user()->id,
                'picking_started_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Сборка заказа начата',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'status_name' => $this->getStatusName($order->status)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при начале сборки: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Завершить сборку заказа
     */
    public function completePicking(Request $request, $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            
            if ($order->status !== 'preparing') {
                return response()->json([
                    'success' => false,
                    'message' => 'Заказ не находится в процессе сборки'
                ], 400);
            }

            $order->update([
                'status' => 'ready',
                'picking_completed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Заказ собран и готов к выдаче',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'status_name' => $this->getStatusName($order->status)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при завершении сборки: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получить детали заказа
     */
    public function getOrderDetails($orderId)
    {
        try {
            $order = Order::with(['items.product', 'user'])
                ->findOrFail($orderId);

            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'status_name' => $this->getStatusName($order->status),
                    'total' => $order->total,
                    'subtotal' => $order->subtotal,
                    'delivery_fee' => $order->delivery_fee,
                    'discount' => $order->discount,
                    'created_at' => $order->created_at->format('d.m.Y H:i'),
                    'delivery_address' => $order->delivery_address,
                    'delivery_phone' => $order->delivery_phone,
                    'delivery_name' => $order->delivery_name,
                    'comment' => $order->comment,
                    'payment_method' => $order->payment_method,
                    'items' => $order->items->map(function($item) {
                        return [
                            'id' => $item->id,
                            'product_name' => $item->product_name,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'total' => $item->total,
                            'collected' => false, // Для отметки собранности
                            'product' => $item->product ? [
                                'id' => $item->product->id,
                                'name_ru' => $item->product->name_ru,
                                'price' => $item->product->price,
                                'category_id' => $item->product->category_id,
                                'image_url' => $item->product->image_url
                            ] : null
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении деталей заказа: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Взять заказ в работу (для мобильного приложения)
     */
    public function takeOrder(Request $request, $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            
            if ($order->status !== 'accepted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Заказ не может быть взят в работу. Текущий статус: ' . $this->getStatusName($order->status)
                ], 400);
            }

            // Получаем ID сборщика из аутентифицированного пользователя
            $picker = $request->user();
            $order->update([
                'status' => 'preparing', 
                'picker_id' => $picker->id,
                'picking_started_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Заказ взят в работу',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'status_name' => $this->getStatusName($order->status)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при взятии заказа: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Завершить заказ (для мобильного приложения)
     */
    public function completeOrder(Request $request, $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            $picker = $request->user();
            
            if ($order->status !== 'preparing') {
                return response()->json([
                    'success' => false,
                    'message' => 'Заказ не находится в процессе сборки'
                ], 400);
            }

            // Проверим, что заказ принадлежит текущему сборщику
            if ($order->picker_id !== $picker->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Вы не можете завершить чужой заказ'
                ], 403);
            }

            $order->update([
                'status' => 'ready',
                'picking_completed_at' => now(),
                'completed_by' => $picker->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Заказ собран и готов к выдаче',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'status_name' => $this->getStatusName($order->status),
                    'completed_by' => $picker->name,
                    'completed_at' => $order->picking_completed_at->format('d.m.Y H:i')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при завершении заказа: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получить статистику сборщика
     */
    public function getStatistics(Request $request)
    {
        try {
            $pickerId = $request->user()->id;
            
            $stats = [
                'orders_today' => Order::where('picker_id', $pickerId)
                    ->whereDate('picking_completed_at', today())
                    ->count(),
                'orders_this_week' => Order::where('picker_id', $pickerId)
                    ->whereBetween('picking_completed_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'orders_total' => Order::where('picker_id', $pickerId)
                    ->whereNotNull('picking_completed_at')
                    ->count(),
                'current_preparing' => Order::where('picker_id', $pickerId)
                    ->where('status', 'preparing')
                    ->count(),
                'average_time' => Order::where('picker_id', $pickerId)
                    ->whereNotNull('picking_started_at')
                    ->whereNotNull('picking_completed_at')
                    ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, picking_started_at, picking_completed_at)) as avg_time')
                    ->first()->avg_time ?? 0
            ];

            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении статистики: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getStatusName($status)
    {
        $names = [
            'processing' => 'В обработке',
            'accepted' => 'Принят',
            'preparing' => 'Собирается',
            'ready' => 'Собран',
            'delivering' => 'Курьер в пути',
            'delivered' => 'Доставлен',
            'completed' => 'Завершен',
            'cancelled' => 'Отменен'
        ];

        return $names[$status] ?? $status;
    }

    private function getDeliveryTypeName($deliveryType)
    {
        $names = [
            'standard' => 'Стандарт (бесплатно)',
            'express' => 'Экспресс (10 сом)'
        ];

        return $names[$deliveryType] ?? $deliveryType;
    }
}
