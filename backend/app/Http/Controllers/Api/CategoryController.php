<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Получить список всех активных категорий
     */
    public function index()
    {
        try {
            $cacheEnabled = config('api_cache.enabled');
            $ttlConfig = config('api_cache.test_mode') ? config('api_cache.test_ttls') : config('api_cache.ttls');
            $ttl = $ttlConfig['categories'] ?? 60;
            $cacheKey = 'api:categories:v1';

            $categories = $cacheEnabled
                ? Cache::remember($cacheKey, $ttl, function () {
                    return Category::where('is_active', 1)
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->get()
                        ->map(function ($category) {
                            return [
                                'id' => $category->id,
                                'name' => $category->name ?? '',
                                'name_ru' => $category->name_ru ?? $category->name ?? '',
                                'slug' => $category->slug ?? '',
                                'icon' => $category->icon ?? '',
                                'description' => $category->description ?? '',
                                'sort_order' => $category->sort_order ?? 0,
                                'is_active' => $category->is_active ?? true,
                                'products_count' => 0,
                            ];
                        });
                })
                : Category::where('is_active', 1)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get()
                    ->map(function ($category) {
                        return [
                            'id' => $category->id,
                            'name' => $category->name ?? '',
                            'name_ru' => $category->name_ru ?? $category->name ?? '',
                            'slug' => $category->slug ?? '',
                            'icon' => $category->icon ?? '',
                            'description' => $category->description ?? '',
                            'sort_order' => $category->sort_order ?? 0,
                            'is_active' => $category->is_active ?? true,
                            'products_count' => 0,
                        ];
                    });

            return response()->json([
                'success' => true,
                'data' => $categories->toArray() // Гарантируем массив
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка загрузки категорий',
                'data' => [], // Всегда возвращаем пустой массив при ошибке
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получить информацию о конкретной категории
     */
    public function show(Category $category)
    {
        if (!$category->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Категория не найдена'
            ], 404);
        }
        $cacheEnabled = config('api_cache.enabled');
        $ttlConfig = config('api_cache.test_mode') ? config('api_cache.test_ttls') : config('api_cache.ttls');
        $ttl = $ttlConfig['categories'] ?? 60;
        $cacheKey = 'api:category:'. $category->id;

        $data = $cacheEnabled ? Cache::remember($cacheKey, $ttl, function () use ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name_ru ?? $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'description' => $category->description,
                'products_count' => $category->products()->active()->count(),
            ];
        }) : [
            'id' => $category->id,
            'name' => $category->name_ru ?? $category->name,
            'slug' => $category->slug,
            'icon' => $category->icon,
            'description' => $category->description,
            'products_count' => $category->products()->active()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
