<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = Banner::ordered()->paginate(10);
        return view('admin.banners.index', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.banners.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'title_ru' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ru' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'link_url' => 'nullable|url',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'target_audience' => 'in:all,mobile,desktop'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        
        // Обработка загрузки изображения
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('banners', 'public');
            $data['image'] = $imagePath;
        }

        // Установка значений по умолчанию
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->has('is_active');
        $data['target_audience'] = $data['target_audience'] ?? 'all';

        Banner::create($data);

        return redirect()->route('admin.banners.index')
            ->with('success', 'Баннер успешно создан!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Banner $banner)
    {
        return view('admin.banners.show', compact('banner'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Banner $banner)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'title_ru' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ru' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'link_url' => 'nullable|url',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'target_audience' => 'in:all,mobile,desktop'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        
        // Обработка загрузки нового изображения
        if ($request->hasFile('image')) {
            // Удаляем старое изображение
            if ($banner->image && Storage::disk('public')->exists($banner->image)) {
                Storage::disk('public')->delete($banner->image);
            }
            
            $image = $request->file('image');
            $imagePath = $image->store('banners', 'public');
            $data['image'] = $imagePath;
        }

        // Установка значений по умолчанию
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->has('is_active');
        $data['target_audience'] = $data['target_audience'] ?? 'all';

        $banner->update($data);

        return redirect()->route('admin.banners.index')
            ->with('success', 'Баннер успешно обновлен!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner)
    {
        // Удаляем изображение
        if ($banner->image && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();

        return redirect()->route('admin.banners.index')
            ->with('success', 'Баннер успешно удален!');
    }

    /**
     * API метод для получения баннеров
     */
    public function apiIndex(Request $request)
    {
        $targetAudience = $request->get('target', 'all');
        $banners = Banner::getActiveForApi($targetAudience);
        
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
     * API метод для клика по баннеру
     */
    public function apiClick(Request $request, Banner $banner)
    {
        $banner->incrementClicks();
        
        return response()->json([
            'success' => true,
            'message' => 'Клик зарегистрирован'
        ]);
    }

    /**
     * Переключение активности баннера
     */
    public function toggleActive(Banner $banner)
    {
        $banner->update(['is_active' => !$banner->is_active]);
        
        $status = $banner->is_active ? 'активирован' : 'деактивирован';
        return redirect()->back()->with('success', "Баннер {$status}!");
    }
}
