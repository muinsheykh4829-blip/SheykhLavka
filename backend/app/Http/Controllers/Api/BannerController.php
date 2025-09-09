<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * Получить список активных баннеров
     */
    public function index(Request $request)
    {
        $targetAudience = $request->get('target', 'all');
        $cacheEnabled = config('api_cache.enabled');
        $ttlConfig = config('api_cache.test_mode') ? config('api_cache.test_ttls') : config('api_cache.ttls');
        $ttl = $ttlConfig['banners'] ?? 60;
        $cacheKey = 'api:banners:v1:' . ($targetAudience ?? 'all');
        $banners = $cacheEnabled
            ? Cache::remember($cacheKey, $ttl, function () use ($targetAudience) {
                return Banner::getActiveForApi($targetAudience);
            })
            : Banner::getActiveForApi($targetAudience);
        
        // Увеличиваем счетчик просмотров для каждого баннера
        foreach ($banners as $banner) {
            $banner->incrementViews();
        }

        return response()->json([
            'success' => true,
            'data' => $banners->map(function($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'title_ru' => $banner->title_ru,
                    'description' => $banner->description,
                    'description_ru' => $banner->description_ru,
                    'image' => $banner->image_url,
                    'link_url' => $banner->link_url,
                    'sort_order' => $banner->sort_order,
                ];
            })
        ]);
    }

    /**
     * Регистрация клика по баннеру
     */
    public function click(Request $request, $id)
    {
        $banner = Banner::find($id);
        
        if (!$banner || !$banner->isCurrentlyActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Баннер не найден или неактивен'
            ], 404);
        }

        $banner->incrementClicks();
        
        return response()->json([
            'success' => true,
            'message' => 'Клик зарегистрирован',
            'redirect_url' => $banner->link_url
        ]);
    }
}
