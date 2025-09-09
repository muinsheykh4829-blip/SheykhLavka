<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Courier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourierController extends Controller
{
    /**
     * Авторизация курьера
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string'
        ]);

        // Поиск курьера по логину
        $courier = Courier::where('login', $request->login)
                         ->where('is_active', true)
                         ->first();

        if (!$courier) {
            return response()->json([
                'success' => false,
                'message' => 'Курьер не найден'
            ], 401);
        }

        if (!$courier->checkPassword($request->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный пароль'
            ], 401);
        }

        // Создаем Sanctum токен
        $token = $courier->createToken('courier-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Успешная авторизация',
            'data' => [
                'token' => $token,
                'courier' => [
                    'id' => $courier->id,
                    'name' => $courier->name,
                    'login' => $courier->login,
                    'phone' => $courier->phone
                ]
            ]
        ]);
    }

    /**
     * Получить заказы для курьера
     */
    public function getOrders(Request $request)
    {
        try {
            $user = $request->user(); // Получаем текущего пользователя из токена
            
            // Проверяем, что это курьер
            if (!($user instanceof \App\Models\Courier)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен. Требуется авторизация курьера.'
                ], 403);
            }
            
            $status = $request->get('status', 'ready'); // По умолчанию готовые к доставке заказы
            
            $orders = Order::with(['items.product', 'user'])
                ->when($status === 'ready', function($query) {
                    // Готовые к доставке заказы (не назначенные никому курьеру)
                    return $query->where('status', 'ready')
                                 ->whereNull('courier_id');
                })
                ->when($status === 'delivering', function($query) use ($user) {
                    // Заказы в доставке текущим курьером
                    return $query->whereIn('status', ['delivering', 'in_delivery'])
                                 ->where('courier_id', $user->id);
                })
                ->when($status === 'delivered', function($query) use ($user) {
                    // Доставленные заказы текущим курьером
                    return $query->where('status', 'delivered')
                                 ->where('delivered_by', $user->id);
                })
                ->when($status === 'history', function($query) use ($user) {
                    // История всех заказов курьера (и доставленные, и отмененные)
                    return $query->whereIn('status', ['delivered', 'cancelled'])
                                 ->where('delivered_by', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            // Форматируем данные заказов
            $formattedOrders = $orders->map(function ($order) use ($user) {
                // Формируем структурированный адрес
                $addressData = null;
                if ($order->address) {
                    $addressData = [
                        'address' => trim(collect([
                            $order->address->city,
                            $order->address->street
                        ])->filter()->implode(', ')),
                        'house' => $order->address->house_number,
                        'entrance' => $order->address->entrance,
                        'floor' => $order->address->floor,
                        'apartment' => $order->address->apartment,
                        'comment' => $order->address->comment,
                    ];
                } else {
                    // Парсим delivery_address строку (дом, подъезд, этаж, кв)
                    $raw = $order->delivery_address;
                    if ($raw) {
                        $addressData = ['address' => $raw];
                        if (preg_match('/дом\s+([\w\-]+)/iu', $raw, $m)) {
                            $addressData['house'] = $m[1];
                        }
                        if (preg_match('/подъезд\s+([\w\-]+)/iu', $raw, $m)) {
                            $addressData['entrance'] = $m[1];
                        }
                        if (preg_match('/этаж\s+([\w\-]+)/iu', $raw, $m)) {
                            $addressData['floor'] = $m[1];
                        }
                        if (preg_match('/кв\.?\s*([\w\-]+)/iu', $raw, $m)) {
                            $addressData['apartment'] = $m[1];
                        }
                    }
                }
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total' => $order->total,
                    'created_at' => $order->created_at,
                    'customer' => [
                        'name' => $order->user->name ?? 'Неизвестно',
                        'phone' => $order->user->phone ?? '',
                    ],
                    'address' => $addressData,
                    'items' => $order->items->map(function ($item) {
                        return [
                            'product_name' => $item->product->name ?? 'Неизвестный товар',
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'unit' => $item->product->unit ?? 'pc', // Добавляем единицы измерения
                        ];
                    }),
                    'completion_info' => ($order->status === 'delivered' && $order->delivered_by) ? [
                        'completed_by' => $order->delivered_by, // ID курьера, который доставил
                        'completed_at' => $order->delivered_at, // Время доставки
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'orders' => $formattedOrders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении заказов: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Взять заказ в доставку
     */
    public function takeOrder(Request $request, $orderId)
    {
        try {
            $user = $request->user();
            
            // Проверяем, что это курьер
            if (!($user instanceof \App\Models\Courier)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен. Требуется авторизация курьера.'
                ], 403);
            }
            
            $order = Order::find($orderId);
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Заказ не найден'
                ], 404);
            }
            
            if ($order->status !== 'ready') {
                return response()->json([
                    'success' => false,
                    'message' => 'Заказ недоступен для доставки'
                ], 400);
            }
            
            if ($order->courier_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Заказ уже взят другим курьером'
                ], 400);
            }
            
            // Назначаем курьера и меняем статус
            $order->update([
                'courier_id' => $user->id,
                'status' => 'delivering'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Заказ взят в доставку'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при взятии заказа: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Завершить доставку заказа
     */
    public function completeOrder(Request $request, $orderId)
    {
        try {
            $user = $request->user();
            
            // Проверяем, что это курьер
            if (!($user instanceof \App\Models\Courier)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен. Требуется авторизация курьера.'
                ], 403);
            }
            
            $order = Order::find($orderId);
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Заказ не найден'
                ], 404);
            }
            
            if ($order->courier_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Это не ваш заказ'
                ], 403);
            }
            
            if ($order->status !== 'delivering') {
                return response()->json([
                    'success' => false,
                    'message' => 'Заказ не находится в доставке'
                ], 400);
            }
            
            // Завершаем доставку
            $order->update([
                'status' => 'delivered',
                'delivered_by' => $user->id,
                'delivered_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Заказ доставлен'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при завершении доставки: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Выход из аккаунта
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Успешный выход'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при выходе'
            ], 500);
        }
    }
}
