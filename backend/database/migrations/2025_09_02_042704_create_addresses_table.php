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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Пользователь
            $table->string('type')->default('home'); // Тип адреса (home, work, other)
            $table->string('title')->nullable(); // Название адреса (Дом, Работа, и т.д.)
            $table->string('street'); // Улица
            $table->string('house_number'); // Номер дома
            $table->string('apartment')->nullable(); // Квартира/офис
            $table->string('entrance')->nullable(); // Подъезд
            $table->string('floor')->nullable(); // Этаж
            $table->string('intercom')->nullable(); // Домофон
            $table->string('city')->default('Ташкент'); // Город
            $table->string('district')->nullable(); // Район
            $table->decimal('latitude', 10, 7)->nullable(); // Широта
            $table->decimal('longitude', 10, 7)->nullable(); // Долгота
            $table->text('comment')->nullable(); // Комментарий к адресу
            $table->boolean('is_default')->default(false); // Адрес по умолчанию
            $table->timestamps();
            
            $table->index(['user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
