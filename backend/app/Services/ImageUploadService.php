<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadService
{
    /**
     * Загрузить изображение для категории
     */
    public function uploadCategoryImage(UploadedFile $file): string
    {
        // Генерируем уникальное имя файла
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = 'uploads/categories/' . $filename;
        
        // Проверяем тип файла
        if (!in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            throw new \InvalidArgumentException('Неподдерживаемый формат файла');
        }
        
        // Перемещаем файл в папку public/uploads/categories
        $file->move(public_path('uploads/categories'), $filename);
        
        return $path;
    }
    
    /**
     * Загрузить изображения для продукта
     */
    public function uploadProductImages(array $files): array
    {
        $paths = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $path = 'uploads/products/' . $filename;
                
                // Проверяем тип файла
                if (!in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    continue; // Пропускаем неподдерживаемые файлы
                }
                
                // Перемещаем файл в папку public/uploads/products
                $file->move(public_path('uploads/products'), $filename);
                
                $paths[] = $path;
            }
        }
        
        return $paths;
    }
    
    /**
     * Удалить изображение
     */
    public function deleteImage(string $path): bool
    {
        $fullPath = public_path($path);
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }
    
    /**
     * Получить URL изображения
     */
    public function getImageUrl(string $path): string
    {
        if (Str::startsWith($path, 'http')) {
            return $path; // Уже полный URL
        }
        
        return url($path);
    }
}
