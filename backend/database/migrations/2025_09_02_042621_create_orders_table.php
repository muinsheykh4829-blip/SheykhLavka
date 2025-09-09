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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // Номер заказа
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Пользователь
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'ready', 'delivering', 'delivered', 'cancelled'])
                  ->default('pending'); // Статус заказа
            $table->decimal('subtotal', 10, 2); // Сумма товаров
            $table->decimal('delivery_fee', 8, 2)->default(0); // Стоимость доставки
            $table->decimal('discount', 8, 2)->default(0); // Размер скидки
            $table->decimal('total', 10, 2); // Итоговая сумма
            $table->string('payment_method')->default('cash'); // Способ оплаты
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending'); // Статус оплаты
            $table->text('delivery_address'); // Адрес доставки
            $table->string('delivery_phone'); // Телефон получателя
            $table->string('delivery_name')->nullable(); // Имя получателя
            $table->datetime('delivery_time')->nullable(); // Время доставки
            $table->text('comment')->nullable(); // Комментарий к заказу
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
