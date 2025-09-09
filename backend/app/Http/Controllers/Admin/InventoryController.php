<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Отображение страницы управления складом
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $categoryId = $request->get('category_id');
        $stockStatus = $request->get('stock_status');

        $query = Product::with(['category']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ru', 'like', "%{$search}%")
                  ->orWhere('name_tj', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($stockStatus) {
            switch ($stockStatus) {
                case 'in_stock':
                    $query->where('stock_quantity_current', '>', 0);
                    break;
                case 'low_stock':
                    $query->whereRaw('stock_quantity_current <= stock_quantity_minimum');
                    break;
                case 'out_of_stock':
                    $query->where('stock_quantity_current', '<=', 0);
                    break;
                case 'reserved':
                    $query->where('stock_quantity_reserved', '>', 0);
                    break;
            }
        }

        $products = $query->orderBy('name')->paginate(20);
        $stats = $this->inventoryService->getInventoryStats();

        return view('admin.inventory.index', compact('products', 'stats'));
    }

    /**
     * Показать форму редактирования товара
     */
    public function edit(Product $product)
    {
        $movements = $this->inventoryService->getProductMovementHistory($product, 20);
        
        return view('admin.inventory.edit', compact('product', 'movements'));
    }

    /**
     * Обновить складские данные товара
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'product_type' => 'required|in:piece,weight,package',
            'stock_quantity_current' => 'required|numeric|min:0',
            'stock_quantity_minimum' => 'required|numeric|min:0',
            'auto_deactivate_on_zero' => 'boolean',
            'reason' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Обновить основные поля
            $product->update([
                'product_type' => $request->product_type,
                'stock_quantity_minimum' => $request->stock_quantity_minimum,
                'auto_deactivate_on_zero' => $request->has('auto_deactivate_on_zero')
            ]);

            // Если изменилось количество на складе - создать корректировку
            if ($product->stock_quantity_current != $request->stock_quantity_current) {
                $this->inventoryService->adjustProductStock(
                    $product, 
                    $request->stock_quantity_current,
                    $request->reason ?: 'Manual adjustment from admin panel'
                );
            }

            return redirect()->route('admin.inventory.index')
                           ->with('success', 'Складские данные товара обновлены');

        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при обновлении: ' . $e->getMessage());
        }
    }

    /**
     * Пополнение склада
     */
    public function restock(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $this->inventoryService->restockProduct(
                $product, 
                $request->quantity,
                $request->reason ?: 'Stock replenishment from admin panel'
            );

            return back()->with('success', 'Склад пополнен успешно');

        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при пополнении: ' . $e->getMessage());
        }
    }

    /**
     * Массовое обновление минимальных остатков
     */
    public function updateMinimumLevels(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'updates' => 'required|array',
            'updates.*.product_id' => 'required|exists:products,id',
            'updates.*.minimum_stock' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $updates = [];
            foreach ($request->updates as $update) {
                $updates[$update['product_id']] = $update['minimum_stock'];
            }

            $this->inventoryService->updateMinimumStockLevels($updates);

            return back()->with('success', 'Минимальные остатки обновлены');

        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при обновлении: ' . $e->getMessage());
        }
    }

    /**
     * История движений товара
     */
    public function movements(Product $product)
    {
        $movements = StockMovement::where('product_id', $product->id)
                                ->orderBy('created_at', 'desc')
                                ->paginate(50);

        return view('admin.inventory.movements', compact('product', 'movements'));
    }

    /**
     * Отчет о складских остатках
     */
    public function report(Request $request)
    {
        $lowStockProducts = $this->inventoryService->getLowStockProducts();
        $outOfStockProducts = $this->inventoryService->getOutOfStockProducts();
        $stats = $this->inventoryService->getInventoryStats();

        return view('admin.inventory.report', compact(
            'lowStockProducts', 
            'outOfStockProducts', 
            'stats'
        ));
    }

    /**
     * Автоматическая деактивация товаров без остатка
     */
    public function autoDeactivate()
    {
        try {
            $count = $this->inventoryService->deactivateOutOfStockProducts();
            
            return back()->with('success', "Деактивировано {$count} товаров");

        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при деактивации: ' . $e->getMessage());
        }
    }

    /**
     * Автоматическая активация товаров при пополнении
     */
    public function autoActivate()
    {
        try {
            $count = $this->inventoryService->activateRestockedProducts();
            
            return back()->with('success', "Активировано {$count} товаров");

        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при активации: ' . $e->getMessage());
        }
    }
}
