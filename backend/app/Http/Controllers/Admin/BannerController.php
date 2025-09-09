<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    /**
     * Список всех баннеров
     */
    public function index(Request $request)
    {
        $query = Banner::query();

        // Фильтрация
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->has('target') && $request->target !== 'all') {
            $query->where('target_audience', $request->target);
        }

        $banners = $query->orderBy('sort_order')
                         ->orderBy('created_at', 'desc')
                         ->paginate(20);

        return view('admin.banners.index', compact('banners'));
    }

    /**
     * Форма создания баннера
     */
    public function create()
    {
        return view('admin.banners.create');
    }

    /**
     * Сохранение нового баннера
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $banner = new Banner();
        
        // Устанавливаем значения по умолчанию
        $banner->title = 'Banner ' . time(); // Генерируем простое название
        $banner->title_ru = 'Баннер ' . time();
        $banner->sort_order = $request->sort_order;
        $banner->is_active = $request->has('is_active');
        $banner->target_audience = 'all'; // По умолчанию для всех

        // Загрузка изображения
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/banners'), $filename);
            $banner->image = 'uploads/banners/' . $filename;
        }

        $banner->save();

        return redirect()->route('admin.banners.index')
                        ->with('success', 'Баннер успешно создан');
    }

    /**
     * Просмотр баннера
     */
    public function show(Banner $banner)
    {
        return view('admin.banners.show', compact('banner'));
    }

    /**
     * Форма редактирования
     */
    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    /**
     * Обновление баннера
     */
    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Обновляем основные поля
        $banner->sort_order = $request->sort_order;
        $banner->is_active = $request->has('is_active');

        // Загрузка нового изображения
        if ($request->hasFile('image')) {
            // Удаляем старое изображение
            if ($banner->image && file_exists(public_path($banner->image))) {
                unlink(public_path($banner->image));
            }

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/banners'), $filename);
            $banner->image = 'uploads/banners/' . $filename;
        }

        $banner->save();

        return redirect()->route('admin.banners.index')
                        ->with('success', 'Баннер успешно обновлен');
    }

    /**
     * Удаление баннера
     */
    public function destroy(Banner $banner)
    {
        // Удаляем изображение
        if ($banner->image && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();

        return redirect()->route('admin.banners.index')
                        ->with('success', 'Баннер успешно удален');
    }

    /**
     * Переключение статуса активности
     */
    public function toggleActive(Banner $banner)
    {
        $banner->is_active = !$banner->is_active;
        $banner->save();

        $status = $banner->is_active ? 'активирован' : 'деактивирован';

        return response()->json([
            'success' => true,
            'message' => "Баннер {$status}",
            'is_active' => $banner->is_active
        ]);
    }

    /**
     * Статистика по баннерам
     */
    public function statistics()
    {
        $stats = [
            'total' => Banner::count(),
            'active' => Banner::where('is_active', true)->count(),
            'inactive' => Banner::where('is_active', false)->count(),
            'total_views' => Banner::sum('view_count'),
            'total_clicks' => Banner::sum('click_count'),
            'top_banners' => Banner::orderBy('click_count', 'desc')
                                  ->limit(10)
                                  ->get(['id', 'title', 'view_count', 'click_count'])
        ];

        return view('admin.banners.statistics', compact('stats'));
    }
}
