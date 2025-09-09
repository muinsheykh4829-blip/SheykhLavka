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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['incoming', 'outgoing', 'adjustment', 'reserved', 'unreserved']);
            $table->decimal('quantity', 10, 3);
            $table->decimal('quantity_before', 10, 3);
            $table->decimal('quantity_after', 10, 3);
            $table->string('reason')->nullable(); // Причина движения
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->string('reference_type')->nullable(); // Тип связанной записи
            $table->unsignedBigInteger('reference_id')->nullable(); // ID связанной записи
            $table->json('metadata')->nullable(); // Дополнительные данные
            $table->string('created_by')->nullable(); // Кто создал движение
            $table->timestamps();
            
            $table->index(['product_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
