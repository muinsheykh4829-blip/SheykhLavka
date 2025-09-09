<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название товара
            $table->string('name_ru')->nullable(); // Русское название
            $table->string('slug')->unique(); // URL-слаг
            $table->text('description')->nullable(); // Описание товара
            $table->text('short_description')->nullable(); // Краткое описание
            $table->decimal('price', 10, 2); // Цена товара
            $table->decimal('discount_price', 10, 2)->nullable(); // Цена со скидкой
            $table->string('unit')->default('шт'); // Единица измерения (кг, шт, л)
            $table->integer('stock_quantity')->default(0); // Количество на складе
            $table->string('sku')->unique()->nullable(); // Артикул товара
            $table->string('barcode')->unique()->nullable(); // Штрихкод
            $table->json('images')->nullable(); // Массив изображений
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); // Связь с категорией
            $table->boolean('is_active')->default(true); // Активен ли товар
            $table->boolean('is_featured')->default(false); // Рекомендуемый товар
            $table->decimal('weight', 8, 3)->nullable(); // Вес товара
            $table->json('attributes')->nullable(); // Дополнительные атрибуты (размер, цвет и т.д.)
            $table->timestamps();
            
            $table->index(['category_id', 'is_active']);
            $table->index(['is_featured', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
