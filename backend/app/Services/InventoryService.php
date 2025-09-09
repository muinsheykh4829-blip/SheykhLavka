<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    /**
     * Обработка заказа - резервирование или списание товаров
     */
    public function processOrder(Order $order, bool $reserve = true)
    {
        DB::beginTransaction();
        
        try {
            foreach ($order->orderItems as $orderItem) {
                $product = $orderItem->product;
                $quantity = $orderItem->quantity;
                
                if ($reserve) {
                    // Резервируем товар
                    $this->reserveProductStock($product, $quantity, $order->id);
                } else {
                    // Списываем товар со склада
                    $this->deductProductStock($product, $quantity, $order->id);
                }
            }
            
            DB::commit();
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Резервирование товара для заказа
     */
    public function reserveProductStock(Product $product, $quantity, $orderId = null)
    {
        if (!$product->isInStock($quantity)) {
            throw new Exception("Недостаточно товара '{$product->name}' на складе. Доступно: {$product->available_stock}");
        }
        
        return $product->reserveStock($quantity, $orderId);
    }
    
    /**
     * Списание товара со склада
     */
    public function deductProductStock(Product $product, $quantity, $orderId = null)
    {
        return $product->deductStock($quantity, $orderId);
    }
    
    /**
     * Отмена заказа - освобождение зарезервированного товара
     */
    public function cancelOrder(Order $order)
    {
        DB::beginTransaction();
        
        try {
            foreach ($order->orderItems as $orderItem) {
                $product = $orderItem->product;
                $quantity = $orderItem->quantity;
                
                // Освобождаем зарезервированный товар
                $product->releaseReservedStock($quantity, $order->id);
            }
            
            DB::commit();
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Пополнение склада
     */
    public function restockProduct(Product $product, $quantity, $reason = null)
    {
        return $product->addStock($quantity, $reason);
    }
    
    /**
     * Корректировка остатков
     */
    public function adjustProductStock(Product $product, $newQuantity, $reason = null)
    {
        return $product->adjustStock($newQuantity, $reason);
    }
    
    /**
     * Проверка товаров с низким остатком
     */
    public function getLowStockProducts()
    {
        return Product::lowStock()->active()->get();
    }
    
    /**
     * Получить товары, требующие пополнения
     */
    public function getOutOfStockProducts()
    {
        return Product::where('stock_quantity_current', '<=', 0)->active()->get();
    }
    
    /**
     * Автоматическая деактивация товаров без остатка
     */
    public function deactivateOutOfStockProducts()
    {
        $products = Product::where('stock_quantity_current', '<=', 0)
                          ->where('auto_deactivate_on_zero', true)
                          ->where('is_active', true)
                          ->get();
        
        $deactivatedCount = 0;
        
        foreach ($products as $product) {
            $product->update(['is_active' => false]);
            $deactivatedCount++;
            
            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'adjustment',
                'quantity' => 0,
                'quantity_before' => $product->stock_quantity_current,
                'quantity_after' => $product->stock_quantity_current,
                'reason' => 'Auto-deactivated due to zero stock'
            ]);
        }
        
        return $deactivatedCount;
    }
    
    /**
     * Автоматическая активация товаров при пополнении
     */
    public function activateRestockedProducts()
    {
        $products = Product::where('stock_quantity_current', '>', 0)
                          ->where('auto_deactivate_on_zero', true)
                          ->where('is_active', false)
                          ->get();
        
        $activatedCount = 0;
        
        foreach ($products as $product) {
            $product->update(['is_active' => true]);
            $activatedCount++;
            
            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'adjustment',
                'quantity' => 0,
                'quantity_before' => $product->stock_quantity_current,
                'quantity_after' => $product->stock_quantity_current,
                'reason' => 'Auto-activated due to stock replenishment'
            ]);
        }
        
        return $activatedCount;
    }
    
    /**
     * Получить историю движения товара
     */
    public function getProductMovementHistory(Product $product, $limit = 50)
    {
        return StockMovement::where('product_id', $product->id)
                           ->orderBy('created_at', 'desc')
                           ->limit($limit)
                           ->get();
    }
    
    /**
     * Получить общую статистику склада
     */
    public function getInventoryStats()
    {
        return [
            'total_products' => Product::count(),
            'active_products' => Product::active()->count(),
            'in_stock_products' => Product::inStock()->count(),
            'low_stock_products' => Product::lowStock()->count(),
            'out_of_stock_products' => Product::where('stock_quantity_current', '<=', 0)->count(),
            'reserved_stock_value' => Product::sum('stock_quantity_reserved'),
            'total_stock_value' => Product::sum('stock_quantity_current')
        ];
    }
    
    /**
     * Валидация достаточности товара для заказа
     */
    public function validateOrderStock(array $orderItems)
    {
        $errors = [];
        
        foreach ($orderItems as $item) {
            $product = Product::find($item['product_id']);
            $quantity = $item['quantity'];
            
            if (!$product) {
                $errors[] = "Товар с ID {$item['product_id']} не найден";
                continue;
            }
            
            if (!$product->is_active) {
                $errors[] = "Товар '{$product->name}' недоступен";
                continue;
            }
            
            if (!$product->isInStock($quantity)) {
                $errors[] = "Недостаточно товара '{$product->name}'. Доступно: {$product->available_stock}, требуется: {$quantity}";
            }
        }
        
        return $errors;
    }
    
    /**
     * Массовое обновление минимальных остатков
     */
    public function updateMinimumStockLevels(array $productUpdates)
    {
        DB::beginTransaction();
        
        try {
        foreach ($productUpdates as $productId => $minimumStock) {
            Product::where('id', $productId)
                   ->update(['stock_quantity_minimum' => $minimumStock]);
        }            DB::commit();
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
