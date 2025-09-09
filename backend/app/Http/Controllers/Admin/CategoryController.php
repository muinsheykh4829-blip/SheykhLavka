<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('products');
        
        // Поиск по названию
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_ru', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }
        
        $categories = $query->orderBy('sort_order')->latest()->paginate(15);
        
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_ru' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories',
            'icon' => 'nullable|string|max:500',
            'icon_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $iconPath = $request->icon; // Путь из поля ввода
        
        // Если загружен файл изображения
        if ($request->hasFile('icon_file')) {
            $imageService = new ImageUploadService();
            $iconPath = $imageService->uploadCategoryImage($request->file('icon_file'));
        }

        $category = Category::create([
            'name' => $request->name_ru, // Устанавливаем name равным name_ru
            'name_ru' => $request->name_ru,
            'slug' => $request->slug,
            'icon' => $iconPath,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Категория успешно создана!');
    }

    public function show(Category $category)
    {
        // Загружаем товары категории с пагинацией
        $products = $category->products()
            ->with('category')
            ->orderBy('name_ru')
            ->paginate(20);
        
        // Статистика категории
        $stats = [
            'total_products' => $category->products()->count(),
            'active_products' => $category->products()->where('is_active', true)->count(),
            'featured_products' => $category->products()->where('is_featured', true)->count(),
            'avg_price' => $category->products()->avg('price'),
            'min_price' => $category->products()->min('price'),
            'max_price' => $category->products()->max('price'),
        ];
        
        return view('admin.categories.show', compact('category', 'products', 'stats'));
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name_ru' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $category->id,
            'icon' => 'nullable|string|max:500',
            'icon_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $iconPath = $request->icon; // Путь из поля ввода
        
        // Если загружен файл изображения
        if ($request->hasFile('icon_file')) {
            $imageService = new ImageUploadService();
            
            // Удаляем старое изображение, если оно было загружено через файл
            if ($category->icon && str_contains($category->icon, 'uploads/categories/')) {
                $imageService->deleteImage($category->icon);
            }
            
            $iconPath = $imageService->uploadCategoryImage($request->file('icon_file'));
        }

        $category->update([
            'name' => $request->name_ru, // Устанавливаем name равным name_ru
            'name_ru' => $request->name_ru,
            'slug' => $request->slug,
            'icon' => $iconPath,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Категория успешно обновлена!');
    }

    public function destroy(Category $category)
    {
        // Проверяем, есть ли товары в этой категории
        if ($category->products()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Нельзя удалить категорию, в которой есть товары!');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Категория успешно удалена!');
    }
    
    // Переключить статус категории
    public function toggleStatus(Category $category)
    {
        try {
            $category->update(['is_active' => !$category->is_active]);
            
            $status = $category->is_active ? 'активирована' : 'деактивирована';
            
            return redirect()->back()
                ->with('success', "Категория «{$category->name_ru}» успешно {$status}!");
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ошибка при изменении статуса: ' . $e->getMessage());
        }
    }
}
