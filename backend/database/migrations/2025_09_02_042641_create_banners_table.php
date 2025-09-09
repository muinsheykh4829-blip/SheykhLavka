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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(); // Заголовок баннера
            $table->string('subtitle')->nullable(); // Подзаголовок
            $table->string('image'); // Путь к изображению
            $table->string('link')->nullable(); // Ссылка при нажатии на баннер
            $table->string('link_type')->default('none'); // Тип ссылки (product, category, external, none)
            $table->unsignedBigInteger('link_id')->nullable(); // ID товара/категории для внутренних ссылок
            $table->integer('sort_order')->default(0); // Порядок сортировки
            $table->boolean('is_active')->default(true); // Активен ли баннер
            $table->date('start_date')->nullable(); // Дата начала показа
            $table->date('end_date')->nullable(); // Дата окончания показа
            $table->timestamps();
            
            $table->index(['is_active', 'sort_order']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
