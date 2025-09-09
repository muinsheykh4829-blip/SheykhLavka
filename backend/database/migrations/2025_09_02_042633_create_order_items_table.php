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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade'); // Заказ
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Товар
            $table->string('product_name'); // Название товара на момент заказа
            $table->string('product_sku')->nullable(); // Артикул товара на момент заказа
            $table->decimal('price', 10, 2); // Цена товара на момент заказа
            $table->decimal('discount_price', 10, 2)->nullable(); // Цена со скидкой на момент заказа
            $table->integer('quantity'); // Количество товара
            $table->decimal('total', 10, 2); // Сумма за позицию (price * quantity)
            $table->timestamps();
            
            $table->index(['order_id']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
