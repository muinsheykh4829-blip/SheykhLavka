<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Показать список продуктов
     */
    public function index(Request $request)
    {
        $query = Product::with('category');
        
        // Поиск
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        // Фильтр по категории
        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->paginate(20);
        $categories = Category::all();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Показать форму создания продукта
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Сохранить новый продукт
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|string',
            'product_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_available' => 'boolean',
            'weight' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:20',
            // Инвентарь
            'product_type' => 'nullable|in:piece,weight,package',
            'stock_quantity_current' => 'nullable|numeric|min:0',
            'stock_quantity_minimum' => 'nullable|numeric|min:0',
            'stock_unit' => 'nullable|string|max:20',
            'auto_deactivate_on_zero' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $images = [];
        
        // Если есть текстовое поле с изображением
        if ($request->image) {
            $images[] = $request->image;
        }
        
        // Если загружены файлы изображений
        if ($request->hasFile('product_images')) {
            $imageService = new ImageUploadService();
            $uploadedImages = $imageService->uploadProductImages($request->file('product_images'));
            $images = array_merge($images, $uploadedImages);
        }

        Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'category_id' => $request->category_id,
            'images' => !empty($images) ? $images : null,
            'is_active' => $request->has('is_available'),
            'weight' => $request->weight ? (float)$request->weight : null,
            'unit' => $request->unit ?? 'шт',
            // Инвентарь
            'product_type' => $request->product_type ?: 'piece',
            'stock_quantity_current' => $request->filled('stock_quantity_current') ? (float)$request->stock_quantity_current : 0,
            'stock_quantity_minimum' => $request->filled('stock_quantity_minimum') ? (float)$request->stock_quantity_minimum : 0,
            'stock_unit' => $request->stock_unit ?: ($request->product_type === 'weight' ? 'кг' : 'шт'),
            'auto_deactivate_on_zero' => $request->boolean('auto_deactivate_on_zero', true),
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Продукт добавлен');
    }

    /**
     * Показать детали продукта
     */
    public function show(Product $product)
    {
        $product->load('category');
        return view('admin.products.show', compact('product'));
    }

    /**
     * Показать форму редактирования
     */
    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Обновить продукт
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|string',
            'product_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_available' => 'boolean',
            'weight' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:20',
            // Инвентарь
            'product_type' => 'nullable|in:piece,weight,package',
            'stock_quantity_current' => 'nullable|numeric|min:0',
            'stock_quantity_minimum' => 'nullable|numeric|min:0',
            'stock_unit' => 'nullable|string|max:20',
            'auto_deactivate_on_zero' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $images = $product->images ?? [];
        
        // Если есть текстовое поле с изображением
        if ($request->image) {
            $images[] = $request->image;
        }
        
        // Если загружены новые файлы изображений
        if ($request->hasFile('product_images')) {
            $imageService = new ImageUploadService();
            $uploadedImages = $imageService->uploadProductImages($request->file('product_images'));
            $images = array_merge($images, $uploadedImages);
        }

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'category_id' => $request->category_id,
            'images' => !empty($images) ? array_unique($images) : null,
            'is_active' => $request->has('is_available'),
            'weight' => $request->weight ? (float)$request->weight : null,
            'unit' => $request->unit ?? 'шт',
            // Инвентарь
            'product_type' => $request->product_type ?: $product->product_type,
            'stock_quantity_current' => $request->filled('stock_quantity_current') ? (float)$request->stock_quantity_current : $product->stock_quantity_current,
            'stock_quantity_minimum' => $request->filled('stock_quantity_minimum') ? (float)$request->stock_quantity_minimum : $product->stock_quantity_minimum,
            'stock_unit' => $request->stock_unit ?: $product->stock_unit,
            'auto_deactivate_on_zero' => $request->has('auto_deactivate_on_zero'),
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Продукт обновлен');
    }

    /**
     * Удалить продукт
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Продукт удален');
    }

    /**
     * API для создания продукта (для мобильного приложения)
     */
    public function storeApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|string',
            'is_available' => 'boolean',
            'weight' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:20',
            'product_type' => 'nullable|in:piece,weight,package',
            'stock_quantity_current' => 'nullable|numeric|min:0',
            'stock_quantity_minimum' => 'nullable|numeric|min:0',
            'stock_unit' => 'nullable|string|max:20',
            'auto_deactivate_on_zero' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'original_price' => $request->original_price,
                'category_id' => $request->category_id,
                'image' => $request->image,
                'is_available' => $request->get('is_available', true),
                'weight' => $request->weight,
                'unit' => $request->unit ?? 'шт',
                // inventory
                'product_type' => $request->product_type ?: 'piece',
                'stock_quantity_current' => $request->filled('stock_quantity_current') ? (float)$request->stock_quantity_current : 0,
                'stock_quantity_minimum' => $request->filled('stock_quantity_minimum') ? (float)$request->stock_quantity_minimum : 0,
                'stock_unit' => $request->stock_unit ?: ($request->product_type === 'weight' ? 'кг' : 'шт'),
                'auto_deactivate_on_zero' => $request->boolean('auto_deactivate_on_zero', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Продукт добавлен',
                'product' => $product->load('category')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании продукта'
            ], 500);
        }
    }

    /**
     * API для обновления продукта
     */
    public function updateApi(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'sometimes|required|numeric|min:0',
                'original_price' => 'nullable|numeric|min:0',
                'category_id' => 'sometimes|required|exists:categories,id',
                'image' => 'nullable|string',
                'is_available' => 'boolean',
                'weight' => 'nullable|string|max:50',
                'unit' => 'nullable|string|max:20',
                'product_type' => 'nullable|in:piece,weight,package',
                'stock_quantity_current' => 'nullable|numeric|min:0',
                'stock_quantity_minimum' => 'nullable|numeric|min:0',
                'stock_unit' => 'nullable|string|max:20',
                'auto_deactivate_on_zero' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка валидации',
                    'errors' => $validator->errors()
                ], 422);
            }

            $product->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Продукт обновлен',
                'product' => $product->load('category')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении продукта'
            ], 500);
        }
    }
}
