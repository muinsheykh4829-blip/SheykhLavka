<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Cart;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Получить все заказы пользователя
     */
    public function index(Request $request)
    {
        try {
            $orders = $request->user()->orders()
                ->with(['items.product'])
                ->orderBy('created_at', 'desc')
                ->get();

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

    /**
     * Создать новый заказ
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'delivery_address' => 'required|string|max:500',
                'delivery_phone' => 'required|string|max:20',
                'delivery_name' => 'nullable|string|max:100',
                'delivery_time' => 'nullable|date_format:Y-m-d H:i:s',
                'payment_method' => 'nullable|string|in:cash,card,online',
                'comment' => 'nullable|string|max:500',
                'delivery_type' => 'nullable|string|in:standard,express', // Добавляем валидацию типа доставки
                'items' => 'nullable|array', // Добавляем поддержку товаров в запросе
                'items.*.product_id' => 'integer|exists:products,id',
                'items.*.quantity' => 'integer|min:1',
                'items.*.weight' => 'nullable|numeric|min:0.001', // Добавляем валидацию веса
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка валидации',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Получить авторизованного пользователя
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не авторизован'
                ], 401);
            }
            
            DB::beginTransaction();

            // Получить товары из корзины или из запроса
            $cartItems = collect();
            
            if ($request->has('items') && is_array($request->items)) {
                // Товары переданы в запросе
                \Log::info('Получены товары в запросе:', $request->items);
                
                foreach ($request->items as $item) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $weight = isset($item['weight']) ? $item['weight'] : null;
                        \Log::info("Товар: {$product->name}, количество: {$item['quantity']}, вес: " . ($weight ? $weight : 'null'));
                        
                        $cartItems->push((object)[
                            'product' => $product,
                            'product_id' => $product->id,
                            'quantity' => $item['quantity'],
                            'weight' => $weight
                        ]);
                    }
                }
            } else {
                // Получить товары из корзины пользователя
                $cartItems = Cart::where('user_id', $user->id)
                    ->with('product')
                    ->get();
            }

            if ($cartItems->isEmpty()) {
                // Создаем тестовый заказ с одним товаром если корзина пуста
                $product = Product::first();
                if ($product) {
                    $cartItems = collect([(object)[
                        'product' => $product,
                        'product_id' => $product->id,
                        'quantity' => 1
                    ]]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Нет товаров для заказа'
                    ], 422);
                }
            }

            // Проверить складские остатки перед созданием заказа
            $inventoryService = new InventoryService();
            $orderItemsForValidation = [];
            
            foreach ($cartItems as $cartItem) {
                $orderItemsForValidation[] = [
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity
                ];
            }
            
            $stockErrors = $inventoryService->validateOrderStock($orderItemsForValidation);
            if (!empty($stockErrors)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Недостаточно товаров на складе',
                    'errors' => $stockErrors
                ], 422);
            }

            // Рассчитать суммы
            $subtotal = 0;
            foreach ($cartItems as $cartItem) {
                if (isset($cartItem->weight) && $cartItem->weight > 0) {
                    // Для весовых товаров: цена за кг * вес в кг
                    $subtotal += $cartItem->product->price * $cartItem->weight;
                } else {
                    // Для штучных товаров: цена * количество
                    $subtotal += $cartItem->product->price * $cartItem->quantity;
                }
            }

            // Рассчитать стоимость доставки в зависимости от типа (в сомах, без копеек)
            $deliveryType = $request->delivery_type ?? 'standard';
            $deliveryFee = 0; // По умолчанию бесплатная доставка
            
            \Log::info('Создание заказа', [
                'delivery_type_from_request' => $request->delivery_type,
                'delivery_type_final' => $deliveryType,
                'subtotal' => $subtotal
            ]);
            
            if ($deliveryType === 'express') {
                // Используем сумму в сомах (ранее было 1000 как "копейки")
                $deliveryFee = 10; // 10 сом за экспресс-доставку
                \Log::info('Применена экспресс-доставка', ['delivery_fee' => $deliveryFee, 'currency_unit' => 'som']);
            } else {
                \Log::info('Применена стандартная доставка', ['delivery_fee' => $deliveryFee, 'currency_unit' => 'som']);
            }
            
            $discount = 0;
            $total = $subtotal + $deliveryFee - $discount;

            // Создать заказ
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $user->id,
                // Сразу помечаем заказ как 'accepted' (принят), минуя 'processing'
                'status' => 'accepted',
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $request->payment_method ?? 'cash',
                'payment_status' => 'pending',
                'delivery_address' => $request->delivery_address,
                'delivery_phone' => $request->delivery_phone,
                'delivery_name' => $request->delivery_name,
                'delivery_time' => $request->delivery_time,
                'delivery_type' => $deliveryType,
                'comment' => $request->comment,
            ]);

            // Создать элементы заказа и зарезервировать товары
            foreach ($cartItems as $cartItem) {
                $itemTotal = isset($cartItem->weight) && $cartItem->weight > 0
                    ? $cartItem->product->price * $cartItem->weight  // Для весовых товаров
                    : $cartItem->product->price * $cartItem->quantity; // Для штучных товаров
                
                \Log::info("Создание OrderItem: товар={$cartItem->product->name}, количество={$cartItem->quantity}, вес=" . ($cartItem->weight ?? 'null') . ", сумма={$itemTotal}");
                    
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_name' => $cartItem->product->name_ru ?? $cartItem->product->name,
                    'quantity' => $cartItem->quantity,
                    'weight' => $cartItem->weight ?? null,
                    'price' => $cartItem->product->price,
                    'total' => $itemTotal,
                ]);
                
                // Резервируем товар на складе
                $inventoryService->reserveProductStock(
                    $cartItem->product, 
                    $cartItem->quantity, 
                    $order->id
                );
            }

            // Очистить корзину
            Cart::where('user_id', $user->id)->delete();

            DB::commit();

            // Загрузить заказ с элементами
            $order->load(['items.product']);

            // Заказ остается в статусе 'accepted' и ждет принятия сборщиком вручную

            return response()->json([
                'success' => true,
                'message' => 'Заказ успешно создан',
                'order' => $order
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании заказа: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получить конкретный заказ
     */
    public function show(Request $request, $id)
    {
        try {
            $order = $request->user()->orders()
                ->with(['items.product'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'order' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Заказ не найден'
            ], 404);
        }
    }

    /**
     * Отменить заказ
     */
    public function cancel(Request $request, $id)
    {
        try {
            $order = $request->user()->orders()->findOrFail($id);

            // Можно отменить только pending, confirmed заказы
            if (!in_array($order->status, ['pending', 'confirmed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Заказ нельзя отменить'
                ], 422);
            }

            $order->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'Заказ отменен',
                'order' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при отмене заказа'
            ], 500);
        }
    }

    /**
     * Генерировать номер заказа
     */
    private function generateOrderNumber()
    {
        $prefix = 'SL'; // Sheykh Lavka
        $date = now()->format('Ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefix . $date . $random;
    }
}
