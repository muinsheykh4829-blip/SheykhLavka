<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\CurrencyHelper;

class BulkProductController extends Controller
{
    /**
     * Показать страницу массового импорта
     */
    public function showBulkImport()
    {
        $categories = Category::where('is_active', 1)->get();
        return view('admin.products.bulk-import', compact('categories'));
    }

    /**
     * Скачать шаблон CSV
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products_template.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Заголовки
            fputcsv($file, [
                'name',
                'description', 
                'price',
                'category',
                'unit',
                'stock_quantity',
                'weight'
            ]);
            
            // Примеры данных
            fputcsv($file, [
                'Помидоры свежие',
                'Красные спелые помидоры высшего качества',
                '12.50',
                'Овощи',
                'кг',
                '100',
                '0.5'
            ]);
            
            fputcsv($file, [
                'Молоко коровье',
                'Пастеризованное молоко 3.2% жирности',
                '8.00',
                'Молочные продукты',
                'л',
                '50',
                '1.0'
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Добавить популярные фрукты и овощи
     */
    public function addPopularFruitsVegetables()
    {
        $category = Category::firstOrCreate(
            ['slug' => 'fruits-vegetables'],
            ['name' => 'Фрукты и овощи', 'is_active' => 1]
        );

        $products = [
            ['name' => 'Помидоры', 'price' => 12.50, 'unit' => 'кг', 'weight' => 0.5],
            ['name' => 'Огурцы', 'price' => 10.00, 'unit' => 'кг', 'weight' => 0.3],
            ['name' => 'Картофель', 'price' => 8.00, 'unit' => 'кг', 'weight' => 0.2],
            ['name' => 'Морковь', 'price' => 9.00, 'unit' => 'кг', 'weight' => 0.15],
            ['name' => 'Лук репчатый', 'price' => 7.00, 'unit' => 'кг', 'weight' => 0.1],
            ['name' => 'Яблоки', 'price' => 15.00, 'unit' => 'кг', 'weight' => 0.2],
            ['name' => 'Бананы', 'price' => 18.00, 'unit' => 'кг', 'weight' => 0.15],
            ['name' => 'Апельсины', 'price' => 20.00, 'unit' => 'кг', 'weight' => 0.25],
        ];

        $added = 0;
        foreach ($products as $productData) {
            $existing = Product::where('name', $productData['name'])->first();
            if (!$existing) {
                Product::create([
                    'name' => $productData['name'],
                    'slug' => \Str::slug($productData['name']),
                    'description' => 'Свежий ' . mb_strtolower($productData['name']),
                    'price' => $productData['price'],
                    'category_id' => $category->id,
                    'unit' => $productData['unit'],
                    'stock_quantity' => 100,
                    'weight' => $productData['weight'],
                    'is_active' => 1,
                ]);
                $added++;
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', "Добавлено $added новых продуктов в категорию 'Фрукты и овощи'");
    }

    /**
     * Добавить молочные продукты
     */
    public function addDairyProducts()
    {
        $category = Category::firstOrCreate(
            ['slug' => 'dairy'],
            ['name' => 'Молочные продукты', 'is_active' => 1]
        );

        $products = [
            ['name' => 'Молоко 3.2%', 'price' => 8.00, 'unit' => 'л', 'weight' => 1.0],
            ['name' => 'Творог домашний', 'price' => 25.00, 'unit' => 'кг', 'weight' => 0.5],
            ['name' => 'Сметана 20%', 'price' => 18.00, 'unit' => 'г', 'weight' => 0.4],
            ['name' => 'Сыр российский', 'price' => 45.00, 'unit' => 'кг', 'weight' => 0.2],
            ['name' => 'Масло сливочное', 'price' => 35.00, 'unit' => 'г', 'weight' => 0.2],
            ['name' => 'Йогурт натуральный', 'price' => 12.00, 'unit' => 'шт', 'weight' => 0.125],
            ['name' => 'Кефир 2.5%', 'price' => 9.00, 'unit' => 'л', 'weight' => 1.0],
        ];

        $added = 0;
        foreach ($products as $productData) {
            $existing = Product::where('name', $productData['name'])->first();
            if (!$existing) {
                Product::create([
                    'name' => $productData['name'],
                    'slug' => \Str::slug($productData['name']),
                    'description' => 'Качественный молочный продукт',
                    'price' => $productData['price'],
                    'category_id' => $category->id,
                    'unit' => $productData['unit'],
                    'stock_quantity' => 50,
                    'weight' => $productData['weight'],
                    'is_active' => 1,
                ]);
                $added++;
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', "Добавлено $added новых молочных продуктов");
    }

    /**
     * Добавить мясные продукты
     */
    public function addMeatProducts()
    {
        $category = Category::firstOrCreate(
            ['slug' => 'meat'],
            ['name' => 'Мясные продукты', 'is_active' => 1]
        );

        $products = [
            ['name' => 'Говядина вырезка', 'price' => 80.00, 'unit' => 'кг', 'weight' => 0.5],
            ['name' => 'Свинина корейка', 'price' => 65.00, 'unit' => 'кг', 'weight' => 0.5],
            ['name' => 'Курица целая', 'price' => 25.00, 'unit' => 'кг', 'weight' => 1.5],
            ['name' => 'Фарш говяжий', 'price' => 45.00, 'unit' => 'кг', 'weight' => 0.5],
            ['name' => 'Колбаса докторская', 'price' => 35.00, 'unit' => 'кг', 'weight' => 0.3],
            ['name' => 'Сосиски молочные', 'price' => 28.00, 'unit' => 'кг', 'weight' => 0.4],
        ];

        $added = 0;
        foreach ($products as $productData) {
            $existing = Product::where('name', $productData['name'])->first();
            if (!$existing) {
                Product::create([
                    'name' => $productData['name'],
                    'slug' => \Str::slug($productData['name']),
                    'description' => 'Свежий мясной продукт высшего качества',
                    'price' => $productData['price'],
                    'category_id' => $category->id,
                    'unit' => $productData['unit'],
                    'stock_quantity' => 30,
                    'weight' => $productData['weight'],
                    'is_active' => 1,
                ]);
                $added++;
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', "Добавлено $added новых мясных продуктов");
    }
}
