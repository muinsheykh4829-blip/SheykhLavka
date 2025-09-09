<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Получить список всех активных товаров с пагинацией
     */
    public function index(Request $request)
    {
    $query = Product::active()->with('category');
        
        // Фильтр по категории
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Поиск по названию
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ru', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Сортировка
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Пагинация
        $perPage = $request->get('per_page', 15);
        $page = (int) $request->get('page', 1);
        $cacheEnabled = config('api_cache.enabled');
        $ttlConfig = config('api_cache.test_mode') ? config('api_cache.test_ttls') : config('api_cache.ttls');
        $ttl = $ttlConfig['products'] ?? 60;
        $cacheKey = 'api:products:index:v1:' . md5(json_encode([
            'c' => $request->get('category_id'),
            's' => $request->get('search'),
            'sb' => $sortBy,
            'so' => $sortOrder,
            'pp' => $perPage,
            'p' => $page,
        ]));

        if ($cacheEnabled) {
            [$products, $mapped] = Cache::remember($cacheKey, $ttl, function () use ($query, $perPage) {
                $p = $query->paginate($perPage);
                $mapped = $p->getCollection()->map(function ($product) {
                    return $this->formatProduct($product);
                });
                return [$p, $mapped];
            });
        } else {
            $products = $query->paginate($perPage);
            $mapped = $products->getCollection()->map(function ($product) {
                return $this->formatProduct($product);
            });
        }
        
        return response()->json([
            'success' => true,
            'data' => $mapped,
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        ]);
    }

    /**
     * Получить информацию о конкретном товаре
     */
    public function show(Product $product)
    {
        if (!$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Товар не найден'
            ], 404);
        }

    $product->load('category');
    $cacheEnabled = config('api_cache.enabled');
    $ttlConfig = config('api_cache.test_mode') ? config('api_cache.test_ttls') : config('api_cache.ttls');
    $ttl = $ttlConfig['products'] ?? 60;
    $cacheKey = 'api:product:show:' . $product->id;
        
        $data = $cacheEnabled ? Cache::remember($cacheKey, $ttl, function () use ($product) {
            return $this->formatProduct($product, true);
        }) : $this->formatProduct($product, true);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Получить товары по категории
     */
    public function byCategory(Category $category, Request $request)
    {
        if (!$category->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Категория не найдена'
            ], 404);
        }

        $query = $category->products()->active();
        
        // Сортировка
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Пагинация
        $perPage = $request->get('per_page', 15);
        $page = (int) $request->get('page', 1);
        $cacheEnabled = config('api_cache.enabled');
        $ttlConfig = config('api_cache.test_mode') ? config('api_cache.test_ttls') : config('api_cache.ttls');
        $ttl = $ttlConfig['products'] ?? 60;
        $cacheKey = 'api:products:category:v1:' . md5(json_encode([
            'cat' => $category->id,
            'sb' => $sortBy,
            'so' => $sortOrder,
            'pp' => $perPage,
            'p' => $page,
        ]));

        if ($cacheEnabled) {
            [$products, $mapped] = Cache::remember($cacheKey, $ttl, function () use ($query, $perPage) {
                $p = $query->paginate($perPage);
                $mapped = $p->getCollection()->map(function ($product) {
                    return $this->formatProduct($product);
                });
                return [$p, $mapped];
            });
        } else {
            $products = $query->paginate($perPage);
            $mapped = $products->getCollection()->map(function ($product) {
                return $this->formatProduct($product);
            });
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name_ru ?? $category->name,
                    'slug' => $category->slug,
                ],
                'products' => $mapped,
            ],
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        ]);
    }

    /**
     * Поиск товаров
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        
        if (!$search) {
            return response()->json([
                'success' => false,
                'message' => 'Поисковый запрос не может быть пустым'
            ], 400);
        }

        $query = Product::active()->with('category')
            ->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ru', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });

        $perPage = $request->get('per_page', 15);
        $page = (int) $request->get('page', 1);
        $cacheEnabled = config('api_cache.enabled');
        $ttlConfig = config('api_cache.test_mode') ? config('api_cache.test_ttls') : config('api_cache.ttls');
        $ttl = $ttlConfig['search'] ?? 60;
        $cacheKey = 'api:products:search:v1:' . md5(json_encode([
            'q' => $search,
            'pp' => $perPage,
            'p' => $page,
        ]));

        if ($cacheEnabled) {
            [$products, $mapped] = Cache::remember($cacheKey, $ttl, function () use ($query, $perPage) {
                $p = $query->paginate($perPage);
                $mapped = $p->getCollection()->map(function ($product) {
                    return $this->formatProduct($product);
                });
                return [$p, $mapped];
            });
        } else {
            $products = $query->paginate($perPage);
            $mapped = $products->getCollection()->map(function ($product) {
                return $this->formatProduct($product);
            });
        }

        return response()->json([
            'success' => true,
            'data' => $mapped,
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        ]);
    }

    /**
     * Форматирование данных товара для API
     */
    private function formatProduct(Product $product, $detailed = false)
    {
        $data = [
            'id' => $product->id,
            'name' => $product->name_ru ?? $product->name,
            'name_ru' => $product->name_ru ?? $product->name,
            'slug' => $product->slug,
            'description' => $product->description ?? '',
            'description_ru' => $product->description ?? '',
            'short_description' => $product->short_description ?? '',
            'price' => (float) $product->price,
            'discount_price' => $product->discount_price ? (float) $product->discount_price : null,
            'unit' => $product->unit ?? 'шт',
            'weight' => $product->weight,
            // Склад
            'product_type' => $product->product_type ?? 'piece',
            'stock_unit' => $product->stock_unit ?? ($product->product_type === 'weight' ? 'кг' : 'шт'),
            'stock_quantity_current' => (float) ($product->stock_quantity_current ?? 0),
            'stock_quantity_reserved' => (float) ($product->stock_quantity_reserved ?? 0),
            'stock_quantity_minimum' => (float) ($product->stock_quantity_minimum ?? 0),
            'available_stock' => (float) (($product->stock_quantity_current ?? 0) - ($product->stock_quantity_reserved ?? 0)),
            // Совместимость со старым полем
            'stock_quantity' => (float) (($product->stock_quantity_current ?? 0) - ($product->stock_quantity_reserved ?? 0)),
            'in_stock' => (($product->stock_quantity_current ?? 0) - ($product->stock_quantity_reserved ?? 0)) > 0,
            'is_active' => $product->is_active ?? true,
            'images' => $product->images ?? [],
            'category_id' => $product->category_id,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name_ru ?? $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'is_featured' => $product->is_featured ?? false,
            'created_at' => $product->created_at->toISOString(),
            'updated_at' => $product->updated_at->toISOString(),
        ];

        if ($detailed) {
            $data['sku'] = $product->sku;
            $data['barcode'] = $product->barcode;
            $data['attributes'] = $product->attributes;
        }

        return $data;
    }
}
