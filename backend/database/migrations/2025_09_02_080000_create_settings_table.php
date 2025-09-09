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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // Ключ настройки (welcome_message, app_name, etc)
            $table->text('value')->nullable(); // Значение настройки
            $table->string('type')->default('text'); // Тип: text, image, boolean, number
            $table->string('group')->default('general'); // Группа: general, welcome, banners, etc
            $table->text('description')->nullable(); // Описание настройки
            $table->boolean('is_active')->default(true); // Активна ли настройка
            $table->timestamps();
            
            $table->index(['key', 'is_active']);
            $table->index(['group', 'is_active']);
        });

        // Добавим базовые настройки
        DB::table('settings')->insert([
            [
                'key' => 'app_name',
                'value' => 'Sheykh Lavka',
                'type' => 'text',
                'group' => 'general',
                'description' => 'Название приложения',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'welcome_message',
                'value' => 'Добро пожаловать в Sheykh Lavka!',
                'type' => 'text',
                'group' => 'welcome',
                'description' => 'Приветственное сообщение',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'welcome_subtitle',
                'value' => 'Свежие продукты с доставкой на дом',
                'type' => 'text',
                'group' => 'welcome',
                'description' => 'Подзаголовок приветствия',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'welcome_image',
                'value' => 'assets/assistant/welcome_sheykh1.jpg',
                'type' => 'image',
                'group' => 'welcome',
                'description' => 'Изображение приветствия',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'delivery_fee',
                'value' => '5000',
                'type' => 'number',
                'group' => 'delivery',
                'description' => 'Стоимость доставки (сомони)',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'min_order_amount',
                'value' => '50000',
                'type' => 'number',
                'group' => 'delivery',
                'description' => 'Минимальная сумма заказа (сомони)',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
